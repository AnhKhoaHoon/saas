@extends('layouts.app', ['title' => 'API Key Detail'])

@section('content')
    <section class="card stack">
        <div class="actions">
            <div>
                <h1>API Key Detail</h1>
                <p class="lead">Thông tin đầy đủ cho key {{ $apiKey->name }} trong project {{ $project->name }}.</p>
            </div>
            <div class="actions">
                <a class="btn secondary" href="{{ route('projects.api-keys.index', $project) }}">Back to list</a>
                <a class="btn secondary" href="{{ route('home') }}">Dashboard</a>
            </div>
        </div>
    </section>

    <section class="card stack">
        <div class="grid cols-2">
            <div class="field">
                <label>Name</label>
                <span class="muted">{{ $apiKey->name }}</span>
            </div>
            <div class="field">
                <label>Status</label>
                <span class="muted">{{ $apiKey->status }}</span>
            </div>
            <div class="field">
                <label>Key prefix</label>
                <span class="muted mono">{{ $apiKey->key_prefix }}...</span>
            </div>
            <div class="field">
                <label>Created by</label>
                <span class="muted">{{ $apiKey->creator?->email ?? 'system' }}</span>
            </div>
            <div class="field">
                <label>Rate limit / minute</label>
                <span class="muted">{{ $apiKey->rate_limit_per_minute }}</span>
            </div>
            <div class="field">
                <label>Quota limit</label>
                <span class="muted">{{ $apiKey->quota_limit ?? 'Unlimited' }}</span>
            </div>
            <div class="field">
                <label>Requests count</label>
                <span class="muted">{{ $apiKey->requests_count }}</span>
            </div>
            <div class="field">
                <label>Usage logs</label>
                <span class="muted">{{ $apiKey->usage_logs_count }}</span>
            </div>
            <div class="field">
                <label>Scopes</label>
                <span class="muted">{{ $apiKey->scopes ? implode(', ', $apiKey->scopes) : 'None' }}</span>
            </div>
            <div class="field">
                <label>IP whitelist</label>
                <span class="muted">{{ $apiKey->ip_whitelist ? implode(', ', $apiKey->ip_whitelist) : 'None' }}</span>
            </div>
            <div class="field">
                <label>Last used at</label>
                <span class="muted">{{ $apiKey->last_used_at?->format('Y-m-d H:i:s') ?? 'Never used' }}</span>
            </div>
            <div class="field">
                <label>Expires at</label>
                <span class="muted">{{ $apiKey->expires_at?->format('Y-m-d H:i:s') ?? 'No expiration' }}</span>
            </div>
            <div class="field">
                <label>Revoked at</label>
                <span class="muted">{{ $apiKey->revoked_at?->format('Y-m-d H:i:s') ?? 'Active' }}</span>
            </div>
            <div class="field">
                <label>Project owner</label>
                <span class="muted">{{ $project->owner->email }}</span>
            </div>
        </div>
        </div>
    </section>

    @if ($apiKey->status === 'active')
        <section class="card stack" style="border-color: var(--danger); margin-top: 2rem;">
            <div>
                <h2 style="color: var(--danger);">Danger Zone</h2>
                <p class="lead">Thu hồi (Revoke) key này sẽ ngay lập tức chặn mọi request sử dụng nó. Hành động này không thể hoàn tác.</p>
            </div>

            <form method="POST" action="{{ route('projects.api-keys.destroy', [$project, $apiKey]) }}" onsubmit="return confirm('Bạn có chắc chắn muốn thu hồi API key này?');">
                @csrf
                @method('DELETE')
                <button type="submit" style="background: var(--danger); border-color: var(--danger);">Revoke API Key</button>
            </form>
        </section>
    @endif
@endsection
