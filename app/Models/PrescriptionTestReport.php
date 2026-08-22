<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrescriptionTestReport extends Model
{
    protected $fillable = [
        'prescription_id',
        'test_name',
        'parameter_name',
        'value',
        'unit',
        'reference_range',
        'sort_order',
    ];

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }
}
