<?php

namespace App\View\Components;

use App\Enums\CashClosureStatus;
use App\Models\CashClosure;
use Illuminate\View\Component;
use Illuminate\View\View;

class AdminLayout extends Component
{
    public int $pendingClosureCount = 0;

    public function __construct(
        public ?string $title = null,
        public ?string $breadcrumb = null,
        public ?string $header = null
    ) {
        $this->pendingClosureCount = CashClosure::where('status', CashClosureStatus::Submitted)->count();
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.admin', [
            'pendingClosureCount' => $this->pendingClosureCount,
        ]);
    }
}
