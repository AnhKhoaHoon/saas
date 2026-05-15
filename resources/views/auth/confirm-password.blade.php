@extends('layouts.app', ['title' => 'Confirm Password'])

@section('content')
    <section class="card stack">
        <div>
            <h1>Confirm password</h1>
            <p class="lead">Fortify đang yêu cầu xác nhận lại mật khẩu trước khi thay đổi cài đặt nhạy cảm như bật hoặc tắt 2FA.</p>
        </div>

        <form class="stack" method="POST" action="{{ route('password.confirm.store') }}">
            @csrf

            <div class="field">
                <label for="password">Current password</label>
                <input id="password" type="password" name="password" required autocomplete="current-password">
            </div>

            <div class="actions">
                <button type="submit">Confirm</button>
            </div>
        </form>
    </section>
@endsection
