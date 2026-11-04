<?php

namespace App\Http\Controllers\Teller;

use App\Actions\CloseCashAction;
use App\Actions\DepositAction;
use App\Actions\SetOpeningCashAction;
use App\Actions\WithdrawAction;
use App\Enums\MemberAccountStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\DepositRequest;
use App\Http\Requests\WithdrawRequest;
use App\Models\Member;
use App\Models\Saver;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class CounterController extends Controller
{
    /**
     * Display the POS Teller Counter (Meja Layanan Loket).
     */
    public function index(Request $request, CloseCashAction $closeCashAction): View
    {
        $teller = $request->user() ?: Auth::user();

        // Calculate real-time drawer balance
        $preview = $closeCashAction->preview($teller);
        $drawerBalance = (int) $preview['system_ending_balance'];
        $openingBalance = (int) $preview['opening_balance'];
        $hasOpenedSession = (bool) ($preview['has_session_opened'] ?? false);
        $isWithinOperatingHours = app(SetOpeningCashAction::class)->isWithinOperatingHours();
        $operatingHoursLabel = '07:00 - 15:30 WIB';
        $todayTrxCount = Transaction::where('teller_id', $teller->id)->whereDate('created_at', today())->count();

        $minMaintainingBalance = WithdrawAction::MINIMUM_MAINTAINING_BALANCE;
        $minInitialDeposit = 10000;

        // Recent 5 transactions by this teller for quick reference
        $recentTransactions = Transaction::with(['saver.user', 'teller'])
            ->where('teller_id', $teller->id)
            ->whereDate('created_at', today())
            ->latest('id')
            ->take(5)
            ->get();

        // Inactive candidate members ready for initial deposit & account activation
        $pendingMembers = Member::with('user')
            ->where('account_status', MemberAccountStatus::Inactive)
            ->latest('id')
            ->take(15)
            ->get();

        // Active savers
        $activeSavers = Saver::with(['user', 'member'])
            ->where('status', 'active')
            ->latest('id')
            ->take(15)
            ->get();

        return view('teller.counter.index', compact(
            'drawerBalance',
            'openingBalance',
            'hasOpenedSession',
            'isWithinOperatingHours',
            'operatingHoursLabel',
            'todayTrxCount',
            'minMaintainingBalance',
            'minInitialDeposit',
            'recentTransactions',
            'pendingMembers',
            'activeSavers'
        ));
    }

    /**
     * Lookup student saver or inactive candidate member.
     */
    public function lookup(Request $request): JsonResponse
    {
        $code = $request->string('code')->trim()->toString();

        if (empty($code)) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter nomor rekening, QR code, atau nama wajib diisi.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // 1. Try to find active or registered Saver first
        /** @var Saver|null $saver */
        $saver = Saver::with(['user', 'member'])
            ->where('account_number', $code)
            ->orWhere('qr_code_token', $code)
            ->orWhereHas('user', function ($q) use ($code) {
                $q->where('name', 'like', "%{$code}%");
            })
            ->orWhereHas('member', function ($q) use ($code) {
                $q->where('student_name', 'like', "%{$code}%");
            })
            ->first();

        if ($saver) {
            $minMaintainingBalance = WithdrawAction::MINIMUM_MAINTAINING_BALANCE;
            $maxWithdrawable = max(0, (int) $saver->balance - $minMaintainingBalance);

            $recentMutations = $saver->transactions()
                ->latest('id')
                ->take(5)
                ->get()
                ->map(fn ($t) => [
                    'id' => $t->id,
                    'transaction_number' => $t->transaction_number,
                    'type' => $t->type->value,
                    'amount' => (int) $t->amount,
                    'formatted_amount' => 'Rp'.number_format((int) $t->amount, 0, ',', '.'),
                    'closing_balance' => (int) $t->closing_balance,
                    'notes' => $t->notes,
                    'date' => $t->created_at?->format('d/m/Y H:i'),
                ]);

            return response()->json([
                'success' => true,
                'type' => 'saver',
                'saver' => [
                    'id' => $saver->id,
                    'account_number' => $saver->account_number,
                    'qr_code_token' => $saver->qr_code_token,
                    'student_name' => $saver->user?->name ?? $saver->member?->student_name ?? 'Nasabah Siswa',
                    'graduation_year' => $saver->member?->graduation_year ?? '-',
                    'balance' => (int) $saver->balance,
                    'formatted_balance' => 'Rp'.number_format((int) $saver->balance, 0, ',', '.'),
                    'max_withdrawable' => $maxWithdrawable,
                    'formatted_max_withdrawable' => 'Rp'.number_format($maxWithdrawable, 0, ',', '.'),
                    'min_maintaining_balance' => $minMaintainingBalance,
                    'status' => $saver->status->value,
                    'opened_at' => $saver->opened_at?->format('d F Y'),
                    'recent_mutations' => $recentMutations,
                ],
            ]);
        }

        // 2. If not found in Savers, check in Members (Candidate inactive members ready for initial deposit)
        /** @var Member|null $member */
        $member = Member::with('user')
            ->where('account_status', MemberAccountStatus::Inactive)
            ->where(function ($q) use ($code) {
                $q->where('student_name', 'like', "%{$code}%")
                    ->orWhere('id', $code)
                    ->orWhereHas('user', function ($uq) use ($code) {
                        $uq->where('name', 'like', "%{$code}%")
                            ->orWhere('email', 'like', "%{$code}%")
                            ->orWhere('username', 'like', "%{$code}%");
                    });
            })
            ->first();

        if ($member) {
            return response()->json([
                'success' => true,
                'type' => 'member',
                'member' => [
                    'id' => $member->id,
                    'student_name' => $member->student_name,
                    'graduation_year' => $member->graduation_year,
                    'account_status' => $member->account_status->value,
                    'username' => $member->user?->username ?? '-',
                    'email' => $member->user?->email ?? '-',
                    'min_initial_deposit' => 10000,
                    'formatted_min_initial_deposit' => 'Rp10.000',
                    'message' => 'Siswa calon penabung belum memiliki rekening aktif. Lakukan setoran awal minimal Rp10.000 dan input 6-digit PIN untuk mengaktifkan rekening.',
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => "Data nasabah atau calon penabung tidak ditemukan dengan kata kunci: '{$code}'.",
        ], Response::HTTP_NOT_FOUND);
    }

    /**
     * Process cash deposit or initial deposit activation.
     */
    public function deposit(DepositRequest $request, DepositAction $action, CloseCashAction $closeCashAction): JsonResponse|RedirectResponse
    {
        $teller = $request->user();
        $amount = $request->integer('amount');
        $pin = $request->filled('pin') ? $request->string('pin')->toString() : null;
        $notes = $request->filled('notes') ? $request->string('notes')->toString() : null;

        $member = null;
        $saver = null;

        if ($request->filled('member_id')) {
            $member = Member::findOrFail($request->integer('member_id'));
        } elseif ($request->filled('saver_id')) {
            $saver = Saver::findOrFail($request->integer('saver_id'));
        } elseif ($request->filled('account_number')) {
            $saver = Saver::where('account_number', $request->string('account_number'))->firstOrFail();
        }

        // Web counter requires PIN verification for all saver transactions
        if ($saver !== null && empty($pin)) {
            throw ValidationException::withMessages([
                'pin' => 'Otorisasi gagal: PIN 6-digit keamanan nasabah wajib dimasukkan.',
            ]);
        }

        $transaction = $action->handle(
            teller: $teller,
            amount: $amount,
            member: $member,
            saver: $saver,
            pin: $pin,
            notes: $notes
        );

        $transaction->load(['saver.user', 'saver.member', 'teller']);
        $newDrawerBalance = (int) $closeCashAction->preview($teller)['system_ending_balance'];

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaksi setoran tunai berhasil diproses dan dicatat ke buku besar.',
                'transaction' => [
                    'id' => $transaction->id,
                    'transaction_number' => $transaction->transaction_number,
                    'type' => $transaction->type->value,
                    'amount' => (int) $transaction->amount,
                    'formatted_amount' => 'Rp'.number_format((int) $transaction->amount, 0, ',', '.'),
                    'opening_balance' => (int) $transaction->opening_balance,
                    'formatted_opening_balance' => 'Rp'.number_format((int) $transaction->opening_balance, 0, ',', '.'),
                    'closing_balance' => (int) $transaction->closing_balance,
                    'formatted_closing_balance' => 'Rp'.number_format((int) $transaction->closing_balance, 0, ',', '.'),
                    'notes' => $transaction->notes,
                    'date' => $transaction->created_at?->format('d/m/Y H:i:s'),
                    'account_number' => $transaction->saver?->account_number,
                    'student_name' => $transaction->saver?->user?->name ?? $transaction->saver?->member?->student_name ?? 'Nasabah',
                    'teller_name' => $transaction->teller?->name ?? 'Teller',
                    'new_drawer_balance' => $newDrawerBalance,
                    'formatted_new_drawer_balance' => 'Rp'.number_format($newDrawerBalance, 0, ',', '.'),
                ],
            ], Response::HTTP_CREATED);
        }

        return redirect()->route('teller.counter')->with('success', 'Transaksi setoran tunai berhasil diproses.');
    }

    /**
     * Process cash withdrawal with 6-digit PIN verification.
     */
    public function withdraw(WithdrawRequest $request, WithdrawAction $action, CloseCashAction $closeCashAction): JsonResponse|RedirectResponse
    {
        $teller = $request->user();
        $accountNumber = $request->string('account_number')->toString();
        $pin = $request->string('pin')->toString();
        $amount = $request->integer('amount');
        $notes = $request->filled('notes') ? $request->string('notes')->toString() : null;

        $saver = Saver::where('account_number', $accountNumber)->firstOrFail();

        $transaction = $action->handle(
            teller: $teller,
            saver: $saver,
            pin: $pin,
            amount: $amount,
            notes: $notes
        );

        $transaction->load(['saver.user', 'saver.member', 'teller']);
        $newDrawerBalance = (int) $closeCashAction->preview($teller)['system_ending_balance'];

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaksi penarikan tunai berhasil diproses dan uang fisik diserahkan.',
                'transaction' => [
                    'id' => $transaction->id,
                    'transaction_number' => $transaction->transaction_number,
                    'type' => $transaction->type->value,
                    'amount' => (int) $transaction->amount,
                    'formatted_amount' => 'Rp'.number_format((int) $transaction->amount, 0, ',', '.'),
                    'opening_balance' => (int) $transaction->opening_balance,
                    'formatted_opening_balance' => 'Rp'.number_format((int) $transaction->opening_balance, 0, ',', '.'),
                    'closing_balance' => (int) $transaction->closing_balance,
                    'formatted_closing_balance' => 'Rp'.number_format((int) $transaction->closing_balance, 0, ',', '.'),
                    'notes' => $transaction->notes,
                    'date' => $transaction->created_at?->format('d/m/Y H:i:s'),
                    'account_number' => $transaction->saver?->account_number,
                    'student_name' => $transaction->saver?->user?->name ?? $transaction->saver?->member?->student_name ?? 'Nasabah',
                    'teller_name' => $transaction->teller?->name ?? 'Teller',
                    'new_drawer_balance' => $newDrawerBalance,
                    'formatted_new_drawer_balance' => 'Rp'.number_format($newDrawerBalance, 0, ',', '.'),
                ],
            ], Response::HTTP_CREATED);
        }

        return redirect()->route('teller.counter')->with('success', 'Transaksi penarikan tunai berhasil diproses.');
    }
}
