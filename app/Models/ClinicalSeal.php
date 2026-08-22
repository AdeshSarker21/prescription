<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ClinicalSeal extends Model
{
    protected $fillable = [
        'name',
        'details',
        'slug',
        'created_by',
        'status',
        'is_active',
        'used_count',
        'tenant_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'used_count' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (ClinicalSeal $model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function prescriptionItems()
    {
        return $this->hasMany(PrescriptionItem::class, 'seal_id');
    }

    public function doctorUsages()
    {
        return $this->morphMany(DoctorItemUsage::class, 'itemable');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('is_active', true);
    }

    public function scopePopular($query)
    {
        return $query->orderBy('used_count', 'desc')->orderBy('name');
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('details', 'like', "%{$term}%");
        });
    }

    public static function findByNameOrCreate(string $name, ?int $createdBy = null): self
    {
        $trimmed = trim($name);
        $normalizedName = mb_strtolower($trimmed);

        $existing = static::whereRaw('LOWER(name) = ?', [$normalizedName])->first();
        if ($existing) {
            return $existing;
        }

        return static::create([
            'name' => $trimmed,
            'created_by' => $createdBy,
        ]);
    }
}
