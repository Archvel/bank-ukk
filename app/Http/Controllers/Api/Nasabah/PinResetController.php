<?php

namespace App\Http\Controllers\Api\Nasabah;

use App\Actions\ResetPinAction;
use App\Enums\PinResetStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ConfirmPinResetRequest;
use App\Http\Requests\RequestPinResetRequest;
use App\Http\Resources\PinResetRequestResource;
use App\Models\PinResetRequest;
use App\Models\Saver;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class PinResetController extends Controller
{
    /**
     * Request a PIN reset for a saver account.
     */
    public function requestReset(RequestPinResetRequest $request): JsonResponse
    {
        $accountNumber = $request->string('account_number')->toString();
        $saver = Saver::where('account_number', $accountNumber)->firstOrFail();

        $token = strtoupper(Str::random(8));

        $pinReset = PinResetRequest::create([
            'saver_id' => $saver->id,
            'reset_token' => $token,
            'status' => PinResetStatus::Pending,
            'expires_at' => now()->addHours(24),
        ]);

        return response()->json([
            'message' => 'Permohonan reset PIN berhasil dibuat. Gunakan token ini untuk konfirmasi PIN baru Anda.',
            'reset_token' => $token,
            'request' => new PinResetRequestResource($pinReset->load('saver.user')),
        ], Response::HTTP_CREATED);
    }

    /**
     * Confirm PIN reset with a valid token and new 6-digit PIN.
     */
    public function confirmReset(ConfirmPinResetRequest $request, ResetPinAction $action): JsonResponse
    {
        $token = $request->string('reset_token')->toString();
        $newPin = $request->string('new_pin')->toString();

        /** @var PinResetRequest|null $pinReset */
        $pinReset = PinResetRequest::where('reset_token', $token)
            ->where('status', PinResetStatus::Pending)
            ->where('expires_at', '>', now())
            ->first();

        if (! $pinReset) {
            return response()->json([
                'message' => 'Token reset PIN tidak valid atau sudah kedaluwarsa.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $verifier = $request->user() ?? $pinReset->saver->user;

        $action->handle(
            saver: $pinReset->saver,
            newPin: $newPin,
            verifier: $verifier,
            pinResetRequest: $pinReset
        );

        return response()->json([
            'message' => 'PIN keamanan 6-digit berhasil diperbarui. Silakan gunakan PIN baru untuk bertransaksi.',
        ]);
    }
}
