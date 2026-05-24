<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Xóa cache permission để Spatie đọc lại dữ liệu mới từ database.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Khai báo danh sách permission chuẩn của KeyForge.
        $permissions = [
            // Cho phép vào admin panel nội bộ.
            'admin.access',
            // Cho phép xem project dashboard.
            'projects.view',
            // Cho phép chỉnh sửa thông tin project.
            'projects.update',
            // Cho phép xóa project.
            'projects.delete',
            // Cho phép quản lý thành viên trong project.
            'projects.manage_team',
            // Cho phép xem danh sách và chi tiết API key.
            'api_keys.view',
            // Cho phép tạo API key.
            'api_keys.create',
            // Cho phép revoke API key.
            'api_keys.revoke',
            // Cho phép xem usage logs.
            'usage_logs.view',
            // Cho phép export usage logs.
            'usage_logs.export',
            // Cho phép xem billing/subscription.
            'subscriptions.view',
            // Cho phép quản lý billing/subscription.
            'subscriptions.manage',
        ];

        // Tạo từng permission nếu chưa tồn tại để seeder chạy lại vẫn an toàn.
        foreach ($permissions as $permission) {
            // Dùng firstOrCreate để không tạo trùng permission.
            Permission::firstOrCreate([
                // Lưu tên permission dạng resource.action.
                'name' => $permission,
                // Dùng guard web cho user dashboard.
                'guard_name' => 'web',
            ]);
        }

        // Tạo role platform_admin cho admin nội bộ toàn hệ thống.
        $platformAdmin = Role::firstOrCreate([
            // Role này dùng cho quyền quản trị global.
            'name' => 'platform_admin',
            // Dùng guard web để khớp guard của user đăng nhập dashboard.
            'guard_name' => 'web',
        ]);

        // Gán toàn bộ permission cho platform_admin.
        $platformAdmin->syncPermissions($permissions);

        // Tạo role owner cho chủ project.
        $owner = Role::firstOrCreate([
            // Tên role khớp với team_members.role.
            'name' => 'owner',
            // Dùng guard web cho dashboard.
            'guard_name' => 'web',
        ]);

        // Owner có toàn bộ quyền trong phạm vi project.
        $owner->syncPermissions([
            // Owner được xem project.
            'projects.view',
            // Owner được cập nhật project.
            'projects.update',
            // Owner được xóa project.
            'projects.delete',
            // Owner được quản lý team.
            'projects.manage_team',
            // Owner được xem API key.
            'api_keys.view',
            // Owner được tạo API key.
            'api_keys.create',
            // Owner được revoke API key.
            'api_keys.revoke',
            // Owner được xem usage logs.
            'usage_logs.view',
            // Owner được export usage logs.
            'usage_logs.export',
            // Owner được xem subscription.
            'subscriptions.view',
            // Owner được quản lý subscription.
            'subscriptions.manage',
        ]);

        // Tạo role admin cho người quản trị project.
        $admin = Role::firstOrCreate([
            // Tên role khớp với team_members.role.
            'name' => 'admin',
            // Dùng guard web cho dashboard.
            'guard_name' => 'web',
        ]);

        // Admin có hầu hết quyền vận hành nhưng không được xóa project.
        $admin->syncPermissions([
            // Admin được xem project.
            'projects.view',
            // Admin được cập nhật project.
            'projects.update',
            // Admin được quản lý team.
            'projects.manage_team',
            // Admin được xem API key.
            'api_keys.view',
            // Admin được tạo API key.
            'api_keys.create',
            // Admin được revoke API key.
            'api_keys.revoke',
            // Admin được xem usage logs.
            'usage_logs.view',
            // Admin được export usage logs.
            'usage_logs.export',
            // Admin được xem subscription.
            'subscriptions.view',
        ]);

        // Tạo role member cho thành viên vận hành project.
        $member = Role::firstOrCreate([
            // Tên role khớp với team_members.role.
            'name' => 'member',
            // Dùng guard web cho dashboard.
            'guard_name' => 'web',
        ]);

        // Member được thao tác API key nhưng không quản lý project/team/billing.
        $member->syncPermissions([
            // Member được xem project.
            'projects.view',
            // Member được xem API key.
            'api_keys.view',
            // Member được tạo API key.
            'api_keys.create',
            // Member được revoke API key.
            'api_keys.revoke',
            // Member được xem usage logs.
            'usage_logs.view',
            // Member được export usage logs.
            'usage_logs.export',
        ]);

        // Tạo role viewer cho người chỉ được đọc dữ liệu.
        $viewer = Role::firstOrCreate([
            // Tên role khớp với team_members.role.
            'name' => 'viewer',
            // Dùng guard web cho dashboard.
            'guard_name' => 'web',
        ]);

        // Viewer chỉ có quyền đọc, không được tạo/sửa/revoke.
        $viewer->syncPermissions([
            // Viewer được xem project.
            'projects.view',
            // Viewer được xem API key.
            'api_keys.view',
            // Viewer được xem usage logs.
            'usage_logs.view',
            // Viewer được xem subscription.
            'subscriptions.view',
        ]);

        // Xóa cache lần nữa sau khi sync role-permission xong.
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
