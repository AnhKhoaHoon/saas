<?php

namespace App\Support;

use App\Models\Subscription;
use App\Models\User;

class PlanCatalog
{
    public const FREE = 'free';

    public const PRO = 'pro';

    public const ENTERPRISE = 'enterprise';

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        // Trả về danh sách plan mock không gọi Stripe hay dịch vụ trả phí.
        return [
            // Gói free mặc định cho user mới.
            self::FREE => [
                // Tên hiển thị trên UI.
                'name' => 'Free',
                // Giá mock chỉ để hiển thị.
                'price' => 0,
                // Giới hạn số project.
                'project_limit' => 1,
                // Giới hạn tổng API key trên các project của owner.
                'api_key_limit' => 2,
                // Giới hạn request mỗi tháng.
                'monthly_request_limit' => 10000,
            ],
            // Gói pro cho team nhỏ.
            self::PRO => [
                // Tên hiển thị trên UI.
                'name' => 'Pro',
                // Giá mock chỉ để hiển thị.
                'price' => 29,
                // Giới hạn số project.
                'project_limit' => 5,
                // Giới hạn tổng API key trên các project của owner.
                'api_key_limit' => 20,
                // Giới hạn request mỗi tháng.
                'monthly_request_limit' => 1000000,
            ],
            // Gói enterprise cho tài khoản lớn.
            self::ENTERPRISE => [
                // Tên hiển thị trên UI.
                'name' => 'Enterprise',
                // Giá mock chỉ để hiển thị.
                'price' => 99,
                // Null nghĩa là không giới hạn project.
                'project_limit' => null,
                // Null nghĩa là không giới hạn API key.
                'api_key_limit' => null,
                // Giới hạn request mỗi tháng.
                'monthly_request_limit' => 10000000,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function get(string $plan): array
    {
        // Lấy plan từ catalog, fallback về free nếu key không hợp lệ.
        return self::all()[$plan] ?? self::all()[self::FREE];
    }

    /**
     * @return array<int, string>
     */
    public static function keys(): array
    {
        // Trả về danh sách key hợp lệ cho validation.
        return array_keys(self::all());
    }

    public static function activeSubscriptionFor(User $user): ?Subscription
    {
        // Lấy subscription active mới nhất của user.
        return $user->subscriptions()
            // Chỉ xét subscription active.
            ->where('status', 'active')
            // Lấy bản ghi mới nhất.
            ->latest()
            // Trả về subscription hoặc null.
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    public static function limitsFor(User $user): array
    {
        // Lấy subscription active hiện tại nếu có.
        $subscription = self::activeSubscriptionFor($user);

        // Nếu user chưa có subscription thì xem như free.
        if (! $subscription) {
            // Trả về giới hạn free từ catalog.
            return self::get(self::FREE);
        }

        // Trả về limit đang lưu trong subscription để admin/action có thể override nếu cần.
        return [
            // Tên hiển thị lấy từ catalog.
            'name' => self::get($subscription->plan)['name'],
            // Giá hiển thị lấy từ catalog.
            'price' => self::get($subscription->plan)['price'],
            // Giới hạn project lấy từ subscription.
            'project_limit' => $subscription->project_limit,
            // Giới hạn API key lấy từ subscription.
            'api_key_limit' => $subscription->api_key_limit,
            // Giới hạn monthly request lấy từ subscription.
            'monthly_request_limit' => $subscription->monthly_request_limit,
        ];
    }
}
