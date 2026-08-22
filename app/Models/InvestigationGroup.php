<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestigationGroup extends Model
{
    protected $fillable = [
        'name',
        'name_bn',
        'description',
        'sort_order',
    ];

    public function parameters()
    {
        return $this->hasMany(InvestigationGroupParameter::class, 'group_id')->orderBy('sort_order');
    }
}
