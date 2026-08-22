<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrescriptionProcedure extends Model
{
    protected $fillable = [
        'prescription_id',
        'procedure_id',
        'procedure_name',
        'sort_order',
    ];

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }

    public function procedure()
    {
        return $this->belongsTo(Procedure::class);
    }
}
