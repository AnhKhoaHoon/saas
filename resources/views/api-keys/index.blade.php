@extends('layouts.app', ['title' => 'API Keys'])

@section('content')
    <div class="actions" style="margin-bottom: 2rem;">
        <a href="{{ route('projects.show', $project) }}" class="btn secondary">&larr; Back to Dashboard</a>
    </div>

    @if (session('status'))
        <div class="notice" style="margin-bottom: 2rem;">
            {{ session('status') }}
        </div>
    @endif

    @if (session('new_api_key'))
        <section class="card stack" style="border-color: var(--success); margin-bottom: 2rem;">
            <div>
                <h2 style="color: var(--success);">🎉 API Key Created Successfully!</h2>
                <p class="lead">Đây là Secret Key của bạn. Vui lòng copy và lưu trữ ở nơi an toàn. <strong>Nó sẽ không bao giờ được hiển thị lại!</strong></p>
            </div>
            <div style="background: rgba(0,0,0,0.3); padding: 1rem; border-radius: 8px; border: 1px solid var(--glass-border); display: flex; justify-content: space-between; align-items: center;">
                <code style="font-size: 1.2rem; color: #fff;">{{ session('new_api_key')['plain_text_key'] }}</code>
                <button onclick="navigator.clipboard.writeText('{{ session('new_api_key')['plain_text_key'] }}'); alert('Copied to clipboard!')" class="secondary" style="padding: 6px 12px; font-size: 0.9em;">Copy</button>
            </div>
        </section>
    @endif

    <section class="card stack">
        <div class="actions" style="justify-content: space-between;">
            <div>
                <h1>API Keys</h1>
                <p class="lead">Quản lý và cấp quyền truy cập API cho project <strong>{{ $project->name }}</strong>.</p>
            </div>
            <a class="btn" href="{{ route('projects.api-keys.create', $project) }}">Create new API key</a>
        </div>

        <div class="grid cols-2">
            <div class="field">
                <label>Project Owner</label>
                <span class="muted">{{ $project->owner->email }}</span>
            </div>
            <div class="field">
                <label>Total Keys</label>
                <span class="muted">{{ count($apiKeys) }} keys</span>
            </div>
        </div>
    </section>

    <section class="card stack">
        <h2>Active Keys</h2>
        @forelse ($apiKeys as $apiKey)
            <article class="card stack" style="background: rgba(0,0,0,0.15);">
                <div class="actions" style="justify-content: space-between;">
                    <div>
                        <h3 style="display: flex; align-items: center; gap: 8px;">
                            {{ $apiKey->name }}
                            @if($apiKey->status !== 'active')
                                <span style="font-size: 0.6em; padding: 2px 8px; border-radius: 12px; background: var(--danger); color: white;">{{ strtoupper($apiKey->status) }}</span>
                            @endif
                        </h3>
                        <p class="lead" style="font-size: 0.9em;">Prefix <code style="color: #fff;">{{ $apiKey->key_prefix }}...</code> • Created by {{ $apiKey->creator?->email ?? 'system' }}</p>
                    </div>
                    <a class="btn secondary" href="{{ route('projects.api-keys.show', [$project, $apiKey]) }}">Manage</a>
                </div>

                <div class="grid cols-2" style="font-size: 0.85em;">
                    <div class="field">
                        <label>Rate limit</label>
                        <span class="muted">{{ $apiKey->rate_limit_per_minute ? $apiKey->rate_limit_per_minute . ' / min' : 'Unlimited' }}</span>
                    </div>
                    <div class="field">
                        <label>Quota limit</label>
                        <span class="muted">{{ $apiKey->quota_limit ? number_format($apiKey->quota_limit) . ' requests' : 'Unlimited' }}</span>
                    </div>
                    <div class="field">
                        <label>Usage logs</label>
                        <span class="muted">{{ number_format($apiKey->usage_logs_count) }}</span>
                    </div>
                    <div class="field">
                        <label>Expires at</label>
                        <span class="muted">{{ $apiKey->expires_at?->format('Y-m-d H:i:s') ?? 'Never' }}</span>
                    </div>
                </div>
            </article>
        @empty
            <p class="muted">Project này chưa có API key nào. Nhấn "Create new API key" để bắt đầu.</p>
        @endforelse
    </section>
@endsection
