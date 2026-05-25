@extends('layouts.app', ['title' => 'Billing Dashboard'])

@section('content')
    <div class="actions" style="margin-bottom: 2rem;">
        <a href="{{ route('home') }}" class="btn secondary">&larr; {{ __('ui.common.back_to_home') }}</a>
    </div>

    <div class="grid cols-2">
        <div class="stack">
            <section class="card stack" style="border-color: {{ $subscription && $subscription->plan === 'pro' ? 'var(--primary)' : 'var(--glass-border)' }};">
                <div>
                    <h2>{{ __('ui.billing.current_plan') }}</h2>
                    <p class="lead">{{ __('ui.billing.current_plan_help') }}</p>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: rgba(0,0,0,0.2); border-radius: 12px;">
                    <div>
                        <div style="font-size: 2rem; font-weight: 900; color: {{ $subscription && $subscription->plan === 'pro' ? 'var(--primary)' : '#fff' }};">
                            {{ $limits['name'] }}
                        </div>
                        <div class="muted">{{ $subscription && $subscription->status === 'active' ? __('ui.billing.active_subscription') : __('ui.billing.no_subscription') }}</div>
                    </div>
                    @if (!$subscription || $subscription->plan === 'free')
                        <a href="{{ route('billing.pricing') }}" class="btn">{{ __('ui.billing.upgrade_plan') }}</a>
                    @else
                        <span style="padding: 6px 12px; background: rgba(16, 185, 129, 0.2); color: #10b981; border-radius: 20px; font-size: 0.85em; font-weight: bold;">ACTIVE</span>
                    @endif
                </div>

                <div class="grid cols-2" style="margin-top: 1rem; font-size: 0.9em;">
                    <div class="field">
                        <label>{{ __('ui.billing.monthly_requests_limit') }}</label>
                        <span class="muted">{{ number_format($limits['monthly_request_limit']) }}</span>
                    </div>
                    <div class="field">
                        <label>{{ __('ui.billing.projects_limit') }}</label>
                        <span class="muted">{{ $limits['project_limit'] ?? __('ui.common.unlimited') }}</span>
                    </div>
                    <div class="field">
                        <label>{{ __('ui.billing.api_keys_limit') }}</label>
                        <span class="muted">{{ $limits['api_key_limit'] ?? __('ui.common.unlimited') }}</span>
                    </div>
                    <div class="field">
                        <label>{{ __('ui.billing.next_billing_date') }}</label>
                        <span class="muted">{{ $subscription?->ends_at ? $subscription->ends_at->format('M d, Y') : 'N/A' }}</span>
                    </div>
                </div>
            </section>

            @if ($subscription && $subscription->plan !== 'free')
                <section class="card stack" style="border-color: var(--danger);">
                    <div>
                        <h3 style="color: var(--danger);">{{ __('ui.billing.downgrade_free') }}</h3>
                        <p class="lead" style="font-size: 0.9em;">{{ __('ui.billing.downgrade_help') }}</p>
                    </div>
                    <form action="{{ route('billing.plan.change') }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn chuyển về gói Free không?');">
                        @csrf
                        <input type="hidden" name="plan" value="free">
                        <button type="submit" style="background: transparent; border: 1px solid var(--danger); color: var(--danger);">{{ __('ui.billing.downgrade_free') }}</button>
                    </form>
                </section>
            @endif
        </div>

        <div>
            <section class="card stack">
                <h2>{{ __('ui.billing.payment_history') }}</h2>
                <p class="lead">{{ __('ui.billing.payment_history_help') }}</p>
                
                <div class="stack">
                    <!-- Fake Data for UI demo -->
                    <div class="actions" style="padding: 1rem 0; border-bottom: 1px solid var(--glass-border-light); justify-content: space-between;">
                        <div>
                            <strong>Invoice #INV-2023-01</strong>
                            <div class="muted" style="font-size: 0.85em;">Pro Plan - Monthly</div>
                        </div>
                        <div style="text-align: right;">
                            <div>$29.00</div>
                            <div style="color: #10b981; font-size: 0.85em;">Paid on Oct 15, 2023</div>
                        </div>
                    </div>
                    <div class="actions" style="padding: 1rem 0; border-bottom: 1px solid var(--glass-border-light); justify-content: space-between;">
                        <div>
                            <strong>Invoice #INV-2023-02</strong>
                            <div class="muted" style="font-size: 0.85em;">Pro Plan - Monthly</div>
                        </div>
                        <div style="text-align: right;">
                            <div>$29.00</div>
                            <div style="color: #10b981; font-size: 0.85em;">Paid on Nov 15, 2023</div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
