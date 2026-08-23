<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ModulePermission extends Model
{
    protected $fillable = [
        'module_id',
        'name',
        'guard_name',
        'description',
    ];

    protected $casts = [];

    /**
     * Get the module this permission belongs to.
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Get the users that have this permission.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_module_permissions')
            ->withPivot(['is_granted', 'granted_by', 'granted_at'])
            ->withTimestamps();
    }

    /**
     * Check if this permission is granted to a specific user.
     */
    public function isGrantedTo(int $userId): bool
    {
        return $this->users()
            ->where('user_id', $userId)
            ->wherePivot('is_granted', true)
            ->exists();
    }

    /**
     * Scope: permissions for a specific module.
     */
    public function scopeForModule($query, int $moduleId)
    {
        return $query->where('module_id', $moduleId);
    }

    /**
     * Scope: permissions by name.
     */
    public function scopeByName($query, string $name)
    {
        return $query->where('name', $name);
    }
}
