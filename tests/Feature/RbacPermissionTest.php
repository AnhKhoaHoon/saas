<?php

namespace Tests\Feature;

use App\Actions\CreateApiKeyAction;
use App\Actions\CreateProjectAction;
use App\Actions\RevokeApiKeyAction;
use App\Models\ApiKey;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RbacPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_spatie_roles_and_permissions_are_seeded(): void
    {
        // Kiểm tra permission tạo API key đã tồn tại trong bảng permissions.
        $this->assertDatabaseHas('permissions', [
            // Tên permission chuẩn cho hành động tạo API key.
            'name' => 'api_keys.create',
            // Guard web là guard chính của dashboard.
            'guard_name' => 'web',
        ]);

        // Lấy role owner từ bảng roles của Spatie.
        $ownerRole = Role::findByName('owner');

        // Xác nhận owner có quyền tạo API key.
        $this->assertTrue($ownerRole->hasPermissionTo('api_keys.create'));

        // Lấy role viewer từ bảng roles của Spatie.
        $viewerRole = Role::findByName('viewer');

        // Xác nhận viewer không có quyền tạo API key.
        $this->assertFalse($viewerRole->hasPermissionTo('api_keys.create'));

        // Xác nhận permission admin.access cũng được seed cho admin panel.
        $this->assertTrue(Permission::where('name', 'admin.access')->exists());
    }

    public function test_viewer_can_view_project_but_cannot_create_api_key(): void
    {
        // Tạo owner dùng để sở hữu project.
        $owner = User::factory()->create();

        // Tạo viewer dùng để kiểm tra role chỉ đọc.
        $viewer = User::factory()->create();

        // Tạo project bằng action để tự sinh owner membership.
        $project = app(CreateProjectAction::class)->execute($owner, [
            // Đặt tên project cho test.
            'name' => 'Viewer Project',
        ]);

        // Thêm viewer vào project với role viewer.
        TeamMember::factory()->create([
            // Gắn membership vào project vừa tạo.
            'project_id' => $project->id,
            // Gắn membership vào user viewer.
            'user_id' => $viewer->id,
            // Người mời là owner.
            'invited_by' => $owner->id,
            // Role viewer chỉ được đọc.
            'role' => 'viewer',
        ]);

        // Viewer được phép xem project.
        $this->assertTrue(Gate::forUser($viewer)->allows('view', $project));

        // Viewer không được phép cập nhật project.
        $this->assertFalse(Gate::forUser($viewer)->allows('update', $project));

        // Chuẩn bị kỳ vọng action tạo key sẽ bị chặn.
        $this->expectException(AuthorizationException::class);

        // Viewer thử tạo API key và phải bị từ chối.
        app(CreateApiKeyAction::class)->execute($viewer, $project, [
            // Tên key không quan trọng vì request phải fail ở authorization.
            'name' => 'Viewer Forbidden Key',
        ]);
    }

    public function test_member_can_create_and_revoke_api_key(): void
    {
        // Tạo owner dùng để sở hữu project.
        $owner = User::factory()->create();

        // Tạo member dùng để kiểm tra quyền vận hành API key.
        $member = User::factory()->create();

        // Tạo project bằng action để có owner membership đúng flow.
        $project = app(CreateProjectAction::class)->execute($owner, [
            // Đặt tên project cho test.
            'name' => 'Member Project',
        ]);

        // Thêm member vào project với role member.
        TeamMember::factory()->create([
            // Gắn membership vào project vừa tạo.
            'project_id' => $project->id,
            // Gắn membership vào user member.
            'user_id' => $member->id,
            // Người mời là owner.
            'invited_by' => $owner->id,
            // Role member được phép tạo và revoke API key.
            'role' => 'member',
        ]);

        // Member tạo API key qua action production.
        $result = app(CreateApiKeyAction::class)->execute($member, $project, [
            // Tên key dùng để assert dễ đọc.
            'name' => 'Member Managed Key',
        ]);

        // Xác nhận key do member tạo.
        $this->assertSame($member->id, $result->apiKey->created_by);

        // Member revoke chính API key vừa tạo.
        $revokedKey = app(RevokeApiKeyAction::class)->execute($member, $result->apiKey);

        // Xác nhận trạng thái key đã chuyển sang revoked.
        $this->assertSame('revoked', $revokedKey->status);
    }

    public function test_project_admin_can_update_but_cannot_delete_project(): void
    {
        // Tạo owner dùng để sở hữu project.
        $owner = User::factory()->create();

        // Tạo admin dùng để kiểm tra role quản trị project.
        $admin = User::factory()->create();

        // Tạo project bằng action để có owner membership đúng flow.
        $project = app(CreateProjectAction::class)->execute($owner, [
            // Đặt tên project cho test.
            'name' => 'Admin Project',
        ]);

        // Thêm admin vào project với role admin.
        TeamMember::factory()->create([
            // Gắn membership vào project vừa tạo.
            'project_id' => $project->id,
            // Gắn membership vào user admin.
            'user_id' => $admin->id,
            // Người mời là owner.
            'invited_by' => $owner->id,
            // Role admin được update nhưng không được delete project.
            'role' => 'admin',
        ]);

        // Admin được phép cập nhật project.
        $this->assertTrue(Gate::forUser($admin)->allows('update', $project));

        // Admin không được phép xóa project.
        $this->assertFalse(Gate::forUser($admin)->allows('delete', $project));
    }

    public function test_outsider_cannot_view_api_key_pages(): void
    {
        // Tạo owner dùng để sở hữu project.
        $owner = User::factory()->create();

        // Tạo outsider không thuộc project.
        $outsider = User::factory()->create();

        // Tạo project bằng action để có owner membership đúng flow.
        $project = app(CreateProjectAction::class)->execute($owner, [
            // Đặt tên project cho test.
            'name' => 'Private Project',
        ]);

        // Tạo API key thuộc project để có trang cần bảo vệ.
        ApiKey::factory()->active()->create([
            // Gắn key vào project vừa tạo.
            'project_id' => $project->id,
            // Người tạo key là owner.
            'created_by' => $owner->id,
        ]);

        // Outsider truy cập danh sách API key phải bị chặn.
        $this->actingAs($outsider)
            // Gọi route index API key của project.
            ->get(route('projects.api-keys.index', $project))
            // Xác nhận response là 403 Forbidden.
            ->assertForbidden();
    }
}
