<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    const STATUS_INVESTIGATION_PENDING = 'investigation_pending';
    const STATUS_REPORT_RECEIVED = 'report_received';
    const STATUS_TREATMENT_STARTED = 'treatment_started';
    const STATUS_FOLLOW_UP = 'follow_up';
    const STATUS_COMPLETED = 'completed';

    const STATUSES = [
        self::STATUS_INVESTIGATION_PENDING,
        self::STATUS_REPORT_RECEIVED,
        self::STATUS_TREATMENT_STARTED,
        self::STATUS_FOLLOW_UP,
        self::STATUS_COMPLETED,
    ];

    const STATUS_COLORS = [
        self::STATUS_INVESTIGATION_PENDING => 'amber',
        self::STATUS_REPORT_RECEIVED => 'blue',
        self::STATUS_TREATMENT_STARTED => 'green',
        self::STATUS_FOLLOW_UP => 'purple',
        self::STATUS_COMPLETED => 'gray',
    ];

    const STATUS_LABELS = [
        self::STATUS_INVESTIGATION_PENDING => 'Investigation Pending',
        self::STATUS_REPORT_RECEIVED => 'Report Received',
        self::STATUS_TREATMENT_STARTED => 'Treatment Started',
        self::STATUS_FOLLOW_UP => 'Follow Up',
        self::STATUS_COMPLETED => 'Completed',
    ];

    protected $fillable = [
        'doctor_id',
        'patient_id',
        'prescription_number',
        'diagnosis',
        'notes',
        'follow_up_instructions',
        'follow_up_date',
        'height',
        'weight',
        'bp_systolic',
        'bp_diastolic',
        'pulse_rate',
        'spo2',
        'status',
        'investigation_pending',
        'treatment_started_at',
        'report_received_at',
        'completed_at',
        'family_history_data',
        'menstrual_history_data',
        'drug_history_data',
        'ot_note_data',
        'anesthesia_data',
    ];

    protected $casts = [
        'follow_up_date' => 'date',
        'spo2' => 'decimal:1',
        'investigation_pending' => 'boolean',
        'treatment_started_at' => 'datetime',
        'report_received_at' => 'datetime',
        'completed_at' => 'datetime',
        'family_history_data' => 'array',
        'menstrual_history_data' => 'array',
        'drug_history_data' => 'array',
        'ot_note_data' => 'array',
        'anesthesia_data' => 'array',
    ];

    public function getNextStatuses(): array
    {
        $map = [
            self::STATUS_INVESTIGATION_PENDING => [self::STATUS_REPORT_RECEIVED, self::STATUS_TREATMENT_STARTED],
            self::STATUS_REPORT_RECEIVED => [self::STATUS_TREATMENT_STARTED, self::STATUS_FOLLOW_UP],
            self::STATUS_TREATMENT_STARTED => [self::STATUS_FOLLOW_UP, self::STATUS_COMPLETED],
            self::STATUS_FOLLOW_UP => [self::STATUS_TREATMENT_STARTED, self::STATUS_COMPLETED],
            self::STATUS_COMPLETED => [self::STATUS_TREATMENT_STARTED],
        ];
        return $map[$this->status] ?? self::STATUSES;
    }

    public function canTransitionTo(string $targetStatus): bool
    {
        if ($this->status === $targetStatus) return false;
        return in_array($targetStatus, self::STATUSES);
    }

    public function doctor() { return $this->belongsTo(User::class, 'doctor_id'); }
    public function patient() { return $this->belongsTo(Patient::class); }
    public function items() { return $this->hasMany(PrescriptionItem::class)->orderBy('sort_order')->orderBy('id'); }
    public function sealItems() { return $this->hasMany(PrescriptionItem::class)->where('type', 'seal')->orderBy('id'); }
    public function advice() { return $this->hasMany(PrescriptionAdvice::class)->orderBy('sort_order'); }
    public function advices() { return $this->belongsToMany(Advice::class, 'prescription_advices')->withTimestamps(); }
    public function complaints() { return $this->belongsToMany(Complaint::class)->withPivot('notes', 'sort_order')->withTimestamps()->orderBy('complaint_prescription.sort_order'); }
    public function prescriptionComplaints() { return $this->hasMany(PrescriptionComplaint::class)->orderBy('sort_order'); }
    public function tests() { return $this->hasMany(PrescriptionTest::class)->orderBy('sort_order'); }
    public function labTests() { return $this->belongsToMany(LaboratoryTest::class, 'prescription_tests')->withTimestamps(); }
    public function testReports() { return $this->hasMany(PrescriptionTestReport::class)->orderBy('sort_order'); }
    public function testReportResults() { return $this->hasMany(TestReport::class); }
    public function procedures() { return $this->hasMany(PrescriptionProcedure::class)->orderBy('sort_order'); }
    public function treatmentPlans() { return $this->hasMany(PrescriptionTreatmentPlan::class)->orderBy('sort_order'); }
    public function statusLogs() { return $this->hasMany(PrescriptionStatusLog::class)->latest('changed_at'); }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePendingInvestigations($query)
    {
        return $query->where('status', self::STATUS_INVESTIGATION_PENDING);
    }

    public function scopeActiveTreatments($query)
    {
        return $query->where('status', self::STATUS_TREATMENT_STARTED);
    }

    public function scopeFollowUps($query)
    {
        return $query->where('status', self::STATUS_FOLLOW_UP);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function isEditable(): bool
    {
        return $this->status !== self::STATUS_COMPLETED;
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    public function statusColor(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'gray';
    }

    public static function colorClasses(string $status): array
    {
        $map = [
            self::STATUS_INVESTIGATION_PENDING => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200', 'dot' => 'bg-amber-500'],
            self::STATUS_REPORT_RECEIVED => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-200', 'dot' => 'bg-blue-500'],
            self::STATUS_TREATMENT_STARTED => ['bg' => 'bg-green-50', 'text' => 'text-green-700', 'border' => 'border-green-200', 'dot' => 'bg-green-500'],
            self::STATUS_FOLLOW_UP => ['bg' => 'bg-purple-50', 'text' => 'text-purple-700', 'border' => 'border-purple-200', 'dot' => 'bg-purple-500'],
            self::STATUS_COMPLETED => ['bg' => 'bg-gray-50', 'text' => 'text-gray-600', 'border' => 'border-gray-200', 'dot' => 'bg-gray-400'],
        ];
        return $map[$status] ?? $map[self::STATUS_COMPLETED];
    }

    public function logStatus(string $newStatus, ?string $notes = null): void
    {
        $oldStatus = $this->status;
        $this->status = $newStatus;
        $this->save();

        $this->statusLogs()->create([
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'notes' => $notes,
            'changed_by' => auth()->id(),
            'changed_at' => now(),
        ]);
    }
}
