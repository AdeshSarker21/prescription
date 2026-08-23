<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmartSerialChamber extends Model
{
    protected $table = 'smart_serial_chambers';

    protected $fillable = [
        'doctor_id',
        'name',
        'chamber_number',
        'is_active',
        'serial_prefix',
        'daily_starting_number',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'daily_starting_number' => 'integer',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(SerialSession::class, 'chamber_id');
    }

    public function todaySession()
    {
        return $this->sessions()->where('session_date', now()->toDateString())->first();
    }

    public function getFullSerialPrefixAttribute(): string
    {
        return $this->serial_prefix ? $this->serial_prefix . '-' : '';
    }
}
