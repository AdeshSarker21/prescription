<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserModulePermission extends Model
{
    protected $table = 'user_module_permissions';

    protected $fillable = [
        'user_id',
        'module_permission_id',
        'is_granted',
        'granted_by',
        'granted_at',
    ];

    protected $casts = [
        'is_granted' => 'boolean',
        'granted_at' => 'datetime',
    ];

    /**
     * Get the user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the module permission.
     */
    public function modulePermission(): BelongsTo
    {
        return $this->belongsTo(ModulePermission::class);
    }

    /**
     * Get the admin who granted this.
     */
    public function grantedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    /**
     * Scope: only granted entries.
     */
    public function scopeGranted($query)
    {
        return $query->where('is_granted', true);
    }

    /**
     * Scope: for a specific user.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: for a specific module permission.
     */
    public function scopeForModulePermission($query, int $modulePermissionId)
    {
        return $query->where('module_permission_id', $modulePermissionId);
    }
}
