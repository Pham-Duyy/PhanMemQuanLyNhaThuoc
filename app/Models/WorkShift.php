<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WorkShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'pharmacy_id',
        'name',
        'start_time',
        'end_time',
        'hours',
        'color',
        'shift_wage',
        'is_active',
        'note',
    ];

    protected $casts = [
        'hours' => 'decimal:1',
        'shift_wage' => 'decimal:0',
        'is_active' => 'boolean',
    ];

    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }
    public function assignments()
    {
        return $this->hasMany(ShiftAssignment::class, 'shift_id');
    }

    public function getTimeRangeAttribute(): string
    {
        return substr($this->start_time, 0, 5) . ' – ' . substr($this->end_time, 0, 5);
    }
}