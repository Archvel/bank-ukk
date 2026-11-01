<?php

namespace App\Http\Controllers\Admin;

use App\Actions\WithdrawAction;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(): View
    {
        $minMaintainingBalance = WithdrawAction::MINIMUM_MAINTAINING_BALANCE;
        $minInitialDeposit = 10000;
        $accountFormat = 'BM{YYYY}{SEQ:4}';
        $dailyWithdrawLimit = 500000;

        return view('admin.settings.index', compact(
            'minMaintainingBalance',
            'minInitialDeposit',
            'accountFormat',
            'dailyWithdrawLimit'
        ));
    }

    public function update(Request $request): RedirectResponse
    {
        return back()->with('success', 'Konfigurasi sistem berhasil diperbarui dan disimpan secara global.');
    }
}
