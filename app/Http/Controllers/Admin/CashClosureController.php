<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ApproveCashClosureAction;
use App\Enums\CashClosureStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveCashClosureRequest;
use App\Models\CashClosure;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CashClosureController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'pending');

        $pendingClosures = CashClosure::with(['teller', 'approver', 'journalEntries'])
            ->where('status', CashClosureStatus::Submitted)
            ->latest('closure_date')
            ->get();

        $disputeClosures = CashClosure::with(['teller', 'approver', 'journalEntries'])
            ->where('difference', '!=', 0)
            ->latest('closure_date')
            ->get();

        $historyClosures = CashClosure::with(['teller', 'approver', 'journalEntries'])
            ->whereIn('status', [CashClosureStatus::Approved, CashClosureStatus::Rejected])
            ->latest('closure_date')
            ->paginate(12)
            ->withQueryString();

        $pendingCount = $pendingClosures->count();
        $disputeCount = $disputeClosures->count();

        return view('admin.cash-closures.index', compact(
            'pendingClosures',
            'disputeClosures',
            'historyClosures',
            'pendingCount',
            'disputeCount',
            'tab'
        ));
    }

    public function show(CashClosure $closure): View
    {
        $closure->load(['teller', 'approver', 'journalEntries.account']);

        return view('admin.cash-closures.show', compact('closure'));
    }

    public function process(
        ApproveCashClosureRequest $request,
        CashClosure $closure,
        ApproveCashClosureAction $action
    ): RedirectResponse {
        $approve = $request->boolean('approve');
        $reason = $request->filled('reason') ? $request->string('reason')->toString() : null;

        $action->handle($closure, $request->user(), $approve, $reason);

        $statusText = $approve
            ? 'disetujui. Kas sebesar Rp'.number_format($closure->physical_ending_balance, 0, ',', '.').' berhasil dipindahkan ke Brankas Utama (Akun 102)'
            : 'ditolak';

        return redirect()->route('admin.cash-closures.index')->with('success', "Laporan penutupan kas loket berhasil {$statusText}.");
    }
}
