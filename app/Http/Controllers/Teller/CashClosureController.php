<?php

namespace App\Http\Controllers\Teller;

use App\Actions\CloseCashAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitCashClosureRequest;
use App\Models\CashClosure;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CashClosureController extends Controller
{
    /**
     * Display the Daily Cash Closure (Penutupan Kas Harian).
     */
    public function index(Request $request, CloseCashAction $action): View
    {
        $teller = $request->user() ?: Auth::user();

        // 1. Get real-time formula calculations
        $preview = $action->preview($teller);

        // 2. Check if a closure report for today was already submitted
        $existingClosure = CashClosure::with(['teller', 'approver'])
            ->where('teller_id', $teller->id)
            ->whereDate('closure_date', today())
            ->first();

        return view('teller.cash-closure.index', compact(
            'preview',
            'existingClosure'
        ));
    }

    /**
     * Preview calculation via JSON API.
     */
    public function calculate(Request $request, CloseCashAction $action): JsonResponse
    {
        $teller = $request->user() ?: Auth::user();
        $preview = $action->preview($teller);

        return response()->json([
            'success' => true,
            'preview' => $preview,
            'formatted_opening_balance' => 'Rp'.number_format($preview['opening_balance'], 0, ',', '.'),
            'formatted_total_deposit' => 'Rp'.number_format($preview['total_deposit'], 0, ',', '.'),
            'formatted_total_withdrawal' => 'Rp'.number_format($preview['total_withdrawal'], 0, ',', '.'),
            'formatted_system_ending_balance' => 'Rp'.number_format($preview['system_ending_balance'], 0, ',', '.'),
        ]);
    }

    /**
     * Submit physical cash count and create daily cash closure draft.
     */
    public function store(SubmitCashClosureRequest $request, CloseCashAction $action): JsonResponse|RedirectResponse
    {
        $teller = $request->user();
        $physicalBalance = $request->integer('physical_ending_balance');
        $notes = $request->filled('notes') ? $request->string('notes')->toString() : null;

        // Verify balance rule: physical cash must equal system balance (zero difference)
        $preview = $action->preview($teller);
        $systemEndingBalance = (int) $preview['system_ending_balance'];
        $difference = $physicalBalance - $systemEndingBalance;

        if ($difference !== 0) {
            $diffText = 'Rp'.number_format(abs($difference), 0, ',', '.');
            $errorMessage = $difference > 0
                ? "Pengajuan penutupan kas ditolak. Terdeteksi LEBIH KAS sebesar {$diffText}. Sesuai SOP integritas pembukuan, uang fisik di laci dan sistem wajib seimbang (selisih Rp 0)."
                : "Pengajuan penutupan kas ditolak. Terdeteksi KURANG KAS sebesar {$diffText}. Sesuai SOP integritas pembukuan, uang fisik di laci dan sistem wajib seimbang (selisih Rp 0).";

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'difference' => $difference,
                    'is_balanced' => false,
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            return back()->withInput()->with('error', $errorMessage);
        }

        $closure = $action->handle($teller, $physicalBalance, $notes);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Laporan penutupan kas harian berhasil disimpan dengan status SEIMBANG (BALANCE) dan diajukan ke Brankas Utama.',
                'closure' => [
                    'id' => $closure->id,
                    'closure_date' => $closure->closure_date?->format('d/m/Y'),
                    'system_ending_balance' => (int) $closure->system_ending_balance,
                    'physical_ending_balance' => (int) $closure->physical_ending_balance,
                    'status' => $closure->status->value,
                ],
            ], Response::HTTP_CREATED);
        }

        return redirect()->route('teller.cash-closure.index')->with('success', 'Laporan penutupan kas harian berhasil diajukan ke Brankas Utama (Menunggu ACC Administrator).');
    }
}
