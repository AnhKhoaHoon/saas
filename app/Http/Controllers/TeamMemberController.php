<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Support\Facades\Gate;

class TeamMemberController extends Controller
{
    public function index(Project $project)
    {
        // Kiểm tra user có quyền xem project/team hay không.
        Gate::authorize('view', $project);

        // Lấy danh sách member hiện tại kèm user để hiển thị tên/email.
        $teamMembers = $project->teamMembers()->with('user')->get();

        // Lấy pending invite bằng accepted_at null và chưa hết hạn.
        $pendingInvites = $project->teamInvites()
            // Chỉ lấy invite chưa accept.
            ->whereNull('accepted_at')
            // Chỉ lấy invite chưa bị cancel.
            ->whereNull('cancelled_at')
            // Chỉ lấy invite chưa hết hạn hoặc không có hạn.
            ->where(function ($query): void {
                // Invite không có expires_at vẫn được xem là pending.
                $query->whereNull('expires_at')
                    // Invite còn hạn cũng được xem là pending.
                    ->orWhere('expires_at', '>', now());
            })
            // Invite mới nhất lên trước.
            ->latest()
            // Thực thi query.
            ->get();

        // Render trang team.
        return view('projects.team.index', compact('project', 'teamMembers', 'pendingInvites'));
    }
}
