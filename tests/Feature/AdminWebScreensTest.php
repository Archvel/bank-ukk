<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Member;
use App\Models\Saver;
use App\Models\User;
use Database\Seeders\AccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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
}
