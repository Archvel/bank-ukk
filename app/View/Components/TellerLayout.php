<?php

namespace App\View\Components;

use App\Actions\CloseCashAction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;
use Illuminate\View\View;

class TellerLayout extends Component
{
    public int $drawerBalance = 0;

    public function __construct(
        public ?string $title = null,
        ?int $drawerBalance = null
    ) {
        if ($drawerBalance !== null) {
            $this->drawerBalance = $drawerBalance;
        } elseif (Auth::check()) {
            try {
                $user = Auth::user();
                if ($user instanceof User) {
                    $preview = app(CloseCashAction::class)->preview($user);
                    $this->drawerBalance = (int) $preview['system_ending_balance'];
                }
            } catch (\Throwable $e) {
                $this->drawerBalance = 0;
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
        ]);
    }
}
