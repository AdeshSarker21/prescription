<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrescriptionItem extends Model
{
    protected $fillable = [
        'prescription_id',
        'type',
        'medicine_id',
        'medicine_name',
        'seal_id',
        'seal_text',
        'seal_details',
        'medicine_suggestion_id',
        'sort_order',
        'dosage',
        'frequency',
        'duration',
        'instructions',
        'route',
        'when_to_take',
        'quantity',
        'refills',
    ];

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function seal(): BelongsTo
    {
        return $this->belongsTo(ClinicalSeal::class, 'seal_id');
    }

    public function medicineSuggestion(): BelongsTo
    {
        return $this->belongsTo(MedicineSuggestion::class, 'medicine_suggestion_id');
    }

    public function isMedicine(): bool
    {
        return $this->type === 'medicine' || $this->type === null;
    }

    public function isSeal(): bool
    {
        return $this->type === 'seal';
    }
}
