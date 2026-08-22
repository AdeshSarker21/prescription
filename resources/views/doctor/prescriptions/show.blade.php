@extends('doctor.layouts.prescription')

@section('title', 'Prescription #'.$prescription->prescription_number)

@section('prescription-content')
<header class="app-header">
    <div class="header-title">
        <h1>{{ strtoupper(config('app.name')) }}</h1>
        <h2>DOCTOR'S PRESCRIPTION</h2>
    </div>
    <a href="{{ route('doctor.prescriptions.index') }}" class="close-btn">&times;</a>
</header>

<section class="patient-info-grid">
    <div class="form-group">
        <label>Patient :</label>
        <input type="text" value="{{ $prescription->patient->name }}" class="w-full" readonly>
    </div>
    <div class="form-group">
        <label>Age:</label>
        <input type="text" value="{{ $prescription->patient->date_of_birth ? \Carbon\Carbon::parse($prescription->patient->date_of_birth)->age : 'N/A' }}" class="w-small" readonly>
    </div>
    <div class="form-group">
        <label>Sex:</label>
        <input type="text" value="{{ ucfirst($prescription->patient->gender ?? 'N/A') }}" class="w-small" readonly>
    </div>
    <div class="form-group">
        <label>Date:</label>
        <input type="text" value="{{ $prescription->created_at->format('d/m/Y') }}" readonly>
    </div>

    <div class="form-group">
        <label>Prescription No:</label>
        <input type="text" value="{{ $prescription->prescription_number }}" class="w-small" readonly>
    </div>
    <div class="form-group" style="justify-content:flex-end;">
        <label>Status:</label>
        @php $statusClasses = \App\Models\Prescription::colorClasses($prescription->status); @endphp
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold {{ $statusClasses['bg'] }} {{ $statusClasses['text'] }} {{ $statusClasses['border'] }} border">
            <span class="w-1.5 h-1.5 rounded-full {{ $statusClasses['dot'] }}"></span>
            {{ $prescription->statusLabel() }}
        </span>
        <button type="button" onclick="document.getElementById('status-change-section').classList.toggle('hidden')" style="margin-left:8px;padding:2px 8px;font-size:11px;border:1px solid rgba(148,163,184,0.3);border-radius:6px;background:rgba(255,255,255,0.5);cursor:pointer;">Change</button>
        @if($prescription->status === \App\Models\Prescription::STATUS_INVESTIGATION_PENDING && ($prescription->testReports->count() > 0 || $prescription->testReportResults->count() > 0))
        <form method="POST" action="{{ route('doctor.prescriptions.status', $prescription) }}" style="display:inline-block;margin-left:8px;" onsubmit="return confirm('Mark this prescription as Report Received?');">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="{{\App\Models\Prescription::STATUS_REPORT_RECEIVED}}">
            <input type="hidden" name="notes" value="Reports reviewed by doctor">
            <button type="submit" style="padding:2px 10px;font-size:11px;border:1px solid #3b82f6;border-radius:6px;background:#eff6ff;color:#3b82f6;cursor:pointer;font-weight:600;">Mark as Report Received</button>
        </form>
        @endif
    </div>
    <div class="form-group span-2">
        <label>Time:</label>
        <input type="text" value="{{ $prescription->created_at->format('g:i A') }}" class="w-medium" readonly>
    </div>

    <div class="form-group span-2">
        <label>Patient's Name:</label>
        <input type="text" value="{{ $prescription->patient->name }}" class="w-full" readonly>
    </div>
    <div class="form-group span-2">
        <label>Address:</label>
        <input type="text" value="{{ $prescription->patient->address ?? 'N/A' }}" class="w-full" readonly>
    </div>

    <div class="form-group span-2">
        <label>Mobile No:</label>
        <input type="text" value="{{ $prescription->patient->phone ?? 'N/A' }}" class="w-full" readonly>
    </div>
</section>

