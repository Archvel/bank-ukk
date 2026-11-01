<?php

namespace Tests\Feature;

use App\Actions\DepositAction;
use App\Actions\WithdrawAction;
use App\Enums\MemberAccountStatus;
use App\Enums\Role;
use App\Models\Member;
use App\Models\PinResetRequest;
use App\Models\Saver;
use App\Models\User;
use Database\Seeders\AccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NasabahWebScreensTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $teller;

    protected User $nasabahAktif;

    protected Member $memberAktif;

    protected Saver $saver;

    protected User $nasabahCalon;

    protected Member $memberCalon;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AccountSeeder::class);

        $this->admin = User::factory()->create([
            'name' => 'Administrator',
            'role' => Role::Admin,
            'is_active' => true,
        ]);

        $this->teller = User::factory()->create([
            'name' => 'Piket Teller',
            'role' => Role::Teller,
            'is_active' => true,
        ]);

        // 1. Nasabah Aktif (with Saver account)
        $this->nasabahAktif = User::factory()->create([
            'name' => 'Siti Nasabah Aktif',
            'email' => 'siti.aktif@bankmini.test',
            'role' => Role::Nasabah,
            'is_active' => true,
        ]);

        $this->memberAktif = Member::create([
            'user_id' => $this->nasabahAktif->id,
            'student_name' => 'Siti Nasabah Aktif',
            'graduation_year' => '2026',
            'account_status' => MemberAccountStatus::Active,
        ]);

        $this->saver = Saver::create([
            'user_id' => $this->nasabahAktif->id,
            'member_id' => $this->memberAktif->id,
            'account_number' => 'BM20260001',
            'balance' => 50000,
            'pin' => Hash::make('123456'),
            'qr_code_token' => 'QR-BM20260001',
            'status' => 'active',
            'opened_at' => now(),
        ]);

        // 2. Nasabah Calon (Member only, no Saver yet)
        $this->nasabahCalon = User::factory()->create([
            'name' => 'Budi Calon Penabung',
            'email' => 'budi.calon@bankmini.test',
            'role' => Role::Nasabah,
            'is_active' => true,
        ]);

        $this->memberCalon = Member::create([
            'user_id' => $this->nasabahCalon->id,
            'student_name' => 'Budi Calon Penabung',
            'graduation_year' => '2027',
            'account_status' => MemberAccountStatus::Inactive,
        ]);
    }

    public function test_nasabah_aktif_can_view_dashboard_with_emerald_card_and_qr(): void
    {
        $response = $this->actingAs($this->nasabahAktif)->get(route('nasabah.dashboard'));

        $response->assertOk()
            ->assertViewIs('nasabah.dashboard')
            ->assertSee('TOTAL SALDO BERJALAN')
            ->assertSee('50.000')
            ->assertSee('Saldo Siap Ditarik')
            ->assertSee('40.000') // 50.000 - 10.000
            ->assertSee('Batas Saldo Mengendap')
            ->assertSee('10.000')
            ->assertSee('BM20260001')
            ->assertSee('REKENING AKTIF')
            ->assertSee('ID SCANNER TELLER')
            ->assertSee('Perbesar QR Code');
    }

    public function test_nasabah_calon_sees_inactive_warning_and_activation_guide(): void
    {
        $response = $this->actingAs($this->nasabahCalon)->get(route('nasabah.dashboard'));

        $response->assertOk()
            ->assertViewIs('nasabah.dashboard')
            ->assertSee('STATUS CALON PENABUNG (BELUM AKTIF)')
            ->assertSee('Setoran Awal Minimal')
            ->assertSee('Rp 10.000')
            ->assertSee('3 Langkah Mudah Aktivasi Rekening Penabung')
            ->assertSee('Kunjungi Loket Teller')
            ->assertSee('Setoran Awal &amp; Tentukan PIN', false)
            ->assertDontSee('TOTAL SALDO BERJALAN');
    }

    public function test_nasabah_calon_is_redirected_from_mutations_and_pin_to_dashboard(): void
    {
        $mutationsResponse = $this->actingAs($this->nasabahCalon)->get(route('nasabah.mutations.index'));
        $mutationsResponse->assertRedirect(route('nasabah.dashboard'))
            ->assertSessionHas('error');

        $pinResponse = $this->actingAs($this->nasabahCalon)->get(route('nasabah.pin.index'));
        $pinResponse->assertRedirect(route('nasabah.dashboard'))
            ->assertSessionHas('error');
    }

    public function test_nasabah_can_view_digital_passbook_with_kpi_and_mutations(): void
    {
        // Add a deposit and a withdrawal transaction
        app(DepositAction::class)->handle(
            teller: $this->teller,
            saver: $this->saver,
            amount: 30000,
            notes: 'Setoran Tabungan Mingguan'
        );

        app(WithdrawAction::class)->handle(
            teller: $this->teller,
            saver: $this->saver,
            amount: 15000,
            pin: '123456',
            notes: 'Tarik Beli Buku'
        );

        $response = $this->actingAs($this->nasabahAktif)->get(route('nasabah.mutations.index'));

        $response->assertOk()
            ->assertViewIs('nasabah.mutations.index')
            ->assertSee('Buku Tabungan Digital')
            ->assertSee('Total Uang Masuk (Setoran)')
            ->assertSee('Total Uang Keluar (Penarikan)')
            ->assertSee('Saldo Akhir Berjalan')
            ->assertSee('Setoran Tabungan Mingguan')
            ->assertSee('Tarik Beli Buku')
            ->assertSee('+Rp 30.000')
            ->assertSee('-Rp 15.000')
            ->assertSee('BM20260001');
    }

    public function test_digital_passbook_is_strictly_read_only_and_cannot_be_manipulated(): void
    {
        $response = $this->actingAs($this->nasabahAktif)->get(route('nasabah.mutations.index'));

        $response->assertOk()
            ->assertDontSee('Edit')
            ->assertDontSee('Hapus')
            ->assertDontSee('Delete')
            ->assertDontSee('action="edit"');
    }

    public function test_nasabah_can_filter_mutations_by_type(): void
    {
        app(DepositAction::class)->handle(
            teller: $this->teller,
            saver: $this->saver,
            amount: 25000,
            notes: 'Setor Khusus'
        );

        app(WithdrawAction::class)->handle(
            teller: $this->teller,
            saver: $this->saver,
            amount: 10000,
            pin: '123456',
            notes: 'Tarik Khusus'
        );

        // Filter deposit only
        $depositResponse = $this->actingAs($this->nasabahAktif)->get(route('nasabah.mutations.index', ['type' => 'deposit']));
        $depositResponse->assertOk()
            ->assertSee('Setor Khusus')
            ->assertDontSee('Tarik Khusus');

        // Filter withdrawal only
        $withdrawalResponse = $this->actingAs($this->nasabahAktif)->get(route('nasabah.mutations.index', ['type' => 'withdrawal']));
        $withdrawalResponse->assertOk()
            ->assertSee('Tarik Khusus')
            ->assertDontSee('Setor Khusus');
    }

    public function test_nasabah_can_request_token_and_confirm_pin_reset(): void
    {
        // 1. View PIN page
        $pageResponse = $this->actingAs($this->nasabahAktif)->get(route('nasabah.pin.index'));
        $pageResponse->assertOk()
            ->assertViewIs('nasabah.pin.index')
            ->assertSee('Pusat Keamanan &amp; Reset PIN', false)
            ->assertSee('Dapatkan Token Reset Baru');

        // 2. Request Token
        $tokenResponse = $this->actingAs($this->nasabahAktif)->post(route('nasabah.pin.request'), [
            'account_number' => $this->saver->account_number,
        ]);

        $tokenResponse->assertRedirect(route('nasabah.pin.index'))
            ->assertSessionHas('success')
            ->assertSessionHas('generated_token');

        $pinRequest = PinResetRequest::where('saver_id', $this->saver->id)->latest('id')->first();
        $this->assertNotNull($pinRequest);
        $token = $pinRequest->reset_token;

        // 3. Confirm Reset with valid new PIN
        $confirmResponse = $this->actingAs($this->nasabahAktif)->post(route('nasabah.pin.confirm'), [
            'reset_token' => $token,
            'new_pin' => '987654',
            'new_pin_confirmation' => '987654',
        ]);

        $confirmResponse->assertRedirect(route('nasabah.pin.index'))
            ->assertSessionHas('success');

        $this->saver->refresh();
        $this->assertTrue(Hash::check('987654', $this->saver->pin));
    }

    public function test_nasabah_cannot_access_admin_or_teller_pages_rubrik_rbac_3_2(): void
    {
        // Nasabah attempting to access Administrator portal directly
        $adminDashboard = $this->actingAs($this->nasabahAktif)->get('/admin/dashboard');
        $adminDashboard->assertForbidden();

        $adminUsers = $this->actingAs($this->nasabahAktif)->get('/admin/users');
        $adminUsers->assertForbidden();

        $adminSavers = $this->actingAs($this->nasabahAktif)->get('/admin/savers');
        $adminSavers->assertForbidden();

        // Nasabah attempting to access Teller portal directly
        $tellerCounter = $this->actingAs($this->nasabahAktif)->get('/teller/counter');
        $tellerCounter->assertForbidden();

        $tellerTransactions = $this->actingAs($this->nasabahAktif)->get('/teller/transactions');
        $tellerTransactions->assertForbidden();

        $tellerCashClosure = $this->actingAs($this->nasabahAktif)->get('/teller/cash-closure');
        $tellerCashClosure->assertForbidden();
    }

    public function test_admin_and_teller_cannot_access_nasabah_portal(): void
    {
        $adminResponse = $this->actingAs($this->admin)->get(route('nasabah.dashboard'));
        $adminResponse->assertForbidden();

        $tellerResponse = $this->actingAs($this->teller)->get(route('nasabah.dashboard'));
        $tellerResponse->assertForbidden();
    }

    public function test_unauthenticated_user_cannot_access_nasabah_routes(): void
    {
        $response = $this->get(route('nasabah.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_generic_dashboard_route_redirects_nasabah_to_nasabah_dashboard(): void
    {
        $response = $this->actingAs($this->nasabahAktif)->get('/dashboard');
        $response->assertRedirect(route('nasabah.dashboard'));
    }

    public function test_nasabah_login_redirects_to_nasabah_dashboard(): void
    {
        $response = $this->post('/login', [
            'email' => $this->nasabahAktif->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('nasabah.dashboard', absolute: false));
    }
}
