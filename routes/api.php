<?php

use App\Actions\WithdrawAction;
use App\Http\Controllers\Api\Admin\AuditJournalController;
use App\Http\Controllers\Api\Admin\CashClosureApprovalController;
use App\Http\Controllers\Api\Admin\MemberController as AdminMemberController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Nasabah\AccountController as NasabahAccountController;
use App\Http\Controllers\Api\Nasabah\PinResetController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\Teller\CashClosureController as TellerCashClosureController;
use App\Http\Controllers\Api\Teller\MemberLookupController as TellerMemberLookupController;
use App\Http\Controllers\Api\Teller\SaverLookupController as TellerSaverLookupController;
use App\Http\Controllers\Api\Teller\TransactionController as TellerTransactionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function (): void {
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
});

Route::prefix('pin-reset')->group(function (): void {
    Route::post('/request', [PinResetController::class, 'requestReset'])->name('pin-reset.request');
    Route::post('/confirm', [PinResetController::class, 'confirmReset'])->name('pin-reset.confirm');
});

/*
|--------------------------------------------------------------------------
| Authenticated Common Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function (): void {
    Route::prefix('auth')->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
    });

    Route::prefix('reports')->group(function (): void {
        Route::get('/receipt/{transaction}', [ReportController::class, 'receipt'])->name('reports.receipt');
        Route::get('/monthly', [ReportController::class, 'monthly'])->name('reports.monthly');
        Route::get('/daily-teller/{closure}', [ReportController::class, 'dailyTeller'])->name('reports.daily-teller');
    });

    /*
    |--------------------------------------------------------------------------
    | Administrator & Supervisor Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function (): void {
        Route::apiResource('users', AdminUserController::class);
        Route::apiResource('members', AdminMemberController::class);

        Route::prefix('audit-journals')->group(function (): void {
            Route::get('/', [AuditJournalController::class, 'index'])->name('audit-journals.index');
            Route::get('/summary', [AuditJournalController::class, 'summary'])->name('audit-journals.summary');
        });

        Route::prefix('cash-closures')->group(function (): void {
            Route::get('/pending', [CashClosureApprovalController::class, 'pending'])->name('cash-closures.pending');
            Route::get('/disputes', [CashClosureApprovalController::class, 'disputes'])->name('cash-closures.disputes');
            Route::get('/{closure}', [CashClosureApprovalController::class, 'show'])->name('cash-closures.show');
            Route::post('/{closure}/process', [CashClosureApprovalController::class, 'process'])->name('cash-closures.process');
        });

        Route::get('/system-settings', function () {
            return response()->json([
                'min_maintaining_balance' => WithdrawAction::MINIMUM_MAINTAINING_BALANCE,
                'min_initial_deposit' => 10000,
                'app_name' => 'E-Teller Bank Mini Sekolah',
            ]);
        })->name('system-settings');
    });

    /*
    |--------------------------------------------------------------------------
    | Piket Teller Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:teller')->prefix('teller')->name('teller.')->group(function (): void {
        Route::prefix('savers')->group(function (): void {
            Route::get('/', [TellerSaverLookupController::class, 'index'])->name('savers.index');
            Route::get('/lookup', [TellerSaverLookupController::class, 'lookup'])->name('savers.lookup');
            Route::get('/{saver}', [TellerSaverLookupController::class, 'show'])->name('savers.show');
        });

        Route::prefix('members')->group(function (): void {
            Route::get('/pending', [TellerMemberLookupController::class, 'pending'])->name('members.pending');
        });

        Route::prefix('transactions')->group(function (): void {
            Route::post('/deposit', [TellerTransactionController::class, 'deposit'])->name('transactions.deposit');
            Route::post('/withdraw', [TellerTransactionController::class, 'withdraw'])->name('transactions.withdraw');
            Route::get('/today', [TellerTransactionController::class, 'today'])->name('transactions.today');
            Route::get('/{transaction}', [TellerTransactionController::class, 'show'])->name('transactions.show');
        });

        Route::prefix('cash-closure')->group(function (): void {
            Route::get('/calculate', [TellerCashClosureController::class, 'calculate'])->name('cash-closure.calculate');
            Route::post('/submit', [TellerCashClosureController::class, 'submit'])->name('cash-closure.submit');
            Route::get('/today', [TellerCashClosureController::class, 'today'])->name('cash-closure.today');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Nasabah / Siswa Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:nasabah')->prefix('nasabah')->name('nasabah.')->group(function (): void {
        Route::get('/account/balance', [NasabahAccountController::class, 'balance'])->name('nasabah.balance');
        Route::get('/account/mutations', [NasabahAccountController::class, 'mutations'])->name('nasabah.mutations');
    });
});
