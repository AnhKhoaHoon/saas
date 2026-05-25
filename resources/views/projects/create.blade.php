@extends('layouts.app', ['title' => __('ui.projects.create_title')])

@section('content')
    <div class="actions" style="margin-bottom: 2rem;">
        <a href="{{ route('projects.index') }}" class="btn secondary">&larr; {{ __('ui.projects.back_to_list') }}</a>
    </div>

    <section class="card stack">
        <div>
            <h1>{{ __('ui.projects.create_title') }}</h1>
            <p class="lead">{{ __('ui.projects.create_help') }}</p>
        </div>

        <form class="stack" method="POST" action="{{ route('projects.store') }}">
            @csrf

            <div class="field">
                <label for="project_name">{{ __('ui.projects.name') }}</label>
                <input id="project_name" type="text" name="name" value="{{ old('name') }}" required autofocus>
                @error('name')<div style="color: #ff4a4a; margin-top: 4px;">{{ $message }}</div>@enderror
            </div>

            <div class="field">
                <label for="project_description">{{ __('ui.projects.description') }}</label>
                <input id="project_description" type="text" name="description" value="{{ old('description') }}">
                @error('description')<div style="color: #ff4a4a; margin-top: 4px;">{{ $message }}</div>@enderror
            </div>

            <div class="field">
                <label for="timezone">{{ __('ui.projects.timezone') }}</label>
                <input id="timezone" type="text" name="timezone" value="{{ old('timezone', config('app.timezone')) }}" required>
                @error('timezone')<div style="color: #ff4a4a; margin-top: 4px;">{{ $message }}</div>@enderror
            </div>

            <div class="actions">
                <button type="submit">{{ __('ui.projects.create_new') }}</button>
            </div>
        </form>
    </section>
@endsection
