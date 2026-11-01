<?php

namespace App\Http\Controllers\Admin;

use App\Enums\JournalEntryType;
use App\Http\Controllers\Api\Admin\AuditJournalController as ApiAuditJournalController;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\JournalEntry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AuditJournalController extends Controller
{
    public function index(Request $request): View|AnonymousResourceCollection
    {
        if ($request->wantsJson()) {
            return app(ApiAuditJournalController::class)->index($request);
        }

        $query = JournalEntry::query()->with(['account', 'transaction.saver.user', 'transaction.teller', 'cashClosure.teller'])->latest('id');

        if ($request->filled('account_code')) {
            $query->where('account_code', $request->string('account_code'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        if ($request->filled('date')) {
            $query->whereDate('entry_date', $request->string('date'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('entry_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('transaction', function ($tq) use ($search) {
                        $tq->where('transaction_number', 'like', "%{$search}%");
                    });
            });
        }

        $entries = $query->paginate(20)->withQueryString();

        // Calculate Equilibrium Check for the filtered context or today in a single query
        $summaryQuery = JournalEntry::query();
        if ($request->filled('date')) {
            $summaryQuery->whereDate('entry_date', $request->string('date'));
        }

        $equilibriumSummary = (clone $summaryQuery)
            ->selectRaw('
                SUM(CASE WHEN type = ? THEN amount ELSE 0 END) as total_debit,
                SUM(CASE WHEN type = ? THEN amount ELSE 0 END) as total_credit,
                COUNT(*) as total_count
            ', [JournalEntryType::Debit->value, JournalEntryType::Credit->value])
            ->first();

        $totalDebit = (int) ($equilibriumSummary?->total_debit ?? 0);
        $totalCredit = (int) ($equilibriumSummary?->total_credit ?? 0);
        $isBalanced = ($totalDebit === $totalCredit);
        $difference = abs($totalDebit - $totalCredit);
        $totalEntriesCount = (int) ($equilibriumSummary?->total_count ?? 0);

        $accounts = Account::orderBy('code')->get();

        return view('admin.audit-journals.index', compact(
            'entries',
            'totalDebit',
            'totalCredit',
            'isBalanced',
            'difference',
            'totalEntriesCount',
            'accounts'
        ));
    }

    public function summary(Request $request): JsonResponse
    {
        return app(ApiAuditJournalController::class)->summary($request);
    }
}
