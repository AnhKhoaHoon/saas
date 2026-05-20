@extends('layouts.app', ['title' => 'Edit Project'])

@section('content')
    <div class="actions" style="margin-bottom: 2rem;">
        <a href="{{ route('projects.index') }}" class="btn secondary">&larr; Back to list</a>
        <a href="{{ route('projects.show', $project) }}" class="btn secondary">View dashboard</a>
    </div>

    <section class="card stack">
        <div>
            <h1>Project Settings</h1>
            <p class="lead">Chỉnh sửa thông tin workspace {{ $project->name }}.</p>
        </div>

        @if (session('status'))
            <div class="notice">
                {{ session('status') }}
            </div>
        @endif

        <form class="stack" method="POST" action="{{ route('projects.update', $project) }}">
            @csrf
            @method('PUT')

            <div class="field">
                <label for="project_name">Project name</label>
                <input id="project_name" type="text" name="name" value="{{ old('name', $project->name) }}" required>
                @error('name')<div style="color: #ff4a4a; margin-top: 4px;">{{ $message }}</div>@enderror
            </div>

            <div class="field">
                <label for="project_description">Description</label>
                <input id="project_description" type="text" name="description" value="{{ old('description', $project->description) }}">
                @error('description')<div style="color: #ff4a4a; margin-top: 4px;">{{ $message }}</div>@enderror
            </div>

            <div class="field">
                <label for="timezone">Timezone</label>
                <input id="timezone" type="text" name="timezone" value="{{ old('timezone', $project->settings['timezone'] ?? config('app.timezone')) }}" required>
                @error('timezone')<div style="color: #ff4a4a; margin-top: 4px;">{{ $message }}</div>@enderror
            </div>

            <div class="actions">
                <button type="submit">Save changes</button>
            </div>
        </form>
    </section>

    <section class="card stack" style="border-color: #ff4a4a; margin-top: 2rem;">
        <div>
            <h2 style="color: #ff4a4a;">Danger Zone</h2>
            <p class="lead">Xóa project sẽ vô hiệu hóa tất cả API keys và team members trực thuộc. Hành động này (tạm thời) có thể khôi phục do dùng SoftDelete.</p>
        </div>

        <form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('Bạn có chắc chắn muốn xóa project này?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="danger">Delete {{ $project->name }}</button>
        </form>
    </section>
@endsection
