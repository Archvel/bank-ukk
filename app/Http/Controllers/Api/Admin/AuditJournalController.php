<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\JournalEntryType;
use App\Http\Controllers\Controller;
use App\Http\Resources\JournalEntryResource;
use App\Models\JournalEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AuditJournalController extends Controller
{
    /**
     * Display a listing of accounting journal entries.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = JournalEntry::query()->with(['account', 'transaction', 'cashClosure'])->latest('id');

        if ($request->filled('account_code')) {
            $query->where('account_code', $request->string('account_code'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('entry_date', [$request->string('start_date'), $request->string('end_date')]);
        } elseif ($request->filled('date')) {
            $query->whereDate('entry_date', $request->string('date'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('entry_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return JournalEntryResource::collection($query->paginate(20));
    }

    /**
     * Get summary of debits vs credits to verify double-entry equilibrium.
     */
    public function summary(Request $request): JsonResponse
    {
        $query = JournalEntry::query();

        if ($request->filled('date')) {
            $query->whereDate('entry_date', $request->string('date'));
        }

        $totalDebit = (int) (clone $query)->where('type', JournalEntryType::Debit)->sum('amount');
        $totalCredit = (int) (clone $query)->where('type', JournalEntryType::Credit)->sum('amount');
        $isBalanced = ($totalDebit === $totalCredit);

        return response()->json([
            'total_debit' => $totalDebit,
            'formatted_total_debit' => 'Rp'.number_format($totalDebit, 0, ',', '.'),
            'total_credit' => $totalCredit,
            'formatted_total_credit' => 'Rp'.number_format($totalCredit, 0, ',', '.'),
            'is_balanced' => $isBalanced,
            'difference' => abs($totalDebit - $totalCredit),
        ]);
    }
}
