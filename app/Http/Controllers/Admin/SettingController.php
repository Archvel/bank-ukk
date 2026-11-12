<?php

namespace App\Http\Controllers\Admin;

use App\Actions\WithdrawAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSettingsRequest;
use App\Models\SystemSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SettingController extends Controller
{
    public function index(): View
    {
        $minMaintainingBalance = WithdrawAction::MINIMUM_MAINTAINING_BALANCE;
        $minInitialDeposit = SystemSetting::getInt('min_initial_deposit', SystemSetting::DEFAULT_MIN_INITIAL_DEPOSIT);
        $accountFormat = 'BM{YYYY}{SEQ:4}';
        $dailyWithdrawLimit = SystemSetting::getInt('daily_withdraw_limit', SystemSetting::DEFAULT_DAILY_WITHDRAW_LIMIT);
        $scannerAutoSubmit = SystemSetting::getBool('scanner_auto_submit', true);
        $scannerBeepSound = SystemSetting::getBool('scanner_beep_sound', true);

        return view('admin.settings.index', compact(
            'minMaintainingBalance',
            'minInitialDeposit',
            'accountFormat',
            'dailyWithdrawLimit',
            'scannerAutoSubmit',
            'scannerBeepSound'
        ));
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        SystemSetting::setValues([
            'min_initial_deposit' => (int) $validated['min_initial_deposit'],
            'daily_withdraw_limit' => (int) $validated['daily_withdraw_limit'],
            'scanner_auto_submit' => (bool) $validated['scanner_auto_submit'],
            'scanner_beep_sound' => (bool) $validated['scanner_beep_sound'],
        ]);

        return back()->with('success', 'Konfigurasi sistem berhasil diperbarui dan disimpan secara global.');
    }
}
