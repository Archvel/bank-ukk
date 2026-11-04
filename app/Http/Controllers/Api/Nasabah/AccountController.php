<?php

namespace App\Http\Controllers\Api\Nasabah;

use App\Actions\GetTellerSessionStatusAction;
use App\Actions\WithdrawAction;
use App\Enums\SaverStatus;
use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Resources\MemberResource;
use App\Http\Resources\SaverResource;
use App\Http\Resources\TransactionResource;
use App\Http\Resources\UserResource;
use App\Models\Saver;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class AccountController extends Controller
{
    /**
     * Get authenticated saver's detailed profile, student info, and passbook metadata.
     */
    public function profile(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user()->load(['member', 'saver']);
        $saver = $user->saver;

        $isActiveSaver = $saver !== null && $saver->status === SaverStatus::Active;

        return response()->json([
            'user' => new UserResource($user),
            'member' => $user->member ? new MemberResource($user->member) : null,
            'is_active_saver' => $isActiveSaver,
            'saver' => $saver ? new SaverResource($saver) : null,
            'summary' => $saver ? [
                'account_number' => $saver->account_number,
                'student_name' => $user->member?->student_name ?? $user->name,
                'balance' => (int) $saver->balance,
                'formatted_balance' => 'Rp'.number_format((int) $saver->balance, 0, ',', '.'),
                'withdrawable_balance' => max(0, (int) $saver->balance - WithdrawAction::MINIMUM_MAINTAINING_BALANCE),
                'formatted_withdrawable_balance' => 'Rp'.number_format(max(0, (int) $saver->balance - WithdrawAction::MINIMUM_MAINTAINING_BALANCE), 0, ',', '.'),
                'minimum_maintaining_balance' => WithdrawAction::MINIMUM_MAINTAINING_BALANCE,
                'qr_code_token' => $saver->qr_code_token,
                'status' => $saver->status?->value ?? (string) $saver->status,
                'opened_at' => $saver->opened_at?->toISOString(),
            ] : null,
        ]);
    }

    /**
     * Get authenticated saver's balance and account status.
     */
    public function balance(Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var Saver|null $saver */
        $saver = Saver::where('user_id', $user->id)->first();

        if (! $saver) {
            return response()->json([
                'message' => 'Anda belum memiliki rekening penabung aktif. Silakan lakukan setoran awal di loket Teller.',
            ], Response::HTTP_NOT_FOUND);
        }

        $withdrawable = max(0, (int) $saver->balance - WithdrawAction::MINIMUM_MAINTAINING_BALANCE);

        return response()->json([
            'saver' => new SaverResource($saver->load(['user', 'member'])),
            'balance' => (int) $saver->balance,
            'formatted_balance' => 'Rp'.number_format((int) $saver->balance, 0, ',', '.'),
            'withdrawable_balance' => $withdrawable,
            'formatted_withdrawable_balance' => 'Rp'.number_format($withdrawable, 0, ',', '.'),
            'minimum_maintaining_balance' => WithdrawAction::MINIMUM_MAINTAINING_BALANCE,
            'account_number' => $saver->account_number,
            'qr_code_token' => $saver->qr_code_token,
        ]);
    }

    /**
     * Get transaction mutations for the authenticated saver (Digital Passbook).
     */
    public function mutations(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $user = $request->user();
        /** @var Saver|null $saver */
        $saver = Saver::where('user_id', $user->id)->first();

        if (! $saver) {
            return response()->json([
                'message' => 'Rekening penabung tidak ditemukan.',
            ], Response::HTTP_NOT_FOUND);
        }

        $query = Transaction::with('teller')->where('saver_id', $saver->id)->latest('id');

        if ($request->filled('type') && in_array($request->string('type')->toString(), ['deposit', 'withdrawal'], true)) {
            $query->where('type', $request->string('type')->toString());
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->string('start_date')->toString())->startOfDay();
            $endDate = Carbon::parse($request->string('end_date')->toString())->endOfDay();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $perPage = min(max((int) $request->integer('per_page', 15), 1), 100);
        $paginated = $query->paginate($perPage);

        $totalDeposit = (int) Transaction::where('saver_id', $saver->id)
            ->where('type', TransactionType::Deposit)
            ->sum('amount');
        $totalWithdrawal = (int) Transaction::where('saver_id', $saver->id)
            ->where('type', TransactionType::Withdrawal)
            ->sum('amount');

        return TransactionResource::collection($paginated)->additional([
            'summary' => [
                'account_number' => $saver->account_number,
                'current_balance' => (int) $saver->balance,
                'formatted_current_balance' => 'Rp'.number_format((int) $saver->balance, 0, ',', '.'),
                'total_deposit' => $totalDeposit,
                'formatted_total_deposit' => 'Rp'.number_format($totalDeposit, 0, ',', '.'),
                'total_withdrawal' => $totalWithdrawal,
                'formatted_total_withdrawal' => 'Rp'.number_format($totalWithdrawal, 0, ',', '.'),
            ],
        ]);
    }

    /**
     * Get real-time counter and teller session operational status for nasabah.
     */
    public function counterStatus(GetTellerSessionStatusAction $action): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $action->handle(),
        ]);
    }
}
