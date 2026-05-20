@extends('layouts.app', ['title' => 'Create API Key'])

@section('content')
    <div class="actions" style="margin-bottom: 2rem;">
        <a href="{{ route('projects.api-keys.index', $project) }}" class="btn secondary">&larr; Back to API Keys</a>
    </div>

    <section class="card stack">
        <div>
            <h1>Create API Key</h1>
            <p class="lead">Khởi tạo key bảo mật mới cho project <strong>{{ $project->name }}</strong>.</p>
        </div>

        <form class="stack" method="POST" action="{{ route('projects.api-keys.store', $project) }}">
            @csrf

            <div class="field">
                <label for="name">Key Name</label>
                <input id="name" type="text" name="name" placeholder="e.g. Production Frontend App" required autofocus>
                <p class="muted" style="font-size: 0.85em;">Tên gợi nhớ để phân biệt các key với nhau.</p>
            </div>

            <div class="grid cols-2">
                <div class="field">
                    <label for="rate_limit_per_minute">Rate Limit / Minute (Optional)</label>
                    <input id="rate_limit_per_minute" type="number" name="rate_limit_per_minute" placeholder="e.g. 60">
                    <p class="muted" style="font-size: 0.85em;">Giới hạn số request mỗi phút. Để trống nếu không giới hạn.</p>
                </div>

                <div class="field">
                    <label for="quota_limit">Quota Limit (Optional)</label>
                    <input id="quota_limit" type="number" name="quota_limit" placeholder="e.g. 100000">
                    <p class="muted" style="font-size: 0.85em;">Giới hạn tổng số request trọn đời của key này.</p>
                </div>
            </div>

            <div class="field">
                <label for="ip_whitelist">IP Whitelist (Optional)</label>
                <input id="ip_whitelist" type="text" name="ip_whitelist" placeholder="e.g. 192.168.1.1, 10.0.0.0/24">
                <p class="muted" style="font-size: 0.85em;">Chỉ cho phép các IP này gọi API. Ngăn cách bằng dấu phẩy.</p>
            </div>

            <div class="field">
                <label for="expires_at">Expiration Date (Optional)</label>
                <input id="expires_at" type="datetime-local" name="expires_at">
                <p class="muted" style="font-size: 0.85em;">Tự động vô hiệu hóa key sau thời điểm này.</p>
            </div>

            <div class="actions" style="margin-top: 1rem;">
                <button type="submit">Generate API Key</button>
            </div>
        </form>
    </section>
@endsection
