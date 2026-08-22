<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrescriptionFooter extends Model
{
    protected $fillable = [
        'name',
        'content',
        'status',
    ];

    public function doctors()
    {
        return $this->hasMany(DoctorPrescriptionSetting::class, 'footer_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
