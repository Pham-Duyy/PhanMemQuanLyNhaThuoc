<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    // ── Xem profile ───────────────────────────────────────────────────────────
    public function show()
    {
        $user = auth()->user()->load('roles', 'pharmacy');
        return view('profile.show', compact('user'));
    }

    // ── Cập nhật thông tin cá nhân ────────────────────────────────────────────
    public function update(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update($data);

        return back()->with('success', 'Cập nhật thông tin thành công!');
    }

    // ── Đổi mật khẩu ─────────────────────────────────────────────────────────
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)],
            'password_confirmation' => 'required',
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'password.min' => 'Mật khẩu mới tối thiểu 8 ký tự.',
            'password_confirmation.required' => 'Vui lòng xác nhận mật khẩu mới.',
        ]);

        $user = auth()->user();

        // Kiểm tra mật khẩu hiện tại
        if (!Hash::check($request->current_password, $user->password)) {
            return back()
                ->withErrors(['current_password' => 'Mật khẩu hiện tại không đúng.'])
                ->withInput();
        }

        // Không cho đặt lại mật khẩu cũ
        if (Hash::check($request->password, $user->password)) {
            return back()
                ->withErrors(['password' => 'Mật khẩu mới không được trùng mật khẩu cũ.'])
                ->withInput();
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', '✅ Đổi mật khẩu thành công!');
    }
}