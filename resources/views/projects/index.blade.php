@extends('layouts.app', ['title' => __('ui.projects.title')])

@section('content')
    <div class="actions" style="justify-content: space-between; margin-bottom: 2rem;">
        <div>
            <h1>{{ __('ui.projects.title') }}</h1>
            <p class="lead">{{ __('ui.projects.subtitle') }}</p>
        </div>
        <a href="{{ route('projects.create') }}" class="btn">{{ __('ui.projects.create_new') }}</a>
    </div>

    @if (session('status'))
        <div class="notice" style="margin-bottom: 2rem;">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid cols-2">
        @forelse ($projects as $project)
            <article class="card stack">
                <div class="actions">
                    <div>
                        <h3>{{ $project->name }}</h3>
                        <span class="muted">{{ $project->slug }}</span>
                    </div>
                </div>

                <p class="lead" style="min-height: 48px;">{{ $project->description ?: __('ui.common.no_description') }}</p>

                <div class="grid cols-2" style="font-size: 0.9em;">
                    <div class="field">
                        <label>{{ __('ui.common.status') }}</label>
                        <span class="muted">{{ $project->status }}</span>
                    </div>
                    <div class="field">
                        <label>{{ __('ui.projects.api_keys') }}</label>
                        <span class="muted">{{ $project->api_keys_count }}</span>
                    </div>
                    <div class="field">
                        <label>{{ __('ui.projects.team_members') }}</label>
                        <span class="muted">{{ $project->team_members_count }}</span>
                    </div>
                    <div class="field">
                        <label>{{ __('ui.projects.usage_logs') }}</label>
                        <span class="muted">{{ $project->usage_logs_count }}</span>
                    </div>
                </div>

                <div class="actions">
                    <a class="btn" href="{{ route('projects.show', $project) }}">{{ __('ui.common.dashboard') }}</a>
                    <a class="btn secondary" href="{{ route('projects.edit', $project) }}">{{ __('ui.common.settings') }}</a>
                </div>
            </article>
        @empty
            <p class="lead" style="grid-column: span 2;">{{ __('ui.projects.empty') }}</p>
        @endforelse
    </div>
@endsection
