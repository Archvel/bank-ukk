<?php

namespace Tests\Feature;

use App\Actions\PromoteStudentsAction;
use App\Enums\MemberAccountStatus;
use App\Enums\Role;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentPromotionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => Role::Admin,
            'is_active' => true,
        ]);
    }

    public function test_promote_action_determines_next_class_correctly(): void
    {
        $action = new PromoteStudentsAction;

        // X -> XI
        $this->assertSame('XI BD', $action->getNextClassName('X BD'));
        $this->assertSame('XI RPL 1', $action->getNextClassName('X RPL 1'));
        $this->assertSame('XI AK 2', $action->getNextClassName('X AK 2'));
        $this->assertSame('XI', $action->getNextClassName('X'));

        // XI -> XII
        $this->assertSame('XII BD', $action->getNextClassName('XI BD'));
        $this->assertSame('XII RPL 1', $action->getNextClassName('XI RPL 1'));
        $this->assertSame('XII AK 2', $action->getNextClassName('XI AK 2'));
        $this->assertSame('XII', $action->getNextClassName('XI'));

        // XII -> Alumni
        $this->assertSame('Alumni BD', $action->getNextClassName('XII BD'));
        $this->assertSame('Alumni RPL 1', $action->getNextClassName('XII RPL 1'));
        $this->assertSame('Alumni', $action->getNextClassName('XII'));

        // Arabic numbers: 10 -> 11, 11 -> 12
        $this->assertSame('11 RPL 1', $action->getNextClassName('10 RPL 1'));
        $this->assertSame('12 RPL 1', $action->getNextClassName('11 RPL 1'));
        $this->assertSame('Alumni RPL 1', $action->getNextClassName('12 RPL 1'));
    }

    public function test_admin_can_promote_single_member_via_web(): void
    {
        $user = User::factory()->create(['role' => Role::Nasabah]);
        $member = Member::create([
            'user_id' => $user->id,
            'student_name' => 'Budi Santoso',
            'class_name' => 'X BD',
            'major' => 'BD',
            'graduation_year' => '2029',
            'academic_year' => '2026 - 2027',
            'account_status' => MemberAccountStatus::Inactive,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.members.promote-single', $member->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'class_name' => 'XI BD',
        ]);
    }

    public function test_admin_can_promote_single_member_via_api(): void
    {
        $user = User::factory()->create(['role' => Role::Nasabah]);
        $member = Member::create([
            'user_id' => $user->id,
            'student_name' => 'Siti Aisyah',
            'class_name' => 'XI RPL 1',
            'major' => 'RPL',
            'graduation_year' => '2028',
            'academic_year' => '2026 - 2027',
            'account_status' => MemberAccountStatus::Inactive,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')->postJson(route('admin.api.members.promote-single', $member->id), [
            'academic_year' => '2027 - 2028',
        ]);

        $response->assertOk()
            ->assertJsonPath('member.class_name', 'XII RPL 1');

        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'class_name' => 'XII RPL 1',
            'academic_year' => '2027 - 2028',
        ]);
    }

    public function test_admin_can_promote_entire_school_bulk_all(): void
    {
        $user1 = User::factory()->create(['role' => Role::Nasabah]);
        $mX = Member::create([
            'user_id' => $user1->id,
            'student_name' => 'Siswa Kelas X',
            'class_name' => 'X RPL 1',
            'graduation_year' => '2029',
            'academic_year' => '2026 - 2027',
            'account_status' => MemberAccountStatus::Inactive,
        ]);

        $user2 = User::factory()->create(['role' => Role::Nasabah]);
        $mXI = Member::create([
            'user_id' => $user2->id,
            'student_name' => 'Siswa Kelas XI',
            'class_name' => 'XI RPL 1',
            'graduation_year' => '2028',
            'academic_year' => '2026 - 2027',
            'account_status' => MemberAccountStatus::Inactive,
        ]);

        $user3 = User::factory()->create(['role' => Role::Nasabah]);
        $mXII = Member::create([
            'user_id' => $user3->id,
            'student_name' => 'Siswa Kelas XII',
            'class_name' => 'XII RPL 1',
            'graduation_year' => '2027',
            'academic_year' => '2026 - 2027',
            'account_status' => MemberAccountStatus::Inactive,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.members.promote'), [
            'mode' => 'all',
            'academic_year' => '2027 - 2028',
        ]);

        $response->assertRedirect('/admin/members');
        $response->assertSessionHas('success');

        // Check each student promoted to the right tier without double-promotion
        $this->assertSame('XI RPL 1', $mX->fresh()->class_name);
        $this->assertSame('XII RPL 1', $mXI->fresh()->class_name);
        $this->assertSame('Alumni RPL 1', $mXII->fresh()->class_name);

        $this->assertSame('2027 - 2028', $mX->fresh()->academic_year);
        $this->assertSame('2027 - 2028', $mXI->fresh()->academic_year);
        $this->assertSame('2027 - 2028', $mXII->fresh()->academic_year);
    }

    public function test_admin_can_promote_by_specific_class(): void
    {
        $user1 = User::factory()->create(['role' => Role::Nasabah]);
        $m1 = Member::create([
            'user_id' => $user1->id,
            'student_name' => 'Khansa Siswa',
            'class_name' => 'X BD',
            'graduation_year' => '2029',
            'account_status' => MemberAccountStatus::Inactive,
        ]);

        $user2 = User::factory()->create(['role' => Role::Nasabah]);
        $m2 = Member::create([
            'user_id' => $user2->id,
            'student_name' => 'Lain Kelas',
            'class_name' => 'X RPL 1',
            'graduation_year' => '2029',
            'account_status' => MemberAccountStatus::Inactive,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.members.promote'), [
            'mode' => 'class',
            'source_class' => 'X BD',
            'target_class' => 'XI BD',
        ]);

        $response->assertRedirect('/admin/members');

        $this->assertSame('XI BD', $m1->fresh()->class_name);
        $this->assertSame('X RPL 1', $m2->fresh()->class_name); // Unaffected
    }

    public function test_admin_can_promote_by_level(): void
    {
        $user1 = User::factory()->create(['role' => Role::Nasabah]);
        $mX = Member::create([
            'user_id' => $user1->id,
            'student_name' => 'Siswa X',
            'class_name' => 'X AK 1',
            'graduation_year' => '2029',
            'account_status' => MemberAccountStatus::Inactive,
        ]);

        $user2 = User::factory()->create(['role' => Role::Nasabah]);
        $mXI = Member::create([
            'user_id' => $user2->id,
            'student_name' => 'Siswa XI',
            'class_name' => 'XI AK 1',
            'graduation_year' => '2028',
            'account_status' => MemberAccountStatus::Inactive,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.members.promote'), [
            'mode' => 'level',
            'source_level' => 'X',
        ]);

        $response->assertRedirect('/admin/members');

        $this->assertSame('XI AK 1', $mX->fresh()->class_name);
        $this->assertSame('XI AK 1', $mXI->fresh()->class_name); // XI was NOT promoted
    }

    public function test_admin_members_page_renders_promotion_button_and_modals(): void
    {
        $user = User::factory()->create(['role' => Role::Nasabah]);
        $member = Member::create([
            'user_id' => $user->id,
            'student_name' => 'Ahmad Test',
            'class_name' => 'X BD',
            'major' => 'BD',
            'graduation_year' => '2029',
            'academic_year' => '2026 - 2027',
            'account_status' => MemberAccountStatus::Inactive,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/members');

        $response->assertOk();
        $response->assertSee('Naik Kelas Siswa');
        $response->assertSee(route('admin.members.promote'));
        $response->assertSee(route('admin.members.promote-single', $member->id));
        $response->assertSee('Kenaikan Kelas Siswa');
        $response->assertSee('Pilih Skema Kenaikan Kelas');
    }
}
