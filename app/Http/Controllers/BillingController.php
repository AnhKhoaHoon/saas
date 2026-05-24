<?php

namespace App\Http\Controllers;

use App\Actions\ChangeSubscriptionPlanAction;
use App\Support\PlanCatalog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BillingController extends Controller
{
    public function pricing(): View
    {
        // Render trang pricing với catalog plan mock.
        return view('billing.pricing', [
            // Danh sách plan Free/Pro/Enterprise.
            'plans' => PlanCatalog::all(),
        ]);
    }

    public function dashboard(Request $request): View
    {
        // Lấy subscription active mới nhất của user.
        $subscription = $request->user()->subscriptions()->latest()->first();

        // Render billing dashboard.
        return view('billing.dashboard', [
            // Subscription hiện tại hoặc null.
            'subscription' => $subscription,
            // Limit effective hiện tại, fallback free nếu chưa có subscription.
            'limits' => PlanCatalog::limitsFor($request->user()),
            // Catalog plan để UI có thể hiển thị tên/giá.
            'plans' => PlanCatalog::all(),
        ]);
    }

    public function changePlan(Request $request, ChangeSubscriptionPlanAction $action): RedirectResponse
    {
        // Validate plan submit từ pricing/dashboard.
        $validated = $request->validate([
            // Plan phải nằm trong catalog mock.
            'plan' => ['required', 'string', Rule::in(PlanCatalog::keys())],
        ]);

        // Đổi plan bằng action mock, không gọi Stripe.
        $subscription = $action->execute($request->user(), $validated['plan']);

        // Redirect về billing dashboard với thông báo thành công.
        return redirect()
            // Quay về dashboard billing.
            ->route('billing.dashboard')
            // Flash message cho user.
            ->with('status', "Plan changed to {$subscription->plan}.");
    }
}
