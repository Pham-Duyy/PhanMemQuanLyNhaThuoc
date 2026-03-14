<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Scopes\PharmacyScope;

class ActivityLog extends Model
{
    public $timestamps = false;
    public $updatedAt = false;

    protected $fillable = [
        'pharmacy_id',
        'user_id',
        'action',
        'module',
        'record_id',
        'description',
        'changes',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'changes' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new PharmacyScope);
    }

    // ── Relations ──────────────────────────────────────────────────────────
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Static helper — dùng mọi nơi ─────────────────────────────────────
    public static function log(
        string $action,
        string $module,
        ?int $recordId = null,
        string $description = '',
        ?array $changes = null
    ): void {
        try {
            if (!auth()->check())
                return;

            static::create([
                'pharmacy_id' => auth()->user()->pharmacy_id,
                'user_id' => auth()->id(),
                'action' => $action,
                'module' => $module,
                'record_id' => $recordId,
                'description' => $description ?: static::buildDescription($action, $module),
                'changes' => $changes,
                'ip_address' => request()->ip(),
                'user_agent' => substr(request()->userAgent() ?? '', 0, 200),
            ]);
        } catch (\Exception) {
            // Không để lỗi log làm crash app
        }
    }

    private static function buildDescription(string $action, string $module): string
    {
        $actionLabels = [
            'create' => 'Tạo mới',
            'update' => 'Cập nhật',
            'delete' => 'Xóa',
            'login' => 'Đăng nhập',
            'logout' => 'Đăng xuất',
            'export' => 'Xuất dữ liệu',
            'print' => 'In phiếu',
            'approve' => 'Duyệt',
            'cancel' => 'Hủy',
            'receive' => 'Nhận hàng',
        ];
        $moduleLabels = [
            'invoice' => 'hóa đơn',
            'purchase' => 'đơn nhập hàng',
            'medicine' => 'thuốc',
            'batch' => 'lô hàng',
            'customer' => 'khách hàng',
            'supplier' => 'nhà cung cấp',
            'user' => 'người dùng',
            'inventory' => 'tồn kho',
            'return_invoice' => 'phiếu trả hàng',
        ];
        return ($actionLabels[$action] ?? $action) . ' ' . ($moduleLabels[$module] ?? $module);
    }

    // ── Accessors ──────────────────────────────────────────────────────────
    public function getActionIconAttribute(): string
    {
        return match ($this->action) {
            'create' => '🟢', 'update' => '🟡', 'delete' => '🔴',
            'login' => '🔵', 'logout' => '⚫', 'export' => '📥',
            'print' => '🖨️', 'approve' => '✅', 'cancel' => '❌',
            'receive' => '📦',
            default => '⚪',
        };
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'create' => 'Tạo mới', 'update' => 'Cập nhật', 'delete' => 'Xóa',
            'login' => 'Đăng nhập', 'logout' => 'Đăng xuất', 'export' => 'Xuất',
            'print' => 'In phiếu', 'approve' => 'Duyệt', 'cancel' => 'Hủy',
            'receive' => 'Nhận hàng',
            default => $this->action,
        };
    }

    public function getActionColorAttribute(): string
    {
        return match ($this->action) {
            'create', 'approve', 'receive' => 'success',
            'update' => 'warning',
            'delete', 'cancel' => 'danger',
            'login', 'logout' => 'info',
            'export', 'print' => 'secondary',
            default => 'light',
        };
    }
}