<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function switch(Request $request, string $locale): RedirectResponse
    {
        // Chặn mọi locale ngoài tiếng Anh và tiếng Việt.
        abort_unless(in_array($locale, ['en', 'vi'], true), 404);

        // Lưu locale vào session để các request sau dùng tiếp.
        $request->session()->put('locale', $locale);

        // Quay lại trang trước đó sau khi đổi ngôn ngữ.
        return back();
    }
}
