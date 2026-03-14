<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'permissions',
        'description',
    ];

    protected $casts = [
        // Cast JSON → PHP array tự động
        'permissions' => 'array',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('assigned_at', 'assigned_by');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    /**
     * Kiểm tra role này có permission cụ thể không.
     */
    public function hasPermission(string $permission): bool
    {
        $permissions = $this->permissions ?? [];

        if (in_array('*', $permissions)) {
            return true;
        }

        return in_array($permission, $permissions);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    /** Lấy role theo tên slug */
    public function scopeByName($query, string $name)
    {
        return $query->where('name', $name);
    }
}