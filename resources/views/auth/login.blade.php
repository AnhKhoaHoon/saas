@extends('layouts.app', ['title' => __('ui.auth.sign_in')])

@section('content')
    <section class="card stack">
        <div>
            <h1>{{ __('ui.auth.sign_in') }}</h1>
            <p class="lead">{{ __('ui.auth.sign_in_help') }}</p>
        </div>

        <form class="stack" method="POST" action="{{ route('login.store') }}">
            @csrf

            <div class="field">
                <label for="email">{{ __('ui.auth.email') }}</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            </div>

            <div class="field">
                <label for="password">{{ __('ui.auth.password') }}</label>
                <input id="password" type="password" name="password" required autocomplete="current-password">
            </div>

            <div class="actions">
                <label class="muted">
                    <input type="checkbox" name="remember">
                    {{ __('ui.auth.remember_me') }}
                </label>
            </div>

            <div class="actions">
                <button type="submit">{{ __('ui.nav.login') }}</button>
                <a class="btn secondary" href="{{ route('password.request') }}">{{ __('ui.auth.forgot_password') }}</a>
                <a class="btn secondary" href="{{ route('register') }}">{{ __('ui.auth.create_account') }}</a>
            </div>
        </form>
    </section>
@endsection
