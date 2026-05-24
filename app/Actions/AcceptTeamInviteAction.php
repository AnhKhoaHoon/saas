<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\TeamInvite;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AcceptTeamInviteAction
{
    /**
     * Accept a pending invite and convert it into a project membership.
     *
     * @throws ValidationException
     */
    public function execute(User $actor, string $token): TeamMember
    {
        // Tìm invite theo token trong database.
        $invite = TeamInvite::query()
            // Load project để dùng cho membership và audit log.
            ->with('project')
            // So khớp token được gửi từ link invite.
            ->where('token', $token)
            // Lấy invite đầu tiên nếu tồn tại.
            ->first();

        // Nếu token không tồn tại thì báo lỗi validation.
        if (! $invite) {
            // Ném lỗi ở field token để controller có thể hiển thị thông báo.
            throw ValidationException::withMessages([
                // Message ngắn, không tiết lộ token nào hợp lệ.
                'token' => 'This invite link is invalid.',
            ]);
        }

        // Kiểm tra invite vẫn còn pending và hợp lệ.
        $this->ensureInviteCanBeAccepted($invite, $actor);

        // Dùng transaction để membership và invite accepted_at luôn đồng bộ.
        return DB::transaction(function () use ($invite, $actor): TeamMember {
            // Tạo hoặc lấy membership hiện có để tránh duplicate khi user bấm link nhiều lần.
            $teamMember = TeamMember::firstOrCreate(
                [
                    // Membership thuộc project của invite.
                    'project_id' => $invite->project_id,
                    // Membership thuộc user đang accept.
                    'user_id' => $actor->id,
                ],
                [
                    // Lưu người đã gửi invite ban đầu.
                    'invited_by' => $invite->invited_by,
                    // Gán role từ invite.
                    'role' => $invite->role,
                    // Lưu thời điểm user tham gia team.
                    'joined_at' => now(),
                ]
            );

            // Nếu membership đã tồn tại trước đó thì cập nhật role theo invite hiện tại.
            if (! $teamMember->wasRecentlyCreated) {
                // Cập nhật metadata membership để phản ánh invite được accept.
                $teamMember->forceFill([
                    // Giữ người mời theo invite mới nhất.
                    'invited_by' => $invite->invited_by,
                    // Đồng bộ role từ invite.
                    'role' => $invite->role,
                    // Nếu joined_at đang trống thì set thời điểm hiện tại.
                    'joined_at' => $teamMember->joined_at ?? now(),
                ])->save();
            }

            // Đánh dấu invite đã được accept.
            $invite->forceFill([
                // accepted_at giúp phân biệt pending invite và invite đã dùng.
                'accepted_at' => now(),
            ])->save();

            // Ghi audit log cho sự kiện accept invite.
            AuditLog::create([
                // User accept invite.
                'user_id' => $actor->id,
                // Project được tham gia.
                'project_id' => $invite->project_id,
                // Model audit là TeamInvite.
                'auditable_type' => TeamInvite::class,
                // ID invite đã accept.
                'auditable_id' => $invite->id,
                // Tên action dùng để filter audit log.
                'action' => 'team_invite.accepted',
                // Mô tả ngắn cho admin/support.
                'description' => "{$actor->email} accepted an invite to {$invite->project->name}.",
                // Metadata không chứa token để tránh lộ link invite.
                'meta' => [
                    // Email được mời.
                    'email' => $invite->email,
                    // Role được gán.
                    'role' => $invite->role,
                    // ID membership được tạo/cập nhật.
                    'team_member_id' => $teamMember->id,
                ],
                // Thời điểm xảy ra action.
                'occurred_at' => now(),
            ]);

            // Trả về membership mới nhất cùng user/project để caller dùng tiếp.
            return $teamMember->fresh(['user', 'project', 'inviter']);
        });
    }

    /**
     * @throws ValidationException
     */
    private function ensureInviteCanBeAccepted(TeamInvite $invite, User $actor): void
    {
        // Chặn nếu invite đã được accept trước đó.
        if ($invite->accepted_at !== null) {
            // Ném lỗi validation cho token đã dùng.
            throw ValidationException::withMessages([
                // Message rõ ràng cho user.
                'token' => 'This invite has already been accepted.',
            ]);
        }

        // Chặn nếu invite đã bị cancel.
        if ($invite->cancelled_at !== null) {
            // Ném lỗi validation cho token đã bị hủy.
            throw ValidationException::withMessages([
                // Message rõ ràng cho user.
                'token' => 'This invite has been cancelled.',
            ]);
        }

        // Chặn nếu invite đã hết hạn.
        if ($invite->expires_at !== null && $invite->expires_at->isPast()) {
            // Ném lỗi validation cho token hết hạn.
            throw ValidationException::withMessages([
                // Message rõ ràng cho user.
                'token' => 'This invite has expired.',
            ]);
        }

        // Chuẩn hóa email của user đang accept.
        $actorEmail = Str::lower($actor->email);

        // Chuẩn hóa email đã được mời.
        $inviteEmail = Str::lower($invite->email);

        // Chỉ user có đúng email được mời mới được accept.
        if ($actorEmail !== $inviteEmail) {
            // Ném lỗi để tránh user khác chiếm invite link.
            throw ValidationException::withMessages([
                // Message gắn vào email vì lỗi do tài khoản không khớp.
                'email' => 'This invite was sent to a different email address.',
            ]);
        }
    }
}
