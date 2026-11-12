<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Models\CashClosure;
use App\Models\Saver;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $reportType = $request->query('type', 'daily');
        $date = $request->query('date', now()->format('Y-m-d'));
        $month = (int) $request->query('month', now()->format('m'));
        $year = (int) $request->query('year', now()->format('Y'));

        // Query transactions withTrashed saver
        if ($reportType === 'monthly') {
            $transactions = Transaction::with(['saver' => fn ($q) => $q->withTrashed()->with('user'), 'teller'])
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->orderBy('created_at', 'desc')
                ->get();

            $totalDeposits = (int) $transactions->where('type', TransactionType::Deposit)->sum('amount');
            $totalWithdrawals = (int) $transactions->where('type', TransactionType::Withdrawal)->sum('amount');
            $netCashFlow = $totalDeposits - $totalWithdrawals;
            $transactionCount = $transactions->count();

            $activeSaversCount = Saver::where('status', 'active')->count();
            $totalSavings = (int) Saver::where('status', 'active')->sum('balance');

            $closures = collect();
            $closure = null;
            $tellerNames = $transactions->pluck('teller.name')
                ->filter()
                ->unique()
                ->values()
                ->all();
        } else {
            // Daily Report
            $transactions = Transaction::with(['saver' => fn ($q) => $q->withTrashed()->with('user'), 'teller'])
                ->whereDate('created_at', $date)
                ->orderBy('id', 'asc')
                ->get();

            $totalDeposits = (int) $transactions->where('type', TransactionType::Deposit)->sum('amount');
            $totalWithdrawals = (int) $transactions->where('type', TransactionType::Withdrawal)->sum('amount');
            $netCashFlow = $totalDeposits - $totalWithdrawals;
            $transactionCount = $transactions->count();

            $activeSaversCount = Saver::where('status', 'active')->count();
            $totalSavings = (int) Saver::where('status', 'active')->sum('balance');

            $closures = CashClosure::with(['teller', 'approver'])
                ->whereDate('closure_date', $date)
                ->orderBy('id')
                ->get();
            $closure = $closures->first();
            $tellerNames = $closures->pluck('teller.name')
                ->merge($transactions->pluck('teller.name'))
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        return view('admin.reports.index', compact(
            'reportType',
            'date',
            'month',
            'year',
            'transactions',
            'totalDeposits',
            'totalWithdrawals',
            'netCashFlow',
            'transactionCount',
            'activeSaversCount',
            'totalSavings',
            'closure',
            'closures',
            'tellerNames'
        ));
    }
}
