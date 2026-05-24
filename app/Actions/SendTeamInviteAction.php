<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\Project;
use App\Models\TeamInvite;
use App\Models\User;
use App\Support\ProjectPermission;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SendTeamInviteAction
{
    private const ALLOWED_ROLES = ['admin', 'member', 'viewer'];

    /**
     * Create or refresh a pending team invite for a project.
     *
     * @param  array{email: string, role?: string, expires_at?: mixed}  $input
     *
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function execute(User $actor, Project $project, array $input): TeamInvite
    {
        // Kiểm tra actor có quyền quản lý team trong project này hay không.
        $this->ensureActorCanManageTeam($actor, $project);

        // Chuẩn hóa email về lowercase để tránh tạo trùng do khác chữ hoa/thường.
        $email = Str::lower(trim((string) $input['email']));

        // Lấy role được chọn, mặc định là member nếu form không truyền lên.
        $role = $input['role'] ?? 'member';

        // Kiểm tra role có nằm trong danh sách role được phép mời hay không.
        $this->ensureRoleIsAllowed($role);

        // Chặn invite nếu email này đã thuộc team của project.
        $this->ensureEmailIsNotAlreadyAMember($project, $email);

        // Tính hạn mời, mặc định 7 ngày để token không sống quá lâu.
        $expiresAt = $input['expires_at'] ?? now()->addDays(7);

        // Tạo hoặc refresh invite trong transaction để audit log luôn đi cùng dữ liệu invite.
        return DB::transaction(function () use ($actor, $project, $email, $role, $expiresAt): TeamInvite {
            // Tìm pending invite hiện có theo project + email.
            $invite = $project->teamInvites()
                // Chỉ xét lời mời gửi tới email đã chuẩn hóa.
                ->where('email', $email)
                // Chỉ refresh lời mời chưa được accept.
                ->whereNull('accepted_at')
                // Chỉ refresh lời mời chưa bị cancel.
                ->whereNull('cancelled_at')
                // Lấy invite pending đầu tiên nếu có.
                ->first();

            // Nếu chưa có pending invite thì tạo model mới.
            $invite ??= new TeamInvite([
                // Gắn invite vào project hiện tại.
                'project_id' => $project->id,
                // Lưu email người được mời.
                'email' => $email,
            ]);

            // Lưu người gửi invite hiện tại.
            $invite->invited_by = $actor->id;

            // Lưu role mà người được mời sẽ nhận khi accept.
            $invite->role = $role;

            // Tạo token mới mỗi lần gửi lại để vô hiệu hóa link cũ.
            $invite->token = $this->generateUniqueToken();

            // Lưu hạn sử dụng của invite.
            $invite->expires_at = $expiresAt;

            // Đảm bảo invite vẫn ở trạng thái pending khi resend.
            $invite->accepted_at = null;

            // Persist invite vào database.
            $invite->save();

            // Ghi audit log để biết ai đã gửi hoặc refresh invite.
            AuditLog::create([
                // User thực hiện hành động.
                'user_id' => $actor->id,
                // Project liên quan tới invite.
                'project_id' => $project->id,
                // Model được audit là TeamInvite.
                'auditable_type' => TeamInvite::class,
                // ID của invite vừa tạo hoặc refresh.
                'auditable_id' => $invite->id,
                // Tên action dùng để filter audit log.
                'action' => 'team_invite.sent',
                // Mô tả ngắn cho admin/support đọc nhanh.
                'description' => "Invite sent to {$invite->email} as {$invite->role}.",
                // Metadata chi tiết nhưng không lưu token để tránh lộ invite link.
                'meta' => [
                    // Email người được mời.
                    'email' => $invite->email,
                    // Role sẽ được gán khi accept.
                    'role' => $invite->role,
                    // Thời điểm invite hết hạn.
                    'expires_at' => $invite->expires_at?->toISOString(),
                ],
                // Thời điểm xảy ra action.
                'occurred_at' => now(),
            ]);

            // Trả về invite bản mới nhất cho controller hoặc mail job dùng tiếp.
            return $invite->fresh(['project', 'inviter']);
        });
    }

    /**
     * @throws AuthorizationException
     */
    private function ensureActorCanManageTeam(User $actor, Project $project): void
    {
        // Kiểm tra permission projects.manage_team theo role của actor trong project.
        $canManageTeam = app(ProjectPermission::class)->userCan($actor, $project, 'projects.manage_team');

        // Nếu actor không có quyền quản lý team thì chặn action.
        if (! $canManageTeam) {
            // Ném AuthorizationException để controller/test nhận đúng lỗi 403.
            throw new AuthorizationException('You are not allowed to invite members to this project.');
        }
    }

    /**
     * @throws ValidationException
     */
    private function ensureRoleIsAllowed(string $role): void
    {
        // Kiểm tra role được truyền vào có thuộc danh sách cho phép hay không.
        if (! in_array($role, self::ALLOWED_ROLES, true)) {
            // Ném lỗi validation để form hiển thị lỗi ở field role.
            throw ValidationException::withMessages([
                // Gắn message vào field role.
                'role' => 'The selected role is not allowed for team invites.',
            ]);
        }
    }

    /**
     * @throws ValidationException
     */
    private function ensureEmailIsNotAlreadyAMember(Project $project, string $email): void
    {
        // Kiểm tra email này đã có user nằm trong team project hay chưa.
        $alreadyMember = $project->teamMembers()
            // Join bảng users để so email của member.
            ->whereHas('user', function ($query) use ($email): void {
                // So email dạng lowercase để tránh lệch chữ hoa/thường.
                $query->whereRaw('LOWER(email) = ?', [$email]);
            })
            // Chỉ cần biết có tồn tại hay không.
            ->exists();

        // Nếu email đã là member thì không gửi invite nữa.
        if ($alreadyMember) {
            // Ném lỗi validation để form hiển thị lỗi ở field email.
            throw ValidationException::withMessages([
                // Gắn message vào field email.
                'email' => 'This user is already a member of the project.',
            ]);
        }
    }

    private function generateUniqueToken(): string
    {
        do {
            // Tạo token dài 64 ký tự để link invite khó đoán.
            $token = Str::random(64);
        } while (
            // Lặp lại nếu token hiếm hoi bị trùng trong database.
            TeamInvite::query()->where('token', $token)->exists()
        );

        // Trả về token duy nhất để lưu vào invite.
        return $token;
    }
}
