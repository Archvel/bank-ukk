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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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

    /**
     * Verify the 6-digit PIN for the authenticated saver (withdrawal authorization check).
     */
    public function verifyPin(Request $request): JsonResponse
    {
        $request->validate([
            'pin' => ['required', 'string', 'size:6', 'regex:/^[0-9]+$/'],
        ], [
            'pin.required' => 'PIN 6-digit keamanan wajib diisi.',
            'pin.size' => 'PIN harus tepat terdiri dari 6 digit.',
            'pin.regex' => 'PIN hanya boleh berisi angka.',
        ]);

        $user = $request->user();
        /** @var Saver|null $saver */
        $saver = Saver::where('user_id', $user->id)->first();

        if (! $saver) {
            return response()->json([
                'status' => 'error',
                'valid' => false,
                'message' => 'Rekening penabung tidak ditemukan.',
            ], Response::HTTP_NOT_FOUND);
        }

        if (! Hash::check($request->string('pin')->toString(), $saver->pin)) {
            return response()->json([
                'status' => 'error',
                'valid' => false,
                'message' => 'PIN keamanan 6-digit tidak cocok.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json([
            'status' => 'success',
            'valid' => true,
            'message' => 'PIN keamanan 6-digit valid.',
        ]);
    }

    /**
     * Change PIN for the authenticated saver using current PIN.
     */
    public function changePin(Request $request): JsonResponse
    {
        $request->validate([
            'current_pin' => ['required', 'string', 'size:6', 'regex:/^[0-9]+$/'],
            'new_pin' => ['required', 'string', 'size:6', 'regex:/^[0-9]+$/', 'different:current_pin'],
            'new_pin_confirmation' => ['required', 'same:new_pin'],
        ], [
            'current_pin.required' => 'PIN saat ini wajib diisi.',
            'current_pin.size' => 'PIN saat ini harus berupa 6 digit angka.',
            'new_pin.required' => 'PIN baru wajib diisi.',
            'new_pin.size' => 'PIN baru harus berupa 6 digit angka.',
            'new_pin.different' => 'PIN baru harus berbeda dengan PIN lama.',
            'new_pin_confirmation.required' => 'Konfirmasi PIN baru wajib diisi.',
            'new_pin_confirmation.same' => 'Konfirmasi PIN baru tidak cocok.',
        ]);

        $user = $request->user();
        /** @var Saver|null $saver */
        $saver = Saver::where('user_id', $user->id)->first();

        if (! $saver) {
            return response()->json([
                'message' => 'Rekening penabung tidak ditemukan.',
            ], Response::HTTP_NOT_FOUND);
        }

        if (! Hash::check($request->string('current_pin')->toString(), $saver->pin)) {
            return response()->json([
                'message' => 'PIN keamanan saat ini tidak sesuai.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $saver->update([
            'pin' => Hash::make($request->string('new_pin')->toString()),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'PIN keamanan 6-digit berhasil diperbarui.',
        ]);
    }

    /**
     * Request a PIN reset token directly for the authenticated saver.
     */
    public function authenticatedRequestReset(Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var Saver|null $saver */
        $saver = Saver::where('user_id', $user->id)->first();

        if (! $saver) {
            return response()->json([
                'message' => 'Rekening penabung tidak ditemukan.',
            ], Response::HTTP_NOT_FOUND);
        }

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
}
