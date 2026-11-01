<?php

namespace App\Http\Controllers\Api;

use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Resources\CashClosureResource;
use App\Http\Resources\TransactionResource;
use App\Models\CashClosure;
use App\Models\Saver;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Get detailed daily teller closing report data.
     */
    public function dailyTeller(CashClosure $closure): JsonResponse
    {
        $closure->load(['teller', 'approver']);

        $transactions = Transaction::with(['saver.user'])
            ->where('teller_id', $closure->teller_id)
            ->whereDate('created_at', $closure->closure_date)
            ->orderBy('id')
            ->get();

        return response()->json([
            'closure' => new CashClosureResource($closure),
            'summary' => [
                'total_transactions_count' => $transactions->count(),
                'deposit_count' => $transactions->where('type', TransactionType::Deposit)->count(),
                'withdrawal_count' => $transactions->where('type', TransactionType::Withdrawal)->count(),
                'formatted_opening_balance' => 'Rp'.number_format($closure->opening_balance, 0, ',', '.'),
                'formatted_total_deposit' => 'Rp'.number_format($closure->total_deposit, 0, ',', '.'),
                'formatted_total_withdrawal' => 'Rp'.number_format($closure->total_withdrawal, 0, ',', '.'),
                'formatted_system_ending' => 'Rp'.number_format($closure->system_ending_balance, 0, ',', '.'),
                'formatted_physical_ending' => 'Rp'.number_format($closure->physical_ending_balance, 0, ',', '.'),
                'formatted_difference' => 'Rp'.number_format($closure->difference, 0, ',', '.'),
                'is_balanced' => ($closure->difference === 0),
            ],
            'transactions' => TransactionResource::collection($transactions),
        ]);
    }

    /**
     * Get monthly banking summary report data (Client requirement).
     */
    public function monthly(Request $request): JsonResponse
    {
        $year = (int) ($request->input('year') ?? date('Y'));
        $month = (int) ($request->input('month') ?? date('m'));

        $transactionsQuery = Transaction::whereYear('created_at', $year)
            ->whereMonth('created_at', $month);

        $totalDeposits = (int) (clone $transactionsQuery)->where('type', TransactionType::Deposit)->sum('amount');
        $totalWithdrawals = (int) (clone $transactionsQuery)->where('type', TransactionType::Withdrawal)->sum('amount');
        $transactionCount = (int) (clone $transactionsQuery)->count();

        $activeSaversCount = Saver::where('status', 'active')->count();
        $totalSavingsBalance = (int) Saver::where('status', 'active')->sum('balance');

        return response()->json([
            'period' => [
                'year' => $year,
                'month' => $month,
                'formatted_period' => date('F Y', mktime(0, 0, 0, $month, 10, $year)),
            ],
            'summary' => [
                'total_deposits' => $totalDeposits,
                'formatted_total_deposits' => 'Rp'.number_format($totalDeposits, 0, ',', '.'),
                'total_withdrawals' => $totalWithdrawals,
                'formatted_total_withdrawals' => 'Rp'.number_format($totalWithdrawals, 0, ',', '.'),
                'net_cash_flow' => $totalDeposits - $totalWithdrawals,
                'formatted_net_cash_flow' => 'Rp'.number_format($totalDeposits - $totalWithdrawals, 0, ',', '.'),
                'total_transactions_count' => $transactionCount,
                'active_savers_count' => $activeSaversCount,
                'total_savings_balance' => $totalSavingsBalance,
                'formatted_total_savings_balance' => 'Rp'.number_format($totalSavingsBalance, 0, ',', '.'),
            ],
        ]);
    }

    /**
     * Get printable slip receipt for a specific transaction.
     */
    public function receipt(Transaction $transaction): JsonResponse
    {
        $transaction->load(['saver.user', 'teller']);

        return response()->json([
            'receipt' => [
                'bank_name' => 'BANK MINI SEKOLAH',
                'title' => $transaction->type === TransactionType::Deposit ? 'SLIP BUKTI SETORAN TUNAI' : 'SLIP BUKTI PENARIKAN TUNAI',
                'transaction_number' => $transaction->transaction_number,
                'date' => $transaction->created_at?->format('d/m/Y H:i:s'),
                'teller_name' => $transaction->teller?->name,
                'account_number' => $transaction->saver?->account_number,
                'saver_name' => $transaction->saver?->user?->name,
                'type' => $transaction->type?->value,
                'amount' => $transaction->amount,
                'formatted_amount' => 'Rp'.number_format($transaction->amount, 0, ',', '.'),
                'opening_balance' => $transaction->opening_balance,
                'closing_balance' => $transaction->closing_balance,
                'formatted_closing_balance' => 'Rp'.number_format($transaction->closing_balance, 0, ',', '.'),
                'notes' => $transaction->notes,
            ],
        ]);
    }
}
