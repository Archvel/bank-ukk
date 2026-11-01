<?php

namespace App\Http\Controllers\Api\Teller;

use App\Actions\CloseCashAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitCashClosureRequest;
use App\Http\Resources\CashClosureResource;
use App\Models\CashClosure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CashClosureController extends Controller
{
    /**
     * Preview real-time calculated system ending balance for today.
     */
    public function calculate(Request $request, CloseCashAction $action): JsonResponse
    {
        $teller = $request->user();
        $preview = $action->preview($teller);

        return response()->json([
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
    public function submit(SubmitCashClosureRequest $request, CloseCashAction $action): JsonResponse
    {
        $teller = $request->user();
        $physicalBalance = $request->integer('physical_ending_balance');
        $notes = $request->filled('notes') ? $request->string('notes')->toString() : null;

        $closure = $action->handle($teller, $physicalBalance, $notes);

        $hasDifference = ($closure->difference !== 0);
        $message = $hasDifference
            ? 'Draf penutupan kas berhasil disimpan. Perhatian: Terdeteksi selisih sebesar Rp'.number_format(abs($closure->difference), 0, ',', '.').'.'
            : 'Draf penutupan kas berhasil disimpan dengan status seimbang (balance).';

        return response()->json([
            'message' => $message,
            'closure' => new CashClosureResource($closure->load(['teller', 'approver'])),
        ], Response::HTTP_CREATED);
    }

    /**
     * Get today's closure record for the current teller.
     */
    public function today(Request $request): JsonResponse
    {
        $teller = $request->user();
        $closure = CashClosure::where('teller_id', $teller->id)
            ->whereDate('closure_date', today())
            ->first();

        if (! $closure) {
            return response()->json([
                'closure' => null,
                'message' => 'Belum ada draf penutupan kas yang diajukan hari ini.',
            ]);
        }

        return response()->json([
            'closure' => new CashClosureResource($closure->load(['teller', 'approver'])),
        ]);
    }
}
