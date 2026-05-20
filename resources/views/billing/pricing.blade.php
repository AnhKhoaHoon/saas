@extends('layouts.app', ['title' => 'Pricing & Plans'])

@section('content')
    <div style="text-align: center; margin-bottom: 3rem;">
        <h1 style="font-size: 3rem; margin-bottom: 1rem;">Simple, transparent pricing</h1>
        <p class="lead" style="font-size: 1.2rem; max-width: 600px; margin: 0 auto;">Chọn gói cước phù hợp với nhu cầu scale của dự án. Không phí ẩn, nâng cấp hoặc hủy bất kỳ lúc nào.</p>
    </div>

    <div class="grid" style="grid-template-columns: repeat(3, 1fr); gap: 2rem; align-items: start;">
        <!-- Hobby Tier -->
        <div class="card stack" style="border-color: rgba(255,255,255,0.1);">
            <div>
                <h3>Hobby</h3>
                <p class="lead">Cho cá nhân trải nghiệm</p>
            </div>
            <div style="margin: 1rem 0;">
                <span style="font-size: 3rem; font-weight: 900;">$0</span>
                <span class="muted">/month</span>
            </div>
            <ul style="list-style: none; padding: 0; margin: 0 0 2rem 0; font-size: 0.95em; line-height: 2;">
                <li>✓ 1 Project</li>
                <li>✓ Tối đa 2 API Keys</li>
                <li>✓ 10,000 requests/tháng</li>
                <li>✓ Log lưu trữ 7 ngày</li>
                <li class="muted">✗ Không hỗ trợ Team</li>
                <li class="muted">✗ Không hỗ trợ Export Log</li>
            </ul>
            <a href="#" class="btn secondary" style="width: 100%; text-align: center;">Current Plan</a>
        </div>

        <!-- Pro Tier -->
        <div class="card stack" style="border-color: var(--primary); transform: scale(1.05); box-shadow: 0 0 40px rgba(99, 102, 241, 0.2);">
            <div style="position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: var(--primary); padding: 4px 12px; border-radius: 20px; font-size: 0.8em; font-weight: bold;">RECOMMENDED</div>
            <div>
                <h3 style="color: var(--primary);">Pro</h3>
                <p class="lead">Dành cho Startup & Team nhỏ</p>
            </div>
            <div style="margin: 1rem 0;">
                <span style="font-size: 3rem; font-weight: 900;">$29</span>
                <span class="muted">/month</span>
            </div>
            <ul style="list-style: none; padding: 0; margin: 0 0 2rem 0; font-size: 0.95em; line-height: 2;">
                <li>✓ 5 Projects</li>
                <li>✓ Tối đa 20 API Keys</li>
                <li>✓ 1,000,000 requests/tháng</li>
                <li>✓ Log lưu trữ 30 ngày</li>
                <li>✓ Tối đa 5 Team members/project</li>
                <li>✓ Xuất Log CSV / PDF</li>
            </ul>
            <form action="#" method="POST">
                @csrf
                <button type="submit" style="width: 100%;">Upgrade to Pro</button>
            </form>
        </div>

        <!-- Enterprise Tier -->
        <div class="card stack" style="border-color: rgba(255,255,255,0.1);">
            <div>
                <h3>Enterprise</h3>
                <p class="lead">Tối ưu cho doanh nghiệp</p>
            </div>
            <div style="margin: 1rem 0;">
                <span style="font-size: 3rem; font-weight: 900;">$99</span>
                <span class="muted">/month</span>
            </div>
            <ul style="list-style: none; padding: 0; margin: 0 0 2rem 0; font-size: 0.95em; line-height: 2;">
                <li>✓ Không giới hạn Projects</li>
                <li>✓ Không giới hạn API Keys</li>
                <li>✓ 10,000,000 requests/tháng</li>
                <li>✓ Log lưu trữ vĩnh viễn (1 năm)</li>
                <li>✓ Không giới hạn Team members</li>
                <li>✓ IP Whitelist nâng cao & SLA 99.9%</li>
            </ul>
            <form action="#" method="POST">
                @csrf
                <button type="button" class="secondary" style="width: 100%;">Contact Sales</button>
            </form>
        </div>
    </div>
@endsection
