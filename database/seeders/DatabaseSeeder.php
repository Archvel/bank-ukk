<?php

namespace Database\Seeders;

use App\Enums\JournalEntryType;
use App\Enums\MemberAccountStatus;
use App\Enums\Role;
use App\Enums\SaverStatus;
use App\Enums\TransactionType;
use App\Models\JournalEntry;
use App\Models\Member;
use App\Models\Saver;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(AccountSeeder::class);

        // 1. Admin & Supervisor User
        $admin = User::firstOrCreate(
            ['email' => 'admin@bankmini.test'],
            [
                'name' => 'Administrator & Supervisor',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'role' => Role::Admin,
                'is_active' => true,
            ]
        );

        // 2. Piket Teller User
        $teller = User::firstOrCreate(
            ['email' => 'teller@bankmini.test'],
            [
                'name' => 'Piket Teller 1',
                'username' => 'teller1',
                'password' => Hash::make('password'),
                'role' => Role::Teller,
                'is_active' => true,
            ]
        );

        // 3. Member User (Inactive, not yet activated into saver)
        $memberUser = User::firstOrCreate(
            ['email' => 'budi@bankmini.test'],
            [
                'name' => 'Budi Santoso',
                'username' => 'budi',
                'password' => Hash::make('password'),
                'role' => Role::Nasabah,
                'is_active' => true,
            ]
        );

        Member::firstOrCreate(
            ['user_id' => $memberUser->id],
            [
                'student_name' => 'Budi Santoso',
                'graduation_year' => '2026',
                'account_status' => MemberAccountStatus::Inactive,
            ]
        );

        // 4. Saver User (Already activated saver with initial balance)
        $saverUser = User::firstOrCreate(
            ['email' => 'siti@bankmini.test'],
            [
                'name' => 'Siti Rahma',
                'username' => 'siti',
                'password' => Hash::make('password'),
                'role' => Role::Nasabah,
                'is_active' => true,
            ]
        );

        $saverMember = Member::firstOrCreate(
            ['user_id' => $saverUser->id],
            [
                'student_name' => 'Siti Rahma',
                'graduation_year' => '2026',
                'account_status' => MemberAccountStatus::Active,
            ]
        );

        $saver = Saver::firstOrCreate(
            ['account_number' => 'BM20260001'],
            [
                'user_id' => $saverUser->id,
                'member_id' => $saverMember->id,
                'pin' => Hash::make('123456'),
                'qr_code_token' => 'QR-BM20260001',
                'balance' => 50000,
                'status' => SaverStatus::Active,
                'opened_at' => now(),
            ]
        );

        // Seed initial transaction for the saver
        $transaction = Transaction::firstOrCreate(
            ['transaction_number' => 'TRX-20261015-0001'],
            [
                'saver_id' => $saver->id,
                'teller_id' => $teller->id,
                'type' => TransactionType::Deposit,
                'amount' => 50000,
                'opening_balance' => 0,
                'closing_balance' => 50000,
                'notes' => 'Setoran awal pembukaan rekening',
            ]
        );

        // Seed double-entry journal for this deposit:
        // Debit: Kas Teller (101) 50.000
        // Credit: Simpanan Tabungan Nasabah (201) 50.000
        JournalEntry::firstOrCreate(
            [
                'transaction_id' => $transaction->id,
                'entry_number' => 'JRN-20261015-0001',
                'type' => JournalEntryType::Debit,
            ],
            [
                'account_code' => '101',
                'amount' => 50000,
                'description' => 'Setoran awal kas teller dari '.$saverUser->name,
                'entry_date' => today(),
            ]
        );

        JournalEntry::firstOrCreate(
            [
                'transaction_id' => $transaction->id,
                'entry_number' => 'JRN-20261015-0001',
                'type' => JournalEntryType::Credit,
            ],
            [
                'account_code' => '201',
                'amount' => 50000,
                'description' => 'Setoran awal simpanan tabungan '.$saverUser->name,
                'entry_date' => today(),
            ]
        );
    }
}
