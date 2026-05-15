@extends('layouts.app', ['title' => 'Reset Password'])

@section('content')
    <section class="card stack">
        <div>
            <h1>Reset password</h1>
            <p class="lead">Đặt mật khẩu mới từ link reset của Fortify.</p>
        </div>

        <form class="stack" method="POST" action="{{ route('password.update') }}">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="field">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus>
            </div>

            <div class="field">
                <label for="password">New password</label>
                <input id="password" type="password" name="password" required autocomplete="new-password">
            </div>

            <div class="field">
                <label for="password_confirmation">Confirm new password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
            </div>

            <div class="actions">
                <button type="submit">Reset password</button>
            </div>
        </form>
    </section>
@endsection
