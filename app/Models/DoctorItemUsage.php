<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DoctorItemUsage extends Model
{
    protected $fillable = [
        'doctor_id',
        'itemable_type',
        'itemable_id',
    ];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function itemable(): MorphTo
    {
        return $this->morphTo();
    }
}
