<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicine extends Model
{
    public static function findDuplicate(string $name, ?string $strength = null, ?string $genericName = null, ?int $excludeId = null): ?self
    {
        return static::whereRaw('LOWER(name) = ?', [mb_strtolower(trim($name))])
            ->whereRaw('LOWER(COALESCE(strength, \'\')) = ?', [mb_strtolower(trim($strength ?? ''))])
            ->whereRaw('LOWER(COALESCE(generic_name, \'\')) = ?', [mb_strtolower(trim($genericName ?? ''))])
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->first();
    }

    protected $fillable = [
        'name', 'generic_name', 'brand_name', 'category_id',
        'strength', 'active_ingredients', 'salt_composition',
        'company_name', 'country', 'batch_required',
        'indication', 'composition', 'pharmacology',
        'adult_dose', 'child_dose', 'max_daily_dose', 'duration_recommendation',
        'side_effects', 'contraindications', 'pregnancy_safe', 'allergy_warning',
        'drug_interaction_notes', 'usage_instructions', 'food_interaction', 'alcohol_warning',
        'overdose_effects', 'therapeutic_class', 'storage_conditions',
        'medex_url',
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

    public function suggestions(): HasMany
    {
        return $this->hasMany(MedicineSuggestion::class, 'medicine_id');
    }
}
