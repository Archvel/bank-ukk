<?php

namespace App\Http\Controllers\Api\Admin;

use App\Actions\ApproveCashClosureAction;
use App\Enums\CashClosureStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveCashClosureRequest;
use App\Http\Resources\CashClosureResource;
use App\Models\CashClosure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CashClosureApprovalController extends Controller
{
    /**
     * Display a listing of pending cash closures.
     */
    public function pending(): AnonymousResourceCollection
    {
        $closures = CashClosure::with(['teller', 'approver'])
            ->where('status', CashClosureStatus::Submitted)
            ->latest('closure_date')
            ->paginate(15);

        return CashClosureResource::collection($closures);
    }

    /**
     * Display a listing of cash closure disputes (where physical cash does not match system).
     */
    public function disputes(): AnonymousResourceCollection
    {
        $closures = CashClosure::with(['teller', 'approver'])
            ->where('difference', '!=', 0)
            ->latest('closure_date')
            ->paginate(15);

        return CashClosureResource::collection($closures);
    }

    /**
     * Display detail of a specific closure.
     */
    public function show(CashClosure $closure): JsonResponse
    {
        return response()->json([
            'closure' => new CashClosureResource($closure->load(['teller', 'approver', 'journalEntries'])),
        ]);
    }

    /**
     * Approve or reject a cash closure.
     */
    public function process(
        ApproveCashClosureRequest $request,
        CashClosure $closure,
        ApproveCashClosureAction $action
    ): JsonResponse {
        $approve = $request->boolean('approve');
        $reason = $request->filled('reason') ? $request->string('reason')->toString() : null;

        $processedClosure = $action->handle($closure, $request->user(), $approve, $reason);

        $actionWord = $approve ? 'disetujui dan kas berhasil dipindahkan ke Brankas Utama' : 'ditolak';

        return response()->json([
            'message' => "Laporan penutupan kas berhasil {$actionWord}.",
            'closure' => new CashClosureResource($processedClosure->load(['teller', 'approver', 'journalEntries'])),
        ]);
    }
}
