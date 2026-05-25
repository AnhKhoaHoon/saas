@extends('layouts.app', ['title' => 'Dashboard: ' . $project->name])

@section('content')
    <div class="actions" style="margin-bottom: 2rem;">
        <a href="{{ route('projects.index') }}" class="btn secondary">&larr; {{ __('ui.projects.back_to_list') }}</a>
    </div>

    @if (session('status'))
        <div class="notice" style="margin-bottom: 2rem;">
            {{ session('status') }}
        </div>
    @endif

    <section class="card stack">
        <div class="actions" style="justify-content: space-between;">
            <div>
                <h1>{{ $project->name }} <span class="muted" style="font-size: 0.5em; font-weight: normal;">{{ $project->slug }}</span></h1>
                <p class="lead">{{ $project->description ?: __('ui.projects.workspace_dashboard') }}</p>
            </div>
            <div class="actions">
                <a href="{{ route('projects.team.index', $project) }}" class="btn secondary">{{ __('ui.projects.team_members') }}</a>
                <a href="{{ route('projects.edit', $project) }}" class="btn secondary">{{ __('ui.common.settings') }}</a>
            </div>
        </div>

        <div class="grid cols-2" style="font-size: 0.9em; margin-bottom: 1rem;">
            <div class="field">
                <label>{{ __('ui.common.status') }}</label>
                <span class="muted">{{ $project->status }}</span>
            </div>
            <div class="field">
                <label>{{ __('ui.projects.timezone') }}</label>
                <span class="muted">{{ $project->settings['timezone'] ?? config('app.timezone') }}</span>
            </div>
        </div>
    </section>

    <!-- Biểu đồ thống kê gọi API -->
    <section class="card stack">
        <div>
            <h2>{{ __('ui.projects.usage_stats') }}</h2>
            <p class="lead">{{ __('ui.projects.usage_stats_help') }}</p>
        </div>
        
        <div style="background: #111; padding: 1rem; border-radius: 8px; border: 1px solid #333;">
            <livewire:project-stats-chart :project="$project" />
        </div>
    </section>

    <div class="grid cols-2">
        <section class="card stack">
            <div class="actions" style="justify-content: space-between;">
                <h2>{{ __('ui.projects.recent_api_keys') }}</h2>
                <a href="{{ route('projects.api-keys.index', $project) }}" class="btn secondary">{{ __('ui.projects.view_all') }}</a>
            </div>
            
            <div class="stack">
                @forelse ($apiKeys as $apiKey)
                    <div class="actions" style="padding: 0.5rem 0; border-bottom: 1px solid #333; justify-content: space-between;">
                        <div>
                            <strong>{{ $apiKey->name }}</strong>
                            <div class="muted mono" style="font-size: 0.85em;">{{ $apiKey->key_prefix }}...</div>
                        </div>
                        <span class="muted" style="font-size: 0.85em;">{{ $apiKey->status }}</span>
                    </div>
                @empty
                    <p class="muted">{{ __('ui.projects.empty_api_keys') }}</p>
                @endforelse
                <div style="margin-top: 1rem;">
                    <a href="{{ route('projects.api-keys.index', $project) }}#create" class="btn">{{ __('ui.projects.create_new_key') }}</a>
                </div>
            </div>
        </section>

        <section class="card stack">
            <div class="actions" style="justify-content: space-between;">
                <h2>{{ __('ui.projects.recent_usage_logs') }}</h2>
                <a href="{{ route('projects.usage-logs.index', $project) }}" class="btn secondary">{{ __('ui.projects.view_all_export') }}</a>
            </div>
            <div class="stack" style="font-size: 0.9em;">
                @forelse ($usageLogs as $log)
                    <div class="actions" style="padding: 0.5rem 0; border-bottom: 1px solid #333; justify-content: space-between;">
                        <div>
                            <strong style="color: {{ $log->status_code >= 400 ? '#ff4a4a' : '#4aff80' }}">{{ $log->method }} {{ $log->status_code }}</strong>
                            <div class="muted mono">{{ $log->endpoint }}</div>
                        </div>
                        <div style="text-align: right;">
                            <div>{{ $log->occurred_at->diffForHumans() }}</div>
                            <span class="muted mono">{{ $log->response_time_ms }}ms</span>
                        </div>
                    </div>
                @empty
                    <p class="muted">{{ __('ui.projects.empty_requests') }}</p>
                @endforelse
            </div>
        </section>
    </div>
@endsection
