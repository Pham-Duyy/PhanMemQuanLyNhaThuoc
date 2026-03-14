<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ShiftAssignment extends Model
{
    protected $fillable = [
        'pharmacy_id',
        'user_id',
        'shift_id',
        'work_date',
        'status',
        'actual_start',
        'actual_end',
        'actual_hours',
        'wage_amount',
        'assigned_by',
        'note',
    ];

    protected $casts = [
        'work_date' => 'date',
        'actual_hours' => 'decimal:1',
        'wage_amount' => 'decimal:0',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function shift()
    {
        return $this->belongsTo(WorkShift::class, 'shift_id');
    }
    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }
    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return [
            'scheduled' => 'Đã xếp',
            'confirmed' => 'Xác nhận',
            'completed' => 'Hoàn thành',
            'absent' => 'Vắng',
            'late' => 'Đi muộn',
        ][$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return [
            'scheduled' => 'secondary',
            'confirmed' => 'primary',
            'completed' => 'success',
            'absent' => 'danger',
            'late' => 'warning',
        ][$this->status] ?? 'secondary';
    }
}