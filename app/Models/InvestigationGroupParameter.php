<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestigationGroupParameter extends Model
{
    protected $fillable = [
        'group_id',
        'parameter_name',
        'parameter_name_bn',
        'unit',
        'reference_range',
        'sort_order',
    ];

    public function group()
    {
        return $this->belongsTo(InvestigationGroup::class, 'group_id');
    }
}
