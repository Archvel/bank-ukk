<?php

namespace Tests\Feature;

use App\Enums\JournalEntryType;
use App\Enums\MemberAccountStatus;
use App\Enums\Role;
use App\Enums\SaverStatus;
use App\Models\JournalEntry;
use App\Models\Member;
use App\Models\Saver;
use App\Models\User;
use Database\Seeders\AccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberActivationAndDepositTest extends TestCase
{
    use RefreshDatabase;

    protected User $teller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AccountSeeder::class);

        $this->teller = User::factory()->create([
            'role' => Role::Teller,
            'is_active' => true,
        ]);
    }

    public function test_initial_deposit_under_10000_is_rejected(): void
    {
        $memberUser = User::factory()->create(['role' => Role::Nasabah]);
        $member = Member::create([
            'user_id' => $memberUser->id,
            'student_name' => 'Calon Siswa',
            'graduation_year' => '2026',
            'account_status' => MemberAccountStatus::Inactive,
        ]);

        $response = $this->actingAs($this->teller, 'sanctum')->postJson(route('teller.transactions.deposit'), [
            'member_id' => $member->id,
            'amount' => 5000,
            'pin' => '123456',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['amount']);
    }

    public function test_initial_deposit_of_10000_or_more_activates_member_into_saver_with_journals(): void
    {
        $memberUser = User::factory()->create(['role' => Role::Nasabah]);
        $member = Member::create([
            'user_id' => $memberUser->id,
            'student_name' => 'Budi Siswa',
            'graduation_year' => '2026',
            'account_status' => MemberAccountStatus::Inactive,
        ]);

        $response = $this->actingAs($this->teller, 'sanctum')->postJson(route('teller.transactions.deposit'), [
            'member_id' => $member->id,
            'amount' => 25000,
            'pin' => '654321',
            'notes' => 'Setoran awal pembukaan rekening',
        ]);

        $response->assertCreated()
            ->assertJsonPath('transaction.amount', 25000)
            ->assertJsonPath('transaction.closing_balance', 25000);

        // Assert Member status updated to active
        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'account_status' => MemberAccountStatus::Active->value,
        ]);

        // Assert Saver created
        $saver = Saver::where('member_id', $member->id)->first();
        $this->assertNotNull($saver);
        $this->assertSame(25000, $saver->balance);
        $this->assertStringStartsWith('BM', $saver->account_number);
        $this->assertStringStartsWith('QR-', $saver->qr_code_token);
        $this->assertSame(SaverStatus::Active, $saver->status);

        // Assert double-entry journals created:
        // Debit: Kas Teller (101) 25.000
        // Credit: Simpanan Tabungan Nasabah (201) 25.000
        $this->assertDatabaseHas('journal_entries', [
            'account_code' => '101',
            'type' => JournalEntryType::Debit->value,
            'amount' => 25000,
        ]);

        $this->assertDatabaseHas('journal_entries', [
            'account_code' => '201',
            'type' => JournalEntryType::Credit->value,
            'amount' => 25000,
        ]);

        // Verify debit and credit balance
        $totalDebit = JournalEntry::where('type', JournalEntryType::Debit)->sum('amount');
        $totalCredit = JournalEntry::where('type', JournalEntryType::Credit)->sum('amount');
        $this->assertSame($totalDebit, $totalCredit);
    }

    public function test_subsequent_deposit_to_existing_saver_increases_balance_and_records_journals(): void
    {
        $user = User::factory()->create(['role' => Role::Nasabah]);
        $member = Member::create([
            'user_id' => $user->id,
            'student_name' => 'Siti',
            'graduation_year' => '2026',
            'account_status' => MemberAccountStatus::Active,
        ]);

        $saver = Saver::create([
            'user_id' => $user->id,
            'member_id' => $member->id,
            'account_number' => 'BM20260099',
            'pin' => bcrypt('123456'),
            'qr_code_token' => 'QR-BM20260099-XYZ',
            'balance' => 20000,
            'status' => SaverStatus::Active,
        ]);

        $response = $this->actingAs($this->teller, 'sanctum')->postJson(route('teller.transactions.deposit'), [
            'account_number' => $saver->account_number,
            'amount' => 30000,
        ]);

        $response->assertCreated()
            ->assertJsonPath('transaction.opening_balance', 20000)
            ->assertJsonPath('transaction.closing_balance', 50000);

        $saver->refresh();
        $this->assertSame(50000, $saver->balance);
    }
}
