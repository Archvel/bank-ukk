<?php

namespace App\Actions;

use App\Enums\MemberAccountStatus;
use App\Enums\SaverStatus;
use App\Models\Member;
use App\Models\Saver;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ActivateMemberAction
{
    /**
     * Activate a member and convert into an active Saver entity.
     */
    public function handle(Member $member, string $pin): Saver
    {
        $year = date('Y');
        $latestId = (int) Saver::withTrashed()->max('id') + 1;
        $accountNumber = sprintf('BM%s%04d', $year, $latestId);
        $qrCodeToken = sprintf('QR-%s-%s', $accountNumber, strtoupper(Str::random(6)));

        $saver = Saver::create([
            'user_id' => $member->user_id,
            'member_id' => $member->id,
            'account_number' => $accountNumber,
            'pin' => Hash::make($pin),
            'qr_code_token' => $qrCodeToken,
            'balance' => 0,
            'status' => SaverStatus::Active,
            'opened_at' => now(),
        ]);

        $member->update([
            'account_status' => MemberAccountStatus::Active,
        ]);

        return $saver;
    }
}
