@extends('layouts.app', ['title' => 'Register'])

@section('content')
    <section class="card stack">
        <div>
            <h1>Create account</h1>
            <p class="lead">Form này gọi trực tiếp vào luồng đăng ký của Fortify đang bật trong project.</p>
        </div>

        <form class="stack" method="POST" action="{{ route('register.store') }}">
            @csrf

            <div class="field">
                <label for="name">Name</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name">
            </div>

            <div class="field">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required autocomplete="new-password">
            </div>

            <div class="field">
                <label for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
            </div>

            <div class="actions">
                <button type="submit">Register</button>
                <a class="btn secondary" href="{{ route('login') }}">Already have an account</a>
            </div>
        </form>
    </section>
@endsection
