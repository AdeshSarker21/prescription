<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Medicine extends Model
{
    protected $fillable = [
        'name', 'generic_name', 'brand_name', 'category_id',
        'strength', 'active_ingredients', 'salt_composition',
        'company_name', 'country', 'batch_required',
        'adult_dose', 'child_dose', 'max_daily_dose', 'duration_recommendation',
        'side_effects', 'contraindications', 'pregnancy_safe', 'allergy_warning',
        'drug_interaction_notes', 'usage_instructions', 'food_interaction', 'alcohol_warning',
        'tenant_id', 'is_global', 'created_by', 'status',
    ];

    protected function casts(): array
    {
        return [
            'batch_required' => 'boolean',
            'is_global' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MedicineCategory::class, 'category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
