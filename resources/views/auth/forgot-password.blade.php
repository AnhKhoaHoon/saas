@extends('layouts.app', ['title' => 'Forgot Password'])

@section('content')
    <section class="card stack">
        <div>
            <h1>Forgot password</h1>
            <p class="lead">Nhập email để nhận link đặt lại mật khẩu.</p>
        </div>

        <form class="stack" method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="field">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
            </div>

            <div class="actions">
                <button type="submit">Send reset link</button>
                <a class="btn secondary" href="{{ route('login') }}">Back to login</a>
            </div>
        </form>
    </section>
@endsection
