<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $fillable = [
        'pharmacy_id',
        'user_id',
        'month',
        'year',
        'total_shifts',
        'total_hours',
        'absent_days',
        'late_days',
        'base_salary',
        'shift_salary',
        'bonus',
        'deduction',
        'net_salary',
        'status',
        'confirmed_by',
        'confirmed_at',
        'note',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
        'net_salary' => 'decimal:0',
        'base_salary' => 'decimal:0',
        'shift_salary' => 'decimal:0',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }
    public function confirmer()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return ['draft' => 'Nháp', 'confirmed' => 'Đã duyệt', 'paid' => 'Đã trả'][$this->status] ?? '';
    }

    public function getStatusColorAttribute(): string
    {
        return ['draft' => 'secondary', 'confirmed' => 'primary', 'paid' => 'success'][$this->status] ?? 'secondary';
    }
}