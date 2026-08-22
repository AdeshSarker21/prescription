<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrescriptionTest extends Model
{
    protected $fillable = [
        'prescription_id',
        'laboratory_test_id',
        'test_name',
        'sort_order',
    ];

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }

    public function laboratoryTest()
    {
        return $this->belongsTo(LaboratoryTest::class);
    }
}
