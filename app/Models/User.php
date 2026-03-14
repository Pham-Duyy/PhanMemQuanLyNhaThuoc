<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'pharmacy_id',
        'name',
        'email',
        'phone',
        'gender',
        'date_of_birth',
        'id_card',
        'address',
        'avatar',
        'password',
        'is_active',
        'last_login_at',
        'position',
        'base_salary',
        'work_pin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }

    /** Many-to-Many với roles qua bảng role_user */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)
            ->withPivot('assigned_at', 'assigned_by')
            ->withTimestamps();
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'created_by');
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'created_by');
    }
    public function shifts()
    {
        return $this->hasMany(\App\Models\ShiftAssignment::class);
    }

    public function payrolls()
    {
        return $this->hasMany(\App\Models\Payroll::class);
    }

    // ── RBAC Methods ───────────────────────────────────────────────────────────

    /**
     * Kiểm tra user có role này không.
     * Dùng trong Blade: @if(auth()->user()->hasRole('admin'))
     */
    public function hasRole(string $role): bool
    {
        return $this->roles->contains('name', $role);
    }

    /**
     * Kiểm tra user có permission này không.
     * Logic: Admin có tất cả. Các role khác kiểm tra mảng permissions JSON.
     *
     * Dùng trong Blade:
     *   @if(auth()->user()->hasPermission('invoice.create'))
     *
     * Dùng trong Middleware:
     *   ->middleware('permission:invoice.create')
     */
    public function hasPermission(string $permission): bool
    {
        // Admin luôn có toàn quyền
        if ($this->hasRole('admin')) {
            return true;
        }

        // Load roles nếu chưa load (tránh N+1)
        if (!$this->relationLoaded('roles')) {
            $this->load('roles');
        }

        foreach ($this->roles as $role) {
            $permissions = $role->permissions ?? [];

            // Wildcard '*' = toàn quyền
            if (in_array('*', $permissions)) {
                return true;
            }

            if (in_array($permission, $permissions)) {
                return true;
            }

            // Hỗ trợ wildcard theo module: 'invoice.*' match 'invoice.create'
            foreach ($permissions as $perm) {
                if (str_ends_with($perm, '.*')) {
                    $prefix = rtrim($perm, '.*');
                    if (str_starts_with($permission, $prefix . '.')) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Lấy tên role đầu tiên để hiển thị UI.
     * Dùng: $user->primary_role_name
     */
    public function getPrimaryRoleNameAttribute(): string
    {
        return $this->roles->first()?->name ?? 'Chưa phân quyền';
    }

    /**
     * Chữ cái đầu tên (dùng cho avatar chữ ở sidebar/bảng).
     * Dùng: $user->avatar_initial  → 'N'
     */
    public function getAvatarInitialAttribute(): string
    {
        return mb_strtoupper(mb_substr($this->name, 0, 1, 'UTF-8'), 'UTF-8');
    }

    /**
     * Màu avatar tự động theo tên (hash → 1 trong 6 màu).
     * Dùng: style="background:{{ $user->avatar_color }}"
     */
    public function getAvatarColorAttribute(): string
    {
        $colors = ['#2471A3', '#1E8449', '#D35400', '#7D3C98', '#117A65', '#B7950B'];
        return $colors[crc32($this->name) % count($colors)];
    }

    /**
     * User có phải admin không?
     * Dùng: $user->is_admin
     */
    public function getIsAdminAttribute(): bool
    {
        return $this->hasRole('admin') || $this->hasRole('Quản lý');
    }

    /**
     * Hiển thị thời gian đăng nhập gần nhất dạng human-friendly.
     * Dùng: $user->last_login_label
     */
    public function getLastLoginLabelAttribute(): string
    {
        return $this->last_login_at
            ? $this->last_login_at->diffForHumans()
            : 'Chưa đăng nhập';
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfPharmacy($query, int $pharmacyId)
    {
        return $query->where('pharmacy_id', $pharmacyId);
    }
}