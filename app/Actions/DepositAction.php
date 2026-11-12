<?php

namespace App\Actions;

use App\Enums\SaverStatus;
use App\Enums\TransactionType;
use App\Models\Member;
use App\Models\Saver;
use App\Models\SystemSetting;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class DepositAction
{
    public function __construct(
        protected ActivateMemberAction $activateMemberAction,
        protected CreateJournalEntryAction $createJournalEntryAction
    ) {}

    /**
     * Execute a cash deposit transaction.
     *
     * @throws ValidationException
     */
    public function handle(
        User $teller,
        int $amount,
        ?Member $member = null,
        ?Saver $saver = null,
        ?string $pin = null,
        ?string $notes = null
    ): Transaction {
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Nominal setoran harus lebih besar dari 0.',
            ]);
        }

        return DB::transaction(function () use ($teller, $amount, $member, $saver, $pin, $notes) {
            // Case 1: Initial deposit from an inactive Member (Account Activation)
            if ($member !== null) {
                $minInitialDeposit = SystemSetting::getInt('min_initial_deposit', SystemSetting::DEFAULT_MIN_INITIAL_DEPOSIT);
                if ($amount < $minInitialDeposit) {
                    $formattedMin = number_format($minInitialDeposit, 0, ',', '.');

                    throw ValidationException::withMessages([
                        'amount' => "Setoran awal untuk aktivasi rekening penabung minimal Rp{$formattedMin}.",
                    ]);
                }

                if (empty($pin) || strlen($pin) !== 6 || ! ctype_digit($pin)) {
                    throw ValidationException::withMessages([
                        'pin' => 'PIN keamanan 6-digit wajib disertakan untuk aktivasi rekening baru.',
                    ]);
                }

                $saver = $this->activateMemberAction->handle($member, $pin);
                $notes = $notes ?? 'Setoran awal aktivasi rekening penabung';
            }

            if (! $saver instanceof Saver) {
                throw ValidationException::withMessages([
                    'saver_id' => 'Data penabung tidak ditemukan atau belum valid.',
                ]);
            }

            // Lock saver row to prevent race conditions
            /** @var Saver $lockedSaver */
            $lockedSaver = Saver::where('id', $saver->id)->lockForUpdate()->firstOrFail();

            if ($lockedSaver->status !== SaverStatus::Active) {
                throw ValidationException::withMessages([
                    'saver' => 'Akun penabung sedang tidak aktif atau terblokir.',
                ]);
            }

            // Verify PIN for existing saver if provided
            if ($member === null && $pin !== null) {
                if (! Hash::check($pin, $lockedSaver->pin)) {
                    throw ValidationException::withMessages([
                        'pin' => 'Otorisasi gagal: PIN 6-digit keamanan nasabah tidak cocok.',
                    ]);
                }
            }

            $openingBalance = $lockedSaver->balance;
            $closingBalance = $openingBalance + $amount;

            $lockedSaver->update([
                'balance' => $closingBalance,
            ]);

            $transactionNumber = Transaction::generateNextNumber();

            $transaction = Transaction::create([
                'transaction_number' => $transactionNumber,
                'saver_id' => $lockedSaver->id,
                'teller_id' => $teller->id,
                'type' => TransactionType::Deposit,
                'amount' => $amount,
                'opening_balance' => $openingBalance,
                'closing_balance' => $closingBalance,
                'notes' => $notes ?? 'Setoran tunai loket',
            ]);

            $this->createJournalEntryAction->recordTransaction($transaction);

            return $transaction;
        });
    }
}
