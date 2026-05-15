@extends('layouts.app', ['title' => 'API Keys'])

@section('content')
    <section class="card stack">
        <div class="actions">
            <div>
                <h1>API Keys</h1>
                <p class="lead">Danh sách đầy đủ API key của project {{ $project->name }}.</p>
            </div>
            <a class="btn secondary" href="{{ route('home') }}">Back to dashboard</a>
        </div>

        <div class="grid cols-2">
            <div class="field">
                <label>Project</label>
                <span class="muted">{{ $project->name }}</span>
            </div>
            <div class="field">
                <label>Owner</label>
                <span class="muted">{{ $project->owner->email }}</span>
            </div>
        </div>
    </section>

    <section class="card stack">
        @forelse ($apiKeys as $apiKey)
            <article class="card stack">
                <div class="actions">
                    <div>
                        <h3>{{ $apiKey->name }}</h3>
                        <p class="lead">Prefix {{ $apiKey->key_prefix }}... • Created by {{ $apiKey->creator?->email ?? 'system' }}</p>
                    </div>
                    <a class="btn secondary" href="{{ route('projects.api-keys.show', [$project, $apiKey]) }}">View detail</a>
                </div>

                <div class="grid cols-2">
                    <div class="field">
                        <label>Status</label>
                        <span class="muted">{{ $apiKey->status }}</span>
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
                        <label>Usage logs</label>
                        <span class="muted">{{ $apiKey->usage_logs_count }}</span>
                    </div>
                    <div class="field">
                        <label>Scopes</label>
                        <span class="muted">{{ $apiKey->scopes ? implode(', ', $apiKey->scopes) : 'None' }}</span>
                    </div>
                    <div class="field">
                        <label>Expires at</label>
                        <span class="muted">{{ $apiKey->expires_at?->format('Y-m-d H:i:s') ?? 'No expiration' }}</span>
                    </div>
                </div>
            </article>
        @empty
            <p class="lead">Project này chưa có API key nào.</p>
        @endforelse
    </section>
@endsection
