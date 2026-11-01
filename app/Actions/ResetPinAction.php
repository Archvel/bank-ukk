<?php

namespace App\Actions;

use App\Enums\PinResetStatus;
use App\Models\PinResetRequest;
use App\Models\Saver;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ResetPinAction
{
    /**
     * Reset a saver's 6-digit security PIN.
     *
     * @throws ValidationException
     */
    public function handle(
        Saver $saver,
        string $newPin,
        User $verifier,
        ?PinResetRequest $pinResetRequest = null
    ): Saver {
        if (strlen($newPin) !== 6 || ! ctype_digit($newPin)) {
            throw ValidationException::withMessages([
                'new_pin' => 'PIN keamanan harus terdiri dari tepat 6 digit numerik.',
            ]);
        }

        $saver->update([
            'pin' => Hash::make($newPin),
        ]);

        if ($pinResetRequest instanceof PinResetRequest) {
            $pinResetRequest->update([
                'status' => PinResetStatus::Completed,
                'verified_by' => $verifier->id,
            ]);
        } else {
            PinResetRequest::where('saver_id', $saver->id)
                ->where('status', PinResetStatus::Pending)
                ->update([
                    'status' => PinResetStatus::Completed,
                    'verified_by' => $verifier->id,
                ]);
        }

        return $saver;
    }
}
