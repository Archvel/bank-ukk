<?php

use App\Http\Controllers\Admin\AuditJournalController as AdminAuditJournalController;
use App\Http\Controllers\Admin\CashClosureController as AdminCashClosureController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MemberController as AdminMemberController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\SaverController as AdminSaverController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Nasabah\DashboardController as NasabahDashboardController;
use App\Http\Controllers\Nasabah\MutationController as NasabahMutationController;
use App\Http\Controllers\Nasabah\PinController as NasabahPinController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Teller\CashClosureController as TellerCashClosureController;
use App\Http\Controllers\Teller\CounterController as TellerCounterController;
use App\Http\Controllers\Teller\OpeningCashController as TellerOpeningCashController;
use App\Http\Controllers\Teller\TransactionHistoryController as TellerTransactionHistoryController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    if (! Auth::check()) {
        return view('landing');
    }

    /** @var User|null $user */
    $user = $request->user();

    if ($user?->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    if ($user?->isTeller()) {
        return redirect()->route('teller.counter');
    }

    if ($user?->isNasabah()) {
        return redirect()->route('nasabah.dashboard');
    }

    return redirect()->route('dashboard');
})->name('landing');

Route::get('/dashboard', function (Request $request) {
    /** @var User|null $user */
    $user = $request->user();

    if ($user?->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    if ($user?->isTeller()) {
        return redirect()->route('teller.counter');
    }

    if ($user?->isNasabah()) {
        return redirect()->route('nasabah.dashboard');
    }

    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // 1. Manajemen Pengguna
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    Route::patch('/users/{user}/toggle-active', [AdminUserController::class, 'toggleActive'])->name('users.toggle-active');

    // 2. Data Calon Penabung (Members)
    Route::get('/members/template-import', [AdminMemberController::class, 'downloadImportTemplate'])->name('members.import-template');
    Route::post('/members/import', [AdminMemberController::class, 'importExcel'])->name('members.import');
    Route::post('/members/promote', [AdminMemberController::class, 'promote'])->name('members.promote');
    Route::post('/members/{member}/promote', [AdminMemberController::class, 'promoteMember'])->name('members.promote-single');
    Route::get('/members', [AdminMemberController::class, 'index'])->name('members.index');
    Route::post('/members', [AdminMemberController::class, 'store'])->name('members.store');
    Route::put('/members/{member}', [AdminMemberController::class, 'update'])->name('members.update');
    Route::delete('/members/{member}', [AdminMemberController::class, 'destroy'])->name('members.destroy');

    // 3. Master Savers / Rekening
    Route::get('/savers', [AdminSaverController::class, 'index'])->name('savers.index');
    Route::get('/savers/{id}', [AdminSaverController::class, 'show'])->name('savers.show');
    Route::post('/savers/{id}/reset-pin', [AdminSaverController::class, 'resetPin'])->name('savers.reset-pin');
    Route::delete('/savers/{id}', [AdminSaverController::class, 'destroy'])->name('savers.destroy');
    Route::post('/savers/{id}/restore', [AdminSaverController::class, 'restore'])->name('savers.restore');

    // 4. Verifikasi Kas Harian
    Route::get('/cash-closures', [AdminCashClosureController::class, 'index'])->name('cash-closures.index');
    Route::get('/cash-closures/{closure}', [AdminCashClosureController::class, 'show'])->name('cash-closures.show');
    Route::post('/cash-closures/{closure}/process', [AdminCashClosureController::class, 'process'])->name('cash-closures.process');

    // 5. Audit Jurnal Akuntansi
    Route::get('/audit-journals', [AdminAuditJournalController::class, 'index'])->name('audit-journals.index');

    // 6. Laporan Transaksi & PDF
    Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');

    // 7. Konfigurasi Sistem
    Route::get('/settings', [AdminSettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [AdminSettingController::class, 'update'])->name('settings.update');
});

// Teller Routes
Route::middleware(['auth', 'role:teller'])->prefix('teller')->name('teller.')->group(function () {
    // 1. Meja Layanan Loket (POS Counter)
    Route::get('/counter', [TellerCounterController::class, 'index'])->name('counter');
    Route::get('/lookup', [TellerCounterController::class, 'lookup'])->name('lookup');
    Route::post('/transactions/deposit', [TellerCounterController::class, 'deposit'])->name('transactions.deposit.web');
    Route::post('/transactions/withdraw', [TellerCounterController::class, 'withdraw'])->name('transactions.withdraw.web');

    // 2. Riwayat Transaksi Hari Ini
    Route::get('/transactions', [TellerTransactionHistoryController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/today', [TellerTransactionHistoryController::class, 'index']);
    Route::get('/transactions/{transaction}/receipt', [TellerTransactionHistoryController::class, 'receipt'])->name('transactions.receipt');

    // 3. Penutupan Kas Harian (Cash Closure)
    Route::get('/cash-closure', [TellerCashClosureController::class, 'index'])->name('cash-closure.index');
    Route::get('/cash-closure/calculate', [TellerCashClosureController::class, 'calculate'])->name('cash-closure.calculate.web');
    Route::post('/cash-closure', [TellerCashClosureController::class, 'store'])->name('cash-closure.store');

    // 4. Kas Awal Sesi Teller (Opening Cash)
    Route::get('/opening-cash', [TellerOpeningCashController::class, 'show'])->name('opening-cash.show');
    Route::post('/opening-cash', [TellerOpeningCashController::class, 'store'])->name('opening-cash.store');
});

// Nasabah / Siswa Web Routes
Route::middleware(['auth', 'role:nasabah'])->prefix('nasabah')->name('nasabah.')->group(function () {
    Route::get('/dashboard', [NasabahDashboardController::class, 'index'])->name('dashboard');
    Route::get('/mutations', [NasabahMutationController::class, 'index'])->name('mutations.index');
    Route::get('/pin', [NasabahPinController::class, 'index'])->name('pin.index');
    Route::post('/pin/request', [NasabahPinController::class, 'requestToken'])->name('pin.request');
    Route::post('/pin/confirm', [NasabahPinController::class, 'confirmReset'])->name('pin.confirm');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
