<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorPrescriptionSetting extends Model
{
    protected $fillable = [
        'doctor_id',
        'header_enabled',
        'header_id',
        'footer_enabled',
        'footer_id',
        'paper_size',
        'paper_width_mm',
        'paper_height_mm',
        'header_margin_top_mm',
        'header_margin_right_mm',
        'header_margin_bottom_mm',
        'header_margin_left_mm',
        'header_padding_top_mm',
        'header_padding_right_mm',
        'header_padding_bottom_mm',
        'header_padding_left_mm',
        'footer_margin_top_mm',
        'footer_margin_right_mm',
        'footer_margin_bottom_mm',
        'footer_margin_left_mm',
        'footer_padding_top_mm',
        'footer_padding_right_mm',
        'footer_padding_bottom_mm',
        'footer_padding_left_mm',
    ];

    protected $casts = [
        'header_enabled' => 'boolean',
        'footer_enabled' => 'boolean',
        'paper_width_mm' => 'decimal:2',
        'paper_height_mm' => 'decimal:2',
        'header_margin_top_mm' => 'decimal:2',
        'header_margin_right_mm' => 'decimal:2',
        'header_margin_bottom_mm' => 'decimal:2',
        'header_margin_left_mm' => 'decimal:2',
        'header_padding_top_mm' => 'decimal:2',
        'header_padding_right_mm' => 'decimal:2',
        'header_padding_bottom_mm' => 'decimal:2',
        'header_padding_left_mm' => 'decimal:2',
        'footer_margin_top_mm' => 'decimal:2',
        'footer_margin_right_mm' => 'decimal:2',
        'footer_margin_bottom_mm' => 'decimal:2',
        'footer_margin_left_mm' => 'decimal:2',
        'footer_padding_top_mm' => 'decimal:2',
        'footer_padding_right_mm' => 'decimal:2',
        'footer_padding_bottom_mm' => 'decimal:2',
        'footer_padding_left_mm' => 'decimal:2',
    ];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function header()
    {
        return $this->belongsTo(PrescriptionHeader::class, 'header_id');
    }

    public function footer()
    {
        return $this->belongsTo(PrescriptionFooter::class, 'footer_id');
    }

    public static function getForDoctor(int $doctorId): self
    {
        return static::firstOrCreate(['doctor_id' => $doctorId]);
    }

    public function getPaperWidthMmAttribute(): float
    {
        return match ($this->attributes['paper_size'] ?? 'A4') {
            'A4' => 210,
            'A5' => 148,
            'Letter' => 216,
            'Custom' => (float) ($this->attributes['paper_width_mm'] ?? 210),
            default => 210,
        };
    }

    public function getPaperHeightMmAttribute(): float
    {
        return match ($this->attributes['paper_size'] ?? 'A4') {
            'A4' => 297,
            'A5' => 210,
            'Letter' => 279,
            'Custom' => (float) ($this->attributes['paper_height_mm'] ?? 297),
            default => 297,
        };
    }
}
