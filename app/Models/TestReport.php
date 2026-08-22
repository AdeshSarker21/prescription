<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestReport extends Model
{
    protected $fillable = [
        'prescription_id',
        'test_name',
        'result',
    ];

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }
}
