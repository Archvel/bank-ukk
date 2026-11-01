<?php

namespace App\Http\Controllers\Nasabah;

use App\Http\Controllers\Controller;
use App\Models\Saver;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MutationController extends Controller
{
    /**
     * Display the Student Digital Passbook (Buku Tabungan Digital / Riwayat Mutasi).
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user() ?: Auth::user();

        /** @var Saver|null $saver */
        $saver = Saver::where('user_id', $user->id)->first();

        if (! $saver) {
            return redirect()->route('nasabah.dashboard')->with('error', 'Rekening tabungan belum aktif. Lakukan setoran awal minimal Rp10.000 di Loket Teller untuk mengaktifkan buku tabungan.');
        }

        $query = Transaction::where('saver_id', $saver->id)->latest('id');

        if ($request->filled('type') && in_array($request->string('type')->toString(), ['deposit', 'withdrawal'])) {
            $query->where('type', $request->string('type')->toString());
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->date('start_date')->startOfDay(),
                $request->date('end_date')->endOfDay(),
            ]);
        } elseif ($request->filled('period')) {
            $period = $request->string('period')->toString();
            if ($period === 'month') {
                $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
            } elseif ($period === 'week') {
                $query->where('created_at', '>=', now()->subDays(7));
            }
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->trim();
            $query->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $transactions = $query->paginate(15)->withQueryString();

        // 3 KPI calculations for this saver in a single query
        $summary = Transaction::where('saver_id', $saver->id)
            ->selectRaw('
                SUM(CASE WHEN type = "deposit" THEN amount ELSE 0 END) as total_deposit,
                SUM(CASE WHEN type = "withdrawal" THEN amount ELSE 0 END) as total_withdrawal
            ')
            ->first();

        $totalDeposit = (int) ($summary?->total_deposit ?? 0);
        $totalWithdrawal = (int) ($summary?->total_withdrawal ?? 0);
        $currentBalance = (int) $saver->balance;

        return view('nasabah.mutations.index', compact(
            'saver',
            'transactions',
            'totalDeposit',
            'totalWithdrawal',
            'currentBalance'
        ));
    }
}
