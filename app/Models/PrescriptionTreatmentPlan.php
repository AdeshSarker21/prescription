<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrescriptionTreatmentPlan extends Model
{
    protected $fillable = [
        'prescription_id',
        'treatment_plan_id',
        'treatment_plan_name',
        'sort_order',
    ];

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }

    public function treatmentPlan()
    {
        return $this->belongsTo(TreatmentPlan::class);
    }
}
