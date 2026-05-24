@extends('layouts.app', ['title' => 'Pricing & Plans'])

@section('content')
    <div style="text-align: center; margin-bottom: 3rem;">
        <h1 style="font-size: 3rem; margin-bottom: 1rem;">Simple, transparent pricing</h1>
        <p class="lead" style="font-size: 1.2rem; max-width: 600px; margin: 0 auto;">Mock billing không gọi Stripe, không thu tiền thật, nhưng vẫn áp dụng quota như SaaS production.</p>
    </div>

    <div class="grid" style="grid-template-columns: repeat(3, 1fr); gap: 2rem; align-items: start;">
        @foreach ($plans as $key => $plan)
            <div class="card stack" style="border-color: {{ $key === 'pro' ? 'var(--primary)' : 'rgba(255,255,255,0.1)' }};">
                @if ($key === 'pro')
                    <div style="position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: var(--primary); padding: 4px 12px; border-radius: 20px; font-size: 0.8em; font-weight: bold;">RECOMMENDED</div>
                @endif

                <div>
                    <h3>{{ $plan['name'] }}</h3>
                    <p class="lead">{{ $key === 'free' ? 'Cho cá nhân trải nghiệm' : ($key === 'pro' ? 'Dành cho startup và team nhỏ' : 'Tối ưu cho doanh nghiệp') }}</p>
                </div>

                <div style="margin: 1rem 0;">
                    <span style="font-size: 3rem; font-weight: 900;">${{ $plan['price'] }}</span>
                    <span class="muted">/month</span>
                </div>

                <ul style="list-style: none; padding: 0; margin: 0 0 2rem 0; font-size: 0.95em; line-height: 2;">
                    <li>Project limit: {{ $plan['project_limit'] ?? 'Unlimited' }}</li>
                    <li>API key limit: {{ $plan['api_key_limit'] ?? 'Unlimited' }}</li>
                    <li>Monthly requests: {{ number_format($plan['monthly_request_limit']) }}</li>
                    <li>Provider: mock billing</li>
                </ul>

                <form method="POST" action="{{ route('billing.plan.change') }}">
                    @csrf
                    <input type="hidden" name="plan" value="{{ $key }}">
                    <button type="submit" style="width: 100%;">Choose {{ $plan['name'] }}</button>
                </form>
            </div>
        @endforeach
    </div>
@endsection
