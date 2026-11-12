<?php

namespace App\Actions;

use App\Models\PinResetRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeleteUserAction
{
    /**
     * Delete a user and any unreferenced member/empty saver records safely.
     *
     * @return array{deleted: bool, message: string}
     */
    public function handle(User $user, ?User $actor = null): array
    {
        if ($actor !== null && $user->id === $actor->id) {
            return [
                'deleted' => false,
                'message' => 'Anda tidak dapat menghapus akun Anda sendiri.',
            ];
        }

        $saver = $user->saver()->withTrashed()->first();

        if ($user->tellerTransactions()->exists() || ($saver !== null && ($saver->transactions()->exists() || $saver->balance > 0))) {
            return [
                'deleted' => false,
                'message' => 'Pengguna tidak dapat dihapus karena memiliki riwayat audit finansial transaksi.',
            ];
        }

        if ($user->tellerClosures()->exists() || $user->approvedClosures()->exists()) {
            return [
                'deleted' => false,
                'message' => 'Pengguna tidak dapat dihapus karena memiliki riwayat penutupan atau otorisasi kas.',
            ];
        }

        if (PinResetRequest::where('verified_by', $user->id)->exists()) {
            return [
                'deleted' => false,
                'message' => 'Pengguna tidak dapat dihapus karena tercatat dalam riwayat verifikasi reset PIN.',
            ];
        }

        DB::transaction(function () use ($user, $saver): void {
            if ($saver !== null) {
                $saver->pinResetRequests()->delete();
                $saver->forceDelete();
            }

            if ($user->member !== null) {
                $user->member->delete();
            }

            $user->delete();
        });

        return [
            'deleted' => true,
            'message' => 'Pengguna berhasil dihapus dari sistem.',
        ];
    }
}
