@extends('layouts.app', ['title' => __('ui.api_keys.create_title')])

@section('content')
    <div class="actions" style="margin-bottom: 2rem;">
        <a href="{{ route('projects.api-keys.index', $project) }}" class="btn secondary">&larr; {{ __('ui.api_keys.back_to_api_keys') }}</a>
    </div>

    <section class="card stack">
        <div>
            <h1>{{ __('ui.api_keys.create_title') }}</h1>
            <p class="lead">{{ __('ui.api_keys.create_help', ['project' => $project->name]) }}</p>
        </div>

        <form class="stack" method="POST" action="{{ route('projects.api-keys.store', $project) }}">
            @csrf

            <div class="field">
                <label for="name">{{ __('ui.api_keys.key_name') }}</label>
                <input id="name" type="text" name="name" placeholder="e.g. Production Frontend App" required autofocus>
                <p class="muted" style="font-size: 0.85em;">{{ __('ui.api_keys.key_name_help') }}</p>
            </div>

            <div class="grid cols-2">
                <div class="field">
                    <label for="rate_limit_per_minute">{{ __('ui.api_keys.rate_limit_optional') }}</label>
                    <input id="rate_limit_per_minute" type="number" name="rate_limit_per_minute" placeholder="e.g. 60">
                    <p class="muted" style="font-size: 0.85em;">{{ __('ui.api_keys.rate_limit_help') }}</p>
                </div>

                <div class="field">
                    <label for="quota_limit">{{ __('ui.api_keys.quota_limit_optional') }}</label>
                    <input id="quota_limit" type="number" name="quota_limit" placeholder="e.g. 100000">
                    <p class="muted" style="font-size: 0.85em;">{{ __('ui.api_keys.quota_limit_help') }}</p>
                </div>
            </div>

            <div class="field">
                <label for="ip_whitelist">{{ __('ui.api_keys.ip_whitelist_optional') }}</label>
                <input id="ip_whitelist" type="text" name="ip_whitelist" placeholder="e.g. 192.168.1.1, 10.0.0.0/24">
                <p class="muted" style="font-size: 0.85em;">{{ __('ui.api_keys.ip_whitelist_help') }}</p>
            </div>

            <div class="field">
                <label for="expires_at">{{ __('ui.api_keys.expires_optional') }}</label>
                <input id="expires_at" type="datetime-local" name="expires_at">
                <p class="muted" style="font-size: 0.85em;">{{ __('ui.api_keys.expires_help') }}</p>
            </div>

            <div class="actions" style="margin-top: 1rem;">
                <button type="submit">{{ __('ui.api_keys.generate') }}</button>
            </div>
        </form>
    </section>
@endsection
