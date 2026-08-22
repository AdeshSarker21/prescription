<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicineSuggestion extends Model
{
    protected $fillable = [
        'doctor_id',
        'category_id',
        'name',
        'generic_name',
        'strength',
        'company_name',
        'reason',
        'status',
        'admin_notes',
    ];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function category()
    {
        return $this->belongsTo(MedicineCategory::class, 'category_id');
    }
}
