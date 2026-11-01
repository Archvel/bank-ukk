<?php

namespace Tests\Feature;

use App\Actions\DepositAction;
use App\Enums\MemberAccountStatus;
use App\Enums\Role;
use App\Models\Member;
use App\Models\Saver;
use App\Models\User;
use Database\Seeders\AccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TellerWebScreensTest extends TestCase
{
    use RefreshDatabase;

    protected User $teller;

    protected User $otherTeller;

    protected User $admin;

    protected User $nasabah;

    protected Saver $saver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AccountSeeder::class);

        $this->teller = User::factory()->create([
            'name' => 'Piket Teller 1',
            'role' => Role::Teller,
            'is_active' => true,
        ]);

        $this->otherTeller = User::factory()->create([
            'name' => 'Piket Teller 2',
            'role' => Role::Teller,
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create([
            'name' => 'Admin Utama',
            'role' => Role::Admin,
            'is_active' => true,
        ]);

        $this->nasabah = User::factory()->create([
            'name' => 'Siswa Nasabah',
            'role' => Role::Nasabah,
            'is_active' => true,
        ]);

        $member = Member::create([
            'user_id' => $this->nasabah->id,
            'student_name' => 'Siswa Nasabah',
            'graduation_year' => '2026',
            'account_status' => MemberAccountStatus::Active,
        ]);

        $this->saver = Saver::create([
            'user_id' => $this->nasabah->id,
            'member_id' => $member->id,
            'account_number' => 'BM20260001',
            'balance' => 100000,
            'pin' => bcrypt('123456'),
            'qr_code_token' => 'QR-BM20260001',
        ]);
    }

    public function test_teller_can_access_transactions_history_page(): void
    {
        $response = $this->actingAs($this->teller)->get(route('teller.transactions.index'));

        $response->assertOk()
            ->assertViewIs('teller.transactions.index')
            ->assertSee('Riwayat Transaksi Hari Ini')
            ->assertSee('Total Transaksi Hari Ini')
            ->assertSee('Total Uang Masuk (Setoran)')
            ->assertSee('Total Uang Keluar (Penarikan)')
            ->assertSee('Arus Kas Bersih (Net Inflow)');
    }

    public function test_transactions_history_displays_today_transactions_and_kpi(): void
    {
        // Process a deposit by this teller
        app(DepositAction::class)->handle(
            teller: $this->teller,
            saver: $this->saver,
            amount: 50000,
            notes: 'Setoran Uji Coba'
        );

        $response = $this->actingAs($this->teller)->get(route('teller.transactions.index'));

        $response->assertOk()
            ->assertSee('50.000')
            ->assertSee('SETORAN TUNAI')
            ->assertSee('BM20260001')
            ->assertSee('Siswa Nasabah')
            ->assertSee('Terkunci (Audit)');
    }

    public function test_transactions_history_is_read_only_without_edit_or_delete_buttons(): void
    {
        $response = $this->actingAs($this->teller)->get(route('teller.transactions.index'));

        $response->assertOk()
            ->assertDontSee('Edit Transaksi')
            ->assertDontSee('Hapus Transaksi')
            ->assertDontSee('action="edit"')
            ->assertDontSee('action="delete"');
    }

    public function test_non_teller_cannot_access_teller_transactions(): void
    {
        $adminResponse = $this->actingAs($this->admin)->get(route('teller.transactions.index'));
        $adminResponse->assertForbidden();

        $nasabahResponse = $this->actingAs($this->nasabah)->get(route('teller.transactions.index'));
        $nasabahResponse->assertForbidden();
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get(route('teller.transactions.index'));

        $response->assertRedirect(route('login'));
    }
}
