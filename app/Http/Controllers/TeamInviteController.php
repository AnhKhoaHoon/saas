<?php

namespace App\Http\Controllers;

use App\Actions\AcceptTeamInviteAction;
use App\Actions\CancelTeamInviteAction;
use App\Actions\SendTeamInviteAction;
use App\Models\Project;
use App\Models\TeamInvite;
use App\Notifications\TeamInviteNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class TeamInviteController extends Controller
{
    public function store(Request $request, Project $project, SendTeamInviteAction $action): RedirectResponse
    {
        // Validate dữ liệu từ form invite member.
        $validated = $request->validate([
            // Email bắt buộc đúng định dạng và không quá dài.
            'email' => ['required', 'email', 'max:255'],
            // Role chỉ được là admin/member/viewer.
            'role' => ['required', 'string', 'in:admin,member,viewer'],
        ]);

        // Tạo hoặc refresh pending invite bằng action nghiệp vụ.
        $invite = $action->execute($request->user(), $project, $validated);

        // Gửi email invite theo dạng on-demand tới email chưa chắc đã có user account.
        Notification::route('mail', $invite->email)
            // Gửi notification chứa link accept invite.
            ->notify(new TeamInviteNotification($invite));

        // Redirect về trang team với thông báo thành công.
        return redirect()
            // Quay lại trang team của project hiện tại.
            ->route('projects.team.index', $project)
            // Flash message để UI báo cho user biết invite đã gửi.
            ->with('status', "Invite sent to {$invite->email}.");
    }

    public function accept(Request $request, string $token, AcceptTeamInviteAction $action): RedirectResponse
    {
        // Accept invite bằng user đang đăng nhập và token từ email.
        $teamMember = $action->execute($request->user(), $token);

        // Redirect về trang team của project sau khi accept thành công.
        return redirect()
            // Dùng project trên membership để đưa user tới đúng team.
            ->route('projects.team.index', $teamMember->project)
            // Flash message xác nhận đã tham gia project.
            ->with('status', "You joined {$teamMember->project->name}.");
    }

    public function destroy(Request $request, Project $project, TeamInvite $teamInvite, CancelTeamInviteAction $action): RedirectResponse
    {
        // Cancel pending invite bằng action nghiệp vụ.
        $invite = $action->execute($request->user(), $project, $teamInvite);

        // Redirect về trang team với thông báo thành công.
        return redirect()
            // Quay lại trang team của project hiện tại.
            ->route('projects.team.index', $project)
            // Flash message để UI báo invite đã bị hủy.
            ->with('status', "Invite to {$invite->email} cancelled.");
    }
}
