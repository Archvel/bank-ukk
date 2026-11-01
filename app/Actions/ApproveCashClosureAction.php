<?php

namespace App\Actions;

use App\Enums\CashClosureStatus;
use App\Models\CashClosure;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApproveCashClosureAction
{
    public function __construct(
        protected CreateJournalEntryAction $createJournalEntryAction
    ) {}

    /**
     * Approve or reject a submitted cash closure and transfer cash to the vault.
     *
     * @throws ValidationException
     */
    public function handle(CashClosure $closure, User $approver, bool $approve = true, ?string $reason = null): CashClosure
    {
        if ($closure->status !== CashClosureStatus::Submitted) {
            throw ValidationException::withMessages([
                'closure' => 'Hanya laporan penutupan kas dengan status "submitted" yang dapat diproses.',
            ]);
        }

        if (! $approve) {
            $closure->update([
                'status' => CashClosureStatus::Rejected,
                'notes' => trim(($closure->notes ?? '').' | Catatan Penolakan: '.($reason ?? 'Selisih tidak wajar')),
            ]);

            return $closure;
        }

        return DB::transaction(function () use ($closure, $approver) {
            $closure->update([
                'status' => CashClosureStatus::Approved,
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);

            // Execute vault cash transfer journal entries
            $this->createJournalEntryAction->recordVaultTransfer($closure);

            return $closure;
        });
    }
}
