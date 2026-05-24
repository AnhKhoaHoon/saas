<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\Subscription;
use App\Models\User;
use App\Support\PlanCatalog;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ChangeSubscriptionPlanAction
{
    /**
     * Change a user's mock subscription plan without calling Stripe.
     *
     * @throws ValidationException
     */
    public function execute(User $actor, string $plan): Subscription
    {
        // Kiểm tra plan có nằm trong catalog mock hay không.
        if (! in_array($plan, PlanCatalog::keys(), true)) {
            // Ném validation để form đổi plan hiển thị lỗi rõ ràng.
            throw ValidationException::withMessages([
                // Gắn lỗi vào field plan.
                'plan' => 'The selected billing plan is not available.',
            ]);
        }

        // Lấy thông tin limit của plan được chọn.
        $planConfig = PlanCatalog::get($plan);

        // Đổi plan trong transaction để subscription và audit log đồng bộ.
        return DB::transaction(function () use ($actor, $plan, $planConfig): Subscription {
            // Lấy subscription active hiện tại của user nếu có.
            $subscription = PlanCatalog::activeSubscriptionFor($actor);

            // Nếu chưa có subscription thì tạo mới.
            $subscription ??= new Subscription([
                // Gắn subscription vào user hiện tại.
                'user_id' => $actor->id,
            ]);

            // Lưu plan mock mới vào subscription.
            $subscription->forceFill([
                // Plan hiện tại.
                'plan' => $plan,
                // Provider mock để biết đây chưa phải Stripe thật.
                'provider' => 'mock',
                // Mock billing không có subscription id từ provider ngoài.
                'provider_subscription_id' => null,
                // Subscription mock luôn active ngay sau khi đổi plan.
                'status' => 'active',
                // Giới hạn project theo catalog.
                'project_limit' => $planConfig['project_limit'],
                // Giới hạn API key theo catalog.
                'api_key_limit' => $planConfig['api_key_limit'],
                // Giới hạn monthly request theo catalog.
                'monthly_request_limit' => $planConfig['monthly_request_limit'],
                // Mock billing không có trial.
                'trial_ends_at' => null,
                // Mock billing không tự kết thúc chu kỳ.
                'ends_at' => null,
            ])->save();

            // Ghi audit log cho hành động đổi plan.
            AuditLog::create([
                // User đổi plan.
                'user_id' => $actor->id,
                // Billing hiện tại không gắn vào project cụ thể.
                'project_id' => null,
                // Model được audit là Subscription.
                'auditable_type' => Subscription::class,
                // ID subscription vừa tạo/cập nhật.
                'auditable_id' => $subscription->id,
                // Tên action dùng để filter audit log.
                'action' => 'subscription.plan_changed',
                // Mô tả ngắn cho admin/support.
                'description' => "Subscription plan changed to {$plan}.",
                // Metadata chi tiết cho billing mock.
                'meta' => [
                    // Plan mới.
                    'plan' => $plan,
                    // Provider mock.
                    'provider' => 'mock',
                    // Giới hạn project.
                    'project_limit' => $subscription->project_limit,
                    // Giới hạn API key.
                    'api_key_limit' => $subscription->api_key_limit,
                    // Giới hạn request tháng.
                    'monthly_request_limit' => $subscription->monthly_request_limit,
                ],
                // Thời điểm xảy ra action.
                'occurred_at' => now(),
            ]);

            // Trả về subscription mới nhất.
            return $subscription->fresh();
        });
    }
}
