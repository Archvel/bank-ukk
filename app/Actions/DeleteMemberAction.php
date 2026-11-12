<?php

namespace App\Actions;

use App\Models\Member;
use App\Models\PinResetRequest;
use Illuminate\Support\Facades\DB;

class DeleteMemberAction
{
    /**
     * Delete a member and their linked user/empty saver safely within foreign key constraints.
     *
     * @return array{deleted: bool, message: string}
     */
    public function handle(Member $member): array
    {
        $saver = $member->saver()->withTrashed()->first();

        if ($saver !== null && ($saver->transactions()->exists() || $saver->balance > 0)) {
            return [
                'deleted' => false,
                'message' => 'Member tidak dapat dihapus karena sudah memiliki buku rekening dan riwayat transaksi finansial.',
            ];
        }

        $user = $member->user;

        if ($user !== null && (
            $user->tellerTransactions()->exists()
            || $user->tellerClosures()->exists()
            || $user->approvedClosures()->exists()
            || PinResetRequest::where('verified_by', $user->id)->exists()
        )) {
            return [
                'deleted' => false,
                'message' => 'Member tidak dapat dihapus karena akun pengguna terkait memiliki riwayat audit sistem.',
            ];
        }

        DB::transaction(function () use ($member, $saver, $user): void {
            if ($saver !== null) {
                $saver->pinResetRequests()->delete();
                $saver->forceDelete();
            }

            $member->delete();
            $user?->delete();
        });

        return [
            'deleted' => true,
            'message' => 'Data calon penabung berhasil dihapus.',
        ];
    }
}
