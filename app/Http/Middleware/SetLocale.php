<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        // Lấy locale từ session, mặc định theo config app.locale.
        $locale = $request->session()->get('locale', config('app.locale'));

        // Chỉ cho phép hai ngôn ngữ đang hỗ trợ.
        if (! in_array($locale, ['en', 'vi'], true)) {
            // Nếu locale không hợp lệ thì fallback về tiếng Anh.
            $locale = 'en';
        }

        // Set locale cho toàn bộ request hiện tại.
        App::setLocale($locale);

        // Tiếp tục request sau khi locale đã sẵn sàng.
        return $next($request);
    }
}
