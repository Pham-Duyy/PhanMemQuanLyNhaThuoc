<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckinLog extends Model
{
    protected $fillable = [
        'pharmacy_id',
        'user_id',
        'assignment_id',
        'work_date',
        'checkin_time',
        'checkout_time',
        'late_minutes',
        'early_minutes',
        'actual_hours',
        'status',
        'note',
    ];

    protected $casts = [
        'work_date' => 'date',
        'actual_hours' => 'decimal:1',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }
    public function assignment()
    {
        return $this->belongsTo(ShiftAssignment::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return [
            'checked_in' => 'Đang làm',
            'checked_out' => 'Đã ra về',
            'incomplete' => 'Thiếu checkout',
        ][$this->status] ?? $this->status;
    }
}