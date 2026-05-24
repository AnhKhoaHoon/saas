<?php

namespace Tests\Feature;

use App\Actions\CreateProjectAction;
use App\Actions\SendTeamInviteAction;
use App\Models\TeamInvite;
use App\Models\TeamMember;
use App\Models\User;
use App\Notifications\TeamInviteNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TeamInviteControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_page_shows_invite_form_and_pending_invites(): void
    {
        // Tạo owner để sở hữu project.
        $owner = User::factory()->create();

        // Tạo project bằng flow production.
        $project = app(CreateProjectAction::class)->execute($owner, [
            // Tên project dùng trong test.
            'name' => 'Team UI Project',
        ]);

        // Tạo pending invite để kiểm tra UI hiển thị.
        TeamInvite::factory()->create([
            // Gắn invite vào project.
            'project_id' => $project->id,
            // Người gửi là owner.
            'invited_by' => $owner->id,
            // Email pending invite.
            'email' => 'pending@keyforge.test',
            // Role pending invite.
            'role' => 'viewer',
            // Invite còn hạn.
            'expires_at' => now()->addDay(),
            // Invite chưa accept.
            'accepted_at' => null,
        ]);

        // Owner mở trang team.
        $this->actingAs($owner)
            // Gọi route team index.
            ->get(route('projects.team.index', $project))
            // Xác nhận trang trả về 200.
            ->assertOk()
            // Xác nhận form post tới route invite thật.
            ->assertSee(route('projects.team-invites.store', $project))
            // Xác nhận email pending invite hiển thị.
            ->assertSee('pending@keyforge.test')
            // Xác nhận nút gửi invite hiển thị.
            ->assertSee('Send Invite');
    }

    public function test_owner_can_send_invite_from_team_page_and_email_is_sent(): void
    {
        // Fake notification để không gửi email thật trong test.
        Notification::fake();

        // Tạo owner để sở hữu project.
        $owner = User::factory()->create();

        // Tạo project bằng flow production.
        $project = app(CreateProjectAction::class)->execute($owner, [
            // Tên project dùng trong test.
            'name' => 'Controller Invite Project',
        ]);

        // Owner submit form invite.
        $response = $this->actingAs($owner)->post(route('projects.team-invites.store', $project), [
            // Email người được mời.
            'email' => 'invited@keyforge.test',
            // Role người được mời.
            'role' => 'member',
        ]);

        // Lấy invite vừa tạo từ database.
        $invite = TeamInvite::where('email', 'invited@keyforge.test')->firstOrFail();

        // Xác nhận redirect về trang team.
        $response->assertRedirect(route('projects.team.index', $project));

        // Xác nhận có flash message thành công.
        $response->assertSessionHas('status', 'Invite sent to invited@keyforge.test.');

        // Xác nhận notification được gửi on-demand tới email invite.
        Notification::assertSentOnDemand(
            // Notification class cần kiểm tra.
            TeamInviteNotification::class,
            // Callback kiểm tra nội dung notification.
            function (TeamInviteNotification $notification, array $channels, AnonymousNotifiable $notifiable) use ($invite): bool {
                // Xác nhận gửi qua mail.
                $sentByMail = in_array('mail', $channels, true);

                // Xác nhận route mail đúng email invite.
                $sentToInviteEmail = $notifiable->routeNotificationFor('mail') === 'invited@keyforge.test';

                // Xác nhận notification chứa đúng invite.
                $containsInvite = $notification->invite->id === $invite->id;

                // Chỉ pass khi cả ba điều kiện đúng.
                return $sentByMail && $sentToInviteEmail && $containsInvite;
            }
        );
    }

    public function test_viewer_cannot_send_invite_from_team_page(): void
    {
        // Fake notification để đảm bảo không có email được gửi.
        Notification::fake();

        // Tạo owner để sở hữu project.
        $owner = User::factory()->create();

        // Tạo viewer để kiểm tra bị chặn.
        $viewer = User::factory()->create();

        // Tạo project bằng flow production.
        $project = app(CreateProjectAction::class)->execute($owner, [
            // Tên project dùng trong test.
            'name' => 'Viewer Blocked Invite Project',
        ]);

        // Thêm viewer vào project.
        TeamMember::factory()->create([
            // Gắn membership vào project.
            'project_id' => $project->id,
            // Gắn membership vào viewer.
            'user_id' => $viewer->id,
            // Người mời là owner.
            'invited_by' => $owner->id,
            // Viewer không có quyền manage_team.
            'role' => 'viewer',
        ]);

        // Viewer submit form invite.
        $this->actingAs($viewer)
            // Gọi store invite.
            ->post(route('projects.team-invites.store', $project), [
                // Email người được mời.
                'email' => 'blocked@keyforge.test',
                // Role hợp lệ nhưng viewer không có quyền.
                'role' => 'member',
            ])
            // Xác nhận bị chặn 403.
            ->assertForbidden();

        // Xác nhận không gửi notification nào.
        Notification::assertNothingSent();
    }

    public function test_invited_user_can_accept_invite_from_email_link(): void
    {
        // Tạo owner để sở hữu project.
        $owner = User::factory()->create();

        // Tạo user đúng email được mời.
        $invitedUser = User::factory()->create([
            // Email khớp invite.
            'email' => 'accept-route@keyforge.test',
        ]);

        // Tạo project bằng flow production.
        $project = app(CreateProjectAction::class)->execute($owner, [
            // Tên project dùng trong test.
            'name' => 'Accept Route Project',
        ]);

        // Tạo invite bằng action production.
        $invite = app(SendTeamInviteAction::class)->execute($owner, $project, [
            // Email người được mời.
            'email' => 'accept-route@keyforge.test',
            // Role được gán.
            'role' => 'admin',
        ]);

        // Invited user bấm link accept.
        $response = $this->actingAs($invitedUser)->get(route('team-invites.accept', $invite->token));

        // Xác nhận redirect về trang team.
        $response->assertRedirect(route('projects.team.index', $project));

        // Xác nhận membership được tạo.
        $this->assertDatabaseHas('team_members', [
            // Project liên quan.
            'project_id' => $project->id,
            // User accept invite.
            'user_id' => $invitedUser->id,
            // Role từ invite.
            'role' => 'admin',
        ]);

        // Xác nhận invite đã được accept.
        $this->assertNotNull($invite->fresh()->accepted_at);
    }

    public function test_user_cannot_accept_invite_for_another_email_from_route(): void
    {
        // Tạo owner để sở hữu project.
        $owner = User::factory()->create();

        // Tạo user không đúng email invite.
        $wrongUser = User::factory()->create([
            // Email không khớp invite.
            'email' => 'wrong-route@keyforge.test',
        ]);

        // Tạo project bằng flow production.
        $project = app(CreateProjectAction::class)->execute($owner, [
            // Tên project dùng trong test.
            'name' => 'Wrong Route Project',
        ]);

        // Tạo invite cho email khác.
        $invite = app(SendTeamInviteAction::class)->execute($owner, $project, [
            // Email được mời thật.
            'email' => 'right-route@keyforge.test',
            // Role hợp lệ.
            'role' => 'member',
        ]);

        // User sai email bấm link accept.
        $this->actingAs($wrongUser)
            // Gọi route accept.
            ->get(route('team-invites.accept', $invite->token))
            // Xác nhận lỗi được đưa vào session.
            ->assertSessionHasErrors('email');

        // Xác nhận không tạo membership cho user sai email.
        $this->assertDatabaseMissing('team_members', [
            // Project liên quan.
            'project_id' => $project->id,
            // User sai email.
            'user_id' => $wrongUser->id,
        ]);
    }

    public function test_owner_can_cancel_invite_from_team_page(): void
    {
        // Tạo owner để sở hữu project.
        $owner = User::factory()->create();

        // Tạo project bằng flow production.
        $project = app(CreateProjectAction::class)->execute($owner, [
            // Tên project dùng trong test.
            'name' => 'Cancel Route Project',
        ]);

        // Tạo pending invite bằng action production.
        $invite = app(SendTeamInviteAction::class)->execute($owner, $project, [
            // Email người được mời.
            'email' => 'cancel-route@keyforge.test',
            // Role hợp lệ.
            'role' => 'member',
        ]);

        // Owner submit form cancel invite.
        $response = $this->actingAs($owner)->delete(route('projects.team-invites.destroy', [$project, $invite]));

        // Xác nhận redirect về trang team.
        $response->assertRedirect(route('projects.team.index', $project));

        // Xác nhận flash message thành công.
        $response->assertSessionHas('status', 'Invite to cancel-route@keyforge.test cancelled.');

        // Xác nhận invite đã bị cancel.
        $this->assertNotNull($invite->fresh()->cancelled_at);
    }

    public function test_cancelled_invite_is_hidden_from_pending_list(): void
    {
        // Tạo owner để sở hữu project.
        $owner = User::factory()->create();

        // Tạo project bằng flow production.
        $project = app(CreateProjectAction::class)->execute($owner, [
            // Tên project dùng trong test.
            'name' => 'Hidden Cancelled Invite Project',
        ]);

        // Tạo invite đã cancel.
        TeamInvite::factory()->cancelled()->create([
            // Project liên quan.
            'project_id' => $project->id,
            // Người gửi là owner.
            'invited_by' => $owner->id,
            // Email không được hiển thị trong pending list.
            'email' => 'hidden-cancelled@keyforge.test',
            // Invite chưa accept.
            'accepted_at' => null,
        ]);

        // Owner mở trang team.
        $this->actingAs($owner)
            // Gọi route team index.
            ->get(route('projects.team.index', $project))
            // Xác nhận trang trả về 200.
            ->assertOk()
            // Xác nhận invite đã cancel không còn trong pending list.
            ->assertDontSee('hidden-cancelled@keyforge.test');
    }

    public function test_viewer_cannot_cancel_invite_from_team_page(): void
    {
        // Tạo owner để sở hữu project.
        $owner = User::factory()->create();

        // Tạo viewer để kiểm tra bị chặn.
        $viewer = User::factory()->create();

        // Tạo project bằng flow production.
        $project = app(CreateProjectAction::class)->execute($owner, [
            // Tên project dùng trong test.
            'name' => 'Viewer Cancel Route Project',
        ]);

        // Thêm viewer vào project.
        TeamMember::factory()->create([
            // Gắn membership vào project.
            'project_id' => $project->id,
            // Gắn membership vào viewer.
            'user_id' => $viewer->id,
            // Người mời là owner.
            'invited_by' => $owner->id,
            // Viewer không có quyền manage_team.
            'role' => 'viewer',
        ]);

        // Owner tạo invite.
        $invite = app(SendTeamInviteAction::class)->execute($owner, $project, [
            // Email người được mời.
            'email' => 'viewer-cancel-route@keyforge.test',
            // Role hợp lệ.
            'role' => 'viewer',
        ]);

        // Viewer submit cancel invite.
        $this->actingAs($viewer)
            // Gọi destroy route.
            ->delete(route('projects.team-invites.destroy', [$project, $invite]))
            // Xác nhận bị chặn 403.
            ->assertForbidden();

        // Xác nhận invite chưa bị cancel.
        $this->assertNull($invite->fresh()->cancelled_at);
    }
}
