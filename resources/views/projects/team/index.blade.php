@extends('layouts.app', ['title' => 'Team Members - ' . $project->name])

@section('content')
    <div class="actions" style="margin-bottom: 2rem;">
        <a href="{{ route('projects.show', $project) }}" class="btn secondary">&larr; {{ __('ui.common.back_to_dashboard') }}</a>
    </div>

    <div class="grid cols-2">
        <div class="stack">
            <section class="card stack">
                <div>
                    <h1>{{ __('ui.team.title') }}</h1>
                    <p class="lead">{{ __('ui.team.subtitle') }}</p>
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
                                    <button class="secondary" style="padding: 6px 12px; font-size: 0.85em;">{{ __('ui.common.delete') }}</button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="muted">{{ __('ui.team.empty_members') }}</p>
                    @endforelse
                </div>
            </section>

            <section class="card stack">
                <h2>{{ __('ui.team.pending_invites') }}</h2>
                <div class="stack">
                    @forelse ($pendingInvites as $invite)
                        <div class="actions" style="padding: 0.5rem 0; border-bottom: 1px solid var(--glass-border-light); justify-content: space-between;">
                            <div>
                                <div style="font-weight: bold;">{{ $invite->email }}</div>
                                <div class="muted" style="font-size: 0.85em;">{{ __('ui.team.invited_as', ['role' => ucfirst($invite->role), 'expires' => $invite->expires_at?->diffForHumans() ?? __('ui.common.never')]) }}</div>
                            </div>
                            <form class="inline" method="POST" action="{{ route('projects.team-invites.destroy', [$project, $invite]) }}">
                                @csrf
                                @method('DELETE')
                                <button class="secondary" type="submit" style="padding: 4px 10px; font-size: 0.85em;">{{ __('ui.common.cancel') }}</button>
                            </form>
                        </div>
                    @empty
                        <p class="muted" style="font-size: 0.9em;">{{ __('ui.team.empty_invites') }}</p>
                    @endforelse
                </div>
            </section>
        </div>

        <div>
            <section class="card stack" style="position: sticky; top: 20px;">
                <div>
                    <h2>{{ __('ui.team.invite_title') }}</h2>
                    <p class="lead">{{ __('ui.team.invite_help') }}</p>
                </div>

                <form class="stack" method="POST" action="{{ route('projects.team-invites.store', $project) }}">
                    @csrf
                    <div class="field">
                        <label for="email">{{ __('ui.team.email') }}</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="colleague@company.com" required>
                    </div>

                    <div class="field">
                        <label for="role">{{ __('ui.team.role') }}</label>
                        <select id="role" name="role" required>
                            <option value="admin" @selected(old('role') === 'admin')>Admin (Can manage keys & members)</option>
                            <option value="member" @selected(old('role', 'member') === 'member')>Member (Can view and manage keys)</option>
                            <option value="viewer" @selected(old('role') === 'viewer')>Viewer (Read-only access)</option>
                        </select>
                    </div>

                    <div class="actions" style="margin-top: 1rem;">
                        <button type="submit" style="width: 100%;">{{ __('ui.team.send') }}</button>
                    </div>
                </form>

                <div class="notice" style="margin-top: 1rem; font-size: 0.85em;">
                    <strong>Note:</strong> {{ __('ui.team.note') }}
                </div>
            </section>
        </div>
    </div>
@endsection