{{-- Status Change Form --}}
<div id="status-change-section" class="hidden" style="padding:12px 16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;margin-bottom:12px;">
    <form method="POST" action="{{ route('doctor.prescriptions.status', $prescription) }}" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
        @csrf @method('PATCH')
        <label style="font-size:13px;font-weight:600;color:var(--text-primary);">Change Status:</label>
        <select name="status" style="padding:6px 10px;border:1px solid rgba(148,163,184,0.3);border-radius:6px;font-size:13px;">
            @foreach(\App\Models\Prescription::STATUSES as $s)
            <option value="{{ $s }}" {{ $prescription->status === $s ? 'selected' : '' }}>
                {{ \App\Models\Prescription::STATUS_LABELS[$s] ?? ucfirst(str_replace('_', ' ', $s)) }}
            </option>
            @endforeach
        </select>
        <input type="text" name="notes" placeholder="Reason for change (optional)" style="padding:6px 10px;border:1px solid rgba(148,163,184,0.3);border-radius:6px;font-size:13px;flex:1;min-width:150px;">
        <button type="submit" style="padding:6px 16px;background:#6366f1;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:13px;font-weight:600;">Update</button>
    </form>
</div>

@if($customHeader || $customFooter)
<div style="padding:8px 15px;background:#f0fff4;border:1px solid #86efac;border-radius:4px;margin:10px 15px;font-size:12px;color:#166534;">
    <strong>Print Settings:</strong>
    @if($customHeader) Header: {{ $customHeader->name }} @endif
    @if($customHeader && $customFooter) | @endif
    @if($customFooter) Footer: {{ $customFooter->name }} @endif
    — <a href="{{ route('doctor.prescriptions.print', $prescription) }}" style="color:#1d4ed8;text-decoration:underline;" target="_blank">Preview in Print</a>
</div>
@endif

@php
    $bp_sys = $prescription->bp_systolic;
    $bp_dia = $prescription->bp_diastolic;
    $pulse = $prescription->pulse_rate;
    $spo2 = $prescription->spo2;
    $ht = $prescription->height;
    $wt = $prescription->weight;
@endphp
@if($bp_sys || $bp_dia || $pulse || $spo2 || $ht || $wt)
<section style="display:flex;gap:20px;padding:8px 15px;background:#f0f7ff;border:1px solid #bce1ec;border-radius:4px;margin:10px 15px;font-size:13px;">
    @if($pulse)<span><strong>Pulse:</strong> {{ $pulse }}/min</span>@endif
    @if($bp_sys && $bp_dia)<span><strong>BP:</strong> {{ $bp_sys }}/{{ $bp_dia }} mmHg</span>@endif
    @if($spo2)<span><strong>SpO₂:</strong> {{ number_format($spo2, 1) }}%</span>@endif
    @if($ht)<span><strong>Ht:</strong> {{ number_format($ht, 1) }} cm</span>@endif
    @if($wt)<span><strong>Wt:</strong> {{ number_format($wt, 2) }} kg</span>@endif
</section>
@endif

