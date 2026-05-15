@extends('layouts.app', ['title' => 'Login'])

@section('content')
    <section class="card stack">
        <div>
            <h1>Sign in</h1>
            <p class="lead">Đăng nhập bằng email và mật khẩu. Nếu tài khoản đã bật xác thực hai lớp, bạn sẽ được chuyển sang bước nhập mã 2FA.</p>
        </div>

        <form class="stack" method="POST" action="{{ route('login.store') }}">
            @csrf

            <div class="field">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required autocomplete="current-password">
            </div>

            <div class="actions">
                <label class="muted">
                    <input type="checkbox" name="remember">
                    Remember me
                </label>
            </div>

            <div class="actions">
                <button type="submit">Login</button>
                <a class="btn secondary" href="{{ route('password.request') }}">Forgot password</a>
                <a class="btn secondary" href="{{ route('register') }}">Create account</a>
            </div>
        </form>
    </section>
@endsection
