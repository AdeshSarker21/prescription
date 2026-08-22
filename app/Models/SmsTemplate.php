<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsTemplate extends Model
{
    protected $fillable = [
        'doctor_id',
        'name',
        'type',
        'message',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForDoctor($query, ?int $doctorId)
    {
        return $query->where(function ($q) use ($doctorId) {
            $q->whereNull('doctor_id')->orWhere('doctor_id', $doctorId);
        });
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public static function getTypes(): array
    {
        return [
            'welcome' => 'Welcome SMS',
            'follow_up' => 'Follow-up Reminder',
            'appointment' => 'Appointment Reminder',
            'custom' => 'Custom SMS',
        ];
    }
}
