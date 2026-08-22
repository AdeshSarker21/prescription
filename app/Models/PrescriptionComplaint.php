<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrescriptionComplaint extends Model
{
    protected $fillable = [
        'prescription_id',
        'complaint_name',
        'notes',
        'sort_order',
    ];

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }
}
