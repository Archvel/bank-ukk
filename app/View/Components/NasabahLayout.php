<?php

namespace App\View\Components;

use App\Actions\GetTellerSessionStatusAction;
use App\Models\Saver;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;
use Illuminate\View\View;

class NasabahLayout extends Component
{
    public ?Saver $saver = null;

    public bool $hasActiveAccount = false;

    /**
     * @var array<string, mixed>
     */
    public array $tellerSessionStatus = [];

    public function __construct(
        public ?string $title = null,
        ?Saver $saver = null
    ) {
        if ($saver !== null) {
            $this->saver = $saver;
            $this->hasActiveAccount = true;
        } elseif (Auth::check()) {
            $user = Auth::user();
            if ($user instanceof User) {
                $this->saver = Saver::where('user_id', $user->id)->first();
                $this->hasActiveAccount = $this->saver !== null;
            }
        }

        try {
            $this->tellerSessionStatus = app(GetTellerSessionStatusAction::class)->handle();
        } catch (\Throwable $e) {
            $this->tellerSessionStatus = [
                'is_open' => false,
                'has_active_session' => false,
                'status_key' => 'unknown',
                'status_title' => 'Informasi Loket',
                'status_badge' => 'INFO LOKET',
                'badge_class' => 'bg-slate-100 text-slate-700 border-slate-300',
                'message' => 'Jam operasional loket: 07:00 - 15:30 WIB.',
                'active_tellers' => [],
                'active_teller_count' => 0,
                'operating_hours' => '07:00 - 15:30 WIB',
                'operating_start' => '07:00',
                'operating_end' => '15:30',
                'is_within_operating_hours' => true,
                'current_time' => now('Asia/Jakarta')->format('H:i').' WIB',
            ];
        }
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.nasabah', [
            'saver' => $this->saver,
            'hasActiveAccount' => $this->hasActiveAccount,
            'tellerSessionStatus' => $this->tellerSessionStatus,
        ]);
    }
}
