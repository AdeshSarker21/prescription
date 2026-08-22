<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorPrescriptionSetting extends Model
{
    protected $fillable = [
        'doctor_id',
        'header_enabled',
        'header_id',
        'footer_enabled',
        'footer_id',
    ];

    protected $casts = [
        'header_enabled' => 'boolean',
        'footer_enabled' => 'boolean',
    ];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function header()
    {
        return $this->belongsTo(PrescriptionHeader::class, 'header_id');
    }

    public function footer()
    {
        return $this->belongsTo(PrescriptionFooter::class, 'footer_id');
    }

    public static function getForDoctor(int $doctorId): self
    {
        return static::firstOrCreate(['doctor_id' => $doctorId]);
    }
}
