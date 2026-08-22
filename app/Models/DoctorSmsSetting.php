<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorSmsSetting extends Model
{
    protected $fillable = [
        'doctor_id',
        'sms_enabled',
        'api_url',
        'api_key',
        'sender_id',
        'username',
        'password',
        'reminder_days_before',
        'sms_template',
    ];

    protected $casts = [
        'sms_enabled' => 'boolean',
        'reminder_days_before' => 'integer',
    ];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function logs()
    {
        return $this->hasMany(SmsLog::class, 'doctor_id');
    }

    public function scopeEnabled($query)
    {
        return $query->where('sms_enabled', true);
    }

    public static function getForDoctor(int $doctorId): self
    {
        return static::firstOrCreate(
            ['doctor_id' => $doctorId],
            [
                'api_url' => '',
                'sms_enabled' => false,
                'reminder_days_before' => 1,
                'sms_template' => "প্রিয় {{patient_name}},\n\nআপনার ডাক্তার {{doctor_name}}-এর কাছে পরবর্তী অ্যাপয়েন্টমেন্ট {{followup_date}} তারিখে নির্ধারিত আছে।\n\nসময়: {{followup_time}}\n\nদয়া করে সঠিক সময়ে উপস্থিত হোন।\n\nধন্যবাদ,\n{{doctor_name}}",
            ]
        );
    }

    public function getDefaultTemplate(): string
    {
        return $this->sms_template ?? "প্রিয় {{patient_name}},\n\nআপনার ডাক্তার {{doctor_name}}-এর কাছে পরবর্তী অ্যাপয়েন্টমেন্ট {{followup_date}} তারিখে নির্ধারিত আছে।\n\nসময়: {{followup_time}}\n\nদয়া করে সঠিক সময়ে উপস্থিত হোন।\n\nধন্যবাদ,\n{{doctor_name}}";
    }
}
