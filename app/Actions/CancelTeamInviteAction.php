<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\Project;
use App\Models\TeamInvite;
use App\Models\User;
use App\Support\ProjectPermission;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CancelTeamInviteAction
{
    /**
     * Cancel a pending team invite without deleting its audit history.
     *
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function execute(User $actor, Project $project, TeamInvite $invite): TeamInvite
    {
        // Kiểm tra invite có thuộc đúng project đang thao tác hay không.
        $this->ensureInviteBelongsToProject($project, $invite);

        // Kiểm tra actor có quyền quản lý team trong project này hay không.
        $this->ensureActorCanManageTeam($actor, $project);

        // Kiểm tra invite còn có thể cancel hay không.
        $this->ensureInviteCanBeCancelled($invite);

        // Dùng transaction để cancelled_at và audit log luôn đi cùng nhau.
        return DB::transaction(function () use ($actor, $project, $invite): TeamInvite {
            // Đánh dấu invite đã bị hủy thay vì xóa khỏi database.
            $invite->forceFill([
                // cancelled_at giúp loại invite khỏi pending list và chặn accept link.
                'cancelled_at' => now(),
            ])->save();

            // Ghi audit log cho hành động cancel invite.
            AuditLog::create([
                // User thực hiện hành động.
                'user_id' => $actor->id,
                // Project liên quan.
                'project_id' => $project->id,
                // Model được audit là TeamInvite.
                'auditable_type' => TeamInvite::class,
                // ID invite đã bị hủy.
                'auditable_id' => $invite->id,
                // Tên action dùng để filter audit log.
                'action' => 'team_invite.cancelled',
                // Mô tả ngắn cho admin/support.
                'description' => "Invite to {$invite->email} was cancelled.",
                // Metadata không chứa token để tránh lộ invite link.
                'meta' => [
                    // Email người từng được mời.
                    'email' => $invite->email,
                    // Role trong invite đã hủy.
                    'role' => $invite->role,
                    // Thời điểm invite bị hủy.
                    'cancelled_at' => $invite->cancelled_at?->toISOString(),
                ],
                // Thời điểm xảy ra action.
                'occurred_at' => now(),
            ]);

            // Trả về invite mới nhất cho controller hoặc test dùng tiếp.
            return $invite->fresh(['project', 'inviter']);
        });
    }

    /**
     * @throws ValidationException
     */
    private function ensureInviteBelongsToProject(Project $project, TeamInvite $invite): void
    {
        // So project_id của invite với project trong URL.
        if ($invite->project_id !== $project->id) {
            // Ném validation để tránh thao tác nhầm hoặc cố ý trên project khác.
            throw ValidationException::withMessages([
                // Message gắn vào invite.
                'invite' => 'This invite does not belong to the selected project.',
            ]);
        }
    }

    /**
     * @throws AuthorizationException
     */
    private function ensureActorCanManageTeam(User $actor, Project $project): void
    {
        // Kiểm tra permission projects.manage_team theo RBAC hiện tại.
        $canManageTeam = app(ProjectPermission::class)->userCan($actor, $project, 'projects.manage_team');

        // Nếu actor không có quyền quản lý team thì chặn action.
        if (! $canManageTeam) {
            // Ném AuthorizationException để controller trả về 403.
            throw new AuthorizationException('You are not allowed to cancel invites for this project.');
        }
    }

    /**
     * @throws ValidationException
     */
    private function ensureInviteCanBeCancelled(TeamInvite $invite): void
    {
        // Chặn cancel invite đã được accept.
        if ($invite->accepted_at !== null) {
            // Ném validation vì invite đã chuyển thành membership.
            throw ValidationException::withMessages([
                // Message gắn vào invite.
                'invite' => 'Accepted invites cannot be cancelled.',
            ]);
        }

        // Chặn cancel invite đã bị cancel trước đó.
        if ($invite->cancelled_at !== null) {
            // Ném validation để action idempotency không che giấu thao tác lặp.
            throw ValidationException::withMessages([
                // Message gắn vào invite.
                'invite' => 'This invite has already been cancelled.',
            ]);
        }
    }
}
