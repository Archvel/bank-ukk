<?php

namespace App\Actions;

use App\Enums\JournalEntryType;
use App\Enums\TransactionType;
use App\Models\CashClosure;
use App\Models\JournalEntry;
use App\Models\Transaction;

class CreateJournalEntryAction
{
    /**
     * Record paired double-entry journal rows for a transaction.
     *
     * @return array<int, JournalEntry>
     */
    public function recordTransaction(Transaction $transaction): array
    {
        $date = today();
        $entryNumber = JournalEntry::generateNextNumber($date);

        $saverName = $transaction->saver?->user?->name ?? 'Nasabah';

        if ($transaction->type === TransactionType::Deposit) {
            // Setoran Tunai:
            // Debet: Kas Teller (101)
            // Kredit: Simpanan Tabungan Nasabah (201)
            $debit = JournalEntry::create([
                'transaction_id' => $transaction->id,
                'entry_number' => $entryNumber,
                'account_code' => '101',
                'type' => JournalEntryType::Debit,
                'amount' => $transaction->amount,
                'description' => "Penerimaan setoran kas loket ({$transaction->transaction_number}) dari {$saverName}",
                'entry_date' => $date,
            ]);

            $credit = JournalEntry::create([
                'transaction_id' => $transaction->id,
                'entry_number' => $entryNumber,
                'account_code' => '201',
                'type' => JournalEntryType::Credit,
                'amount' => $transaction->amount,
                'description' => "Penambahan saldo tabungan nasabah ({$transaction->transaction_number}) {$saverName}",
                'entry_date' => $date,
            ]);

            return [$debit, $credit];
        }

        // Penarikan Tunai:
        // Debet: Simpanan Tabungan Nasabah (201)
        // Kredit: Kas Teller (101)
        $debit = JournalEntry::create([
            'transaction_id' => $transaction->id,
            'entry_number' => $entryNumber,
            'account_code' => '201',
            'type' => JournalEntryType::Debit,
            'amount' => $transaction->amount,
            'description' => "Penarikan tabungan nasabah ({$transaction->transaction_number}) {$saverName}",
            'entry_date' => $date,
        ]);

        $credit = JournalEntry::create([
            'transaction_id' => $transaction->id,
            'entry_number' => $entryNumber,
            'account_code' => '101',
            'type' => JournalEntryType::Credit,
            'amount' => $transaction->amount,
            'description' => "Pengeluaran uang kas teller ({$transaction->transaction_number}) untuk {$saverName}",
            'entry_date' => $date,
        ]);

        return [$debit, $credit];
    }

    /**
     * Record vault transfer double-entry rows upon supervisor cash closure approval.
     *
     * @return array<int, JournalEntry>
     */
    public function recordVaultTransfer(CashClosure $closure): array
    {
        $date = today();
        $entryNumber = JournalEntry::generateNextNumber($date);

        $tellerName = $closure->teller?->name ?? 'Teller';
        $amountToTransfer = $closure->physical_ending_balance;

        if ($amountToTransfer <= 0) {
            return [];
        }

        // Pemindahan Kas ke Brankas Utama:
        // Debet: Kas Brankas Utama (102)
        // Kredit: Kas Teller Loket (101)
        $debit = JournalEntry::create([
            'cash_closure_id' => $closure->id,
            'entry_number' => $entryNumber,
            'account_code' => '102',
            'type' => JournalEntryType::Debit,
            'amount' => $amountToTransfer,
            'description' => "Penerimaan fisik kas penutupan harian teller {$tellerName} ke Brankas Utama",
            'entry_date' => $date,
        ]);

        $credit = JournalEntry::create([
            'cash_closure_id' => $closure->id,
            'entry_number' => $entryNumber,
            'account_code' => '101',
            'type' => JournalEntryType::Credit,
            'amount' => $amountToTransfer,
            'description' => "Penyerahan saldo kas fisik laci teller {$tellerName} ke Brankas Utama",
            'entry_date' => $date,
        ]);

        return [$debit, $credit];
    }
}
