@extends('layouts.app', ['title' => 'Projects'])

@section('content')
    <div class="actions" style="justify-content: space-between; margin-bottom: 2rem;">
        <div>
            <h1>Projects</h1>
            <p class="lead">Quản lý các workspace của bạn.</p>
        </div>
        <a href="{{ route('projects.create') }}" class="btn">Create new project</a>
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

                <p class="lead" style="min-height: 48px;">{{ $project->description ?: 'No description yet.' }}</p>

                <div class="grid cols-2" style="font-size: 0.9em;">
                    <div class="field">
                        <label>Status</label>
                        <span class="muted">{{ $project->status }}</span>
                    </div>
                    <div class="field">
                        <label>API keys</label>
                        <span class="muted">{{ $project->api_keys_count }}</span>
                    </div>
                    <div class="field">
                        <label>Team members</label>
                        <span class="muted">{{ $project->team_members_count }}</span>
                    </div>
                    <div class="field">
                        <label>Usage logs</label>
                        <span class="muted">{{ $project->usage_logs_count }}</span>
                    </div>
                </div>

                <div class="actions">
                    <a class="btn" href="{{ route('projects.show', $project) }}">Dashboard</a>
                    <a class="btn secondary" href="{{ route('projects.edit', $project) }}">Settings</a>
                </div>
            </article>
        @empty
            <p class="lead" style="grid-column: span 2;">Chưa có project nào. Hãy tạo project đầu tiên để bắt đầu hệ thống.</p>
        @endforelse
    </div>
@endsection
