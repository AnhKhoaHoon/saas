@extends('layouts.app', ['title' => 'User Profile'])

@section('content')
    <div class="stack">
        <div>
            <h1>User Profile</h1>
            <p class="lead">Quản lý thông tin tài khoản, mật khẩu và bảo mật hai lớp (2FA).</p>
        </div>

        <div class="grid cols-2">
            <section class="card stack">
                <div>
                    <h2>Profile Information</h2>
                    <p class="lead">Cập nhật tên, email và avatar của tài khoản.</p>
                </div>

                @if (auth()->user()->avatar)
                    <div class="actions">
                        <img
                            src="{{ Storage::disk('public')->url(auth()->user()->avatar) }}"
                            alt="Current avatar"
                            style="width: 88px; height: 88px; border-radius: 20px; object-fit: cover; border: 1px solid #d6c6ae;"
                        >
                        <span class="muted">Avatar hiện tại</span>
                    </div>
                @endif

                <form class="stack" method="POST" action="{{ route('user-profile-information.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="field">
                        <label for="name">Name</label>
                        <input id="name" type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required>
                    </div>

                    <div class="field">
                        <label for="email">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required>
                    </div>

                    <div class="field">
                        <label for="avatar">Avatar</label>
                        <input id="avatar" type="file" name="avatar" accept="image/png,image/jpeg,image/webp,image/gif">
                        <p class="muted">Tối đa 2MB. Hỗ trợ PNG, JPG, WEBP, GIF.</p>
                    </div>

                    @if (auth()->user()->avatar)
                        <div class="actions">
                            <label class="muted">
                                <input type="checkbox" name="remove_avatar" value="1">
                                Remove current avatar
                            </label>
                        </div>
                    @endif

                    <div class="actions">
                        <button type="submit">Save profile</button>
                    </div>
                </form>
            </section>

            <section class="card stack">
                <div>
                    <h2>Update Password</h2>
                    <p class="lead">Đảm bảo tài khoản của bạn sử dụng mật khẩu dài và an toàn.</p>
                </div>

                <form class="stack" method="POST" action="{{ route('user-password.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="field">
                        <label for="current_password">Current password</label>
                        <input id="current_password" type="password" name="current_password" required>
                    </div>

                    <div class="field">
                        <label for="new_password">New password</label>
                        <input id="new_password" type="password" name="password" required>
                    </div>

                    <div class="field">
                        <label for="new_password_confirmation">Confirm new password</label>
                        <input id="new_password_confirmation" type="password" name="password_confirmation" required>
                    </div>

                    <div class="actions">
                        <button type="submit">Update password</button>
                    </div>
                </form>
            </section>
        </div>

        <section class="card stack">
            <div>
                <h2>Two-factor Authentication (2FA)</h2>
                <p class="lead">
                    @if (auth()->user()->hasEnabledTwoFactorAuthentication())
                        2FA đã được bật và xác nhận cho tài khoản này.
                    @elseif (auth()->user()->two_factor_secret)
                        2FA đã được khởi tạo nhưng chưa xác nhận. Hãy quét QR và nhập mã để hoàn tất.
                    @else
                        2FA chưa được bật. Khi bật, Fortify sẽ sinh secret, QR code và recovery codes.
                    @endif
                </p>
            </div>

            @if (! auth()->user()->two_factor_secret)
                <form method="POST" action="{{ route('two-factor.enable') }}">
                    @csrf
                    <button type="submit">Enable 2FA</button>
                </form>
            @else
                <div class="grid cols-2">
                    <section class="stack">
                        <div>
                            <h3>Scan QR code</h3>
                            <p class="lead">Dùng Google Authenticator, 1Password hoặc app TOTP bất kỳ để quét mã này.</p>
                        </div>

                        <div class="qr" style="background: white; padding: 1rem; border-radius: 8px; display: inline-block;">
                            {!! auth()->user()->twoFactorQrCodeSvg() !!}
                        </div>
                    </section>

                    <section class="stack">
                        <div>
                            <h3>Recovery codes</h3>
                            <p class="lead">Lưu các mã này ở nơi an toàn. Mỗi mã dùng được một lần.</p>
                        </div>

                        <ul class="code-list mono" style="background: #1a1a1a; padding: 1rem; border-radius: 8px; list-style-type: none;">
                            @foreach (auth()->user()->recoveryCodes() as $code)
                                <li>{{ $code }}</li>
                            @endforeach
                        </ul>
                    </section>
                </div>

                @if (! auth()->user()->hasEnabledTwoFactorAuthentication())
                    <form class="stack" method="POST" action="{{ route('two-factor.confirm') }}">
                        @csrf

                        <div class="field">
                            <label for="code">Confirmation code</label>
                            <input id="code" type="text" name="code" inputmode="numeric" required>
                        </div>

                        <div class="actions">
                            <button type="submit">Confirm 2FA</button>
                        </div>
                    </form>
                @endif

                <div class="actions">
                    <form method="POST" action="{{ route('two-factor.regenerate-recovery-codes') }}">
                        @csrf
                        <button class="secondary" type="submit">Regenerate recovery codes</button>
                    </form>

                    <form method="POST" action="{{ route('two-factor.disable') }}">
                        @csrf
                        @method('DELETE')
                        <button class="danger" type="submit">Disable 2FA</button>
                    </form>
                </div>
            @endif
        </section>
    </div>
@endsection
