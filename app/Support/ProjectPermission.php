<?php

namespace App\Support;

use App\Models\Project;
use App\Models\User;
use Spatie\Permission\Models\Role;

class ProjectPermission
{
    public function userCan(User $user, Project $project, string $permission): bool
    {
        // Cho admin nội bộ đi qua mọi quyền trong app.
        if ($user->is_admin) {
            // Trả về true vì admin nội bộ được toàn quyền quản trị.
            return true;
        }

        // Tìm membership của user trong đúng project đang được kiểm tra.
        $teamMember = $project->teamMembers()
            // Chỉ lấy membership thuộc user hiện tại.
            ->where('user_id', $user->id)
            // Lấy bản ghi đầu tiên vì mỗi user chỉ có một role trong một project.
            ->first();

        // Nếu user là owner legacy nhưng chưa có team_members thì vẫn dùng role owner.
        $roleName = $teamMember?->role ?? ($project->user_id === $user->id ? 'owner' : null);

        // Nếu user không thuộc project và cũng không phải owner legacy thì không có quyền.
        if (! $roleName) {
            // Trả về false để chặn outsider.
            return false;
        }

        // Tìm role Spatie tương ứng với role đang lưu trong team_members.
        $role = Role::query()
            // Role dùng guard web vì đây là quyền dashboard/web user.
            ->where('guard_name', 'web')
            // Tên role giữ cùng giá trị owner/admin/member/viewer.
            ->where('name', $roleName)
            // Lấy role đầu tiên nếu đã được seed vào bảng roles.
            ->first();

        // Nếu role chưa được seed thì mặc định chặn để tránh mở quyền ngoài ý muốn.
        if (! $role) {
            // Trả về false vì thiếu role nghĩa là RBAC chưa sẵn sàng cho quyền này.
            return false;
        }

        // Hỏi Spatie xem role này có permission được yêu cầu hay không.
        return $role->hasPermissionTo($permission, 'web');
    }
}
