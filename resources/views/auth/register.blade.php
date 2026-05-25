@extends('layouts.app', ['title' => __('ui.auth.register')])

@section('content')
    <section class="card stack">
        <div>
            <h1>{{ __('ui.auth.create_account') }}</h1>
            <p class="lead">{{ __('ui.welcome.subtitle') }}</p>
        </div>

        <form class="stack" method="POST" action="{{ route('register.store') }}">
            @csrf

            <div class="field">
                <label for="name">{{ __('ui.auth.name') }}</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name">
            </div>

            <div class="field">
                <label for="email">{{ __('ui.auth.email') }}</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
            </div>

            <div class="field">
                <label for="password">{{ __('ui.auth.password') }}</label>
                <input id="password" type="password" name="password" required autocomplete="new-password">
            </div>

            <div class="field">
                <label for="password_confirmation">{{ __('ui.auth.confirm_password') }}</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
            </div>

            <div class="actions">
                <button type="submit">{{ __('ui.auth.register') }}</button>
                <a class="btn secondary" href="{{ route('login') }}">{{ __('ui.welcome.already_have_account') }}</a>
            </div>
        </form>
    </section>
@endsection
