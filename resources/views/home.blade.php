@extends('layouts.app', ['title' => 'Account'])

@section('content')
    <section class="card stack">
        <div>
            <h1>Account</h1>
            <p class="lead">Trang này gom phần auth hiện tại với workspace data của KeyForge, bắt đầu từ việc tạo project, API key và theo dõi usage logs.</p>
        </div>

        @if (session('new_api_key'))
            <div class="notice">
                <strong>Copy API key now:</strong>
                <div class="mono" style="margin-top: 10px; word-break: break-all;">{{ session('new_api_key.plain_text_key') }}</div>
                <div class="muted" style="margin-top: 8px;">
                    Key này chỉ hiển thị một lần cho project {{ session('new_api_key.project_name') }}.
                </div>
            </div>
        @endif

        <div class="grid cols-2">
            <section class="card stack">
                <div>
                    <h2>Profile</h2>
                    <p class="lead">Cập nhật tên, email và avatar của tài khoản hiện tại.</p>
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
                    <h2>Password</h2>
                    <p class="lead">Đổi mật khẩu tài khoản qua endpoint Fortify.</p>
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
    </section>

    <section class="card stack">
        <div>
            <h2>Create project</h2>
            <p class="lead">Mỗi project là một workspace tách biệt cho API keys, usage logs, thành viên và quota.</p>
        </div>

        <form class="stack" method="POST" action="{{ route('projects.store') }}">
            @csrf

            <div class="field">
                <label for="project_name">Project name</label>
                <input id="project_name" type="text" name="name" value="{{ old('name') }}" required>
            </div>

            <div class="field">
                <label for="project_description">Description</label>
                <input id="project_description" type="text" name="description" value="{{ old('description') }}">
            </div>

            <div class="field">
                <label for="timezone">Timezone</label>
                <input id="timezone" type="text" name="timezone" value="{{ old('timezone', config('app.timezone')) }}">
            </div>

            <div class="actions">
                <button type="submit">Create project</button>
            </div>
        </form>
    </section>

    <section class="card stack">
        <div>
            <h2>Workspace overview</h2>
            <p class="lead">
                @if ($subscription)
                    Plan hiện tại: <strong>{{ strtoupper($subscription->plan) }}</strong> - trạng thái {{ $subscription->status }}.
                @else
                    Bạn chưa có subscription record nào. Hiện tại workspace sẽ chạy như plan mặc định.
                @endif
            </p>
        </div>

        @forelse ($projects as $project)
            <article class="card stack">
                <div class="actions">
                    <div>
                        <h3>{{ $project->name }}</h3>
                        <p class="lead">{{ $project->description ?: 'No description yet.' }}</p>
                    </div>
                    <span class="muted">{{ $project->slug }}</span>
                </div>

                <div class="grid cols-2">
                    <div class="field">
                        <label>Status</label>
                        <span class="muted">{{ $project->status }}</span>
                    </div>
                    <div class="field">
                        <label>Timezone</label>
                        <span class="muted">{{ data_get($project->settings, 'timezone', config('app.timezone')) }}</span>
                    </div>
                    <div class="field">
                        <label>API keys</label>
                        <span class="muted">{{ $project->api_keys_count }}</span>
                    </div>
                    <div class="field">
                        <label>Team members</label>
                        <span class="muted">{{ $project->team_members_count }}</span>
                    </div>
                    <div class="field">
                        <label>Usage logs</label>
                        <span class="muted">{{ $project->usage_logs_count }}</span>
                    </div>
                    <div class="field">
                        <label>Owner</label>
                        <span class="muted">{{ $project->owner->email }}</span>
                    </div>
                </div>

                <div class="actions">
                    <a class="btn secondary" href="{{ route('projects.api-keys.index', $project) }}">View all API keys</a>
                </div>

                <section class="card stack">
                    <div>
                        <h3>Create API key</h3>
                        <p class="lead">Tạo key cho project này. Raw key sẽ chỉ hiện đúng một lần sau khi submit thành công.</p>
                    </div>

                    <form class="stack" method="POST" action="{{ route('projects.api-keys.store', $project) }}">
                        @csrf

                        <div class="field">
                            <label for="api_key_name_{{ $project->id }}">Key name</label>
                            <input id="api_key_name_{{ $project->id }}" type="text" name="name" value="{{ old('name') }}" required>
                        </div>

                        <div class="grid cols-2">
                            <div class="field">
                                <label for="api_key_rate_{{ $project->id }}">Rate limit / minute</label>
                                <input id="api_key_rate_{{ $project->id }}" type="text" name="rate_limit_per_minute" value="{{ old('rate_limit_per_minute', 60) }}">
                            </div>

                            <div class="field">
                                <label for="api_key_quota_{{ $project->id }}">Quota limit</label>
                                <input id="api_key_quota_{{ $project->id }}" type="text" name="quota_limit" value="{{ old('quota_limit') }}">
                            </div>
                        </div>

                        <div class="field">
                            <label for="api_key_scopes_{{ $project->id }}">Scopes</label>
                            <input id="api_key_scopes_{{ $project->id }}" type="text" name="scopes" value="{{ old('scopes', 'read') }}">
                            <p class="muted">Ví dụ: `read,write,billing`</p>
                        </div>

                        <div class="field">
                            <label for="api_key_ip_{{ $project->id }}">IP whitelist</label>
                            <input id="api_key_ip_{{ $project->id }}" type="text" name="ip_whitelist" value="{{ old('ip_whitelist') }}">
                            <p class="muted">Ví dụ: `127.0.0.1,10.0.0.5`</p>
                        </div>

                        <div class="field">
                            <label for="api_key_expires_{{ $project->id }}">Expires at</label>
                            <input id="api_key_expires_{{ $project->id }}" type="text" name="expires_at" value="{{ old('expires_at') }}">
                            <p class="muted">Dùng định dạng như `2026-12-31 23:59:59`.</p>
                        </div>

                        <div class="actions">
                            <button type="submit">Create API key</button>
                        </div>
                    </form>

                    @if ($project->apiKeys->isNotEmpty())
                        <div class="stack">
                            <h3>Recent API keys</h3>
                            @foreach ($project->apiKeys as $apiKey)
                                <div class="actions">
                                    <div>
                                        <strong>{{ $apiKey->name }}</strong>
                                        <div class="muted mono">{{ $apiKey->key_prefix }}...</div>
                                    </div>
                                    <div class="actions">
                                        <a class="btn secondary" href="{{ route('projects.api-keys.show', [$project, $apiKey]) }}">Detail</a>
                                        <span class="muted">{{ $apiKey->status }}</span>
                                        @if ($apiKey->status !== 'revoked')
                                            <form method="POST" action="{{ route('projects.api-keys.destroy', [$project, $apiKey]) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button class="secondary" type="submit">Revoke</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>
            </article>
        @empty
            <p class="lead">Chưa có project nào. Tạo project đầu tiên để bắt đầu quản lý API keys và usage tracking.</p>
        @endforelse
    </section>

    <section class="card stack">
        <div>
            <h2>Usage logs</h2>
            <p class="lead">Bản xem nhanh 20 request gần nhất đã đi qua API key middleware. Dùng bộ lọc để soi theo project, key, method hoặc status code.</p>
        </div>

        <form class="grid cols-2" method="GET" action="{{ route('home') }}">
            <div class="field">
                <label for="usage_project_id">Project</label>
                <select id="usage_project_id" name="usage_project_id">
                    <option value="">All projects</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}" @selected((string) request('usage_project_id') === (string) $project->id)>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="usage_api_key_id">API key</label>
                <select id="usage_api_key_id" name="usage_api_key_id">
                    <option value="">All keys</option>
                    @foreach ($usageApiKeys as $apiKey)
                        <option value="{{ $apiKey->id }}" @selected((string) request('usage_api_key_id') === (string) $apiKey->id)>
                            {{ $apiKey->name }} ({{ $apiKey->key_prefix }}...)
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="usage_method">Method</label>
                <select id="usage_method" name="usage_method">
                    <option value="">All methods</option>
                    @foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] as $method)
                        <option value="{{ $method }}" @selected(request('usage_method') === $method)>{{ $method }}</option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="usage_status_code">Status code</label>
                <input id="usage_status_code" type="text" name="usage_status_code" value="{{ request('usage_status_code') }}">
            </div>

            <div class="actions">
                <button type="submit">Apply filters</button>
                <a class="btn secondary" href="{{ route('home') }}">Reset</a>
            </div>
        </form>

        @forelse ($usageLogs as $log)
            <article class="card stack">
                <div class="actions">
                    <div>
                        <h3>{{ $log->method }} {{ $log->endpoint }}</h3>
                        <p class="lead">{{ $log->project->name }} • {{ $log->apiKey->name }} ({{ $log->apiKey->key_prefix }}...)</p>
                    </div>
                    <span class="muted">{{ $log->occurred_at->format('Y-m-d H:i:s') }}</span>
                </div>

                <div class="grid cols-2">
                    <div class="field">
                        <label>Status code</label>
                        <span class="muted">{{ $log->status_code }}</span>
                    </div>
                    <div class="field">
                        <label>Response time</label>
                        <span class="muted">{{ $log->response_time_ms ?? 0 }} ms</span>
                    </div>
                    <div class="field">
                        <label>Response size</label>
                        <span class="muted">{{ $log->response_size_bytes ?? 0 }} bytes</span>
                    </div>
                    <div class="field">
                        <label>IP address</label>
                        <span class="muted">{{ $log->ip_address ?? 'Unknown' }}</span>
                    </div>
                    <div class="field">
                        <label>Request id</label>
                        <span class="muted mono">{{ $log->request_id ?? 'None' }}</span>
                    </div>
                    <div class="field">
                        <label>Units</label>
                        <span class="muted">{{ $log->units }}</span>
                    </div>
                </div>
            </article>
        @empty
            <p class="lead">Chưa có usage log nào khớp với bộ lọc hiện tại.</p>
        @endforelse
    </section>

    <section class="card stack">
        <div>
            <h2>Two-factor authentication</h2>
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

                    <div class="qr">
                        {!! auth()->user()->twoFactorQrCodeSvg() !!}
                    </div>
                </section>

                <section class="stack">
                    <div>
                        <h3>Recovery codes</h3>
                        <p class="lead">Lưu các mã này ở nơi an toàn. Mỗi mã dùng được một lần.</p>
                    </div>

                    <ul class="code-list mono">
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
@endsection
