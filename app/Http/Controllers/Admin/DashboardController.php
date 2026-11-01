<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CashClosureStatus;
use App\Enums\JournalEntryType;
use App\Enums\SaverStatus;
use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Models\CashClosure;
use App\Models\JournalEntry;
use App\Models\Saver;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(): View
    {
        // 1. Core Summary Metrics
        $totalSavings = (int) Saver::where('status', SaverStatus::Active)->sum('balance');

        $vaultSummary = JournalEntry::where('account_code', '102')
            ->selectRaw('
                SUM(CASE WHEN type = ? THEN amount ELSE 0 END) as debit,
                SUM(CASE WHEN type = ? THEN amount ELSE 0 END) as credit
            ', [JournalEntryType::Debit->value, JournalEntryType::Credit->value])
            ->first();
        $vaultBalance = max(0, (int) ($vaultSummary?->debit ?? 0) - (int) ($vaultSummary?->credit ?? 0));

        $todaySummary = Transaction::today()
            ->selectRaw('
                SUM(CASE WHEN type = ? THEN amount ELSE 0 END) as deposits,
                SUM(CASE WHEN type = ? THEN amount ELSE 0 END) as withdrawals
            ', [TransactionType::Deposit->value, TransactionType::Withdrawal->value])
            ->first();
        $todayDeposits = (int) ($todaySummary?->deposits ?? 0);
        $todayWithdrawals = (int) ($todaySummary?->withdrawals ?? 0);
        $todayTurnover = $todayDeposits + $todayWithdrawals;

        $activeSaversCount = Saver::where('status', SaverStatus::Active)->count();

        // 2. Pending Cash Closures needing verification
        $pendingClosures = CashClosure::with(['teller'])
            ->where('status', CashClosureStatus::Submitted)
            ->latest('closure_date')
            ->take(5)
            ->get();

        // 3. Recent Savers
        $recentSavers = Saver::with(['user', 'member'])
            ->latest('opened_at')
            ->take(6)
            ->get();

        // 4. Recent Transactions
        $recentTransactions = Transaction::with(['saver.user', 'teller'])
            ->latest('id')
            ->take(6)
            ->get();

        // 5. Chart Data: 7-Day Inflow vs Outflow in a single grouped query
        $sevenDaysAgo = Carbon::today()->subDays(6)->startOfDay();
        $chartData = Transaction::where('created_at', '>=', $sevenDaysAgo)
            ->selectRaw('DATE(created_at) as date, type, SUM(amount) as total')
            ->groupBy('date', 'type')
            ->get()
            ->groupBy('date');

        $chartLabels = [];
        $chartDeposits = [];
        $chartWithdrawals = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dateKey = $date->format('Y-m-d');
            $chartLabels[] = $date->format('d M');

            $dayTransactions = $chartData->get($dateKey, collect());
            $depositItem = $dayTransactions->firstWhere('type', TransactionType::Deposit);
            $withdrawalItem = $dayTransactions->firstWhere('type', TransactionType::Withdrawal);

            $chartDeposits[] = (int) ($depositItem?->total ?? 0);
            $chartWithdrawals[] = (int) ($withdrawalItem?->total ?? 0);
        }

        return view('admin.dashboard', compact(
            'totalSavings',
            'vaultBalance',
            'todayDeposits',
            'todayWithdrawals',
            'todayTurnover',
            'activeSaversCount',
            'pendingClosures',
            'recentSavers',
            'recentTransactions',
            'chartLabels',
            'chartDeposits',
            'chartWithdrawals'
        ));
    }
}
