@extends('layouts.app', ['title' => 'Two-Factor Challenge'])

@section('content')
    <section class="card stack">
        <div>
            <h1>Two-factor challenge</h1>
            <p class="lead">Nhập mã từ app xác thực hoặc dùng một recovery code nếu bạn không còn truy cập được thiết bị.</p>
        </div>

        <form class="stack" method="POST" action="{{ route('two-factor.login.store') }}">
            @csrf

            <div class="field">
                <label for="code">Authenticator code</label>
                <input id="code" type="text" name="code" inputmode="numeric" autocomplete="one-time-code">
            </div>

            <div class="field">
                <label for="recovery_code">Recovery code</label>
                <input id="recovery_code" type="text" name="recovery_code" autocomplete="one-time-code">
            </div>

            <div class="actions">
                <button type="submit">Verify</button>
                <a class="btn secondary" href="{{ route('login') }}">Back to login</a>
            </div>
        </form>
    </section>
@endsection