<div class="prescription-body">
    <div class="left-pane">
        <div class="pane-section">
            <h3>Complaints:</h3>
            <div style="display:flex;flex-wrap:wrap;gap:6px;padding:8px;background:#fff;border:1px solid #71a9ce;min-height:36px;font-size:13px;">
                @forelse($prescription->complaints as $complaint)
                <div style="padding:4px 8px;background:#e0f0ff;border:1px solid #71a9ce;border-radius:4px;">
                    <strong>{{ $complaint->name }}</strong>
                    @if($complaint->pivot?->notes)
                    <span style="color:#666;font-size:11px;display:block;margin-top:2px;">{{ $complaint->pivot->notes }}</span>
                    @endif
                </div>
                @empty
                <span style="color:#999;">No complaints recorded.</span>
                @endforelse
            </div>
        </div>

        <div class="pane-section">
            <h3>Tests:</h3>
            <div style="display:flex;flex-wrap:wrap;gap:6px;padding:8px;background:#fff;border:1px solid #71a9ce;min-height:36px;font-size:13px;">
                @forelse($prescription->tests as $test)
                <div style="padding:4px 8px;background:#e0f0ff;border:1px solid #71a9ce;border-radius:4px;font-size:12px;">
                    <strong>{{ $test->test_name }}</strong>
                </div>
                @empty
                <span style="color:#999;">No tests recorded.</span>
                @endforelse
            </div>

            @php
                $reportGroups = $prescription->testReports->groupBy('test_name');
            @endphp

            @if($reportGroups->count() > 0)
            <div style="margin-top:10px;">
                <h3 style="font-size:13px;margin-bottom:6px;color:#1e4d7c;">Test Reports</h3>
                @foreach($reportGroups as $testName => $reports)
                <div style="margin-bottom:8px;padding:6px;background:#f8fbff;border:1px solid #71a9ce;border-radius:4px;">
                    <div style="font-weight:bold;font-size:12px;margin-bottom:4px;color:#1e4d7c;">{{ $testName }}</div>
                    <table style="width:100%;border-collapse:collapse;font-size:11px;">
                        <thead>
                            <tr style="background:#e8f0fe;">
                                <th style="padding:2px 4px;text-align:left;border:1px solid #ccc;">Parameter</th>
                                <th style="padding:2px 4px;text-align:left;border:1px solid #ccc;">Value</th>
                                <th style="padding:2px 4px;text-align:left;border:1px solid #ccc;">Unit</th>
                                <th style="padding:2px 4px;text-align:left;border:1px solid #ccc;">Ref. Range</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reports as $report)
                            <tr>
                                <td style="padding:2px 4px;border:1px solid #ddd;font-weight:bold;">{{ $report->parameter_name }}</td>
                                <td style="padding:2px 4px;border:1px solid #ddd;">{{ $report->value ?? '-' }}</td>
                                <td style="padding:2px 4px;border:1px solid #ddd;color:#555;">{{ $report->unit ?? '' }}</td>
                                <td style="padding:2px 4px;border:1px solid #ddd;color:#888;font-size:10px;">{{ $report->reference_range ?? '' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endforeach
            </div>
            @endif

            @if($prescription->testReportResults->count() > 0)
            <div style="margin-top:10px;">
                <h3 style="font-size:13px;margin-bottom:6px;color:#1e4d7c;">Test Results</h3>
                <div style="font-size:12px;background:#fff;border:1px solid #71a9ce;border-radius:4px;padding:8px;">
                    @foreach($prescription->testReportResults as $result)
                    <div style="padding:2px 0;border-bottom:1px dotted #ddd;display:flex;justify-content:space-between;">
                        <strong>{{ $result->test_name }}</strong>
                        <span>{{ $result->result ?? '—' }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Status Timeline --}}
        @if($prescription->statusLogs->count() > 0)
        <div class="pane-section" style="margin-top:16px;">
            <h3>Status Timeline</h3>
            <div style="position:relative;padding-left:24px;">
                <div style="position:absolute;left:8px;top:4px;bottom:4px;width:2px;background:#e2e8f0;"></div>
                @foreach($prescription->statusLogs as $log)
                @php $sc = \App\Models\Prescription::colorClasses($log->new_status); @endphp
                <div style="position:relative;padding-bottom:14px;">
                    <div style="position:absolute;left:-20px;top:4px;width:12px;height:12px;border-radius:50%;{{ $sc['dot'] }};border:2px solid #fff;box-shadow:0 0 0 2px {{ $sc['dot'] == 'bg-amber-500' ? '#f59e0b' : ($sc['dot'] == 'bg-blue-500' ? '#3b82f6' : ($sc['dot'] == 'bg-green-500' ? '#10b981' : ($sc['dot'] == 'bg-purple-500' ? '#8b5cf6' : '#6b7280'))) }};"></div>
                    <div style="font-size:11px;">
                        <span style="font-weight:700;color:var(--text-primary);">{{ \App\Models\Prescription::STATUS_LABELS[$log->new_status] ?? ucfirst(str_replace('_', ' ', $log->new_status)) }}</span>
                        <span style="color:var(--text-muted);margin-left:6px;">{{ $log->changed_at->format('d M Y, g:i A') }}</span>
                        @if($log->changedBy)
                        <span style="color:var(--text-muted);font-size:10px;display:block;">by {{ $log->changedBy->name }}</span>
                        @endif
                        @if($log->notes)
                        <span style="color:#666;font-size:10px;display:block;font-style:italic;">{{ $log->notes }}</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="right-pane">
        <table class="drug-table">
            <thead>
                <tr>
                    <th style="width:35%">Drug</th>
                    <th style="width:18%">Frequency</th>
                    <th style="width:10%">Day</th>
                    <th style="width:27%">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse($prescription->items->sortBy('sort_order') as $item)
                @if($item->type === 'seal')
                <tr style="background:rgba(251,191,36,0.06);">
                    <td style="padding:6px;font-size:12px;white-space:pre-wrap;">
                        <span style="display:inline-block;padding:1px 6px;background:rgba(251,191,36,0.2);color:#92400e;border-radius:3px;font-size:9px;font-weight:700;margin-right:4px;">SEAL</span>
                        <strong style="color:#92400e;">{{ $item->seal_text }}</strong>
                        @if($item->seal_details)
                        <br><span style="font-weight:normal;color:#555;">{{ $item->seal_details }}</span>
                        @endif
                    </td>
                    <td></td>
                    <td style="padding:6px;font-size:13px;">{{ $item->duration ?? '—' }}</td>
                    <td></td>
                </tr>
                @else
                <tr>
                    <td style="padding:6px;font-size:13px;">
                        <strong>{{ $item->medicine_name }}{{ $item->dosage && !str_contains($item->medicine_name, $item->dosage) ? ' ' . $item->dosage : '' }}</strong>
                        @if($item->seal_text)
                        <br><strong style="font-size:12px;color:#333;">{{ $item->seal_text }}</strong>
                        @endif
                        @if($item->seal_details)
                        <br><span style="font-weight:normal;color:#555;">{{ $item->seal_details }}</span>
                        @endif
                    </td>
                    <td style="padding:6px;font-size:13px;">{{ $item->frequency ?? '—' }}</td>
                    <td style="padding:6px;font-size:13px;">{{ $item->duration ?? '—' }}</td>
                    <td style="padding:6px;font-size:13px;">{{ $item->instructions ?? '—' }}</td>
                </tr>
                @endif
                @empty
                <tr>
                    <td colspan="4" style="padding:12px;text-align:center;color:#999;">No medicine prescribed at this visit.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="advice-section">
            <h3>Advice</h3>
            <div style="display:flex;flex-wrap:wrap;gap:6px;padding:8px;background:#fff;border:1px solid #71a9ce;min-height:36px;">
                @forelse($prescription->advices as $advice)
                <span style="display:inline-block;padding:2px 10px;background:#e0f0ff;border:1px solid #71a9ce;border-radius:12px;font-size:12px;">{{ $advice->name }}</span>
                @empty
                @forelse($prescription->advice as $advice)
                <span style="display:inline-block;padding:2px 10px;background:#e0f0ff;border:1px solid #71a9ce;border-radius:12px;font-size:12px;">{{ $advice->advice }}</span>
                @empty
                <span style="color:#999;font-size:13px;">No advice recorded.</span>
                @endforelse
                @endforelse
            </div>
        </div>

        <div class="advice-section" style="margin-top:10px;">
            <h3>Follow-up</h3>
            <div style="padding:8px;background:#fff;border:1px solid #71a9ce;font-size:13px;min-height:36px;">
                {{ $prescription->follow_up_instructions ?? 'No follow-up instructions.' }}
            </div>
            @if($prescription->follow_up_date)
            <div style="margin-top:6px;font-size:13px;font-weight:bold;color:#111;">
                Follow-up Date: {{ \Carbon\Carbon::parse($prescription->follow_up_date)->format('d/m/Y') }}
            </div>
            @endif
        </div>

        @if($prescription->report_received_at)
        <div style="margin-top:6px;font-size:11px;color:#3b82f6;">
            Reports received: {{ $prescription->report_received_at->format('d M Y, g:i A') }}
        </div>
        @endif
        @if($prescription->treatment_started_at)
        <div style="margin-top:2px;font-size:11px;color:#10b981;">
            Treatment started: {{ $prescription->treatment_started_at->format('d M Y, g:i A') }}
        </div>
        @endif
        @if($prescription->completed_at)
        <div style="margin-top:2px;font-size:11px;color:#6b7280;">
            Completed: {{ $prescription->completed_at->format('d M Y, g:i A') }}
        </div>
        @endif
    </div>
</div>
@endsection

@section('right-sidebar-buttons')
@if($prescription->isEditable())
    @if($prescription->status === 'investigation_pending' || $prescription->status === 'report_received')
    <a href="{{ route('doctor.prescriptions.edit', $prescription) }}">
        <button type="button" class="btn-action btn-success">Add Medicines & Start Treatment</button>
    </a>
    @else
    <a href="{{ route('doctor.prescriptions.edit', $prescription) }}">
        <button type="button" class="btn-action btn-blue">Edit Record</button>
    </a>
    @endif
    @if(in_array($prescription->status, [\App\Models\Prescription::STATUS_TREATMENT_STARTED, \App\Models\Prescription::STATUS_FOLLOW_UP]))
    <form method="POST" action="{{ route('doctor.prescriptions.status', $prescription) }}" onsubmit="return confirm('Mark this treatment as completed? This will make the prescription read-only.');" style="display:block;">
        @csrf @method('PATCH')
        <input type="hidden" name="status" value="{{\App\Models\Prescription::STATUS_COMPLETED}}">
        <input type="hidden" name="notes" value="Treatment completed">
        <button type="submit" class="btn-action" style="width:100%;background:#6b7280;color:white;border:none;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Mark Treatment Completed</button>
    </form>
    @endif
@else
    <div style="padding:10px;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);border-radius:8px;text-align:center;font-size:12px;color:#dc2626;font-weight:600;margin-bottom:8px;">
        Prescription is completed. Reopen to edit.
    </div>
    <form method="POST" action="{{ route('doctor.prescriptions.status', $prescription) }}" onsubmit="return confirm('Reopen this completed prescription?');" style="display:block;">
        @csrf @method('PATCH')
        <input type="hidden" name="status" value="{{\App\Models\Prescription::STATUS_TREATMENT_STARTED}}">
        <input type="hidden" name="notes" value="Reopened for further treatment">
        <button type="submit" style="width:100%;background:#f59e0b;color:white;border:none;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Reopen Prescription</button>
    </form>
@endif
<button type="button" class="btn-action btn-orange" onclick="if(confirm('Delete this prescription?')){document.getElementById('delete-form').submit()}">Delete Record</button>
<a href="{{ route('doctor.prescriptions.print', $prescription) }}" target="_blank">
    <button type="button" class="btn-action btn-purple">Print View</button>
</a>
<a href="{{ route('doctor.prescriptions.index') }}">
    <button type="button" class="btn-action btn-navy">Back to List</button>
</a>
<form id="delete-form" method="POST" action="{{ route('doctor.prescriptions.destroy', $prescription) }}" style="display:none">
    @csrf @method('DELETE')
</form>
@endsection

@section('record-info', $prescription->prescription_number)
