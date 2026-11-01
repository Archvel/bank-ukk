<?php

namespace Database\Seeders;

use App\Enums\AccountCategory;
use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            [
                'code' => '101',
                'name' => 'Kas Teller Loket',
                'category' => AccountCategory::Asset,
            ],
            [
                'code' => '102',
                'name' => 'Kas Brankas Utama',
                'category' => AccountCategory::Asset,
            ],
            [
                'code' => '201',
                'name' => 'Simpanan Tabungan Nasabah',
                'category' => AccountCategory::Liability,
            ],
        ];

        foreach ($accounts as $account) {
            Account::updateOrCreate(['code' => $account['code']], $account);
        }
    }
}
