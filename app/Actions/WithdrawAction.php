<?php

namespace App\Actions;

use App\Enums\SaverStatus;
use App\Enums\TransactionType;
use App\Models\Saver;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class WithdrawAction
{
    public const MINIMUM_MAINTAINING_BALANCE = 10000;

    public function __construct(
        protected CreateJournalEntryAction $createJournalEntryAction
    ) {}

    /**
     * Execute a cash withdrawal transaction.
     *
     * @throws ValidationException
     */
    public function handle(
        User $teller,
        Saver $saver,
        string $pin,
        int $amount,
        ?string $notes = null
    ): Transaction {
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Nominal penarikan harus lebih besar dari 0.',
            ]);
        }

        // Verify PIN
        if (! Hash::check($pin, $saver->pin)) {
            throw ValidationException::withMessages([
                'pin' => 'Otorisasi gagal: PIN 6-digit keamanan nasabah tidak cocok.',
            ]);
        }

        return DB::transaction(function () use ($teller, $saver, $amount, $notes) {
            /** @var Saver $lockedSaver */
            $lockedSaver = Saver::where('id', $saver->id)->lockForUpdate()->firstOrFail();

            if ($lockedSaver->status !== SaverStatus::Active) {
                throw ValidationException::withMessages([
                    'saver' => 'Akun penabung sedang tidak aktif atau terblokir.',
                ]);
            }

            $openingBalance = $lockedSaver->balance;
            $closingBalance = $openingBalance - $amount;

            // Enforce minimum maintaining balance rule
            if ($closingBalance < self::MINIMUM_MAINTAINING_BALANCE) {
                $formattedBalance = number_format($openingBalance, 0, ',', '.');
                $formattedMin = number_format(self::MINIMUM_MAINTAINING_BALANCE, 0, ',', '.');

                throw ValidationException::withMessages([
                    'amount' => "Penarikan ditolak: Sisa saldo setelah penarikan minimal Rp{$formattedMin}. Saldo saat ini: Rp{$formattedBalance}.",
                ]);
            }

            $lockedSaver->update([
                'balance' => $closingBalance,
            ]);

            $dateStr = now()->format('Ymd');
            $count = Transaction::whereDate('created_at', today())->count() + 1;
            $transactionNumber = sprintf('TRX-%s-%04d', $dateStr, $count);

            $transaction = Transaction::create([
                'transaction_number' => $transactionNumber,
                'saver_id' => $lockedSaver->id,
                'teller_id' => $teller->id,
                'type' => TransactionType::Withdrawal,
                'amount' => $amount,
                'opening_balance' => $openingBalance,
                'closing_balance' => $closingBalance,
                'notes' => $notes ?? 'Penarikan tunai loket',
            ]);

            $this->createJournalEntryAction->recordTransaction($transaction);

            return $transaction;
        });
    }
}
