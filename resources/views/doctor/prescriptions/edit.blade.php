@extends('doctor.layouts.prescription')

@section('title', 'Edit Prescription')

@push('styles')
<style>
    .rx-card-active {
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 2px rgba(99,102,241,0.2) !important;
        background: rgba(99,102,241,0.04) !important;
    }
    .rx-card-active .preset-btn {
        background: var(--primary) !important;
        color: #fff !important;
        border-color: var(--primary) !important;
    }
    .med-autocomplete-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 9999;
        max-height: 220px;
        overflow-y: auto;
        background: rgba(255,255,255,0.97);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(148,163,184,0.2);
        border-radius: 8px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        margin-top: 2px;
    }
    .med-autocomplete-item {
        padding: 8px 12px;
        cursor: pointer;
        font-size: 12px;
        border-bottom: 1px solid rgba(148,163,184,0.08);
        transition: background 0.15s;
    }
    .med-autocomplete-item:hover,
    .med-autocomplete-item.active {
        background: rgba(99,102,241,0.08);
    }
    .med-autocomplete-item strong {
        color: #1e293b;
        font-weight: 600;
    }
    .med-autocomplete-item .med-generic {
        color: #94a3b8;
        font-size: 10px;
        margin-left: 4px;
    }
    .med-no-results {
        padding: 10px 12px;
        color: #94a3b8;
        font-size: 12px;
        text-align: center;
        font-style: italic;
    }
    .med-search-input:focus {
        background: rgba(99,102,241,0.04);
        box-shadow: inset 0 0 0 2px rgba(99,102,241,0.1);
        border-radius: 4px;
    }
    .drug-table tbody tr:hover td {
        background: rgba(99,102,241,0.03);
    }
    .seal-row td {
        background: rgba(251,191,36,0.06) !important;
        border-bottom: 1px dashed rgba(251,191,36,0.3) !important;
    }
    .seal-row td input {
        font-weight: 600;
        color: #92400e;
    }
    .seal-tag {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        background: rgba(251,191,36,0.15);
        color: #92400e;
        border: 1px solid rgba(251,191,36,0.3);
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
    }
    .seal-search-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 9999;
        max-height: 280px;
        overflow-y: auto;
        background: rgba(255,255,255,0.97);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(148,163,184,0.2);
        border-radius: 8px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        margin-top: 2px;
    }
    .seal-search-item {
        padding: 8px 12px;
        cursor: pointer;
        font-size: 12px;
        border-bottom: 1px solid rgba(148,163,184,0.08);
        transition: background 0.15s;
    }
    .seal-search-item:hover,
    .seal-search-item.active {
        background: rgba(251,191,36,0.12);
    }
    .seal-search-item .seal-item-name {
        color: #1e293b;
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .seal-search-item .seal-item-count {
        color: #94a3b8;
        font-size: 10px;
    }
    @media print {
        .med-autocomplete-dropdown { display: none !important; }
        .seal-search-dropdown { display: none !important; }
        .med-search-input { border: none !important; box-shadow: none !important; }
    }
</style>
@endpush

@section('prescription-content')
<header class="app-header">
    <div class="header-title">
        <h1>{{ strtoupper(config('app.name')) }}</h1>
        <h2>DOCTOR'S PRESCRIPTION SOFTWARE</h2>
    </div>
    <a href="{{ route('doctor.prescriptions.index') }}" class="close-btn">&times;</a>
</header>

<form method="POST" action="{{ route('doctor.prescriptions.update', $prescription) }}" id="prescription-form">
    @csrf
    @method('PUT')

    {{-- Item Search Modals --}}
    <x-item-search-modal type="complaint" title="Add Complaints" />
    <x-item-search-modal type="test" title="Add Tests" />
    <x-item-search-modal type="medical_history" title="Add Medical History" />
    <x-item-search-modal type="advice" title="Add Advice" />

    <section class="patient-info-grid">
        <div class="form-group" style="grid-column:span 2;">
            <label>Patient :</label>
            <div style="display:flex;gap:6px;align-items:center;flex:1;">
            <select name="patient_id" required class="w-full select2" id="patient-select" style="flex:1;">
                <option value="">-- Select Patient --</option>
                @foreach($patients as $patient)
                <option value="{{ $patient->id }}"
                    data-name="{{ $patient->name }}"
                    data-age="{{ $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->age : '' }}"
                    data-sex="{{ $patient->gender }}"
                    data-address="{{ $patient->address }}"
                    data-mobile="{{ $patient->phone }}"
                    data-weight="{{ $previousWeightByPatient[$patient->id] ?? '' }}"
                    data-height="{{ $previousHeightByPatient[$patient->id] ?? '' }}"
                    {{ ($prescription->patient_id == $patient->id) ? 'selected' : '' }}>
                    {{ $patient->name }}
                </option>
                @endforeach
            </select>
            <button type="button" onclick="openAddPatientModal()" class="btn-primary" style="padding:6px 14px;font-size:16px;border:none;border-radius:8px;cursor:pointer;font-weight:700;white-space:nowrap;">+</button>
            </div>
        </div>
        <div class="form-group">
            <label>Age:</label>
            <input type="text" id="patient-age" class="w-small" readonly>
        </div>
        <div class="form-group">
            <label>Sex:</label>
            <input type="text" id="patient-sex" class="w-small" readonly>
        </div>
        <div class="form-group">
            <label>Date:</label>
            <input type="text" value="{{ \Carbon\Carbon::parse($prescription->created_at)->format('d/m/Y') }}" readonly>
        </div>

        <div class="form-group">
            <label>Prescription No:</label>
            <input type="text" value="{{ $prescription->prescription_number }}" class="w-small" readonly>
        </div>
        <div class="form-group">
            <label>Time:</label>
            <input type="text" value="{{ \Carbon\Carbon::parse($prescription->created_at)->format('g:i A') }}" class="w-medium" readonly>
        </div>

        <div class="form-group span-2">
            <label>Patient's Name:</label>
            <input type="text" id="patient-name-display" class="w-full" readonly>
        </div>
        <div class="form-group span-2">
            <label>Father's /Guardian's Name:</label>
            <input type="text" name="guardian_name" id="guardian-name" class="w-full" placeholder="Father or guardian name">
        </div>

        <div class="form-group span-2">
            <label>Address:</label>
            <textarea rows="2" class="w-full" id="patient-address" readonly></textarea>
        </div>
        <div class="form-group span-2">
            <label>Mobile No:</label>
            <input type="text" class="w-full" id="patient-mobile" readonly>
        </div>

        <div class="vitals-group" style="grid-column:span 4;">
            <div class="form-group inline-vitals">
                <label>BP:</label>
                <input type="text" name="bp_systolic" value="{{ $prescription->bp_systolic }}" class="w-vitals" placeholder="120">
                <span style="color:var(--text-muted);font-size:14px;">/</span>
                <input type="text" name="bp_diastolic" value="{{ $prescription->bp_diastolic }}" class="w-vitals" placeholder="80">
                <span class="unit">mmHg</span>
            </div>
            <div class="form-group inline-vitals">
                <label>Pulse Rate:</label>
                <input type="text" name="pulse_rate" value="{{ $prescription->pulse_rate }}" class="w-vitals" placeholder="72">
                <span class="unit">/min</span>
            </div>
            <div class="form-group inline-vitals">
                <label>SpO₂:</label>
                <input type="text" name="spo2" value="{{ $prescription->spo2 }}" class="w-vitals" placeholder="98" min="0" max="100" step="0.1">
                <span class="unit">%</span>
            </div>
            <div class="form-group inline-vitals">
                <label>Ht (cm):</label>
                <input type="text" name="height" id="rx-height" value="{{ $prescription->height ?? 0 }}" class="w-vitals" placeholder="170" step="0.1" min="0" max="300">
                <span id="rx-height-prev" class="prev-val"></span>
            </div>
            <div class="form-group inline-vitals">
                <label>Wt (kg):</label>
                <input type="text" name="weight" id="rx-weight" value="{{ $prescription->weight ?? 0 }}" class="w-vitals" placeholder="70" step="0.01" min="0" max="500">
                <span id="rx-weight-prev" class="prev-val"></span>
            </div>
        </div>
    </section>

    <div class="prescription-body">
        <div class="left-pane">
            @if($featureSetting->complaints)
            <div class="glass-card pane-section" x-data="modalComplaints()" id="complaints-section">
                <h3 style="display:flex;justify-content:space-between;align-items:center;">
                    Complaints
                    <button type="button" @click="$dispatch('open-item-modal-complaint')" class="preset-btn" style="font-size:11px;padding:3px 12px;display:inline-flex;align-items:center;gap:4px;">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Add
                    </button>
                </h3>
                <div x-show="selected.length > 0" style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:6px;min-height:40px;padding:8px;background:rgba(255,255,255,0.4);border:1px solid rgba(148,163,184,0.2);border-radius:8px;">
                    <template x-for="(complaint, i) in selected" :key="complaint.id || i">
                        <div class="complain-item">
                            <div style="display:flex;align-items:center;gap:6px;">
                                <span x-text="complaint.name" style="font-weight:600;"></span>
                                <button type="button" @click="removeItem(i)" style="border:none;background:none;cursor:pointer;font-size:16px;line-height:1;color:var(--danger);padding:0 2px;">&times;</button>
                            </div>
                            <input type="text" x-model="complaint.notes" placeholder="Add notes..." style="padding:3px 6px;border:1px solid rgba(148,163,184,0.2);border-radius:4px;font-size:11px;width:140px;background:rgba(255,255,255,0.5);">
                        </div>
                    </template>
                </div>
                <div x-show="selected.length === 0" style="text-align:center;padding:12px;color:#999;font-size:12px;">
                    Click "Add Complaint" to select complaints
                </div>
            </div>
            @endif
            @if($featureSetting->tests)
            <div class="glass-card pane-section" x-data="modalTests()" id="tests-section">
                <h3 style="display:flex;justify-content:space-between;align-items:center;">
                    Tests
                    <button type="button" @click="$dispatch('open-item-modal-test')" class="preset-btn" style="font-size:11px;padding:3px 12px;display:inline-flex;align-items:center;gap:4px;">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Add
                    </button>
                </h3>
                <div x-show="selected.length > 0" style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:6px;min-height:40px;padding:8px;background:rgba(255,255,255,0.4);border:1px solid rgba(148,163,184,0.2);border-radius:8px;">
                    <template x-for="(test, i) in selected" :key="test.id || i">
                        <div class="test-item">
                            <div style="display:flex;align-items:center;gap:6px;">
                                <span x-text="test.name" style="font-weight:600;"></span>
                                <button type="button" @click="removeItem(i)" style="border:none;background:none;cursor:pointer;font-size:16px;line-height:1;color:var(--danger);padding:0 2px;">&times;</button>
                            </div>
                            <input type="text" x-model="test.result" placeholder="Result (e.g. 95 mg/dL)" style="padding:3px 6px;border:1px solid rgba(148,163,184,0.2);border-radius:4px;font-size:11px;width:160px;background:rgba(255,255,255,0.5);">
                        </div>
                    </template>
                </div>
                <div x-show="selected.length === 0" style="text-align:center;padding:12px;color:#999;font-size:12px;">
                    Click "Add Test" to select tests
                </div>
            </div>
            @endif
            @if($featureSetting->medical_history)
            <div class="glass-card pane-section" x-data="modalMedicalHistory()" id="medical-history-section">
                <h3 style="display:flex;justify-content:space-between;align-items:center;">
                    Past Medical History
                    <button type="button" @click="$dispatch('open-item-modal-medical_history')" class="preset-btn" style="font-size:11px;padding:3px 12px;display:inline-flex;align-items:center;gap:4px;">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Add
                    </button>
                </h3>
                <div x-show="selected.length > 0" style="display:flex;flex-wrap:wrap;gap:6px;min-height:36px;padding:8px;background:rgba(255,255,255,0.4);border:1px solid rgba(148,163,184,0.2);border-radius:8px;">
                    <template x-for="(item, i) in selected" :key="item.id || i">
                        <div style="display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:500;background:rgba(251,191,36,0.15);color:#92400e;border:1px solid rgba(251,191,36,0.3);">
                            <span x-text="item.name"></span>
                            <button type="button" @click="removeItem(i)" style="border:none;background:none;cursor:pointer;font-size:14px;line-height:1;color:#dc2626;padding:0;">&times;</button>
                        </div>
                    </template>
                </div>
                <div x-show="selected.length === 0" style="text-align:center;padding:12px;color:#999;font-size:12px;">
                    Click "Add History" to add medical history
                </div>
            </div>
            @endif

            {{-- Family History Section --}}
            @if($featureSetting->family_history)
            <div class="glass-card pane-section" x-data="featureFamilyHistory()">
                <h3 style="display:flex;justify-content:space-between;align-items:center;">
                    Family History
                    <button type="button" @click="openModal()" class="preset-btn" style="font-size:11px;padding:3px 12px;display:inline-flex;align-items:center;gap:4px;">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Add
                    </button>
                </h3>
                <div x-show="items.length > 0" style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:6px;min-height:40px;padding:8px;background:rgba(255,255,255,0.4);border:1px solid rgba(148,163,184,0.2);border-radius:8px;">
                    <template x-for="(item, i) in items" :key="i">
                        <div class="complain-item">
                            <div style="display:flex;align-items:center;gap:6px;">
                                <span x-text="item.name" style="font-weight:600;"></span>
                                <span x-show="item.relation" x-text="'(' + item.relation + ')'" style="font-size:10px;color:#666;"></span>
                                <button type="button" @click="removeItem(i)" style="border:none;background:none;cursor:pointer;font-size:16px;line-height:1;color:var(--danger);padding:0 2px;">&times;</button>
                            </div>
                            <input type="text" x-model="item.notes" placeholder="Notes..." style="padding:3px 6px;border:1px solid rgba(148,163,184,0.2);border-radius:4px;font-size:11px;width:120px;background:rgba(255,255,255,0.5);">
                        </div>
                    </template>
                </div>
                <div x-show="items.length === 0" style="text-align:center;padding:12px;color:#999;font-size:12px;">
                    Click "Add Family History" to add diseases
                </div>
                <input type="hidden" name="family_history_data" :value="JSON.stringify({diseases: items, notes: globalNotes})">
            </div>
            @endif

            {{-- Menstrual History Section --}}
            @if($featureSetting->menstrual_history)
            <div class="glass-card pane-section" x-data="featureMenstrualHistory()">
                <h3 style="display:flex;justify-content:space-between;align-items:center;">
                    Menstrual History
                    <button type="button" @click="openModal()" class="preset-btn" style="font-size:11px;padding:3px 12px;display:inline-flex;align-items:center;gap:4px;">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Add
                    </button>
                </h3>
                <div x-show="data.lmp || data.cycle || data.duration || data.flow || data.notes" style="padding:8px;background:rgba(255,255,255,0.4);border:1px solid rgba(148,163,184,0.2);border-radius:8px;min-height:40px;">
                    <div style="display:flex;flex-wrap:wrap;gap:6px;">
                        <span x-show="data.lmp" style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:500;background:rgba(196,181,253,0.15);color:#5b21b6;border:1px solid rgba(196,181,253,0.3);">
                            LMP: <span x-text="data.lmp"></span>
                        </span>
                        <span x-show="data.cycle" style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:500;background:rgba(196,181,253,0.15);color:#5b21b6;border:1px solid rgba(196,181,253,0.3);">
                            Cycle: <span x-text="data.cycle"></span>
                        </span>
                        <span x-show="data.duration" style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:500;background:rgba(196,181,253,0.15);color:#5b21b6;border:1px solid rgba(196,181,253,0.3);">
                            Duration: <span x-text="data.duration"></span>
                        </span>
                        <span x-show="data.flow" style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:500;background:rgba(196,181,253,0.15);color:#5b21b6;border:1px solid rgba(196,181,253,0.3);">
                            Flow: <span x-text="data.flow"></span>
                        </span>
                    </div>
                    <div x-show="data.notes" x-text="data.notes" style="margin-top:4px;font-size:11px;color:#666;"></div>
                </div>
                <div x-show="!(data.lmp || data.cycle || data.duration || data.flow || data.notes)" style="text-align:center;padding:12px;color:#999;font-size:12px;">
                    Click "Add Menstrual History" to enter details
                </div>
                <input type="hidden" name="menstrual_history_data" :value="JSON.stringify(data)">
            </div>
            @endif

            {{-- Drug History Section --}}
            @if($featureSetting->drug_history)
            <div class="glass-card pane-section" x-data="featureDrugHistory()">
                <h3 style="display:flex;justify-content:space-between;align-items:center;">
                    Drug History
                    <button type="button" @click="openModal()" class="preset-btn" style="font-size:11px;padding:3px 12px;display:inline-flex;align-items:center;gap:4px;">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Add
                    </button>
                </h3>
                <div x-show="items.length > 0" style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:6px;min-height:40px;padding:8px;background:rgba(255,255,255,0.4);border:1px solid rgba(148,163,184,0.2);border-radius:8px;">
                    <template x-for="(item, i) in items" :key="i">
                        <div class="complain-item">
                            <div style="display:flex;align-items:center;gap:6px;">
                                <span x-text="item.name" style="font-weight:600;"></span>
                                <span x-show="item.dose" x-text="item.dose" style="font-size:10px;color:#666;"></span>
                                <button type="button" @click="removeItem(i)" style="border:none;background:none;cursor:pointer;font-size:16px;line-height:1;color:var(--danger);padding:0 2px;">&times;</button>
                            </div>
                            <input type="text" x-model="item.notes" placeholder="Notes..." style="padding:3px 6px;border:1px solid rgba(148,163,184,0.2);border-radius:4px;font-size:11px;width:120px;background:rgba(255,255,255,0.5);">
                        </div>
                    </template>
                </div>
                <div x-show="items.length === 0" style="text-align:center;padding:12px;color:#999;font-size:12px;">
                    Click "Add Drug History" to add medications
                </div>
                <input type="hidden" name="drug_history_data" :value="JSON.stringify({drugs: items, notes: globalNotes})">
            </div>
            @endif

            {{-- OT Note Section --}}
            @if($featureSetting->ot_note)
            <div class="glass-card pane-section" x-data="featureOtNote()">
                <h3 style="display:flex;justify-content:space-between;align-items:center;">
                    OT Note / Procedure Done
                    <button type="button" @click="openModal()" class="preset-btn" style="font-size:11px;padding:3px 12px;display:inline-flex;align-items:center;gap:4px;">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Add
                    </button>
                </h3>
                <div x-show="data.procedure || data.date || data.notes" style="padding:8px;background:rgba(255,255,255,0.4);border:1px solid rgba(148,163,184,0.2);border-radius:8px;min-height:40px;">
                    <div style="display:flex;flex-wrap:wrap;gap:6px;">
                        <span x-show="data.procedure" style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:500;background:rgba(254,202,202,0.15);color:#991b1b;border:1px solid rgba(254,202,202,0.3);">
                            <span x-text="data.procedure"></span>
                        </span>
                        <span x-show="data.date" style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:500;background:rgba(254,202,202,0.15);color:#991b1b;border:1px solid rgba(254,202,202,0.3);">
                            Date: <span x-text="data.date"></span>
                        </span>
                    </div>
                    <div x-show="data.notes" x-text="data.notes" style="margin-top:4px;font-size:11px;color:#666;"></div>
                </div>
                <div x-show="!(data.procedure || data.date || data.notes)" style="text-align:center;padding:12px;color:#999;font-size:12px;">
                    Click "Add OT Note" to enter details
                </div>
                <input type="hidden" name="ot_note_data" :value="JSON.stringify(data)">
            </div>
            @endif

            {{-- Anesthesia Section --}}
            @if($featureSetting->anesthesia)
            <div class="glass-card pane-section" x-data="featureAnesthesia()">
                <h3 style="display:flex;justify-content:space-between;align-items:center;">
                    Anesthesia (LA / Surface)
                    <button type="button" @click="openModal()" class="preset-btn" style="font-size:11px;padding:3px 12px;display:inline-flex;align-items:center;gap:4px;">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Add
                    </button>
                </h3>
                <div x-show="data.type || data.agent || data.dose || data.notes" style="padding:8px;background:rgba(255,255,255,0.4);border:1px solid rgba(148,163,184,0.2);border-radius:8px;min-height:40px;">
                    <div style="display:flex;flex-wrap:wrap;gap:6px;">
                        <span x-show="data.type" style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:500;background:rgba(249,168,212,0.15);color:#9d174d;border:1px solid rgba(249,168,212,0.3);">
                            Type: <span x-text="data.type"></span>
                        </span>
                        <span x-show="data.agent" style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:500;background:rgba(249,168,212,0.15);color:#9d174d;border:1px solid rgba(249,168,212,0.3);">
                            Agent: <span x-text="data.agent"></span>
                        </span>
                        <span x-show="data.dose" style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:500;background:rgba(249,168,212,0.15);color:#9d174d;border:1px solid rgba(249,168,212,0.3);">
                            Dose: <span x-text="data.dose"></span>
                        </span>
                    </div>
                    <div x-show="data.notes" x-text="data.notes" style="margin-top:4px;font-size:11px;color:#666;"></div>
                </div>
                <div x-show="!(data.type || data.agent || data.dose || data.notes)" style="text-align:center;padding:12px;color:#999;font-size:12px;">
                    Click "Add Anesthesia" to enter details
                </div>
                <input type="hidden" name="anesthesia_data" :value="JSON.stringify(data)">
            </div>
            @endif

            <div class="glass-card pane-section">
                <h3>D D</h3>
                <textarea name="diagnosis" rows="3" class="w-full" style="padding:8px;border:1px solid rgba(148,163,184,0.3);border-radius:8px;background:rgba(255,255,255,0.6);font-size:13px;outline:none;width:100%;resize:vertical;">{{ $prescription->diagnosis }}</textarea>
            </div>
        </div>

        <div style="display:flex;flex-direction:column;" x-data="prescriptionItems()">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                <span style="font-size:12px;font-weight:700;color:var(--text-primary);display:flex;align-items:center;gap:6px;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Medicines &amp; Clinical Seals
                </span>
                <button type="button" @click="openSealModal()" class="preset-btn" style="font-size:11px;padding:3px 12px;display:inline-flex;align-items:center;gap:4px;background:rgba(251,191,36,0.15);color:#92400e;border:1px solid rgba(251,191,36,0.4);border-radius:6px;cursor:pointer;">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Clinical Seal
                </button>
            </div>
            <table class="drug-table">
                <thead>
                    <tr>
                        <th style="width:35%">Drug / Seal</th>
                        <th style="width:15%">Frequency</th>
                        <th style="width:10%">Day</th>
                        <th style="width:30%">Remarks</th>
                        <th style="width:10%"></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(item, index) in items" :key="index">
                        <tr :class="item.type === 'seal' ? 'seal-row' : ''">
                            <template x-if="item.type === 'medicine'">
                                <td style="position:relative;" @click.outside="medClose()">
                                    <input type="text"
                                           x-model="item.medicine_name"
                                           :name="'items[' + item.sort_order + '][medicine_name]'"
                                           @input.debounce.300ms="medSearch(index)"
                                           @focus="medOpen = index; medSearch(index)"
                                           @keydown.escape="medClose()"
                                           @keydown.arrow-down.prevent="medHighlightNext()"
                                           @keydown.arrow-up.prevent="medHighlightPrev()"
                                           @keydown.enter.prevent="medSelectHighlighted(index)"
                                           placeholder="Type drug name..."
                                           class="med-search-input"
                                           autocomplete="off">
                                    <input type="hidden" :name="'items[' + item.sort_order + '][medicine_id]'" x-model="item.medicine_id">
                                    <input type="hidden" :name="'items[' + item.sort_order + '][strength]'" x-model="item.strength">
                                    <input type="hidden" :name="'items[' + item.sort_order + '][seal_id]'" x-model="item.seal_id">
                                    <input type="hidden" :name="'items[' + item.sort_order + '][seal_text]'" x-model="item.seal_text">
                                    <input type="hidden" :name="'items[' + item.sort_order + '][seal_details]'" x-model="item.seal_details">
                                    <div x-show="item.seal_text" style="margin-top:3px;padding:3px 6px;background:rgba(251,191,36,0.08);border:1px dashed rgba(251,191,36,0.3);border-radius:4px;font-size:11px;color:#92400e;">
                                        <div style="display:flex;align-items:center;gap:4px;">
                                            <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <span x-text="item.seal_text" style="font-weight:600;"></span>
                                            <button type="button" @click="removeSealFromMedicine(index)" style="border:none;background:none;cursor:pointer;font-size:12px;color:#dc2626;padding:0 2px;line-height:1;">&times;</button>
                                        </div>
                                        <div x-show="item.seal_details" x-text="item.seal_details" style="font-weight:normal;color:#666;margin-top:2px;font-size:10px;"></div>
                                    </div>
                                    <div x-show="medOpen === index && medResults.length > 0"
                                         x-transition
                                         class="med-autocomplete-dropdown">
                                        <template x-for="(result, i) in medResults" :key="result.id">
                                            <div class="med-autocomplete-item"
                                                 :class="{ 'active': i === medHighlighted }"
                                                 @click="medSelect(index, result)"
                                                 @mouseenter="medHighlighted = i">
                                                <strong x-text="result.brand_name + (result.strength && !result.brand_name.includes(result.strength) ? ' ' + result.strength : '')"></strong>
                                                <span class="med-generic" x-text="result.generic_name ? '(' + result.generic_name + ')' : ''"></span>
                                            </div>
                                        </template>
                                        <div x-show="medResults.length === 0 && !medLoading && (item.medicine_name || '').length >= 2" class="med-no-results">
                                            <div style="margin-bottom:6px;">No medicines found</div>
                                            <button type="button" @click="quickSuggestMedicine(index)" style="padding:5px 12px;font-size:11px;font-weight:600;border:none;border-radius:6px;cursor:pointer;background:#059669;color:white;display:inline-flex;align-items:center;gap:4px;transition:background 0.2s;">
                                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                                Suggest this medicine
                                            </button>
                                            <div x-show="item._suggestStatus" x-text="item._suggestStatus" style="margin-top:4px;font-size:10px;color:#059669;"></div>
                                        </div>
                                    </div>
                                </td>
                            </template>
                            <template x-if="item.type === 'medicine'">
                                <td>
                                    <input type="text"
                                           x-model="item.frequency"
                                           :name="'items[' + item.sort_order + '][frequency]'"
                                           list="frequency-options"
                                           placeholder="e.g. 1+0+1"
                                           @focus="$el.select()"
                                           style="width:100%;border:none;padding:8px 10px;font-size:12px;outline:none;background:transparent;text-align:center;">
                                </td>
                            </template>
                            <template x-if="item.type === 'medicine'">
                                <td>
                                    <input type="text" x-model="item.duration" :name="'items[' + item.sort_order + '][duration]'" placeholder="Days" style="text-align:center">
                                </td>
                            </template>
                            <template x-if="item.type === 'medicine'">
                                <td>
                                    <input type="text" x-model="item.instructions" :name="'items[' + item.sort_order + '][instructions]'" placeholder="Remarks / notes">
                                    <div class="quick-remark-btns" style="margin-top:4px;display:flex;gap:4px;flex-wrap:wrap;">
                                        <button type="button" @click="item.instructions = 'খাবার আগে'" class="preset-btn">খাবার আগে</button>
                                        <button type="button" @click="item.instructions = 'খাবার পরে'" class="preset-btn">খাবার পরে</button>
                                    </div>
                                </td>
                            </template>
                            <template x-if="item.type === 'medicine'">
                                <td style="text-align:center">
                                    <div style="display:flex;gap:4px;justify-content:center;align-items:center;">
                                        <button type="button" @click="openSealForMedicine(index)" title="Add Clinical Seal" style="border:none;background:rgba(251,191,36,0.15);border-radius:4px;padding:3px 5px;cursor:pointer;color:#92400e;display:inline-flex;align-items:center;">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </button>
                                        <button type="button" class="remove-row" x-show="items.length > 1" @click="removeItem(index)">&times;</button>
                                    </div>
                                </td>
                            </template>

                            <template x-if="item.type === 'seal'">
                                <td colspan="4" style="position:relative;padding:6px 10px;">
                                    <div style="display:flex;align-items:flex-start;gap:8px;">
                                        <span class="seal-tag" style="margin-top:4px;">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            SEAL
                                        </span>
                                        <div style="flex:1;display:flex;flex-direction:column;gap:4px;">
                                            <input type="text"
                                                   x-model="item.seal_text"
                                                   :name="'items[' + item.sort_order + '][seal_text]'"
                                                   style="width:100%;border:1px solid rgba(251,191,36,0.3);border-radius:4px;padding:4px 8px;font-size:12px;font-weight:600;color:#92400e;background:rgba(255,255,255,0.7);outline:none;"
                                                   placeholder="Seal Name / Title">
                                            <input type="text"
                                                   x-model="item.seal_details"
                                                   :name="'items[' + item.sort_order + '][seal_details]'"
                                                   style="width:100%;border:1px solid rgba(251,191,36,0.2);border-radius:4px;padding:3px 8px;font-size:11px;color:#666;background:rgba(255,255,255,0.5);outline:none;"
                                                   placeholder="Seal Details / Instructions (optional)">
                                        </div>
                                        <input type="text"
                                               x-model="item.duration"
                                               :name="'items[' + item.sort_order + '][duration]'"
                                               placeholder="Days"
                                               style="width:60px;border:1px solid rgba(251,191,36,0.3);border-radius:4px;padding:4px 8px;font-size:12px;color:#92400e;background:rgba(255,255,255,0.7);outline:none;text-align:center;margin-top:4px;">
                                        <button type="button" @click="editSealInline(index)" title="Edit seal" style="border:none;background:rgba(251,191,36,0.2);border-radius:4px;padding:4px 6px;cursor:pointer;color:#92400e;margin-top:4px;">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </template>
                            <template x-if="item.type === 'seal'">
                                <td style="text-align:center">
                                    <button type="button" class="remove-row" @click="removeItem(index)" style="color:#dc2626;">&times;</button>
                                </td>
                            </template>
                        </tr>
                    </template>
                </tbody>
            </table>

            <div class="submit-bar">
                <button type="button" class="add-row-btn" @click="addItem()">+ Add Medicine</button>
                <button type="button" @click="openSealModal()" style="padding:6px 14px;font-size:12px;font-weight:600;border:1px dashed rgba(251,191,36,0.5);border-radius:8px;cursor:pointer;background:rgba(251,191,36,0.1);color:#92400e;display:inline-flex;align-items:center;gap:4px;">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Clinical Seal
                </button>
                <button type="submit" class="btn-primary" style="padding:8px 20px;font-size:13px;font-weight:600;border:none;border-radius:10px;cursor:pointer;">Save Changes</button>
                <a href="{{ route('doctor.prescriptions.index') }}"><button type="button" class="btn-secondary" style="padding:8px 20px;font-size:13px;font-weight:600;border-radius:10px;cursor:pointer;">Cancel</button></a>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px;">
                @if($featureSetting->advice)
                <div class="glass-card advice-section" x-data="modalAdvice()">
                    <h3 style="display:flex;justify-content:space-between;align-items:center;">
                        Advice
                        <button type="button" @click="$dispatch('open-item-modal-advice')" class="preset-btn" style="font-size:11px;padding:3px 12px;display:inline-flex;align-items:center;gap:4px;">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            Add Advice
                        </button>
                    </h3>
                    <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:6px;min-height:36px;padding:8px;background:rgba(255,255,255,0.4);border:1px solid rgba(148,163,184,0.2);border-radius:8px;">
                        <template x-for="(tag, i) in selected" :key="tag.id || i">
                            <span class="tag-pill">
                                <span x-text="tag.name"></span>
                                <button type="button" @click="removeItem(i)" class="remove-tag">&times;</button>
                            </span>
                        </template>
                        <div x-show="selected.length === 0" style="text-align:center;padding:4px;width:100%;color:#999;font-size:12px;">
                            Click "Add Advice" to select advice
                        </div>
                    </div>
                </div>
                @endif
                <div class="glass-card advice-section">
                    <h3>Follow-up</h3>
                    <textarea name="follow_up_instructions" rows="2" class="w-full" style="padding:8px;border:1px solid rgba(148,163,184,0.3);border-radius:8px;background:rgba(255,255,255,0.6);font-size:13px;outline:none;transition:all 0.2s;">{{ $prescription->follow_up_instructions }}</textarea>
                    <div style="margin-top:8px;display:flex;gap:12px;align-items:center;">
                        <label style="font-size:13px;font-weight:600;color:var(--text-primary);">Follow-up Date:</label>
                        <input type="date" name="follow_up_date" value="{{ $prescription->follow_up_date?->format('Y-m-d') }}" style="padding:6px 12px;border:1px solid rgba(148,163,184,0.3);border-radius:8px;background:rgba(255,255,255,0.6);font-size:13px;outline:none;transition:all 0.2s;">
                    </div>
                </div>
            </div>

            <input type="hidden" name="notes" id="notes-hidden">
            <input type="hidden" name="complaints_json" id="complaints-json">
            <input type="hidden" name="tests_json" id="tests-json">
            <input type="hidden" name="test_reports_json" id="test-reports-json">
            <input type="hidden" name="test_results_json" id="test-results-json">
            <input type="hidden" name="advice_json" id="advice-json">
            <input type="hidden" name="medical_histories_json" id="medical-histories-json">
            <input type="hidden" name="seals_json" id="seals-json">
        </div>
    </div>
</form>

<datalist id="frequency-options">
    <option value="1+0+0">
    <option value="0+0+1">
    <option value="1+0+1">
    <option value="0+1+0">
    <option value="1+1+0">
    <option value="0+1+1">
    <option value="1+1+1">
    <option value="1+1+2">
    <option value="2+2+2">
    <option value="SOS">
    <option value="Once daily">
    <option value="Twice daily">
    <option value="Thrice daily">
    <option value="Before Meal">
    <option value="After Meal">
    <option value="At bedtime">
    <option value="In the morning">
    <option value="Weekly">
</datalist>

@endsection

{{-- Clinical Seal Search Modal --}}
<div id="seal-search-modal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;z-index:3000;align-items:center;justify-content:center;background:rgba(0,0,0,0.3);backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px);">
    <div style="background:rgba(255,255,255,0.75);backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);border-radius:16px;padding:28px;width:520px;max-width:95vw;max-height:85vh;overflow-y:auto;border:1px solid rgba(255,255,255,0.5);box-shadow:0 25px 60px rgba(0,0,0,0.15);animation:fadeInUp 0.3s ease-out;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h3 style="font-size:18px;font-weight:700;color:var(--text-primary);display:flex;align-items:center;gap:8px;">
                <div style="width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:rgba(251,191,36,0.15);color:#92400e;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                Select Clinical Seal
            </h3>
            <button type="button" onclick="closeSealModal()" style="background:rgba(148,163,184,0.15);border:none;font-size:24px;cursor:pointer;color:var(--text-muted);padding:4px 10px;border-radius:8px;">&times;</button>
        </div>
        <div style="position:relative;margin-bottom:12px;">
            <svg style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#9ca3af;" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" id="seal-search-input" placeholder="Search seals or type new seal text..." autocomplete="off" oninput="searchSeals()" onkeydown="if(event.key==='Enter'){event.preventDefault();sealSearchEnter();}" style="width:100%;padding:10px 12px 10px 38px;background:#f3f4f6;border:1px solid #e5e7eb;border-radius:10px;font-size:14px;outline:none;transition:border-color 0.15s;">
        </div>
        <div id="seal-search-results" style="max-height:400px;overflow-y:auto;"></div>
        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:16px;border-top:1px solid rgba(148,163,184,0.2);padding-top:12px;">
            <button type="button" onclick="closeSealModal()" class="btn-secondary" style="padding:8px 16px;font-size:13px;font-weight:600;border:none;border-radius:8px;cursor:pointer;">Cancel</button>
            <button type="button" id="seal-create-btn" onclick="createSealFromSearch()" style="padding:8px 16px;font-size:13px;font-weight:600;border:none;border-radius:8px;cursor:pointer;background:#059669;color:white;display:none;">Create &amp; Insert</button>
        </div>
    </div>
</div>

@section('right-sidebar-buttons')
<button type="button" class="btn-action btn-primary" onclick="submitForm()">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
    Save
</button>
<form id="delete-prescription-form" method="POST" action="{{ route('doctor.prescriptions.destroy', $prescription) }}" style="width:100%;">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn-action btn-warning" onclick="return confirm('Delete this prescription?')">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
        Delete
    </button>
</form>
<button type="button" class="btn-action btn-primary" onclick="window.location.href='{{ route('doctor.prescriptions.show', $prescription) }}'">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    Close
</button>
<button type="button" class="btn-action btn-primary" onclick="window.location.href='{{ route('doctor.prescriptions.index') }}'">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
    All Prescriptions
</button>
@endsection

{{-- Add Patient Modal --}}
<div id="add-patient-modal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;z-index:2000;align-items:center;justify-content:center;background:rgba(0,0,0,0.3);backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px);">
    <div style="background:rgba(255,255,255,0.75);backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);border-radius:16px;padding:28px;width:440px;max-width:90vw;border:1px solid rgba(255,255,255,0.5);box-shadow:0 25px 60px rgba(0,0,0,0.15);animation:fadeInUp 0.3s ease-out;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h3 style="font-size:18px;font-weight:700;color:var(--text-primary);">Add New Patient</h3>
            <button type="button" onclick="closeAddPatientModal()" style="background:rgba(148,163,184,0.15);border:none;font-size:20px;cursor:pointer;color:var(--text-muted);padding:4px 10px;border-radius:8px;transition:all 0.2s;">&times;</button>
        </div>
        <form id="quick-patient-form" onsubmit="return quickAddPatient(event)">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <div style="grid-column:span 2;">
                    <label style="font-size:13px;font-weight:600;color:var(--text-primary);margin-bottom:4px;display:block;">Full Name *</label>
                    <input type="text" name="name" required style="width:100%;padding:10px 12px;border:1px solid rgba(148,163,184,0.3);border-radius:8px;font-size:13px;background:rgba(255,255,255,0.6);outline:none;transition:all 0.2s;">
                </div>
                <div>
                    <label style="font-size:13px;font-weight:600;color:var(--text-primary);margin-bottom:4px;display:block;">Phone</label>
                    <input type="text" name="phone" style="width:100%;padding:10px 12px;border:1px solid rgba(148,163,184,0.3);border-radius:8px;font-size:13px;background:rgba(255,255,255,0.6);outline:none;transition:all 0.2s;">
                </div>
                <div>
                    <label style="font-size:13px;font-weight:600;color:var(--text-primary);margin-bottom:4px;display:block;">Gender</label>
                    <select name="gender" style="width:100%;padding:10px 12px;border:1px solid rgba(148,163,184,0.3);border-radius:8px;font-size:13px;background:rgba(255,255,255,0.6);outline:none;">
                        <option value="">Select</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:13px;font-weight:600;color:var(--text-primary);margin-bottom:4px;display:block;">Age (yrs)</label>
                    <input type="number" name="age" id="quick-age" min="0" max="150" style="width:100%;padding:10px 12px;border:1px solid rgba(148,163,184,0.3);border-radius:8px;font-size:13px;background:rgba(255,255,255,0.6);outline:none;">
                </div>
                <div style="grid-column:span 2;">
                    <label style="font-size:13px;font-weight:600;color:var(--text-primary);margin-bottom:4px;display:block;">Address</label>
                    <input type="text" name="address" style="width:100%;padding:10px 12px;border:1px solid rgba(148,163,184,0.3);border-radius:8px;font-size:13px;background:rgba(255,255,255,0.6);outline:none;">
                </div>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;">
                <button type="button" onclick="closeAddPatientModal()" class="btn-secondary" style="padding:10px 20px;font-size:13px;font-weight:600;border:none;border-radius:8px;cursor:pointer;">Cancel</button>
                <button type="submit" class="btn-primary" style="padding:10px 20px;font-size:13px;font-weight:600;border:none;border-radius:8px;cursor:pointer;">Save Patient</button>
            </div>
        </form>
        <div id="quick-patient-error" style="color:#dc2626;font-size:12px;margin-top:8px;display:none;"></div>
    </div>
</div>

@push('scripts')
@php
    $editPrescriptionData = [
        'items' => $prescription->items->map(fn($i) => $i->isSeal() ? [
            'type' => 'seal',
            'seal_id' => $i->seal_id,
            'seal_text' => $i->seal_text,
            'seal_details' => $i->seal_details,
            'duration' => $i->duration,
            'sort_order' => (int) $i->sort_order,
        ] : [
            'type' => 'medicine',
            'medicine_id' => $i->medicine_id,
            'medicine_name' => $i->medicine_name,
            'strength' => $i->dosage,
            'frequency' => $i->frequency,
            'duration' => $i->duration,
            'instructions' => $i->instructions,
            'seal_id' => $i->seal_id,
            'seal_text' => $i->seal_text,
            'seal_details' => $i->seal_details,
            'sort_order' => (int) $i->sort_order,
        ])->values(),
        'complaints' => $prescription->complaints->map(fn($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'notes' => $c->pivot?->notes ?? '',
        ])->values(),
        'tests' => $prescription->tests->map(fn($t) => [
            'id' => $t->laboratory_test_id,
            'name' => $t->test_name,
            'result' => $prescription->testReportResults->firstWhere('test_name', $t->test_name)?->result ?? '',
        ])->values(),
        'medicalHistories' => $prescription->patient->medicalHistories->map(fn($m) => [
            'id' => $m->medical_history_condition_id ?? $m->id,
            'name' => $m->condition_name,
        ])->values(),
        'advice' => $prescription->advices->map(fn($a) => ['id' => $a->id, 'name' => $a->name])->values(),
        'familyHistory' => $prescription->family_history_data,
        'menstrualHistory' => $prescription->menstrual_history_data,
        'drugHistory' => $prescription->drug_history_data,
        'otNote' => $prescription->ot_note_data,
        'anesthesia' => $prescription->anesthesia_data,
        'notes' => $prescription->notes,
    ];
@endphp
<script>
    window.editPrescriptionData = @json($editPrescriptionData);

    function openAddPatientModal() {
        document.getElementById('add-patient-modal').style.display = 'flex';
        document.getElementById('quick-patient-error').style.display = 'none';
    }

    function closeAddPatientModal() {
        document.getElementById('add-patient-modal').style.display = 'none';
        document.getElementById('quick-patient-form').reset();
    }

    function quickAddPatient(event) {
        event.preventDefault();
        const form = document.getElementById('quick-patient-form');
        const data = new FormData(form);
        const errEl = document.getElementById('quick-patient-error');
        errEl.style.display = 'none';

        fetch('{{ route("doctor.patients.quick-add") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: data,
        })
        .then(r => r.json())
        .then(res => {
            if (!res.success) {
                errEl.textContent = 'Failed to create patient.';
                errEl.style.display = 'block';
                return;
            }
            const p = res.patient;
            const sel = document.getElementById('patient-select');
            const opt = document.createElement('option');
            opt.value = p.id;
            opt.dataset.name = p.name;
            opt.dataset.age = p.age || '';
            opt.dataset.sex = p.gender || '';
            opt.dataset.address = p.address || '';
            opt.dataset.mobile = p.phone || '';
            opt.dataset.weight = '';
            opt.dataset.height = '';
            opt.textContent = p.name;
            sel.appendChild(opt);
            sel.value = p.id;
            $('#patient-select').select2('destroy');
            $('#patient-select').select2({
                placeholder: '-- Select Patient --',
                allowClear: true,
                width: '100%',
                templateResult: function(opt) {
                    if (!opt.id) return opt.text;
                    const mobile = $(opt.element).data('mobile');
                    return $('<span>' + opt.text + (mobile ? ' <span style="color:var(--text-muted);font-size:0.85em;">' + mobile + '</span>' : '') + '</span>');
                },
                templateSelection: function(opt) {
                    return opt.text || opt.id;
                },
                matcher: function(params, data) {
                    if (!params.term) return data;
                    const q = params.term.toLowerCase();
                    const mobile = $(data.element).data('mobile') || '';
                    if (data.text.toLowerCase().includes(q) || mobile.toLowerCase().includes(q)) {
                        return data;
                    }
                    return null;
                },
            }).on('change', populatePatientData);
            populatePatientData();
            closeAddPatientModal();
        })
        .catch(() => {
            errEl.textContent = 'Network error. Please try again.';
            errEl.style.display = 'block';
        });

        return false;
    }

    function prescriptionItems() {
        const editData = window.editPrescriptionData || {};
        const seedItems = (editData.items && editData.items.length)
            ? editData.items.map(function(i, idx) {
                return {
                    type: i.type || 'medicine',
                    medicine_name: i.medicine_name || '',
                    medicine_id: i.medicine_id || '',
                    strength: i.strength || '',
                    frequency: i.frequency || '',
                    duration: i.duration || '',
                    instructions: i.instructions || '',
                    sort_order: (i.sort_order !== undefined ? i.sort_order : idx),
                    seal_id: i.seal_id || null,
                    seal_text: i.seal_text || '',
                    seal_details: i.seal_details || '',
                };
            })
            : [{ type: 'medicine', medicine_name: '', medicine_id: '', strength: '', frequency: '', duration: '', instructions: '', sort_order: 0, seal_id: null, seal_text: '', seal_details: '' }];

        return {
            items: seedItems,
            nextSortOrder: seedItems.reduce(function(m, i) { return Math.max(m, (i.sort_order !== undefined ? i.sort_order : 0)); }, -1) + 1,
            // Medicine autocomplete state per row
            medOpen: -1,
            medHighlighted: -1,
            medResults: [],
            medLoading: false,
            medQuery: '',
            targetMedicineForSeal: -1,
            addItem() {
                this.items.push({ type: 'medicine', medicine_name: '', medicine_id: '', strength: '', frequency: '', duration: '', instructions: '', sort_order: this.nextSortOrder++, seal_id: null, seal_text: '', seal_details: '' });
            },
            addSeal(sealId, sealName, sealDetails) {
                const pos = this.nextSortOrder++;
                this.items.push({ type: 'seal', seal_id: sealId || null, seal_text: sealName, seal_details: sealDetails || '', duration: '', sort_order: pos });
            },
            getUsedSealIds() {
                const ids = new Set();
                this.items.forEach(item => {
                    if (item.seal_id) ids.add(item.seal_id);
                });
                return ids;
            },
            isSealUsed(sealId) {
                return this.getUsedSealIds().has(sealId);
            },
            removeItem(index) {
                if (this.items.length > 1) this.items.splice(index, 1);
            },
            async medSearch(index) {
                const q = (this.items[index].medicine_name || '').trim();
                if (q.length < 2) { this.medResults = []; return; }
                this.medLoading = true;
                this.medOpen = index;
                try {
                    const url = '{{ route("doctor.medicines.search") }}?q=' + encodeURIComponent(q);
                    const res = await fetch(url, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    this.medResults = await res.json();
                    this.medHighlighted = this.medResults.length > 0 ? 0 : -1;
                } catch (e) {
                    this.medResults = [];
                }
                this.medLoading = false;
            },
            medSelect(index, med) {
                this.items[index].medicine_name = med.brand_name + (med.strength && !med.brand_name.includes(med.strength) ? ' ' + med.strength : '');
                this.items[index].medicine_id = med.id;
                this.items[index].strength = med.strength;
                this.medOpen = -1;
                this.medResults = [];
                this.medHighlighted = -1;
            },
            medClose() { this.medOpen = -1; this.medResults = []; this.medHighlighted = -1; },
            medHighlightNext() { if (this.medHighlighted < this.medResults.length - 1) this.medHighlighted++; },
            medHighlightPrev() { if (this.medHighlighted > 0) this.medHighlighted--; },
            medSelectHighlighted(index) {
                if (this.medHighlighted >= 0 && this.medHighlighted < this.medResults.length) {
                    this.medSelect(index, this.medResults[this.medHighlighted]);
                }
            },
            async quickSuggestMedicine(index) {
                const name = (this.items[index].medicine_name || '').trim();
                if (!name) return;

                this.items[index]._suggestStatus = 'Submitting...';

                try {
                    const res = await fetch('{{ route("doctor.medicines.quickSuggest") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ name: name, strength: this.items[index].strength || '' }),
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.items[index]._suggestStatus = data.status === 'pending' ? 'Suggested (pending admin review)' : data.message;
                        this.items[index]._suggestStatusColor = '#059669';
                    } else {
                        this.items[index]._suggestStatus = data.message;
                        this.items[index]._suggestStatusColor = '#dc2626';
                    }
                } catch (e) {
                    this.items[index]._suggestStatus = 'Failed to suggest. Please try again.';
                    this.items[index]._suggestStatusColor = '#dc2626';
                }
            },
            openSealModal() {
                window.openSealSearchModal();
            },
            editSealInline(index) {
                window.openSealSearchModal(this.items[index]);
            },
            openSealForMedicine(index) {
                this.targetMedicineForSeal = index;
                window.openSealSearchModalForMedicine();
            },
            removeSealFromMedicine(index) {
                this.items[index].seal_id = null;
                this.items[index].seal_text = '';
                this.items[index].seal_details = '';
            },
            serializeSeals() {
                return this.items
                    .filter(i => i.type === 'seal')
                    .map(i => ({ seal_id: i.seal_id, seal_text: i.seal_text, seal_details: i.seal_details, duration: i.duration, position: i.sort_order }));
            },
        };
    }

    function adviceTags() {
        return {
            tags: [],
            newTag: '',
            addTag() {
                const t = this.newTag.trim();
                if (t && !this.tags.includes(t)) {
                    this.tags.push(t);
                }
                this.newTag = '';
            },
            removeTag(i) {
                this.tags.splice(i, 1);
            },
            addPreset(text) {
                if (!this.tags.includes(text)) {
                    this.tags.push(text);
                }
            }
        };
    }

    function modalComplaints() {
        const editData = window.editPrescriptionData || {};
        return {
            selected: (editData.complaints || []).map(function(c) { return { id: c.id, name: c.name, notes: c.notes || '' }; }),
            init() {
                window.addEventListener('items-confirmed-complaint', (e) => {
                    const items = e.detail.items || [];
                    items.forEach(item => {
                        if (!this.selected.find(c => c.id === item.id)) {
                            this.selected.push({ id: item.id, name: item.name, notes: '' });
                        }
                    });
                });
            },
            removeItem(i) {
                this.selected.splice(i, 1);
            }
        };
    }

    function modalTests() {
        const editData = window.editPrescriptionData || {};
        return {
            selected: (editData.tests || []).map(function(t) { return { id: t.id, name: t.name, result: t.result || '' }; }),
            init() {
                window.addEventListener('items-confirmed-test', (e) => {
                    const items = e.detail.items || [];
                    items.forEach(item => {
                        if (!this.selected.find(t => t.id === item.id)) {
                            this.selected.push({ id: item.id, name: item.name, result: '' });
                        }
                    });
                });
            },
            removeItem(i) {
                this.selected.splice(i, 1);
            }
        };
    }

    function modalMedicalHistory() {
        const editData = window.editPrescriptionData || {};
        return {
            selected: (editData.medicalHistories || []).map(function(m) { return { id: m.id, name: m.name }; }),
            init() {
                window.addEventListener('items-confirmed-medical_history', (e) => {
                    const items = e.detail.items || [];
                    items.forEach(item => {
                        if (!this.selected.find(m => m.id === item.id)) {
                            this.selected.push({ id: item.id, name: item.name });
                        }
                    });
                });
            },
            removeItem(i) {
                this.selected.splice(i, 1);
            }
        };
    }

    function modalAdvice() {
        const editData = window.editPrescriptionData || {};
        return {
            selected: (editData.advice || []).map(function(a) { return { id: a.id, name: a.name }; }),
            init() {
                window.addEventListener('items-confirmed-advice', (e) => {
                    const items = e.detail.items || [];
                    items.forEach(item => {
                        if (!this.selected.find(a => a.id === item.id)) {
                            this.selected.push({ id: item.id, name: item.name });
                        }
                    });
                });
            },
            removeItem(i) {
                this.selected.splice(i, 1);
            }
        };
    }

    // Feature Modal Functions
    function createFeatureModal(type, title, icon, fields, color) {
        if (document.getElementById('feature-modal-backdrop-' + type)) return;

        var backdrop = document.createElement('div');
        backdrop.id = 'feature-modal-backdrop-' + type;
        backdrop.style.cssText = 'position:fixed!important;top:0!important;left:0!important;right:0!important;bottom:0!important;z-index:999999!important;background:rgba(0,0,0,0.5)!important;backdrop-filter:blur(4px)!important;display:none;transition:opacity 0.2s ease;';
        backdrop.onclick = function() { closeFeatureModal(type); };

        var container = document.createElement('div');
        container.id = 'feature-modal-box-' + type;
        container.style.cssText = 'position:fixed!important;top:50%!important;left:50%!important;transform:translate(-50%,-50%)!important;z-index:1000000!important;width:100%!important;max-width:28rem!important;display:none;';

        var fieldsHtml = '';
        fields.forEach(function(f) {
            if (f.type === 'textarea') {
                fieldsHtml += '<div><label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">' + f.label + '</label>'
                    + '<textarea id="ff-' + type + '-' + f.key + '" placeholder="' + (f.placeholder || '') + '" rows="2" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;outline:none;resize:vertical;box-sizing:border-box;"></textarea></div>';
            } else {
                fieldsHtml += '<div><label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">' + f.label + '</label>'
                    + '<input type="text" id="ff-' + type + '-' + f.key + '" placeholder="' + (f.placeholder || '') + '" style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box;"></div>';
            }
        });

        container.innerHTML = ''
            + '<div style="background:white;border-radius:16px;box-shadow:0 25px 60px rgba(0,0,0,0.25);overflow:hidden;display:flex;flex-direction:column;">'
            + '<div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #e5e7eb;background:#f9fafb;">'
            +   '<div style="display:flex;align-items:center;gap:10px;">'
            +     '<div style="width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:' + color.bg + ';color:' + color.text + ';">' + icon + '</div>'
            +     '<h3 style="font-size:15px;font-weight:700;color:#111827;margin:0;">' + title + '</h3>'
            +   '</div>'
            +   '<button onclick="closeFeatureModal(\'' + type + '\')" style="width:32px;height:32px;border-radius:8px;border:none;background:#f3f4f6;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#6b7280;">'
            +     '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>'
            +   '</button>'
            + '</div>'
            + '<div style="padding:20px;display:flex;flex-direction:column;gap:12px;">'
            +   fieldsHtml
            + '</div>'
            + '<div style="padding:12px 20px;border-top:1px solid #e5e7eb;background:#f9fafb;display:flex;flex-direction:column;gap:8px;">'
            +   '<div id="feature-modal-error-' + type + '" style="display:none;color:#dc2626;font-size:12px;font-weight:500;line-height:1.4;"></div>'
            +   '<div style="display:flex;justify-content:flex-end;">'
            +     '<button id="feature-modal-submit-' + type + '" onclick="confirmFeatureModal(\'' + type + '\')" style="padding:8px 20px;background:#4f46e5;color:white;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Add</button>'
            +   '</div>'
            + '</div>'
            + '</div>';

        document.body.appendChild(backdrop);
        document.body.appendChild(container);
    }

    function openFeatureModal(type) {
        var backdrop = document.getElementById('feature-modal-backdrop-' + type);
        var box = document.getElementById('feature-modal-box-' + type);
        if (backdrop) backdrop.style.display = 'block';
        if (box) box.style.display = 'block';
        document.body.style.overflow = 'hidden';
        var errEl = document.getElementById('feature-modal-error-' + type);
        if (errEl) { errEl.textContent = ''; errEl.style.display = 'none'; }
        var cfg = featureModalConfigs[type];
        if (cfg && cfg.fields) clearFeatureModalFields(type, cfg.fields);
        var firstInput = box.querySelector('input, textarea');
        if (firstInput) setTimeout(function() { firstInput.focus(); }, 100);
    }

    function closeFeatureModal(type) {
        var backdrop = document.getElementById('feature-modal-backdrop-' + type);
        var box = document.getElementById('feature-modal-box-' + type);
        if (backdrop) backdrop.style.display = 'none';
        if (box) box.style.display = 'none';
        document.body.style.overflow = '';
    }

    function getFeatureModalValues(type, fields) {
        var values = {};
        fields.forEach(function(f) {
            var el = document.getElementById('ff-' + type + '-' + f.key);
            values[f.key] = el ? el.value.trim() : '';
        });
        return values;
    }

    function clearFeatureModalFields(type, fields) {
        fields.forEach(function(f) {
            var el = document.getElementById('ff-' + type + '-' + f.key);
            if (el) el.value = '';
        });
    }

    function featureFieldLabel(cfg, key) {
        var f = (cfg.fields || []).find(function(f) { return f.key === key; });
        return f ? f.label : key;
    }

    function setFeatureSubmitting(type, submitting) {
        var btn = document.getElementById('feature-modal-submit-' + type);
        if (!btn) return;
        btn.disabled = submitting;
        btn.style.opacity = submitting ? '0.6' : '1';
        btn.style.cursor = submitting ? 'wait' : 'pointer';
        btn.textContent = submitting ? 'Saving...' : 'Add';
    }

    function showFeatureError(type, msg) {
        var el = document.getElementById('feature-modal-error-' + type);
        if (el) {
            el.textContent = msg;
            el.style.display = 'block';
        }
    }

    function confirmFeatureModal(type) {
        var cfg = featureModalConfigs[type];
        if (!cfg) return;

        var v = getFeatureModalValues(type, cfg.fields);

        var missing = (cfg.required || []).filter(function(key) { return !v[key] || !String(v[key]).trim(); });
        if (missing.length) {
            var msg = 'Please fill in: ' + missing.map(function(key) { return featureFieldLabel(cfg, key); }).join(', ');
            showFeatureError(type, msg);
            return;
        }

        if (cfg.minOne && (cfg.fields || []).every(function(f) { return !v[f.key] || !String(v[f.key]).trim(); })) {
            showFeatureError(type, 'Please fill in at least one field.');
            return;
        }

        setFeatureSubmitting(type, true);

        fetch('{{ route("doctor.features.store", ["type" => "TYPE"]) }}'.replace('TYPE', type), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: JSON.stringify(v),
        }).then(function(response) {
            return response.json().then(function(data) {
                return { ok: response.ok, data: data };
            });
        }).then(function(res) {
            setFeatureSubmitting(type, false);
            if (res.ok && res.data && res.data.success) {
                clearFeatureModalFields(type, cfg.fields);
                closeFeatureModal(type);
                window.dispatchEvent(new CustomEvent('feature-confirmed-' + type, { detail: v }));
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: (res.data.message && res.data.message !== 'Saved successfully.') ? res.data.message : cfg.successMessage || 'Saved successfully.',
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                var msg = res.data && res.data.message ? res.data.message : 'Failed to save. Please try again.';
                if (res.data && res.data.errors) {
                    var keys = Object.keys(res.data.errors);
                    if (keys.length) {
                        msg = res.data.errors[keys[0]][0] || msg;
                    }
                }
                showFeatureError(type, msg);
                Swal.fire({ icon: 'error', title: 'Submission Failed', text: msg });
            }
        }).catch(function() {
            setFeatureSubmitting(type, false);
            var msg = 'Network error. Please try again.';
            showFeatureError(type, msg);
            Swal.fire({ icon: 'error', title: 'Submission Failed', text: msg });
        });
    }

    var featureModalConfigs = {
        family_history: {
            title: 'Add Family History',
            type: 'family_history',
            icon: '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
            color: { bg: '#dcfce7', text: '#166534' },
            fields: [
                { key: 'name', label: 'Disease Name', placeholder: 'e.g. Diabetes' },
                { key: 'relation', label: 'Relation', placeholder: 'e.g. Father, Mother' },
            ],
            required: ['name'],
            successMessage: 'Family history added successfully.',
        },
        menstrual_history: {
            title: 'Add Menstrual History',
            type: 'menstrual_history',
            icon: '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            color: { bg: '#ede9fe', text: '#5b21b6' },
            fields: [
                { key: 'lmp', label: 'LMP', placeholder: 'e.g. 15/06/2026' },
                { key: 'cycle', label: 'Cycle', placeholder: 'e.g. 28 days' },
                { key: 'duration', label: 'Duration', placeholder: 'e.g. 5 days' },
                { key: 'flow', label: 'Flow', placeholder: 'e.g. Normal, Heavy' },
                { key: 'notes', label: 'Notes', placeholder: 'Additional notes...', type: 'textarea' },
            ],
            minOne: true,
            successMessage: 'Menstrual history saved successfully.',
        },
        drug_history: {
            title: 'Add Drug History',
            type: 'drug_history',
            icon: '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>',
            color: { bg: '#fef3c7', text: '#92400e' },
            fields: [
                { key: 'name', label: 'Drug Name', placeholder: 'e.g. Metformin' },
                { key: 'dose', label: 'Dose', placeholder: 'e.g. 500mg' },
            ],
            required: ['name'],
            successMessage: 'Drug history added successfully.',
        },
        ot_note: {
            title: 'Add OT Note / Procedure',
            type: 'ot_note',
            icon: '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
            color: { bg: '#fee2e2', text: '#991b1b' },
            fields: [
                { key: 'procedure', label: 'Procedure Name', placeholder: 'e.g. Appendectomy' },
                { key: 'date', label: 'Date', placeholder: 'e.g. 15/06/2026' },
                { key: 'notes', label: 'Notes', placeholder: 'Additional notes...', type: 'textarea' },
            ],
            required: ['procedure'],
            successMessage: 'OT note added successfully.',
        },
        anesthesia: {
            title: 'Add Anesthesia',
            type: 'anesthesia',
            icon: '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>',
            color: { bg: '#fce7f3', text: '#9d174d' },
            fields: [
                { key: 'type', label: 'Type (LA / Surface)', placeholder: 'e.g. LA, Surface' },
                { key: 'agent', label: 'Agent', placeholder: 'e.g. Lidocaine' },
                { key: 'dose', label: 'Dose', placeholder: 'e.g. 2%' },
                { key: 'notes', label: 'Notes', placeholder: 'Additional notes...', type: 'textarea' },
            ],
            required: ['type'],
            successMessage: 'Anesthesia saved successfully.',
        },
    };

    function featureFamilyHistory() {
        const editData = window.editPrescriptionData || {};
        const fh = editData.familyHistory || {};
        return {
            items: (fh.diseases || []).map(function(d) { return { name: d.name, relation: d.relation || '', notes: d.notes || '' }; }),
            globalNotes: fh.notes || '',
            init() {
                var self = this;
                window.addEventListener('feature-confirmed-family_history', function(e) {
                    var v = e.detail;
                    self.items.push({ name: v.name, relation: v.relation, notes: '' });
                });
            },
            removeItem(i) { this.items.splice(i, 1); },
            openModal() {
                var cfg = featureModalConfigs.family_history;
                createFeatureModal(cfg.type, cfg.title, cfg.icon, cfg.fields, cfg.color);
                openFeatureModal(cfg.type);
            }
        };
    }

    function featureMenstrualHistory() {
        const editData = window.editPrescriptionData || {};
        const mh = editData.menstrualHistory || {};
        return {
            data: { lmp: mh.lmp || '', cycle: mh.cycle || '', duration: mh.duration || '', flow: mh.flow || '', notes: mh.notes || '' },
            init() {
                var self = this;
                window.addEventListener('feature-confirmed-menstrual_history', function(e) {
                    self.data = e.detail;
                });
            },
            openModal() {
                var cfg = featureModalConfigs.menstrual_history;
                createFeatureModal(cfg.type, cfg.title, cfg.icon, cfg.fields, cfg.color);
                openFeatureModal(cfg.type);
            }
        };
    }

    function featureDrugHistory() {
        const editData = window.editPrescriptionData || {};
        const dh = editData.drugHistory || {};
        return {
            items: (dh.drugs || []).map(function(d) { return { name: d.name, dose: d.dose || '', notes: d.notes || '' }; }),
            globalNotes: dh.notes || '',
            init() {
                var self = this;
                window.addEventListener('feature-confirmed-drug_history', function(e) {
                    var v = e.detail;
                    self.items.push({ name: v.name, dose: v.dose, notes: '' });
                });
            },
            removeItem(i) { this.items.splice(i, 1); },
            openModal() {
                var cfg = featureModalConfigs.drug_history;
                createFeatureModal(cfg.type, cfg.title, cfg.icon, cfg.fields, cfg.color);
                openFeatureModal(cfg.type);
            }
        };
    }

    function featureOtNote() {
        const editData = window.editPrescriptionData || {};
        const ot = editData.otNote || {};
        return {
            data: { procedure: ot.procedure || '', date: ot.date || '', notes: ot.notes || '' },
            init() {
                var self = this;
                window.addEventListener('feature-confirmed-ot_note', function(e) {
                    self.data = e.detail;
                });
            },
            openModal() {
                var cfg = featureModalConfigs.ot_note;
                createFeatureModal(cfg.type, cfg.title, cfg.icon, cfg.fields, cfg.color);
                openFeatureModal(cfg.type);
            }
        };
    }

    function featureAnesthesia() {
        const editData = window.editPrescriptionData || {};
        const an = editData.anesthesia || {};
        return {
            data: { type: an.type || '', agent: an.agent || '', dose: an.dose || '', notes: an.notes || '' },
            init() {
                var self = this;
                window.addEventListener('feature-confirmed-anesthesia', function(e) {
                    self.data = e.detail;
                });
            },
            openModal() {
                var cfg = featureModalConfigs.anesthesia;
                createFeatureModal(cfg.type, cfg.title, cfg.icon, cfg.fields, cfg.color);
                openFeatureModal(cfg.type);
            }
        };
    }

    // Clinical Seal Search Modal
    let sealSearchTimer = null;
    let sealSearchResults = [];
    let sealRecentItems = [];
    let sealPopularItems = [];
    let sealEditingIndex = null;

    function openSealSearchModal(editItem) {
        sealEditingIndex = null;
        if (editItem) {
            sealEditingIndex = editItem.sort_order;
        }
        document.getElementById('seal-search-modal').style.display = 'flex';
        document.getElementById('seal-search-input').value = '';
        document.getElementById('seal-create-btn').style.display = 'none';
        document.getElementById('seal-search-input').focus();
        loadSealDefaults();
    }

    function openSealSearchModalForMedicine() {
        sealEditingIndex = null;
        document.getElementById('seal-search-modal').style.display = 'flex';
        document.getElementById('seal-search-input').value = '';
        document.getElementById('seal-create-btn').style.display = 'none';
        document.getElementById('seal-search-input').focus();
        loadSealDefaults();
    }

    function closeSealModal() {
        document.getElementById('seal-search-modal').style.display = 'none';
        sealSearchResults = [];
        sealEditingIndex = null;
    }

    function loadSealDefaults() {
        const resultsEl = document.getElementById('seal-search-results');
        resultsEl.innerHTML = '<div style="padding:20px;text-align:center;color:#9ca3af;font-size:13px;">Loading...</div>';

        Promise.all([
            fetch('{{ route("doctor.clinical-seals.recent") }}').then(r => r.json()),
            fetch('{{ route("doctor.clinical-seals.popular") }}').then(r => r.json()),
        ]).then(data => {
            sealRecentItems = data[0];
            sealPopularItems = data[1];
            renderSealResults();
        }).catch(() => {
            resultsEl.innerHTML = '<div style="padding:20px;text-align:center;color:#9ca3af;font-size:13px;">Failed to load seals</div>';
        });
    }

    function searchSeals() {
        const q = document.getElementById('seal-search-input').value.trim();
        const createBtn = document.getElementById('seal-create-btn');

        clearTimeout(sealSearchTimer);

        if (!q) {
            document.getElementById('seal-create-btn').style.display = 'none';
            loadSealDefaults();
            return;
        }

        createBtn.style.display = 'inline-block';

        sealSearchTimer = setTimeout(() => {
            fetch('{{ route("doctor.clinical-seals.search") }}?q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(results => {
                    sealSearchResults = results;
                    renderSealResults();
                });
        }, 200);
    }

    function sealSearchEnter() {
        if (sealSearchResults.length === 1) {
            selectSealItem(sealSearchResults[0]);
        } else if (sealSearchResults.length === 0) {
            createSealFromSearch();
        }
    }

    function createSealFromSearch() {
        const q = document.getElementById('seal-search-input').value.trim();
        if (!q) return;

        fetch('{{ route("doctor.clinical-seals.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ name: q }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.item) {
                selectSealItem(data.item);
            }
        });
    }

    function selectSealItem(seal) {
        // Find the Alpine component scope
        const prescEl = document.querySelector('[x-data="prescriptionItems()"]');
        if (prescEl && window.Alpine) {
            const data = window.Alpine.$data(prescEl);
            if (data.targetMedicineForSeal >= 0) {
                // Add seal to specific medicine item
                const medIndex = data.targetMedicineForSeal;
                // Check if this seal is already used by another medicine or seal row
                if (seal.id && data.isSealUsed(seal.id)) {
                    // Check if the current medicine already has this seal (editing case)
                    if (data.items[medIndex].seal_id !== seal.id) {
                        alert('This Clinical Seal is already added to the prescription. Please select a different seal.');
                        return;
                    }
                }
                data.items[medIndex].seal_id = seal.id;
                data.items[medIndex].seal_text = seal.name;
                data.items[medIndex].seal_details = seal.details || '';
                data.targetMedicineForSeal = -1;
            } else if (sealEditingIndex !== null) {
                // Edit existing seal in-place - check if replacing with a different seal that's already used
                if (seal.id) {
                    const currentSeal = data.items.find(i => i.type === 'seal' && i.sort_order === sealEditingIndex);
                    if (currentSeal && currentSeal.seal_id !== seal.id && data.isSealUsed(seal.id)) {
                        alert('This Clinical Seal is already added to the prescription. Please select a different seal.');
                        return;
                    }
                }
                const existing = data.items.find(i => i.type === 'seal' && i.sort_order === sealEditingIndex);
                if (existing) {
                    existing.seal_id = seal.id;
                    existing.seal_text = seal.name;
                    existing.seal_details = seal.details || '';
                }
            } else {
                // Adding a new seal row - check for duplicates
                if (seal.id && data.isSealUsed(seal.id)) {
                    alert('This Clinical Seal is already added to the prescription. Please select a different seal.');
                    return;
                }
                data.addSeal(seal.id, seal.name, seal.details);
            }
        }
        closeSealModal();
    }

    function renderSealResults() {
        const container = document.getElementById('seal-search-results');
        const q = document.getElementById('seal-search-input').value.trim();
        let html = '';

        // Get currently used seal IDs from Alpine component
        const prescEl = document.querySelector('[x-data="prescriptionItems()"]');
        let usedSealIds = new Set();
        if (prescEl && window.Alpine) {
            const data = window.Alpine.$data(prescEl);
            usedSealIds = data.getUsedSealIds();
        }

        if (q) {
            if (sealSearchResults.length === 0) {
                html = '<div style="padding:24px 20px;text-align:center;">'
                    + '<div style="width:48px;height:48px;border-radius:12px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;color:#9ca3af;">'
                    + '<svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>'
                    + '</div>'
                    + '<p style="font-size:14px;color:#6b7280;margin:0 0 4px 0;">No matching seal found</p>'
                    + '<p style="font-size:12px;color:#9ca3af;margin:0 0 12px 0;">Click "Create & Insert" to create and use this seal</p>'
                    + '</div>';
            } else {
                html = '<div style="padding:6px 16px;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;background:#f9fafb;border-bottom:1px solid #f3f4f6;">Search Results</div>';
                sealSearchResults.forEach(item => {
                    html += renderSealItem(item, usedSealIds);
                });
            }
        } else {
            if (sealRecentItems.length > 0) {
                html += '<div style="padding:6px 16px;font-size:11px;font-weight:600;color:#d97706;text-transform:uppercase;letter-spacing:0.05em;background:rgba(251,191,36,0.06);border-bottom:1px solid #fef3c7;display:flex;align-items:center;gap:6px;">'
                    + '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
                    + 'Recently Used</div>';
                sealRecentItems.forEach(item => { html += renderSealItem(item, usedSealIds); });
            }
            if (sealPopularItems.length > 0) {
                html += '<div style="padding:6px 16px;font-size:11px;font-weight:600;color:#2563eb;text-transform:uppercase;letter-spacing:0.05em;background:rgba(59,130,246,0.06);border-bottom:1px solid #dbeafe;display:flex;align-items:center;gap:6px;">'
                    + '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>'
                    + 'Most Frequently Used</div>';
                sealPopularItems.forEach(item => { html += renderSealItem(item, usedSealIds); });
            }
            if (!sealRecentItems.length && !sealPopularItems.length) {
                html = '<div style="padding:32px 20px;text-align:center;color:#9ca3af;"><p style="font-size:13px;margin:0;">Type to search clinical seals</p></div>';
            }
        }

        container.innerHTML = html;

        container.querySelectorAll('.seal-result-item').forEach(el => {
            const isAlreadyAdded = el.getAttribute('data-already-added') === 'true';
            if (!isAlreadyAdded) {
                el.onclick = function() {
                    const itemData = JSON.parse(el.getAttribute('data-item'));
                    selectSealItem(itemData);
                };
                el.onmouseenter = function() { this.style.background = 'rgba(251,191,36,0.08)'; };
                el.onmouseleave = function() { this.style.background = 'transparent'; };
            } else {
                el.style.cursor = 'not-allowed';
                el.style.opacity = '0.6';
                el.onmouseenter = function() { this.style.background = 'rgba(239,68,68,0.05)'; };
                el.onmouseleave = function() { this.style.background = 'transparent'; };
            }
        });
    }

    function renderSealItem(item, usedSealIds) {
        const isAlreadyAdded = usedSealIds && usedSealIds.has(item.id);
        const alreadyAddedBadge = isAlreadyAdded
            ? '<span style="display:inline-block;padding:1px 6px;background:#fef2f2;color:#dc2626;border-radius:3px;font-size:9px;font-weight:700;margin-left:6px;">ALREADY ADDED</span>'
            : '';
        const arrowIcon = isAlreadyAdded
            ? '<svg width="16" height="16" fill="none" stroke="#dc2626" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>'
            : '<svg width="16" height="16" fill="none" stroke="#d1d5db" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>';

        return '<div class="seal-result-item" data-item=\'' + JSON.stringify(item).replace(/'/g, "&#39;") + '\' data-already-added=\'' + (isAlreadyAdded ? 'true' : 'false') + '\''
            + ' style="display:flex;align-items:center;gap:12px;padding:10px 16px;cursor:pointer;transition:background 0.1s;border-bottom:1px solid rgba(148,163,184,0.06);">'
            + '<div style="width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;background:' + (isAlreadyAdded ? 'rgba(239,68,68,0.1)' : 'rgba(251,191,36,0.15)') + ';color:' + (isAlreadyAdded ? '#dc2626' : '#92400e') + ';font-size:11px;font-weight:700;flex-shrink:0;">'
            + '<svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'
            + '</div>'
            + '<div style="flex:1;min-width:0;">'
            +   '<div class="seal-item-name">' + item.name + alreadyAddedBadge + '</div>'
            +   (item.details ? '<div style="font-size:11px;color:#6b7280;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:250px;">' + item.details + '</div>' : '')
            +   (item.used_count > 0 ? '<div class="seal-item-count">Used ' + item.used_count + 'x</div>' : '')
            + '</div>'
            + arrowIcon
            + '</div>';
    }


    function populatePatientData() {
        const sel = document.getElementById('patient-select');
        const opt = sel.options[sel.selectedIndex];
        document.getElementById('patient-age').value = opt?.dataset?.age || '';
        document.getElementById('patient-sex').value = opt?.dataset?.sex || '';
        document.getElementById('patient-name-display').value = opt?.dataset?.name || '';
        document.getElementById('patient-address').value = opt?.dataset?.address || '';
        document.getElementById('patient-mobile').value = opt?.dataset?.mobile || '';

        const prevWt = opt?.dataset?.weight;
        const prevHt = opt?.dataset?.height;
        const wtEl = document.getElementById('rx-weight-prev');
        const htEl = document.getElementById('rx-height-prev');
        const wtInput = document.getElementById('rx-weight');
        const htInput = document.getElementById('rx-height');

        wtEl.dataset.prev = prevWt || '';
        htEl.dataset.prev = prevHt || '';

        updateComparison(wtInput, wtEl, 2, 'kg');
        updateComparison(htInput, htEl, 1, 'cm');

        renderMedicalHistories(sel.value);
    }

    function renderMedicalHistories(patientId) {
        const container = document.getElementById('medical-history-list');
        const addBtn = document.getElementById('add-mh-toggle');
        if (!container) return;
        if (!patientId) {
            container.innerHTML = '<span style="color:#999;font-size:12px;width:100%;text-align:center;">Select a patient to view medical history</span>';
            if (addBtn) addBtn.style.display = 'none';
            return;
        }
        if (addBtn) addBtn.style.display = 'inline-block';
        container.innerHTML = '<span style="color:#94a3b8;font-size:12px;width:100%;text-align:center;">Loading...</span>';
        var url = '{{ route("doctor.prescriptions.patient-medical-histories", ["patient" => "_PID_"]) }}'.replace('_PID_', patientId);
        fetch(url, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(items => {
            if (!items || !items.length) {
                container.innerHTML = '<span style="color:#999;font-size:12px;width:100%;text-align:center;">No medical history recorded</span>';
                return;
            }
            container.innerHTML = items.map(function(h) {
                return '<span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;background:#fff;border:1px solid #e2e8f0;border-radius:20px;font-size:12px;box-shadow:0 1px 2px rgba(0,0,0,0.04);">' +
                    '<span class="w-1.5 h-1.5 rounded-full ' + (h.status === 'active' ? 'bg-amber-500' : 'bg-green-500') + '"></span>' +
                    '<strong>' + h.condition + '</strong>' +
                    (h.date ? '<span style="color:#94a3b8;font-size:10px;">' + h.date + '</span>' : '') +
                    (h.notes ? '<span style="color:#94a3b8;font-size:10px;margin-left:2px;">- ' + h.notes + '</span>' : '') +
                    '</span>';
            }).join('');
        })
        .catch(() => {
            container.innerHTML = '<span style="color:#999;font-size:12px;width:100%;text-align:center;">Failed to load medical history</span>';
        });
    }

    function toggleAddMedicalHistory() {
        const form = document.getElementById('add-mh-form');
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    }

    function saveMedicalHistory() {
        const sel = document.getElementById('patient-select');
        const patientId = sel.value;
        if (!patientId) return;

        const condition = document.getElementById('mh-condition').value.trim();
        if (!condition) { alert('Condition is required'); return; }

        const date = document.getElementById('mh-date').value;
        const status = document.getElementById('mh-status').value;
        const notes = document.getElementById('mh-notes').value.trim();

        const url = '{{ route("doctor.prescriptions.patient-medical-histories.store", ["patient" => "_PID_"]) }}'.replace('_PID_', patientId);

        fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ condition_name: condition, diagnosed_date: date || null, status: status, notes: notes || null })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                document.getElementById('mh-condition').value = '';
                document.getElementById('mh-date').value = '';
                document.getElementById('mh-status').value = 'active';
                document.getElementById('mh-notes').value = '';
                toggleAddMedicalHistory();
                renderMedicalHistories(patientId);
            }
        })
        .catch(() => alert('Failed to save medical history'));
    }

    function updateComparison(input, el, decimals, unit) {
        const prev = el.dataset.prev;
        if (!prev) {
            el.textContent = '';
            el.style.display = 'none';
            return;
        }
        const cur = parseFloat(input.value) || 0;
        if (cur > 0) {
            el.textContent = parseFloat(prev).toFixed(decimals) + ' ' + unit + ' \u00BB ' + cur.toFixed(decimals) + ' ' + unit;
        } else {
            el.textContent = '\u00AB ' + parseFloat(prev).toFixed(decimals) + ' ' + unit;
        }
        el.style.display = 'inline';
    }

    document.addEventListener('input', function(e) {
        if (e.target.id === 'rx-weight') {
            updateComparison(e.target, document.getElementById('rx-weight-prev'), 2, 'kg');
        }
        if (e.target.id === 'rx-height') {
            updateComparison(e.target, document.getElementById('rx-height-prev'), 1, 'cm');
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const editData = window.editPrescriptionData || {};
        const notes = editData.notes || '';
        const guardianEl = document.getElementById('guardian-name');
        const guardianMatch = String(notes).match(/^Guardian:\s*(.+)$/m);
        if (guardianMatch && guardianEl) {
            guardianEl.value = guardianMatch[1].trim();
        }
        document.getElementById('notes-hidden').value = notes || '';
        populatePatientData();
    });

    function serializeComplaints() {
        const el = document.getElementById('complaints-section');
        if (el && window.Alpine) {
            const data = window.Alpine.$data(el);
            const plain = (data.selected || []).map(c => ({ id: c.id, name: c.name, notes: c.notes }));
            document.getElementById('complaints-json').value = JSON.stringify(plain);
        }
    }

    function serializeTests() {
        const el = document.getElementById('tests-section');
        if (el && window.Alpine) {
            const data = window.Alpine.$data(el);
            const tests = (data.selected || []).map(t => ({ id: t.id, name: t.name }));
            document.getElementById('tests-json').value = JSON.stringify(tests);
            const results = (data.selected || []).filter(t => t.result && t.result.trim() !== '').map(t => ({ test_name: t.name, result: t.result }));
            document.getElementById('test-results-json').value = JSON.stringify(results);
        }
    }

    function serializeAdvice() {
        const el = document.querySelector('[x-data="modalAdvice()"]');
        if (el && window.Alpine) {
            const data = window.Alpine.$data(el);
            const advice = (data.selected || []).map(a => ({ id: a.id, name: a.name }));
            document.getElementById('advice-json').value = JSON.stringify(advice);
        }
    }

    function serializeMedicalHistories() {
        const el = document.getElementById('medical-history-section');
        if (el && window.Alpine) {
            const data = window.Alpine.$data(el);
            const histories = (data.selected || []).map(h => ({ id: h.id, name: h.name }));
            document.getElementById('medical-histories-json').value = JSON.stringify(histories);
        }
    }

    function serializeSeals() {
        const el = document.querySelector('[x-data="prescriptionItems()"]');
        if (el && window.Alpine) {
            const data = window.Alpine.$data(el);
            const seals = data.serializeSeals();
            document.getElementById('seals-json').value = JSON.stringify(seals);
        }
    }

    function submitForm() {
        serializeComplaints();
        serializeTests();
        serializeAdvice();
        serializeMedicalHistories();
        serializeSeals();
        document.getElementById('prescription-form').submit();
    }

    function saveAndAddMedicinesLater() {
        serializeComplaints();
        serializeTests();
        serializeAdvice();
        serializeMedicalHistories();
        serializeSeals();
        const form = document.getElementById('prescription-form');
        form.insertAdjacentHTML('beforeend', '<input type="hidden" name="_add_medicines_later" value="1">');
        form.submit();
    }

    document.getElementById('prescription-form')?.addEventListener('submit', function() {
        const guardian = document.querySelector('[name="guardian_name"]')?.value || '';

        if (guardian) {
            document.getElementById('notes-hidden').value = 'Guardian: ' + guardian + '\n';
        }

        serializeComplaints();
        serializeTests();
        serializeAdvice();
        serializeMedicalHistories();
        serializeSeals();
    });

    $(document).ready(function() {
        $('#patient-select').select2({
            placeholder: '-- Select Patient --',
            allowClear: true,
            width: '100%',
            templateResult: function(opt) {
                if (!opt.id) return opt.text;
                const mobile = $(opt.element).data('mobile');
                return $('<span>' + opt.text + (mobile ? ' <span style="color:var(--text-muted);font-size:0.85em;">' + mobile + '</span>' : '') + '</span>');
            },
            templateSelection: function(opt) {
                return opt.text || opt.id;
            },
            matcher: function(params, data) {
                if (!params.term) return data;
                const q = params.term.toLowerCase();
                const mobile = $(data.element).data('mobile') || '';
                if (data.text.toLowerCase().includes(q) || mobile.toLowerCase().includes(q)) {
                    return data;
                }
                return null;
            },
        }).on('change', function(e) {
            populatePatientData(e);
            const patientId = e.target.value;
            window.dispatchEvent(new CustomEvent('patient-selected', { detail: { patientId: patientId ? parseInt(patientId) : null } }));
        });
    });
</script>
@endpush

<x-ai-assistant-panel />

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        function createItemSearchModal(type) {
            return () => ({
                showModal: false,
                searchQuery: '',
                searchResults: [],
                recentItems: [],
                popularItems: [],
                highlightedIndex: -1,
                loading: false,

                async openModal() {
                    this.showModal = true;
                    this.searchQuery = '';
                    this.searchResults = [];
                    this.highlightedIndex = -1;
                    await this.$nextTick();
                    this.$refs.searchInput?.focus();
                    this.loadDefaults();
                },

                closeModal() {
                    this.showModal = false;
                    this.searchQuery = '';
                    this.searchResults = [];
                },

                async loadDefaults() {
                    this.loading = true;
                    try {
                        const [recentRes, popularRes] = await Promise.all([
                            fetch(`/doctor/items/${type}/recent`),
                            fetch(`/doctor/items/${type}/popular`),
                        ]);
                        this.recentItems = await recentRes.json();
                        this.popularItems = await popularRes.json();
                    } catch (e) {
                        console.error('Failed to load defaults:', e);
                    } finally {
                        this.loading = false;
                    }
                },

                async search() {
                    if (this.searchQuery.length === 0) {
                        this.searchResults = [];
                        this.highlightedIndex = -1;
                        this.loadDefaults();
                        return;
                    }
                    this.loading = true;
                    this.highlightedIndex = -1;
                    try {
                        const res = await fetch(`/doctor/items/${type}/search?q=${encodeURIComponent(this.searchQuery)}`);
                        this.searchResults = await res.json();
                    } catch (e) {
                        console.error('Search failed:', e);
                    } finally {
                        this.loading = false;
                    }
                },

                async createAndSelect() {
                    if (!this.searchQuery.trim()) return;
                    this.loading = true;
                    try {
                        const res = await fetch(`/doctor/items/${type}/store`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ name: this.searchQuery.trim() }),
                        });
                        const data = await res.json();
                        this.selectItem(data.item);
                    } catch (e) {
                        console.error('Create failed:', e);
                    } finally {
                        this.loading = false;
                    }
                },

                selectItem(item) {
                    window.dispatchEvent(new CustomEvent(`item-selected-${type}`, {
                        detail: { id: item.id, name: item.name, used_count: item.used_count }
                    }));
                    this.closeModal();
                },

                selectHighlighted() {
                    const allItems = this.searchQuery.length > 0
                        ? this.searchResults
                        : [...this.recentItems, ...this.popularItems];
                    if (this.highlightedIndex >= 0 && this.highlightedIndex < allItems.length) {
                        this.selectItem(allItems[this.highlightedIndex]);
                    } else if (this.searchQuery.length > 0 && this.searchResults.length === 0) {
                        this.createAndSelect();
                    }
                },

                moveHighlight(direction) {
                    const allItems = this.searchQuery.length > 0
                        ? this.searchResults
                        : [...this.recentItems, ...this.popularItems];
                    if (allItems.length === 0) return;
                    if (this.highlightedIndex === -1) {
                        this.highlightedIndex = direction === 1 ? 0 : allItems.length - 1;
                    } else {
                        this.highlightedIndex = (this.highlightedIndex + direction + allItems.length) % allItems.length;
                    }
                    const container = this.$refs.resultsContainer;
                    const items = container?.querySelectorAll('[class*="cursor-pointer"]');
                    if (items && items[this.highlightedIndex]) {
                        items[this.highlightedIndex].scrollIntoView({ block: 'nearest' });
                    }
                },
            });
        }

        Alpine.data('itemSearchModal_complaint', createItemSearchModal('complaint'));
        Alpine.data('itemSearchModal_test', createItemSearchModal('test'));
        Alpine.data('itemSearchModal_medical_history', createItemSearchModal('medical_history'));
        Alpine.data('itemSearchModal_advice', createItemSearchModal('advice'));
    });
</script>
@endpush
