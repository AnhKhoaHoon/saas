<?php

namespace Tests\Feature;

use App\Actions\ChangeSubscriptionPlanAction;
use App\Actions\CreateApiKeyAction;
use App\Actions\CreateProjectAction;
use App\Models\ApiKey;
use App\Models\Subscription;
use App\Models\UsageLog;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MockBillingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_change_mock_subscription_plan(): void
    {
        // Tạo user cần đổi plan.
        $user = User::factory()->create();

        // Đổi sang plan pro bằng action mock.
        $subscription = app(ChangeSubscriptionPlanAction::class)->execute($user, 'pro');

        // Xác nhận plan đã là pro.
        $this->assertSame('pro', $subscription->plan);

        // Xác nhận provider là mock, không phải Stripe thật.
        $this->assertSame('mock', $subscription->provider);

        // Xác nhận limit project theo catalog pro.
        $this->assertSame(5, $subscription->project_limit);

        // Xác nhận limit API key theo catalog pro.
        $this->assertSame(20, $subscription->api_key_limit);

        // Xác nhận audit log đổi plan đã được ghi.
        $this->assertDatabaseHas('audit_logs', [
            // User đổi plan.
            'user_id' => $user->id,
            // Action billing mock.
            'action' => 'subscription.plan_changed',
            // Model được audit.
            'auditable_type' => Subscription::class,
            // ID subscription vừa đổi.
            'auditable_id' => $subscription->id,
        ]);
    }

    public function test_invalid_mock_plan_is_rejected(): void
    {
        // Tạo user cần đổi plan.
        $user = User::factory()->create();

        // Kỳ vọng lỗi validation khi plan không tồn tại.
        $this->expectException(ValidationException::class);

        // Thử đổi sang plan không có trong catalog.
        app(ChangeSubscriptionPlanAction::class)->execute($user, 'gold');
    }

    public function test_free_plan_enforces_project_limit(): void
    {
        // Tạo user mặc định không có subscription nên được xem như free.
        $user = User::factory()->create();

        // Tạo project đầu tiên trong giới hạn free.
        app(CreateProjectAction::class)->execute($user, [
            // Tên project đầu tiên.
            'name' => 'First Project',
        ]);

        // Kỳ vọng lỗi validation khi tạo project thứ hai.
        $this->expectException(ValidationException::class);

        // Tạo project thứ hai phải bị chặn.
        app(CreateProjectAction::class)->execute($user, [
            // Tên project thứ hai.
            'name' => 'Second Project',
        ]);
    }

    public function test_pro_plan_allows_multiple_projects(): void
    {
        // Tạo user cần nhiều project.
        $user = User::factory()->create();

        // Nâng user lên pro mock.
        app(ChangeSubscriptionPlanAction::class)->execute($user, 'pro');

        // Tạo project thứ nhất.
        $firstProject = app(CreateProjectAction::class)->execute($user, [
            // Tên project thứ nhất.
            'name' => 'First Project',
        ]);

        // Tạo project thứ hai.
        $secondProject = app(CreateProjectAction::class)->execute($user, [
            // Tên project thứ hai.
            'name' => 'Second Project',
        ]);

        // Xác nhận hai project được tạo.
        $this->assertNotSame($firstProject->id, $secondProject->id);
    }

    public function test_free_plan_enforces_api_key_limit(): void
    {
        // Tạo owner free.
        $owner = User::factory()->create();

        // Tạo project duy nhất của free plan.
        $project = app(CreateProjectAction::class)->execute($owner, [
            // Tên project.
            'name' => 'API Limit Project',
        ]);

        // Tạo API key thứ nhất.
        app(CreateApiKeyAction::class)->execute($owner, $project, [
            // Tên key thứ nhất.
            'name' => 'First Key',
        ]);

        // Tạo API key thứ hai.
        app(CreateApiKeyAction::class)->execute($owner, $project, [
            // Tên key thứ hai.
            'name' => 'Second Key',
        ]);

        // Kỳ vọng lỗi authorization khi tạo key thứ ba.
        $this->expectException(AuthorizationException::class);

        // Tạo API key thứ ba phải bị chặn.
        app(CreateApiKeyAction::class)->execute($owner, $project, [
            // Tên key thứ ba.
            'name' => 'Third Key',
        ]);
    }

    public function test_monthly_request_quota_is_enforced(): void
    {
        // Tạo owner.
        $owner = User::factory()->create();

        // Tạo subscription limit thấp để test nhanh.
        Subscription::factory()->create([
            // Gắn subscription vào owner.
            'user_id' => $owner->id,
            // Plan vẫn là free.
            'plan' => 'free',
            // Provider mock.
            'provider' => 'mock',
            // Subscription active.
            'status' => 'active',
            // Cho phép tạo project.
            'project_limit' => 1,
            // Cho phép tạo key.
            'api_key_limit' => 2,
            // Limit request rất thấp để test quota.
            'monthly_request_limit' => 1,
        ]);

        // Tạo project.
        $project = app(CreateProjectAction::class)->execute($owner, [
            // Tên project.
            'name' => 'Monthly Quota Project',
        ]);

        // Chuẩn bị plain API key.
        $plainTextKey = 'kfg_live_monthlyquotatest000000000000000';

        // Tạo API key active.
        $apiKey = ApiKey::factory()->active()->create([
            // Gắn vào project.
            'project_id' => $project->id,
            // Người tạo là owner.
            'created_by' => $owner->id,
            // Prefix khớp key.
            'key_prefix' => substr($plainTextKey, 0, 12),
            // Hash khớp key.
            'key_hash' => hash('sha256', $plainTextKey),
            // Không chặn bởi IP whitelist.
            'ip_whitelist' => null,
            // Quota riêng của key không chặn test này.
            'quota_limit' => 100,
            // Requests count chưa chạm key quota.
            'requests_count' => 0,
        ]);

        // Tạo một usage log trong tháng hiện tại để chạm monthly quota.
        UsageLog::factory()->create([
            // Project liên quan.
            'project_id' => $project->id,
            // API key liên quan.
            'api_key_id' => $apiKey->id,
            // Thời điểm trong tháng hiện tại.
            'occurred_at' => now(),
        ]);

        // Gọi API bằng key khi monthly quota đã exhausted.
        $this->withHeader('X-API-Key', $plainTextKey)
            // Route test API key.
            ->getJson('/api/ping')
            // Xác nhận bị chặn 429.
            ->assertStatus(429)
            // Xác nhận message đúng.
            ->assertJson([
                // Message quota plan.
                'message' => 'Monthly request quota has been exhausted for this plan.',
            ]);
    }
}
