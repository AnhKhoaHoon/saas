@extends('layouts.app', ['title' => 'Create Project'])

@section('content')
    <div class="actions" style="margin-bottom: 2rem;">
        <a href="{{ route('projects.index') }}" class="btn secondary">&larr; Back</a>
    </div>

    <section class="card stack">
        <div>
            <h1>Create project</h1>
            <p class="lead">Mỗi project là một workspace tách biệt cho API keys, usage logs, thành viên và quota.</p>
        </div>

        <form class="stack" method="POST" action="{{ route('projects.store') }}">
            @csrf

            <div class="field">
                <label for="project_name">Project name</label>
                <input id="project_name" type="text" name="name" value="{{ old('name') }}" required autofocus>
                @error('name')<div style="color: #ff4a4a; margin-top: 4px;">{{ $message }}</div>@enderror
            </div>

            <div class="field">
                <label for="project_description">Description</label>
                <input id="project_description" type="text" name="description" value="{{ old('description') }}">
                @error('description')<div style="color: #ff4a4a; margin-top: 4px;">{{ $message }}</div>@enderror
            </div>

            <div class="field">
                <label for="timezone">Timezone</label>
                <input id="timezone" type="text" name="timezone" value="{{ old('timezone', config('app.timezone')) }}" required>
                @error('timezone')<div style="color: #ff4a4a; margin-top: 4px;">{{ $message }}</div>@enderror
            </div>

            <div class="actions">
                <button type="submit">Create project</button>
            </div>
        </form>
    </section>
@endsection
