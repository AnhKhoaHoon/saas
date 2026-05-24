<?php

namespace Tests\Feature;

use App\Actions\CancelTeamInviteAction;
use App\Actions\CreateProjectAction;
use App\Actions\SendTeamInviteAction;
use App\Models\TeamInvite;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CancelTeamInviteActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_cancel_pending_invite(): void
    {
        // Tạo owner để sở hữu project.
        $owner = User::factory()->create();

        // Tạo project bằng action production.
        $project = app(CreateProjectAction::class)->execute($owner, [
            // Tên project phục vụ test.
            'name' => 'Cancel Invite Project',
        ]);

        // Tạo invite pending bằng action production.
        $invite = app(SendTeamInviteAction::class)->execute($owner, $project, [
            // Email người được mời.
            'email' => 'cancel-me@keyforge.test',
            // Role hợp lệ.
            'role' => 'member',
        ]);

        // Owner cancel invite.
        $cancelledInvite = app(CancelTeamInviteAction::class)->execute($owner, $project, $invite);

        // Xác nhận cancelled_at đã được set.
        $this->assertNotNull($cancelledInvite->cancelled_at);

        // Xác nhận invite vẫn chưa accept.
        $this->assertNull($cancelledInvite->accepted_at);

        // Xác nhận database lưu cancelled_at.
        $this->assertDatabaseHas('team_invites', [
            // ID invite vừa cancel.
            'id' => $invite->id,
            // Project liên quan.
            'project_id' => $project->id,
            // Email người được mời.
            'email' => 'cancel-me@keyforge.test',
        ]);

        // Xác nhận audit log cancel đã được ghi.
        $this->assertDatabaseHas('audit_logs', [
            // Project liên quan.
            'project_id' => $project->id,
            // User thực hiện action.
            'user_id' => $owner->id,
            // Action chuẩn của cancel invite.
            'action' => 'team_invite.cancelled',
            // Model được audit.
            'auditable_type' => TeamInvite::class,
            // ID invite đã cancel.
            'auditable_id' => $invite->id,
        ]);
    }

    public function test_admin_can_cancel_pending_invite(): void
    {
        // Tạo owner để sở hữu project.
        $owner = User::factory()->create();

        // Tạo admin để kiểm tra quyền manage_team.
        $admin = User::factory()->create();

        // Tạo project bằng action production.
        $project = app(CreateProjectAction::class)->execute($owner, [
            // Tên project phục vụ test.
            'name' => 'Admin Cancel Invite Project',
        ]);

        // Thêm admin vào project.
        TeamMember::factory()->create([
            // Project liên quan.
            'project_id' => $project->id,
            // User admin.
            'user_id' => $admin->id,
            // Người mời là owner.
            'invited_by' => $owner->id,
            // Role admin có projects.manage_team.
            'role' => 'admin',
        ]);

        // Owner tạo invite.
        $invite = app(SendTeamInviteAction::class)->execute($owner, $project, [
            // Email người được mời.
            'email' => 'admin-cancel@keyforge.test',
            // Role hợp lệ.
            'role' => 'viewer',
        ]);

        // Admin cancel invite.
        $cancelledInvite = app(CancelTeamInviteAction::class)->execute($admin, $project, $invite);

        // Xác nhận invite đã bị cancel.
        $this->assertNotNull($cancelledInvite->cancelled_at);
    }

    public function test_viewer_cannot_cancel_invite(): void
    {
        // Tạo owner để sở hữu project.
        $owner = User::factory()->create();

        // Tạo viewer để kiểm tra bị chặn.
        $viewer = User::factory()->create();

        // Tạo project bằng action production.
        $project = app(CreateProjectAction::class)->execute($owner, [
            // Tên project phục vụ test.
            'name' => 'Viewer Cancel Invite Project',
        ]);

        // Thêm viewer vào project.
        TeamMember::factory()->create([
            // Project liên quan.
            'project_id' => $project->id,
            // User viewer.
            'user_id' => $viewer->id,
            // Người mời là owner.
            'invited_by' => $owner->id,
            // Viewer không có projects.manage_team.
            'role' => 'viewer',
        ]);

        // Owner tạo invite.
        $invite = app(SendTeamInviteAction::class)->execute($owner, $project, [
            // Email người được mời.
            'email' => 'viewer-blocked@keyforge.test',
            // Role hợp lệ.
            'role' => 'member',
        ]);

        // Kỳ vọng lỗi authorization.
        $this->expectException(AuthorizationException::class);

        // Viewer thử cancel invite.
        app(CancelTeamInviteAction::class)->execute($viewer, $project, $invite);
    }

    public function test_accepted_invite_cannot_be_cancelled(): void
    {
        // Tạo owner để sở hữu project.
        $owner = User::factory()->create();

        // Tạo project bằng action production.
        $project = app(CreateProjectAction::class)->execute($owner, [
            // Tên project phục vụ test.
            'name' => 'Accepted Cancel Invite Project',
        ]);

        // Tạo invite đã accept.
        $invite = TeamInvite::factory()->accepted()->create([
            // Project liên quan.
            'project_id' => $project->id,
            // Người mời là owner.
            'invited_by' => $owner->id,
            // Email người được mời.
            'email' => 'accepted-cancel@keyforge.test',
            // Invite chưa cancel.
            'cancelled_at' => null,
        ]);

        // Kỳ vọng lỗi validation.
        $this->expectException(ValidationException::class);

        // Owner thử cancel invite đã accept.
        app(CancelTeamInviteAction::class)->execute($owner, $project, $invite);
    }

    public function test_cancelled_invite_cannot_be_cancelled_again(): void
    {
        // Tạo owner để sở hữu project.
        $owner = User::factory()->create();

        // Tạo project bằng action production.
        $project = app(CreateProjectAction::class)->execute($owner, [
            // Tên project phục vụ test.
            'name' => 'Double Cancel Invite Project',
        ]);

        // Tạo invite đã cancel.
        $invite = TeamInvite::factory()->cancelled()->create([
            // Project liên quan.
            'project_id' => $project->id,
            // Người mời là owner.
            'invited_by' => $owner->id,
            // Email người được mời.
            'email' => 'already-cancelled@keyforge.test',
            // Invite chưa accept.
            'accepted_at' => null,
        ]);

        // Kỳ vọng lỗi validation.
        $this->expectException(ValidationException::class);

        // Owner thử cancel lại invite đã cancel.
        app(CancelTeamInviteAction::class)->execute($owner, $project, $invite);
    }
}
