<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function ($user): ?bool {
            // Cho user có cờ is_admin đi qua mọi Gate nội bộ.
            if ($user->is_admin) {
                // Trả về true để Laravel bỏ qua các policy cụ thể.
                return true;
            }

            // Cho user có permission Spatie admin.access đi qua mọi Gate nội bộ.
            if ($user->hasPermissionTo('admin.access')) {
                // Trả về true để role platform_admin có quyền quản trị toàn hệ thống.
                return true;
            }

            // Trả về null để Laravel tiếp tục kiểm tra policy bình thường.
            return null;
        });
    }
}
