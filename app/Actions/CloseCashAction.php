<?php

namespace App\Actions;

use App\Enums\CashClosureStatus;
use App\Models\CashClosure;
use App\Models\Transaction;
use App\Models\User;

class CloseCashAction
{
    /**
     * Calculate current daily totals and submit a teller cash closure draft.
     */
    public function handle(User $teller, int $physicalEndingBalance, ?string $notes = null): CashClosure
    {
        $today = today();

        $todayClosure = CashClosure::where('teller_id', $teller->id)
            ->whereDate('closure_date', $today)
            ->first();

        if ($todayClosure !== null) {
            $openingBalance = (int) $todayClosure->opening_balance;
        } else {
            // Get opening balance from previous day's approved closure, or default to 0
            $previousApprovedClosure = CashClosure::where('teller_id', $teller->id)
                ->where('closure_date', '<', $today)
                ->where('status', CashClosureStatus::Approved)
                ->orderByDesc('closure_date')
                ->first();

            $openingBalance = $previousApprovedClosure ? (int) $previousApprovedClosure->physical_ending_balance : 0;
        }

        $trxSummary = Transaction::where('teller_id', $teller->id)
            ->whereDate('created_at', $today)
            ->selectRaw('
                SUM(CASE WHEN type = "deposit" THEN amount ELSE 0 END) as total_deposit,
                SUM(CASE WHEN type = "withdrawal" THEN amount ELSE 0 END) as total_withdrawal
            ')
            ->first();

        $totalDeposit = (int) ($trxSummary?->total_deposit ?? 0);
        $totalWithdrawal = (int) ($trxSummary?->total_withdrawal ?? 0);

        $systemEndingBalance = $openingBalance + $totalDeposit - $totalWithdrawal;
        $difference = $physicalEndingBalance - $systemEndingBalance;

        return CashClosure::updateOrCreate(
            [
                'teller_id' => $teller->id,
                'closure_date' => $today,
            ],
            [
                'opening_balance' => $openingBalance,
                'total_deposit' => $totalDeposit,
                'total_withdrawal' => $totalWithdrawal,
                'system_ending_balance' => $systemEndingBalance,
                'physical_ending_balance' => $physicalEndingBalance,
                'difference' => $difference,
                'status' => CashClosureStatus::Submitted,
                'notes' => $notes ?? $todayClosure?->notes,
            ]
        );
    }

    /**
     * Preview calculation without persisting.
     *
     * @return array<string, int|bool|string|null>
     */
    public function preview(User $teller): array
    {
        $today = today();

        $todayClosure = CashClosure::where('teller_id', $teller->id)
            ->whereDate('closure_date', $today)
            ->first();

        if ($todayClosure !== null) {
            $openingBalance = (int) $todayClosure->opening_balance;
        } else {
            $previousApprovedClosure = CashClosure::where('teller_id', $teller->id)
                ->where('closure_date', '<', $today)
                ->where('status', CashClosureStatus::Approved)
                ->orderByDesc('closure_date')
                ->first();

            $openingBalance = $previousApprovedClosure ? (int) $previousApprovedClosure->physical_ending_balance : 0;
        }

        $trxSummary = Transaction::where('teller_id', $teller->id)
            ->whereDate('created_at', $today)
            ->selectRaw('
                SUM(CASE WHEN type = "deposit" THEN amount ELSE 0 END) as total_deposit,
                SUM(CASE WHEN type = "withdrawal" THEN amount ELSE 0 END) as total_withdrawal
            ')
            ->first();

        $totalDeposit = (int) ($trxSummary?->total_deposit ?? 0);
        $totalWithdrawal = (int) ($trxSummary?->total_withdrawal ?? 0);

        $systemEndingBalance = $openingBalance + $totalDeposit - $totalWithdrawal;

        return [
            'opening_balance' => $openingBalance,
            'total_deposit' => $totalDeposit,
            'total_withdrawal' => $totalWithdrawal,
            'system_ending_balance' => $systemEndingBalance,
            'has_session_opened' => ($todayClosure !== null),
            'closure_status' => $todayClosure?->status?->value,
        ];
    }
}
