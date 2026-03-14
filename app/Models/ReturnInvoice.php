<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Scopes\PharmacyScope;

class ReturnInvoice extends Model
{
    protected $fillable = [
        'pharmacy_id',
        'invoice_id',
        'customer_id',
        'created_by',
        'code',
        'return_date',
        'total_amount',
        'refund_amount',
        'refund_method',
        'reason',
        'note',
        'status',
    ];

    protected $casts = [
        'return_date' => 'date',
        'total_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new PharmacyScope);

        // Auto generate code: TH-YYYYMM-XXX
        static::creating(function ($m) {
            $prefix = 'TH-' . now()->format('Ym') . '-';
            $last = static::withoutGlobalScopes()
                ->where('code', 'like', $prefix . '%')
                ->max('code');
            $seq = $last ? ((int) substr($last, -3)) + 1 : 1;
            $m->code = $prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);
        });
    }

    // ── Relations ──────────────────────────────────────────────────────────
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }
    public function items(): HasMany
    {
        return $this->hasMany(ReturnInvoiceItem::class);
    }

    // ── Accessors ──────────────────────────────────────────────────────────
    public function getRefundMethodLabelAttribute(): string
    {
        return match ($this->refund_method) {
            'cash' => '💵 Tiền mặt',
            'account' => '🏦 Chuyển khoản',
            default => $this->refund_method,
        };
    }
}