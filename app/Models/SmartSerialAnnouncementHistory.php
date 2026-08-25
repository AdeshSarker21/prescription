<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmartSerialAnnouncementHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'serial_session_id',
        'patient_queue_id',
        'patient_id',
        'announcement_type',
        'text_spoken',
        'tts_provider_used',
        'audio_cache_key',
        'success',
        'error_message',
        'announced_at',
    ];

    protected $casts = [
        'success' => 'boolean',
        'announced_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(SerialSession::class, 'serial_session_id');
    }

    public function queue()
    {
        return $this->belongsTo(PatientQueue::class, 'patient_queue_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
}
