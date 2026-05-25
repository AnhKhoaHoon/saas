<?php

namespace Tests\Feature;

use App\Actions\ChangeSubscriptionPlanAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleSwitchTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_switch_to_vietnamese(): void
    {
        // Guest đổi locale sang tiếng Việt.
        $this->get(route('language.switch', 'vi'))
            // Sau khi đổi locale thì redirect về trang trước.
            ->assertRedirect()
            // Session phải lưu locale vi.
            ->assertSessionHas('locale', 'vi');

        // Trang welcome phải hiển thị tiếng Việt.
        $this->withSession(['locale' => 'vi'])
            // Mở trang chủ.
            ->get('/')
            // Trang trả về 200.
            ->assertOk()
            // Nội dung hero tiếng Việt phải xuất hiện.
            ->assertSee('Tạo, theo dõi và bảo vệ mọi API key');
    }

    public function test_authenticated_user_can_switch_to_english(): void
    {
        // Tạo user để test trong layout authenticated.
        $user = User::factory()->create();

        // Tạo subscription mock để billing dashboard có dữ liệu active.
        app(ChangeSubscriptionPlanAction::class)->execute($user, 'pro');

        // User đổi locale sang tiếng Anh.
        $this->actingAs($user)
            // Gọi route đổi ngôn ngữ.
            ->get(route('language.switch', 'en'))
            // Sau khi đổi locale thì redirect về trang trước.
            ->assertRedirect()
            // Session phải lưu locale en.
            ->assertSessionHas('locale', 'en');

        // Billing dashboard phải hiển thị tiếng Anh.
        $this->actingAs($user)
            // Set session locale en.
            ->withSession(['locale' => 'en'])
            // Mở billing dashboard.
            ->get(route('billing.dashboard'))
            // Trang trả về 200.
            ->assertOk()
            // Nội dung tiếng Anh phải xuất hiện.
            ->assertSee('Current Plan')
            // Navbar cũng phải có nhãn Billing tiếng Anh.
            ->assertSee('Billing');
    }

    public function test_invalid_locale_returns_404(): void
    {
        // Locale ngoài en/vi phải bị chặn.
        $this->get('/language/fr')
            // Xác nhận trả về 404.
            ->assertNotFound();
    }
}
