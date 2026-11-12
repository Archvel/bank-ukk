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
        $todayClosure = $this->findTodayClosure($teller, $today);
        $openingBalance = $this->resolveOpeningBalance($teller, $today, $todayClosure);
        $trxSummary = $this->summarizeDailyTransactions($teller, $today);

        $totalDeposit = $trxSummary['total_deposit'];
        $totalWithdrawal = $trxSummary['total_withdrawal'];

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
        $todayClosure = $this->findTodayClosure($teller, $today);
        $openingBalance = $this->resolveOpeningBalance($teller, $today, $todayClosure);
        $trxSummary = $this->summarizeDailyTransactions($teller, $today);

        $totalDeposit = $trxSummary['total_deposit'];
        $totalWithdrawal = $trxSummary['total_withdrawal'];

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

    private function findTodayClosure(User $teller, mixed $today): ?CashClosure
    {
        return CashClosure::where('teller_id', $teller->id)
            ->whereDate('closure_date', $today)
            ->first();
    }

    private function resolveOpeningBalance(User $teller, mixed $today, ?CashClosure $todayClosure): int
    {
        if ($todayClosure !== null) {
            return (int) $todayClosure->opening_balance;
        }

        $previousApprovedClosure = CashClosure::where('teller_id', $teller->id)
            ->where('closure_date', '<', $today)
            ->where('status', CashClosureStatus::Approved)
            ->orderByDesc('closure_date')
            ->first();

        return $previousApprovedClosure ? (int) $previousApprovedClosure->physical_ending_balance : 0;
    }

    /**
     * @return array{count: int, total_deposit: int, total_withdrawal: int}
     */
    private function summarizeDailyTransactions(User $teller, mixed $today): array
    {
        $query = Transaction::where('teller_id', $teller->id)
            ->whereDate('created_at', $today);

        return Transaction::summarizeTotals($query);
    }
}
