<?php

namespace Tests\Feature;

use App\Actions\DepositAction;
use App\Enums\CashClosureStatus;
use App\Enums\JournalEntryType;
use App\Enums\MemberAccountStatus;
use App\Enums\Role;
use App\Models\CashClosure;
use App\Models\Member;
use App\Models\Saver;
use App\Models\User;
use Database\Seeders\AccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AdminWebScreensTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $teller;

    protected User $nasabah;

    protected Saver $saver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AccountSeeder::class);

        $this->admin = User::factory()->create([
            'name' => 'Administrator Utama',
            'role' => Role::Admin,
            'is_active' => true,
        ]);

        $this->teller = User::factory()->create([
            'name' => 'Piket Teller 1',
            'role' => Role::Teller,
            'is_active' => true,
        ]);

        $this->nasabah = User::factory()->create([
            'name' => 'Siti Siswa',
            'role' => Role::Nasabah,
            'is_active' => true,
        ]);

        $member = Member::create([
            'user_id' => $this->nasabah->id,
            'student_name' => 'Siti Siswa',
            'graduation_year' => '2026',
            'account_status' => 'active',
        ]);

        $this->saver = Saver::create([
            'user_id' => $this->nasabah->id,
            'member_id' => $member->id,
            'account_number' => 'BM20260001',
            'pin' => Hash::make('123456'),
            'qr_code_token' => 'QR-BM20260001',
            'balance' => 50000,
            'status' => 'active',
            'opened_at' => now(),
        ]);
    }

    public function test_guest_cannot_access_admin_dashboard_and_is_redirected_to_login(): void
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_teller_or_nasabah_cannot_access_admin_portal(): void
    {
        $responseTeller = $this->actingAs($this->teller)->get('/admin/dashboard');
        $responseTeller->assertForbidden();

        $responseNasabah = $this->actingAs($this->nasabah)->get('/admin/dashboard');
        $responseNasabah->assertForbidden();
    }

    public function test_admin_can_view_all_8_admin_screens_successfully(): void
    {
        // 1. Dashboard
        $this->actingAs($this->admin)->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Dashboard Administrator')
            ->assertSee('Total Simpanan Siswa')
            ->assertDontSee('Supervisor');

        // 2. Manajemen Pengguna
        $this->actingAs($this->admin)->get('/admin/users')
            ->assertOk()
            ->assertSee('Manajemen Pengguna')
            ->assertSee('+ Tambah Pengguna Baru');

        // 3. Data Calon Penabung (Members)
        $this->actingAs($this->admin)->get('/admin/members')
            ->assertOk()
            ->assertSee('Data Calon Penabung')
            ->assertSee('Rp 10.000');

        // 4. Master Savers / Rekening
        $this->actingAs($this->admin)->get('/admin/savers')
            ->assertOk()
            ->assertSee('Master Savers')
            ->assertSee('BM20260001')
            ->assertSee('Reset PIN');

        // 5. Verifikasi Kas Harian
        $this->actingAs($this->admin)->get('/admin/cash-closures')
            ->assertOk()
            ->assertSee('Penutupan Kas Harian');

        // 6. Audit Jurnal Akuntansi
        $this->actingAs($this->admin)->get('/admin/audit-journals')
            ->assertOk()
            ->assertSee('Audit Jurnal Akuntansi')
            ->assertSee('SEIMBANG');

        // 7. Laporan Transaksi & PDF
        $this->actingAs($this->admin)->get('/admin/reports')
            ->assertOk()
            ->assertSee('Laporan Transaksi')
            ->assertSee('LEMBAGA PENDIDIKAN DAN KEUANGAN MIKRO SEKOLAH');

        // 8. Konfigurasi Sistem
        $this->actingAs($this->admin)->get('/admin/settings')
            ->assertOk()
            ->assertSee('Konfigurasi Parameter Sistem')
            ->assertSee('10.000');
    }

    public function test_admin_can_reset_pin_via_web_form(): void
    {
        $response = $this->actingAs($this->admin)->post("/admin/savers/{$this->saver->id}/reset-pin", [
            'new_pin' => '654321',
        ]);

        $response->assertSessionHas('success');
        $this->saver->refresh();
        $this->assertTrue(Hash::check('654321', $this->saver->pin));
    }

    public function test_admin_can_soft_delete_and_restore_saver(): void
    {
        // 1. SoftDelete
        $deleteResponse = $this->actingAs($this->admin)->delete("/admin/savers/{$this->saver->id}");
        $deleteResponse->assertSessionHas('success');
        $this->assertSoftDeleted('savers', ['id' => $this->saver->id]);

        // 2. Restore
        $restoreResponse = $this->actingAs($this->admin)->post("/admin/savers/{$this->saver->id}/restore");
        $restoreResponse->assertSessionHas('success');
        $this->assertNotSoftDeleted('savers', ['id' => $this->saver->id]);
    }

    public function test_admin_can_toggle_user_active_status(): void
    {
        $response = $this->actingAs($this->admin)->patch("/admin/users/{$this->teller->id}/toggle-active");
        $response->assertSessionHas('success');

        $this->teller->refresh();
        $this->assertFalse($this->teller->is_active);
    }

    public function test_admin_can_update_member_via_web_form_and_redirects_to_members_page(): void
    {
        $member = Member::first();

        $response = $this->actingAs($this->admin)->put("/admin/members/{$member->id}", [
            'student_name' => 'Nama Baru Siswa',
            'email' => 'namabaru@bankmini.test',
            'graduation_year' => '2028',
            'username' => 'siswa_baru_2028',
            'phone_number' => '081299998888',
        ]);

        $response->assertRedirect('/admin/members');
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'student_name' => 'Nama Baru Siswa',
            'graduation_year' => '2028',
        ]);
    }

    public function test_admin_can_update_user_via_web_form_and_redirects_to_users_page(): void
    {
        $response = $this->actingAs($this->admin)->put("/admin/users/{$this->teller->id}", [
            'name' => 'Teller Satu Update',
            'email' => 'teller1_update@bankmini.test',
            'username' => 'teller1_update',
            'role' => 'teller',
        ]);

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'id' => $this->teller->id,
            'name' => 'Teller Satu Update',
        ]);
    }

    public function test_admin_is_redirected_from_generic_dashboard_to_admin_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get('/dashboard');
        $response->assertRedirect('/admin/dashboard');
    }

    public function test_admin_web_route_names_do_not_collide_with_api_routes(): void
    {
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

        $this->assertSame('/admin/cash-closures/'.$closure->id, route('admin.cash-closures.show', $closure, false));
        $this->assertSame('/admin/cash-closures/'.$closure->id.'/process', route('admin.cash-closures.process', $closure, false));
        $this->assertSame('/admin/audit-journals', route('admin.audit-journals.index', [], false));
        $this->assertSame('/admin/members', route('admin.members.index', [], false));
        $this->assertSame('/admin/users', route('admin.users.index', [], false));

        $this->assertSame('/api/admin/cash-closures/'.$closure->id, route('admin.api.cash-closures.show', $closure, false));
        $this->assertSame('/api/admin/cash-closures/'.$closure->id.'/process', route('admin.api.cash-closures.process', $closure, false));
    }

    public function test_admin_can_open_cash_closure_detail_from_dashboard_and_index_and_approve_via_web(): void
    {
        $closure = CashClosure::create([
            'teller_id' => $this->teller->id,
            'closure_date' => today(),
            'opening_balance' => 0,
            'total_deposit' => 200000,
            'total_withdrawal' => 150000,
            'system_ending_balance' => 50000,
            'physical_ending_balance' => 50000,
            'difference' => 0,
            'status' => CashClosureStatus::Submitted,
        ]);

        $expectedShowUrl = route('admin.cash-closures.show', $closure);
        $expectedProcessUrl = route('admin.cash-closures.process', $closure);

        // 1. Dashboard "Periksa & ACC" links to the web detail route
        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Periksa & ACC', false)
            ->assertSee('href="'.$expectedShowUrl.'"', false);

        // 2. Cash closures index "Periksa & Otorisasi" links to the web detail route
        $this->actingAs($this->admin)
            ->get(route('admin.cash-closures.index'))
            ->assertOk()
            ->assertSee('Periksa & Otorisasi', false)
            ->assertSee('href="'.$expectedShowUrl.'"', false);

        // 3. Visiting the detail route renders the authorization page with the web process action
        $this->actingAs($this->admin)
            ->get($expectedShowUrl)
            ->assertOk()
            ->assertViewIs('admin.cash-closures.show')
            ->assertSee('Otorisasi Penutupan Kas')
            ->assertSee('action="'.$expectedProcessUrl.'"', false);

        // 4. Submitting approval redirects back to index with success and records vault transfer journals
        $processResponse = $this->actingAs($this->admin)
            ->post($expectedProcessUrl, [
                'approve' => '1',
                'reason' => 'Sesuai fisik di brankas',
            ]);

        $processResponse->assertRedirect(route('admin.cash-closures.index'));
        $processResponse->assertSessionHas('success');

        $closure->refresh();
        $this->assertSame(CashClosureStatus::Approved, $closure->status);
        $this->assertSame($this->admin->id, $closure->approved_by);

        $this->assertDatabaseHas('journal_entries', [
            'cash_closure_id' => $closure->id,
            'account_code' => '102',
            'type' => JournalEntryType::Debit->value,
            'amount' => 50000,
        ]);

        $this->assertDatabaseHas('journal_entries', [
            'cash_closure_id' => $closure->id,
            'account_code' => '101',
            'type' => JournalEntryType::Credit->value,
            'amount' => 50000,
        ]);
    }

    public function test_admin_can_update_and_persist_system_settings_and_deposit_enforces_it(): void
    {
        $response = $this->actingAs($this->admin)
            ->from('/admin/settings')
            ->post(route('admin.settings.update'), [
                'min_initial_deposit' => '25.000',
                'daily_withdraw_limit' => '750.000',
                'scanner_auto_submit' => '0',
                'scanner_beep_sound' => '1',
            ]);

        $response->assertRedirect('/admin/settings');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('system_settings', ['key' => 'min_initial_deposit', 'value' => '25000']);
        $this->assertDatabaseHas('system_settings', ['key' => 'daily_withdraw_limit', 'value' => '750000']);
        $this->assertDatabaseHas('system_settings', ['key' => 'scanner_auto_submit', 'value' => '0']);
        $this->assertDatabaseHas('system_settings', ['key' => 'scanner_beep_sound', 'value' => '1']);

        // Settings screen and API reflect persisted values
        $this->actingAs($this->admin)
            ->get('/admin/settings')
            ->assertOk()
            ->assertSee('value="25.000"', false)
            ->assertSee('value="750.000"', false);

        $this->actingAs($this->admin, 'sanctum')
            ->getJson(route('admin.api.system-settings'))
            ->assertOk()
            ->assertJsonPath('min_initial_deposit', 25000)
            ->assertJsonPath('daily_withdraw_limit', 750000);

        // Initial deposit below updated 25.000 is rejected
        $newStudent = User::factory()->create(['role' => Role::Nasabah]);
        $newMember = Member::create([
            'user_id' => $newStudent->id,
            'student_name' => 'Siswa Baru',
            'graduation_year' => '2028',
            'account_status' => MemberAccountStatus::Inactive,
        ]);

        $this->expectException(ValidationException::class);
        app(DepositAction::class)->handle(
            teller: $this->teller,
            amount: 15000,
            member: $newMember,
            pin: '123456'
        );
    }

    public function test_admin_can_delete_member_with_empty_soft_deleted_saver_via_web(): void
    {
        $studentUser = User::factory()->create(['role' => Role::Nasabah]);
        $member = Member::create([
            'user_id' => $studentUser->id,
            'student_name' => 'Siswa Web Kosong',
            'graduation_year' => '2028',
            'account_status' => MemberAccountStatus::Active,
        ]);
        $saver = Saver::create([
            'user_id' => $studentUser->id,
            'member_id' => $member->id,
            'account_number' => 'BM20267777',
            'pin' => Hash::make('123456'),
            'qr_code_token' => 'QR-BM20267777',
            'balance' => 0,
            'status' => 'active',
        ]);
        $saver->delete();

        $response = $this->actingAs($this->admin)->delete("/admin/members/{$member->id}");

        $response->assertRedirect('/admin/members');
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('savers', ['id' => $saver->id]);
        $this->assertDatabaseMissing('members', ['id' => $member->id]);
        $this->assertDatabaseMissing('users', ['id' => $studentUser->id]);
    }

    public function test_daily_report_displays_all_tellers_active_on_that_day(): void
    {
        $secondTeller = User::factory()->create([
            'name' => 'Piket Teller 2',
            'role' => Role::Teller,
            'is_active' => true,
        ]);

        CashClosure::create([
            'teller_id' => $this->teller->id,
            'closure_date' => today(),
            'opening_balance' => 0,
            'total_deposit' => 20000,
            'total_withdrawal' => 0,
            'system_ending_balance' => 20000,
            'physical_ending_balance' => 20000,
            'difference' => 0,
            'status' => CashClosureStatus::Submitted,
        ]);

        CashClosure::create([
            'teller_id' => $secondTeller->id,
            'closure_date' => today(),
            'opening_balance' => 0,
            'total_deposit' => 30000,
            'total_withdrawal' => 0,
            'system_ending_balance' => 30000,
            'physical_ending_balance' => 30000,
            'difference' => 0,
            'status' => CashClosureStatus::Submitted,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.reports.index', [
            'type' => 'daily',
            'date' => today()->format('Y-m-d'),
        ]));

        $response->assertOk()
            ->assertSee('Piket Teller 1, Piket Teller 2');
    }
}
