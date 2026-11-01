<?php

namespace App\Http\Controllers\Api\Nasabah;

use App\Http\Controllers\Controller;
use App\Http\Resources\SaverResource;
use App\Http\Resources\TransactionResource;
use App\Models\Saver;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class AccountController extends Controller
{
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

        return response()->json([
            'saver' => new SaverResource($saver->load(['user', 'member'])),
        ]);
    }

    /**
     * Get transaction mutations for the authenticated saver.
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

        $query = Transaction::where('saver_id', $saver->id)->latest('id');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->string('start_date')->startOfDay(),
                $request->string('end_date')->endOfDay(),
            ]);
        }

        return TransactionResource::collection($query->paginate(15));
    }
}
