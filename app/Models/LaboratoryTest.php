<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LaboratoryTest extends Model
{
    protected $fillable = [
        'test_name',
        'slug',
        'used_count',
        'created_by',
        'status',
    ];

    protected $casts = [
        'used_count' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (LaboratoryTest $model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->test_name);
            }
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function prescriptions()
    {
        return $this->belongsToMany(Prescription::class, 'prescription_tests')
            ->withTimestamps();
    }

    public function prescriptionTests()
    {
        return $this->hasMany(PrescriptionTest::class, 'laboratory_test_id');
    }

    public function doctorUsages()
    {
        return $this->morphMany(DoctorItemUsage::class, 'itemable');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePopular($query)
    {
        return $query->orderBy('used_count', 'desc')->orderBy('test_name');
    }

    public function scopeSearch($query, $term)
    {
        return $query->where('test_name', 'like', "%{$term}%");
    }

    public static function findByNameOrCreate(string $name, ?int $createdBy = null): self
    {
        $trimmed = trim($name);
        $normalizedName = mb_strtolower($trimmed);

        $existing = static::whereRaw('LOWER(test_name) = ?', [$normalizedName])->first();
        if ($existing) {
            return $existing;
        }

        return static::create([
            'test_name' => $trimmed,
            'created_by' => $createdBy,
        ]);
    }
}
