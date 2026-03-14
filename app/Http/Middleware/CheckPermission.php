<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware kiểm tra quyền truy cập.
 *
 * Cách dùng trong routes:
 *   ->middleware('permission:invoice.create')
 *   ->middleware('permission:invoice.create,invoice.view')  // 1 trong 2 đều được
 */
class CheckPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        // Chưa đăng nhập → về trang login
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Kiểm tra user đang active không
        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('login')
                ->with('error', 'Tài khoản của bạn đã bị vô hiệu hóa.');
        }

        // Không truyền permission → chỉ cần đăng nhập là đủ
        if (empty($permissions)) {
            return $next($request);
        }

        // Có ít nhất 1 permission match → cho qua
        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission)) {
                return $next($request);
            }
        }

        // Không có quyền
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Bạn không có quyền thực hiện thao tác này.'
            ], 403);
        }

        abort(403, 'Bạn không có quyền truy cập trang này.');
    }
}