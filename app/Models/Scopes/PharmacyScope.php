<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * PharmacyScope — Global Scope bảo mật multi-tenant.
 *
 * Áp dụng vào tất cả Model có cột pharmacy_id.
 * Kết quả: mọi query tự động thêm WHERE pharmacy_id = ?
 *
 * => Nhân viên nhà thuốc A KHÔNG BAO GIỜ thấy dữ liệu nhà thuốc B.
 *
 * Cách dùng trong Model:
 *   protected static function booted(): void
 *   {
 *       static::addGlobalScope(new PharmacyScope());
 *   }
 *
 * Cách BỎ QUA scope (dùng cho Admin/Console):
 *   Medicine::withoutGlobalScope(PharmacyScope::class)->get();
 */
class PharmacyScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Chỉ áp dụng khi đã đăng nhập và user có pharmacy_id
        if (auth()->check() && auth()->user()->pharmacy_id) {
            $builder->where(
                $model->getTable() . '.pharmacy_id',
                auth()->user()->pharmacy_id
            );
        }
    }
}