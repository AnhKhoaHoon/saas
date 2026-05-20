@extends('layouts.app', ['title' => 'Team Members - ' . $project->name])

@section('content')
    <div class="actions" style="margin-bottom: 2rem;">
        <a href="{{ route('projects.show', $project) }}" class="btn secondary">&larr; Back to Dashboard</a>
    </div>

    <div class="grid cols-2">
        <div class="stack">
            <section class="card stack">
                <div>
                    <h1>Team Members</h1>
                    <p class="lead">Quản lý những ai có quyền truy cập vào Project này.</p>
                </div>

                <div class="stack">
                    @forelse ($teamMembers as $member)
                        <div class="actions" style="padding: 1rem 0; border-bottom: 1px solid var(--glass-border-light); justify-content: space-between;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.2em;">
                                    {{ strtoupper(substr($member->user->name ?? $member->user->email, 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight: bold;">{{ $member->user->name ?? 'User' }}</div>
                                    <div class="muted">{{ $member->user->email }}</div>
                                </div>
                            </div>
                            <div class="actions">
                                <span style="padding: 4px 12px; border-radius: 20px; background: rgba(255,255,255,0.1); font-size: 0.85em;">{{ ucfirst($member->role) }}</span>
                                @if($member->user_id !== auth()->id() && $member->role !== 'owner')
                                    <button class="secondary" style="padding: 6px 12px; font-size: 0.85em;">Remove</button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="muted">Chưa có thành viên nào ngoài bạn.</p>
                    @endforelse
                </div>
            </section>

            <section class="card stack">
                <h2>Pending Invites</h2>
                <div class="stack">
                    @forelse ($pendingInvites as $invite)
                        <div class="actions" style="padding: 0.5rem 0; border-bottom: 1px solid var(--glass-border-light); justify-content: space-between;">
                            <div>
                                <div style="font-weight: bold;">{{ $invite->email }}</div>
                                <div class="muted" style="font-size: 0.85em;">Invited as {{ ucfirst($invite->role) }} • {{ $invite->created_at->diffForHumans() }}</div>
                            </div>
                            <button class="secondary" style="padding: 4px 10px; font-size: 0.85em;">Revoke</button>
                        </div>
                    @empty
                        <p class="muted" style="font-size: 0.9em;">Không có lời mời nào đang chờ.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <div>
            <section class="card stack" style="position: sticky; top: 20px;">
                <div>
                    <h2>Invite New Member</h2>
                    <p class="lead">Gửi email mời cộng tác viên tham gia vào Project.</p>
                </div>

                <form class="stack" method="POST" action="#">
                    @csrf
                    <div class="field">
                        <label for="email">Email Address</label>
                        <input id="email" type="email" name="email" placeholder="colleague@company.com" required>
                    </div>

                    <div class="field">
                        <label for="role">Role</label>
                        <select id="role" name="role" required>
                            <option value="admin">Admin (Can manage keys & members)</option>
                            <option value="member" selected>Member (Can view and manage keys)</option>
                            <option value="viewer">Viewer (Read-only access)</option>
                        </select>
                    </div>

                    <div class="actions" style="margin-top: 1rem;">
                        <button type="submit" style="width: 100%;">Send Invite</button>
                    </div>
                </form>

                <div class="notice" style="margin-top: 1rem; font-size: 0.85em;">
                    <strong>Lưu ý:</strong> Người được mời cần phải có tài khoản trên hệ thống hoặc sẽ được yêu cầu tạo tài khoản mới khi bấm vào link trong email.
                </div>
            </section>
        </div>
    </div>
@endsection
