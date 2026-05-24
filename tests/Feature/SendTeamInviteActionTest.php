<?php

namespace Tests\Feature;

use App\Actions\CreateProjectAction;
use App\Actions\SendTeamInviteAction;
use App\Models\TeamInvite;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SendTeamInviteActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_send_team_invite(): void
    {
        // Tạo owner để sở hữu project.
        $owner = User::factory()->create();

        // Tạo project bằng action để có owner membership đúng flow.
        $project = app(CreateProjectAction::class)->execute($owner, [
            // Đặt tên project phục vụ test.
            'name' => 'Invite Project',
        ]);

        // Gửi invite bằng action production.
        $invite = app(SendTeamInviteAction::class)->execute($owner, $project, [
            // Email cố tình viết hoa để kiểm tra normalize lowercase.
            'email' => 'NEW.MEMBER@KEYFORGE.TEST',
            // Role member là role mặc định phổ biến.
            'role' => 'member',
        ]);

        // Xác nhận invite thuộc đúng project.
        $this->assertSame($project->id, $invite->project_id);

        // Xác nhận email đã được chuẩn hóa lowercase.
        $this->assertSame('new.member@keyforge.test', $invite->email);

        // Xác nhận role được lưu đúng.
        $this->assertSame('member', $invite->role);

        // Xác nhận token đã được sinh đủ dài.
        $this->assertSame(64, strlen($invite->token));

        // Xác nhận invite có hạn sử dụng.
        $this->assertNotNull($invite->expires_at);

        // Xác nhận invite vẫn đang pending.
        $this->assertNull($invite->accepted_at);

        // Xác nhận database có invite vừa tạo.
        $this->assertDatabaseHas('team_invites', [
            // Project liên quan.
            'project_id' => $project->id,
            // Người gửi invite.
            'invited_by' => $owner->id,
            // Email đã normalize.
            'email' => 'new.member@keyforge.test',
            // Role invite.
            'role' => 'member',
        ]);

        // Xác nhận audit log team_invite.sent đã được ghi.
        $this->assertDatabaseHas('audit_logs', [
            // Project liên quan.
            'project_id' => $project->id,
            // User thực hiện action.
            'user_id' => $owner->id,
            // Action chuẩn của invite.
            'action' => 'team_invite.sent',
            // Model được audit.
            'auditable_type' => TeamInvite::class,
            // ID invite vừa tạo.
            'auditable_id' => $invite->id,
        ]);
    }

    public function test_admin_can_send_team_invite(): void
    {
        // Tạo owner để sở hữu project.
        $owner = User::factory()->create();

        // Tạo admin để kiểm tra quyền projects.manage_team.
        $admin = User::factory()->create();

        // Tạo project bằng action để có owner membership đúng flow.
        $project = app(CreateProjectAction::class)->execute($owner, [
            // Đặt tên project phục vụ test.
            'name' => 'Admin Invite Project',
        ]);

        // Thêm admin vào project.
        TeamMember::factory()->create([
            // Gắn membership vào project.
            'project_id' => $project->id,
            // Gắn membership vào user admin.
            'user_id' => $admin->id,
            // Người mời là owner.
            'invited_by' => $owner->id,
            // Role admin có quyền manage_team.
            'role' => 'admin',
        ]);

        // Admin gửi invite.
        $invite = app(SendTeamInviteAction::class)->execute($admin, $project, [
            // Email người được mời.
            'email' => 'teammate@keyforge.test',
            // Role viewer được phép mời.
            'role' => 'viewer',
        ]);

        // Xác nhận người gửi invite là admin.
        $this->assertSame($admin->id, $invite->invited_by);

        // Xác nhận role viewer được lưu.
        $this->assertSame('viewer', $invite->role);
    }

    public function test_viewer_cannot_send_team_invite(): void
    {
        // Tạo owner để sở hữu project.
        $owner = User::factory()->create();

        // Tạo viewer để kiểm tra bị chặn.
        $viewer = User::factory()->create();

        // Tạo project bằng action để có owner membership đúng flow.
        $project = app(CreateProjectAction::class)->execute($owner, [
            // Đặt tên project phục vụ test.
            'name' => 'Viewer Invite Project',
        ]);

        // Thêm viewer vào project.
        TeamMember::factory()->create([
            // Gắn membership vào project.
            'project_id' => $project->id,
            // Gắn membership vào user viewer.
            'user_id' => $viewer->id,
            // Người mời là owner.
            'invited_by' => $owner->id,
            // Viewer không có projects.manage_team.
            'role' => 'viewer',
        ]);

        // Kỳ vọng action bị chặn bởi authorization.
        $this->expectException(AuthorizationException::class);

        // Viewer thử gửi invite và phải fail.
        app(SendTeamInviteAction::class)->execute($viewer, $project, [
            // Email không quan trọng vì fail ở quyền.
            'email' => 'blocked@keyforge.test',
            // Role member hợp lệ nhưng viewer không được mời.
            'role' => 'member',
        ]);
    }

    public function test_pending_invite_is_refreshed_instead_of_duplicated(): void
    {
        // Tạo owner để sở hữu project.
        $owner = User::factory()->create();

        // Tạo project bằng action để có owner membership đúng flow.
        $project = app(CreateProjectAction::class)->execute($owner, [
            // Đặt tên project phục vụ test.
            'name' => 'Refresh Invite Project',
        ]);

        // Tạo pending invite cũ.
        $existingInvite = TeamInvite::factory()->create([
            // Gắn invite vào project.
            'project_id' => $project->id,
            // Người gửi ban đầu là owner.
            'invited_by' => $owner->id,
            // Email pending invite cũ.
            'email' => 'refresh@keyforge.test',
            // Role cũ.
            'role' => 'viewer',
            // Token cũ để kiểm tra có refresh.
            'token' => 'old-token-for-refresh-test',
            // Invite chưa accept.
            'accepted_at' => null,
        ]);

        // Gửi lại invite cùng email.
        $refreshedInvite = app(SendTeamInviteAction::class)->execute($owner, $project, [
            // Email trùng invite cũ.
            'email' => 'refresh@keyforge.test',
            // Role mới.
            'role' => 'admin',
        ]);

        // Xác nhận vẫn là cùng một record.
        $this->assertSame($existingInvite->id, $refreshedInvite->id);

        // Xác nhận role đã được update.
        $this->assertSame('admin', $refreshedInvite->role);

        // Xác nhận token đã được refresh.
        $this->assertNotSame('old-token-for-refresh-test', $refreshedInvite->token);

        // Xác nhận không tạo duplicate invite pending.
        $this->assertSame(1, TeamInvite::where('project_id', $project->id)->where('email', 'refresh@keyforge.test')->count());
    }

    public function test_cannot_invite_existing_project_member(): void
    {
        // Tạo owner để sở hữu project.
        $owner = User::factory()->create();

        // Tạo user đã là member.
        $member = User::factory()->create([
            // Email dùng để invite lại.
            'email' => 'already.member@keyforge.test',
        ]);

        // Tạo project bằng action để có owner membership đúng flow.
        $project = app(CreateProjectAction::class)->execute($owner, [
            // Đặt tên project phục vụ test.
            'name' => 'Existing Member Project',
        ]);

        // Thêm user vào team trước.
        TeamMember::factory()->create([
            // Gắn membership vào project.
            'project_id' => $project->id,
            // Gắn membership vào member.
            'user_id' => $member->id,
            // Người mời là owner.
            'invited_by' => $owner->id,
            // Role member hiện tại.
            'role' => 'member',
        ]);

        // Kỳ vọng lỗi validation khi invite lại member hiện có.
        $this->expectException(ValidationException::class);

        // Owner thử invite lại email đã là member.
        app(SendTeamInviteAction::class)->execute($owner, $project, [
            // Email đã thuộc team.
            'email' => 'already.member@keyforge.test',
            // Role mới không quan trọng vì phải fail.
            'role' => 'admin',
        ]);
    }

    public function test_invalid_role_is_rejected(): void
    {
        // Tạo owner để sở hữu project.
        $owner = User::factory()->create();

        // Tạo project bằng action để có owner membership đúng flow.
        $project = app(CreateProjectAction::class)->execute($owner, [
            // Đặt tên project phục vụ test.
            'name' => 'Invalid Role Invite Project',
        ]);

        // Kỳ vọng lỗi validation khi role không hợp lệ.
        $this->expectException(ValidationException::class);

        // Owner thử invite với role owner, role này không cho mời trực tiếp.
        app(SendTeamInviteAction::class)->execute($owner, $project, [
            // Email người được mời.
            'email' => 'bad-role@keyforge.test',
            // Owner không nằm trong danh sách role được invite.
            'role' => 'owner',
        ]);
    }
}
