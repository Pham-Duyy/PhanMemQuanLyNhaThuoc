<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Đảm bảo user đã được gán vào 1 nhà thuốc.
 * Nếu chưa → redirect về trang báo lỗi.
 */
class EnsurePharmacySelected
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && !auth()->user()->pharmacy_id) {
            auth()->logout();
            return redirect()->route('login')
                ->with('error', 'Tài khoản chưa được gán vào nhà thuốc. Vui lòng liên hệ quản trị viên.');
        }

        return $next($request);
    }
}