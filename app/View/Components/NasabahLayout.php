<?php

namespace App\View\Components;

use App\Models\Saver;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;
use Illuminate\View\View;

class NasabahLayout extends Component
{
    public ?Saver $saver = null;

    public bool $hasActiveAccount = false;

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
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.nasabah', [
            'saver' => $this->saver,
            'hasActiveAccount' => $this->hasActiveAccount,
        ]);
    }
}
