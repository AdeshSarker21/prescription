<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MenstrualHistory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'used_count',
        'created_by',
        'status',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'used_count' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (MenstrualHistory $model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
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
        return $query->where('name', 'like', "%{$term}%");
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
