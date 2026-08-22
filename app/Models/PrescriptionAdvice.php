<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrescriptionAdvice extends Model
{
    protected $fillable = [
        'prescription_id',
        'advice',
        'sort_order',
    ];

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }
}
