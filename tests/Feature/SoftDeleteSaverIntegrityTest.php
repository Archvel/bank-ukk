<?php

namespace Tests\Feature;

use App\Actions\DepositAction;
use App\Enums\CashClosureStatus;
use App\Enums\MemberAccountStatus;
use App\Enums\Role;
use App\Enums\SaverStatus;
use App\Models\CashClosure;
use App\Models\Member;
use App\Models\Saver;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\AccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SoftDeleteSaverIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $teller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AccountSeeder::class);

        $this->admin = User::factory()->create([
            'role' => Role::Admin,
            'is_active' => true,
        ]);

        $this->teller = User::factory()->create([
            'role' => Role::Teller,
            'is_active' => true,
        ]);
    }

    public function test_transactions_remain_valid_and_appear_in_reports_even_after_saver_is_soft_deleted(): void
    {
        // 1. Setup a saver
        $user = User::factory()->create(['role' => Role::Nasabah]);
        $member = Member::create([
            'user_id' => $user->id,
            'student_name' => 'Siswa Dihapus',
            'graduation_year' => '2026',
            'account_status' => MemberAccountStatus::Active,
        ]);

        $saver = Saver::create([
            'user_id' => $user->id,
            'member_id' => $member->id,
            'account_number' => 'BM20269999',
            'pin' => Hash::make('123456'),
            'qr_code_token' => 'QR-BM20269999',
            'balance' => 0,
            'status' => SaverStatus::Active,
        ]);

        // 2. Perform a deposit on this saver today
        $transaction = app(DepositAction::class)->handle(
            teller: $this->teller,
            amount: 50000,
            saver: $saver
        );

        // 3. Soft-delete the saver (e.g. student leaves school)
        $saver->delete();
        $this->assertSoftDeleted('savers', ['id' => $saver->id]);

        // 4. Assert transaction still exists in database and relation works via withTrashed()
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'amount' => 50000,
        ]);

        $loadedTransaction = Transaction::find($transaction->id);
        $this->assertNotNull($loadedTransaction->saver);
        $this->assertSame('BM20269999', $loadedTransaction->saver->account_number);

        // 5. Submit cash closure by teller
        $closure = CashClosure::create([
            'teller_id' => $this->teller->id,
            'closure_date' => today(),
            'opening_balance' => 0,
            'total_deposit' => 50000,
            'total_withdrawal' => 0,
            'system_ending_balance' => 50000,
            'physical_ending_balance' => 50000,
            'difference' => 0,
            'status' => CashClosureStatus::Submitted,
        ]);

        // 6. Admin accesses daily teller report
        $reportResponse = $this->actingAs($this->admin, 'sanctum')->getJson(
            route('reports.daily-teller', $closure)
        );

        $reportResponse->assertOk()
            ->assertJsonPath('summary.total_transactions_count', 1)
            ->assertJsonPath('summary.formatted_total_deposit', 'Rp50.000')
            ->assertJsonPath('transactions.0.transaction_number', $transaction->transaction_number)
            ->assertJsonPath('transactions.0.saver.account_number', 'BM20269999');
    }
}
