@extends('layouts.app', ['title' => 'Verify Email'])

@section('content')
    <section class="card stack">
        <div>
            <h1>Verify your email</h1>
            <p class="lead">Tài khoản đã đăng nhập nhưng chưa xác minh email. Bạn có thể gửi lại email xác minh từ đây.</p>
        </div>

        <div class="actions">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit">Resend verification email</button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="secondary" type="submit">Log out</button>
            </form>
        </div>
    </section>
@endsection
