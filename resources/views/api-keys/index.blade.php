@extends('layouts.app', ['title' => __('ui.api_keys.title')])

@section('content')
    <div class="actions" style="margin-bottom: 2rem;">
        <a href="{{ route('projects.show', $project) }}" class="btn secondary">&larr; {{ __('ui.common.back_to_dashboard') }}</a>
    </div>

    @if (session('status'))
        <div class="notice" style="margin-bottom: 2rem;">
            {{ session('status') }}
        </div>
    @endif

    @if (session('new_api_key'))
        <section class="card stack" style="border-color: var(--success); margin-bottom: 2rem;">
            <div>
                <h2 style="color: var(--success);">{{ __('ui.api_keys.created_title') }}</h2>
                <p class="lead">{{ __('ui.api_keys.created_help') }}</p>
            </div>
            <div style="background: rgba(0,0,0,0.3); padding: 1rem; border-radius: 8px; border: 1px solid var(--glass-border); display: flex; justify-content: space-between; align-items: center;">
                <code style="font-size: 1.2rem; color: #fff;">{{ session('new_api_key')['plain_text_key'] }}</code>
                <button onclick="navigator.clipboard.writeText('{{ session('new_api_key')['plain_text_key'] }}'); alert('Copied to clipboard!')" class="secondary" style="padding: 6px 12px; font-size: 0.9em;">{{ __('ui.common.copy') }}</button>
            </div>
        </section>
    @endif

    <section class="card stack">
        <div class="actions" style="justify-content: space-between;">
            <div>
                <h1>{{ __('ui.api_keys.title') }}</h1>
                <p class="lead">{{ __('ui.api_keys.subtitle', ['project' => $project->name]) }}</p>
            </div>
            <a class="btn" href="{{ route('projects.api-keys.create', $project) }}">{{ __('ui.api_keys.create_new') }}</a>
        </div>

        <div class="grid cols-2">
            <div class="field">
                <label>{{ __('ui.api_keys.project_owner') }}</label>
                <span class="muted">{{ $project->owner->email }}</span>
            </div>
            <div class="field">
                <label>{{ __('ui.api_keys.total_keys') }}</label>
                <span class="muted">{{ count($apiKeys) }} keys</span>
            </div>
        </div>
    </section>

    <section class="card stack">
        <h2>{{ __('ui.api_keys.active_keys') }}</h2>
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
                        <p class="lead" style="font-size: 0.9em;">{{ __('ui.api_keys.prefix_created_by', ['prefix' => $apiKey->key_prefix, 'creator' => $apiKey->creator?->email ?? __('ui.common.system')]) }}</p>
                    </div>
                    <a class="btn secondary" href="{{ route('projects.api-keys.show', [$project, $apiKey]) }}">{{ __('ui.common.manage') }}</a>
                </div>

                <div class="grid cols-2" style="font-size: 0.85em;">
                    <div class="field">
                        <label>{{ __('ui.api_keys.rate_limit') }}</label>
                        <span class="muted">{{ $apiKey->rate_limit_per_minute ? $apiKey->rate_limit_per_minute . ' / min' : __('ui.common.unlimited') }}</span>
                    </div>
                    <div class="field">
                        <label>{{ __('ui.api_keys.quota_limit') }}</label>
                        <span class="muted">{{ $apiKey->quota_limit ? number_format($apiKey->quota_limit) . ' requests' : __('ui.common.unlimited') }}</span>
                    </div>
                    <div class="field">
                        <label>{{ __('ui.api_keys.usage_logs') }}</label>
                        <span class="muted">{{ number_format($apiKey->usage_logs_count) }}</span>
                    </div>
                    <div class="field">
                        <label>{{ __('ui.api_keys.expires_at') }}</label>
                        <span class="muted">{{ $apiKey->expires_at?->format('Y-m-d H:i:s') ?? __('ui.common.never') }}</span>
                    </div>
                </div>
            </article>
        @empty
            <p class="muted">{{ __('ui.api_keys.empty') }}</p>
        @endforelse
    </section>
@endsection
