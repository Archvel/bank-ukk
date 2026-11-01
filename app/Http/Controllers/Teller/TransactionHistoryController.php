<?php

namespace App\Http\Controllers\Teller;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionHistoryController extends Controller
{
    /**
     * Display today's transaction history for the authenticated teller.
     */
    public function index(Request $request): View
    {
        $teller = $request->user() ?: Auth::user();

        $query = Transaction::with(['saver.user', 'saver.member', 'teller'])
            ->where('teller_id', $teller->id)
            ->whereDate('created_at', today())
            ->latest('id');

        if ($request->filled('type') && in_array($request->string('type')->toString(), ['deposit', 'withdrawal'])) {
            $query->where('type', $request->string('type')->toString());
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->trim();
            $query->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                    ->orWhereHas('saver', function ($sq) use ($search) {
                        $sq->where('account_number', 'like', "%{$search}%")
                            ->orWhereHas('user', function ($uq) use ($search) {
                                $uq->where('name', 'like', "%{$search}%");
                            });
                    });
            });
        }

        $transactions = $query->paginate(20)->withQueryString();

        // Summary KPI calculations for today's teller shift in a single query
        $summary = Transaction::where('teller_id', $teller->id)
            ->whereDate('created_at', today())
            ->selectRaw('
                COUNT(*) as count,
                SUM(CASE WHEN type = "deposit" THEN amount ELSE 0 END) as total_deposit,
                SUM(CASE WHEN type = "withdrawal" THEN amount ELSE 0 END) as total_withdrawal
            ')
            ->first();

        $totalTransactionsCount = (int) ($summary?->count ?? 0);
        $totalDepositAmount = (int) ($summary?->total_deposit ?? 0);
        $totalWithdrawalAmount = (int) ($summary?->total_withdrawal ?? 0);
        $netCashFlow = $totalDepositAmount - $totalWithdrawalAmount;

        return view('teller.transactions.index', compact(
            'transactions',
            'totalTransactionsCount',
            'totalDepositAmount',
            'totalWithdrawalAmount',
            'netCashFlow'
        ));
    }

    /**
     * Get transaction slip receipt for reprint.
     */
    public function receipt(Request $request, Transaction $transaction): JsonResponse|View
    {
        $teller = $request->user();

        // Ensure teller only views their own transactions unless admin
        if ($transaction->teller_id !== $teller->id && ! $teller->isAdmin()) {
            abort(403, 'Akses struk transaksi ditolak.');
        }

        $transaction->load(['saver.user', 'saver.member', 'teller']);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'transaction' => [
                    'id' => $transaction->id,
                    'transaction_number' => $transaction->transaction_number,
                    'type' => $transaction->type->value,
                    'amount' => (int) $transaction->amount,
                    'formatted_amount' => 'Rp'.number_format((int) $transaction->amount, 0, ',', '.'),
                    'opening_balance' => (int) $transaction->opening_balance,
                    'formatted_opening_balance' => 'Rp'.number_format((int) $transaction->opening_balance, 0, ',', '.'),
                    'closing_balance' => (int) $transaction->closing_balance,
                    'formatted_closing_balance' => 'Rp'.number_format((int) $transaction->closing_balance, 0, ',', '.'),
                    'notes' => $transaction->notes,
                    'date' => $transaction->created_at?->format('d/m/Y H:i:s'),
                    'account_number' => $transaction->saver?->account_number,
                    'student_name' => $transaction->saver?->user?->name ?? $transaction->saver?->member?->student_name ?? 'Nasabah',
                    'teller_name' => $transaction->teller?->name ?? 'Teller',
                ],
            ]);
        }

        return view('teller.transactions.receipt', compact('transaction'));
    }
}
