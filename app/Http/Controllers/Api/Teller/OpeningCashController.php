<?php

namespace App\Http\Controllers\Api\Teller;

use App\Actions\CloseCashAction;
use App\Actions\SetOpeningCashAction;
use App\Enums\CashClosureStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\SetOpeningCashRequest;
use App\Models\CashClosure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OpeningCashController extends Controller
{
    /**
     * Get opening cash status for today's session.
     */
    public function show(
        Request $request,
        CloseCashAction $closeCashAction,
        SetOpeningCashAction $setOpeningCashAction
    ): JsonResponse {
        $teller = $request->user();
        $preview = $closeCashAction->preview($teller);

        $todayClosure = CashClosure::where('teller_id', $teller->id)
            ->whereDate('closure_date', today())
            ->first();

        $suggested = $setOpeningCashAction->getSuggestedOpeningBalance($teller);
        $isLocked = $todayClosure && in_array($todayClosure->status, [
            CashClosureStatus::Submitted,
            CashClosureStatus::Approved,
        ]);
        $isWithinOperatingHours = $setOpeningCashAction->isWithinOperatingHours();

        return response()->json([
            'success' => true,
            'opening_balance' => (int) $preview['opening_balance'],
            'formatted_opening_balance' => 'Rp'.number_format((int) $preview['opening_balance'], 0, ',', '.'),
            'suggested_opening_balance' => $suggested,
            'formatted_suggested_opening_balance' => 'Rp'.number_format($suggested, 0, ',', '.'),
            'has_session_opened' => (bool) ($preview['has_session_opened'] ?? false),
            'is_locked' => $isLocked,
            'is_within_operating_hours' => $isWithinOperatingHours,
            'operating_hours' => [
                'start' => SetOpeningCashAction::OPERATING_START,
                'end' => SetOpeningCashAction::OPERATING_END,
                'timezone' => 'WIB',
                'label' => '07:00 - 15:30 WIB',
            ],
            'status' => $todayClosure?->status?->value ?? 'not_started',
            'notes' => $todayClosure?->notes,
            'drawer_balance' => (int) $preview['system_ending_balance'],
            'formatted_drawer_balance' => 'Rp'.number_format((int) $preview['system_ending_balance'], 0, ',', '.'),
        ]);
    }

    /**
     * Store or update opening cash for today's session.
     */
    public function store(
        SetOpeningCashRequest $request,
        SetOpeningCashAction $action,
        CloseCashAction $closeCashAction
    ): JsonResponse {
        $teller = $request->user();
        $openingBalance = $request->integer('opening_balance');
        $notes = $request->filled('notes') ? $request->string('notes')->toString() : null;

        $closure = $action->handle($teller, $openingBalance, $notes);
        $preview = $closeCashAction->preview($teller);

        $formattedNominal = 'Rp'.number_format($openingBalance, 0, ',', '.');

        return response()->json([
            'success' => true,
            'message' => "Kas awal sesi loket berhasil disimpan sebesar {$formattedNominal}.",
            'closure' => [
                'id' => $closure->id,
                'opening_balance' => (int) $closure->opening_balance,
                'formatted_opening_balance' => $formattedNominal,
                'system_ending_balance' => (int) $preview['system_ending_balance'],
                'formatted_system_ending_balance' => 'Rp'.number_format((int) $preview['system_ending_balance'], 0, ',', '.'),
                'status' => $closure->status->value,
                'notes' => $closure->notes,
            ],
            'new_drawer_balance' => (int) $preview['system_ending_balance'],
        ], Response::HTTP_OK);
    }
}
