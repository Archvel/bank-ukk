<?php

namespace App\View\Components;

use App\Actions\CloseCashAction;
use App\Actions\SetOpeningCashAction;
use App\Enums\CashClosureStatus;
use App\Models\CashClosure;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;
use Illuminate\View\View;

class TellerLayout extends Component
{
    public int $drawerBalance = 0;

    public int $openingBalance = 0;

    public int $suggestedOpeningBalance = 0;

    public bool $hasOpenedSession = false;

    public bool $isClosureSubmitted = false;

    public bool $isWithinOperatingHours = true;

    public string $operatingHoursLabel = '07:00 - 15:30 WIB';

    public ?CashClosure $todayClosure = null;

    public function __construct(
        public ?string $title = null,
        ?int $drawerBalance = null
    ) {
        if (Auth::check()) {
            try {
                $user = Auth::user();
                if ($user instanceof User) {
                    $preview = app(CloseCashAction::class)->preview($user);
                    $this->drawerBalance = $drawerBalance ?? (int) $preview['system_ending_balance'];
                    $this->openingBalance = (int) $preview['opening_balance'];
                    $this->hasOpenedSession = (bool) ($preview['has_session_opened'] ?? false);
                    $this->suggestedOpeningBalance = app(SetOpeningCashAction::class)->getSuggestedOpeningBalance($user);
                    $this->isWithinOperatingHours = app(SetOpeningCashAction::class)->isWithinOperatingHours();

                    $this->todayClosure = CashClosure::where('teller_id', $user->id)
                        ->whereDate('closure_date', today())
                        ->first();

                    $this->isClosureSubmitted = $this->todayClosure && in_array($this->todayClosure->status, [
                        CashClosureStatus::Submitted,
                        CashClosureStatus::Approved,
                    ]);
                }
            } catch (\Throwable $e) {
                $this->drawerBalance = $drawerBalance ?? 0;
            }
        }
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.teller', [
            'drawerBalance' => $this->drawerBalance,
            'openingBalance' => $this->openingBalance,
            'suggestedOpeningBalance' => $this->suggestedOpeningBalance,
            'hasOpenedSession' => $this->hasOpenedSession,
            'isClosureSubmitted' => $this->isClosureSubmitted,
            'isWithinOperatingHours' => $this->isWithinOperatingHours,
            'operatingHoursLabel' => $this->operatingHoursLabel,
            'todayClosure' => $this->todayClosure,
        ]);
    }
}
