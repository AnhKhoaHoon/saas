<?php

namespace Tests\Feature;

use App\Actions\AcceptTeamInviteAction;
use App\Actions\CreateProjectAction;
use App\Actions\SendTeamInviteAction;
use App\Models\TeamInvite;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AcceptTeamInviteActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_accept_pending_team_invite(): void
    {
        // Tạo owner để sở hữu project.
        $owner = User::factory()->create();

        // Tạo user đúng email được mời.
        $invitedUser = User::factory()->create([
            // Email khớp với invite.
            'email' => 'accepted.member@keyforge.test',
        ]);

        // Tạo project bằng action để có owner membership đúng flow.
        $project = app(CreateProjectAction::class)->execute($owner, [
            // Đặt tên project phục vụ test.
            'name' => 'Accept Invite Project',
        ]);

        // Owner gửi invite cho user.
        $invite = app(SendTeamInviteAction::class)->execute($owner, $project, [
            // Email người được mời.
            'email' => 'accepted.member@keyforge.test',
            // Role sẽ được gán khi accept.
            'role' => 'member',
        ]);

        // User accept invite bằng token.
        $teamMember = app(AcceptTeamInviteAction::class)->execute($invitedUser, $invite->token);

        // Xác nhận membership thuộc đúng project.
        $this->assertSame($project->id, $teamMember->project_id);

        // Xác nhận membership thuộc đúng user.
        $this->assertSame($invitedUser->id, $teamMember->user_id);

        // Xác nhận role được lấy từ invite.
        $this->assertSame('member', $teamMember->role);

        // Xác nhận joined_at đã được set.
        $this->assertNotNull($teamMember->joined_at);

        // Xác nhận invite đã được đánh dấu accepted.
        $this->assertNotNull($invite->fresh()->accepted_at);

        // Xác nhận database có membership mới.
        $this->assertDatabaseHas('team_members', [
            // Project liên quan.
            'project_id' => $project->id,
            // User accept invite.
            'user_id' => $invitedUser->id,
            // Người mời ban đầu.
            'invited_by' => $owner->id,
            // Role được gán.
            'role' => 'member',
        ]);

        // Xác nhận audit log accept invite đã được ghi.
        $this->assertDatabaseHas('audit_logs', [
            // Project liên quan.
            'project_id' => $project->id,
            // User accept invite.
            'user_id' => $invitedUser->id,
            // Action chuẩn của accept invite.
            'action' => 'team_invite.accepted',
            // Model được audit.
            'auditable_type' => TeamInvite::class,
            // ID invite đã accept.
            'auditable_id' => $invite->id,
        ]);
    }

    public function test_invalid_invite_token_is_rejected(): void
    {
        // Tạo user bất kỳ để thử token sai.
        $user = User::factory()->create();

        // Kỳ vọng lỗi validation khi token không tồn tại.
        $this->expectException(ValidationException::class);

        // Gọi action với token không có trong database.
        app(AcceptTeamInviteAction::class)->execute($user, 'missing-token');
    }

    public function test_user_cannot_accept_invite_for_different_email(): void
    {
        // Tạo owner để sở hữu project.
        $owner = User::factory()->create();

        // Tạo user có email khác invite.
        $wrongUser = User::factory()->create([
            // Email này không khớp invite.
            'email' => 'wrong.user@keyforge.test',
        ]);

        // Tạo project bằng action để có owner membership đúng flow.
        $project = app(CreateProjectAction::class)->execute($owner, [
            // Đặt tên project phục vụ test.
            'name' => 'Wrong Email Invite Project',
        ]);

        // Owner gửi invite cho email khác.
        $invite = app(SendTeamInviteAction::class)->execute($owner, $project, [
            // Email được mời không phải email của wrongUser.
            'email' => 'right.user@keyforge.test',
            // Role hợp lệ.
            'role' => 'viewer',
        ]);

        // Kỳ vọng lỗi validation khi email không khớp.
        $this->expectException(ValidationException::class);

        // User sai email thử accept token.
        app(AcceptTeamInviteAction::class)->execute($wrongUser, $invite->token);
    }

    public function test_expired_invite_is_rejected(): void
    {
        // Tạo owner để sở hữu project.
        $owner = User::factory()->create();

        // Tạo user đúng email được mời.
        $invitedUser = User::factory()->create([
            // Email khớp invite.
            'email' => 'expired.member@keyforge.test',
        ]);

        // Tạo project bằng action để có owner membership đúng flow.
        $project = app(CreateProjectAction::class)->execute($owner, [
            // Đặt tên project phục vụ test.
            'name' => 'Expired Invite Project',
        ]);

        // Tạo invite hết hạn.
        $invite = TeamInvite::factory()->create([
            // Gắn invite vào project.
            'project_id' => $project->id,
            // Người gửi là owner.
            'invited_by' => $owner->id,
            // Email đúng với invitedUser.
            'email' => 'expired.member@keyforge.test',
            // Role hợp lệ.
            'role' => 'member',
            // Hạn đã qua.
            'expires_at' => now()->subMinute(),
            // Invite chưa accept.
            'accepted_at' => null,
        ]);

        // Kỳ vọng lỗi validation khi invite hết hạn.
        $this->expectException(ValidationException::class);

        // User thử accept invite hết hạn.
        app(AcceptTeamInviteAction::class)->execute($invitedUser, $invite->token);
    }

    public function test_already_accepted_invite_is_rejected(): void
    {
        // Tạo owner để sở hữu project.
        $owner = User::factory()->create();

        // Tạo user đúng email được mời.
        $invitedUser = User::factory()->create([
            // Email khớp invite.
            'email' => 'already.accepted@keyforge.test',
        ]);

        // Tạo project bằng action để có owner membership đúng flow.
        $project = app(CreateProjectAction::class)->execute($owner, [
            // Đặt tên project phục vụ test.
            'name' => 'Already Accepted Invite Project',
        ]);

        // Tạo invite đã accept.
        $invite = TeamInvite::factory()->accepted()->create([
            // Gắn invite vào project.
            'project_id' => $project->id,
            // Người gửi là owner.
            'invited_by' => $owner->id,
            // Email đúng với invitedUser.
            'email' => 'already.accepted@keyforge.test',
            // Role hợp lệ.
            'role' => 'member',
            // Hạn vẫn còn để lỗi do accepted_at.
            'expires_at' => now()->addDay(),
        ]);

        // Kỳ vọng lỗi validation khi invite đã dùng.
        $this->expectException(ValidationException::class);

        // User thử accept lại invite đã accept.
        app(AcceptTeamInviteAction::class)->execute($invitedUser, $invite->token);
    }

    public function test_cancelled_invite_is_rejected(): void
    {
        // Tạo owner để sở hữu project.
        $owner = User::factory()->create();

        // Tạo user đúng email được mời.
        $invitedUser = User::factory()->create([
            // Email khớp invite.
            'email' => 'cancelled.member@keyforge.test',
        ]);

        // Tạo project bằng action để có owner membership đúng flow.
        $project = app(CreateProjectAction::class)->execute($owner, [
            // Đặt tên project phục vụ test.
            'name' => 'Cancelled Invite Project',
        ]);

        // Tạo invite đã bị cancel.
        $invite = TeamInvite::factory()->cancelled()->create([
            // Gắn invite vào project.
            'project_id' => $project->id,
            // Người gửi là owner.
            'invited_by' => $owner->id,
            // Email đúng với invitedUser.
            'email' => 'cancelled.member@keyforge.test',
            // Role hợp lệ.
            'role' => 'member',
            // Hạn vẫn còn để lỗi do cancelled_at.
            'expires_at' => now()->addDay(),
            // Invite chưa accept.
            'accepted_at' => null,
        ]);

        // Kỳ vọng lỗi validation khi invite đã bị cancel.
        $this->expectException(ValidationException::class);

        // User thử accept invite đã cancel.
        app(AcceptTeamInviteAction::class)->execute($invitedUser, $invite->token);
    }

    public function test_existing_membership_is_updated_when_accepting_valid_invite(): void
    {
        // Tạo owner để sở hữu project.
        $owner = User::factory()->create();

        // Tạo user đã có membership legacy.
        $member = User::factory()->create([
            // Email khớp invite.
            'email' => 'legacy.member@keyforge.test',
        ]);

        // Tạo project bằng action để có owner membership đúng flow.
        $project = app(CreateProjectAction::class)->execute($owner, [
            // Đặt tên project phục vụ test.
            'name' => 'Legacy Member Invite Project',
        ]);

        // Tạo membership cũ chưa có joined_at.
        $existingMembership = TeamMember::factory()->create([
            // Gắn membership vào project.
            'project_id' => $project->id,
            // Gắn membership vào user.
            'user_id' => $member->id,
            // Người mời cũ là null.
            'invited_by' => null,
            // Role cũ là viewer.
            'role' => 'viewer',
            // joined_at cũ đang trống.
            'joined_at' => null,
        ]);

        // Tạo invite mới hợp lệ cho cùng user.
        $invite = TeamInvite::factory()->create([
            // Gắn invite vào project.
            'project_id' => $project->id,
            // Người gửi là owner.
            'invited_by' => $owner->id,
            // Email đúng với member.
            'email' => 'legacy.member@keyforge.test',
            // Role mới là admin.
            'role' => 'admin',
            // Hạn còn hiệu lực.
            'expires_at' => now()->addDay(),
            // Invite chưa accept.
            'accepted_at' => null,
        ]);

        // Member accept invite.
        $teamMember = app(AcceptTeamInviteAction::class)->execute($member, $invite->token);

        // Xác nhận vẫn dùng membership cũ.
        $this->assertSame($existingMembership->id, $teamMember->id);

        // Xác nhận role được cập nhật theo invite.
        $this->assertSame('admin', $teamMember->role);

        // Xác nhận joined_at đã được set.
        $this->assertNotNull($teamMember->joined_at);
    }
}
