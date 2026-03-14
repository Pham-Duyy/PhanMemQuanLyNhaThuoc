<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')
            ->where('pharmacy_id', auth()->user()->pharmacy_id)
            ->orderBy('name')
            ->paginate(20);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::whereIn('name', ['admin', 'manager', 'pharmacist'])->get();
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $allowedRoleIds = Role::whereIn('name', ['admin', 'manager', 'pharmacist'])->pluck('id');
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
            'role_id' => ['required', 'exists:roles,id', Rule::in($allowedRoleIds)],
            'gender' => 'nullable|in:male,female,other',
        ]);

        $user = User::create([
            'pharmacy_id' => auth()->user()->pharmacy_id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'gender' => $data['gender'] ?? null,
            'password' => Hash::make($data['password']),
            'is_active' => true,
        ]);

        $user->roles()->attach($data['role_id'], ['assigned_at' => now()]);

        return redirect()->route('users.index')
            ->with('success', 'Tạo tài khoản "' . $user->name . '" thành công!');
    }

    public function edit(User $user)
    {
        $roles = Role::whereIn('name', ['admin', 'manager', 'pharmacist'])->get();
        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $allowedRoleIds = Role::whereIn('name', ['admin', 'manager', 'pharmacist'])->pluck('id');
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female,other',
            'position' => 'nullable|string|max:100',
            'base_salary' => 'nullable|numeric|min:0',
            'role_id' => ['required', 'exists:roles,id', Rule::in($allowedRoleIds)],
            'password' => 'nullable|string|min:6|confirmed',
            'is_active' => 'boolean',
        ]);

        $updateData = [
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'gender' => $data['gender'] ?? null,
            'position' => $data['position'] ?? null,
            'base_salary' => $data['base_salary'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = bcrypt($data['password']);
        }

        $user->update($updateData);

        // Đổi role
        $user->roles()->sync([$data['role_id'] => ['assigned_at' => now()]]);

        return redirect()->route('users.index')
            ->with('success', 'Cập nhật tài khoản thành công!');
    }

    public function destroy(User $user)
    {
        // Không thể tự xóa chính mình
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Không thể xóa tài khoản đang đăng nhập.');
        }
        $user->update(['is_active' => false]);
        return back()->with('success', 'Đã vô hiệu hóa tài khoản "' . $user->name . '".');
    }
    // ── Admin đặt lại mật khẩu không cần email ───────────────────────────────
    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'new_password' => 'required|min:8|confirmed',
        ], [
            'new_password.required' => 'Vui lòng nhập mật khẩu mới.',
            'new_password.min' => 'Mật khẩu tối thiểu 8 ký tự.',
            'new_password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);

        // Chỉ admin/manager mới được reset
        if (!auth()->user()->hasPermission('user.edit')) {
            abort(403);
        }

        $user->update(['password' => bcrypt($request->new_password)]);

        return back()->with('success', "✅ Đã đặt lại mật khẩu cho {$user->name} thành công!");
    }

}