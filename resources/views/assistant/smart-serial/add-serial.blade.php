@extends('assistant.layouts.app')
@section('title', 'Add Serial')
@section('header', 'Add Serial')

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="addSerialApp()">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--text-primary);">Add Serial</h1>
            <p class="mt-1" style="color:var(--text-muted);">Add a patient to the queue</p>
        </div>
        <a href="{{ route('assistant.smart-serial.index', ['doctor_id' => $doctorId]) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">&larr; Back to Queue</a>
    </div>

    {{-- Doctor Selector --}}
    @if($doctors->count() > 1)
    <div class="dashboard-card animate-card" style="border-left:4px solid #6366f1;">
        <div class="flex items-center gap-4 flex-wrap">
            <span class="text-sm font-semibold" style="color:var(--text-muted);">Doctor:</span>
            @foreach($doctors as $doc)
                <a href="{{ route('assistant.smart-serial.add-serial', ['doctor_id' => $doc->id]) }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ ($doctorId == $doc->id) ? 'bg-blue-600 text-white shadow-md' : 'bg-white/50 text-gray-600 hover:bg-white/70' }}">
                    {{ $doc->name }}@if($doc->clinic_name) - {{ $doc->clinic_name }}@endif
                </a>
            @endforeach
        </div>
    </div>
    @endif

    @if(session('success'))
        <div class="dashboard-card animate-card" style="border-left:4px solid #10b981;">
            <div class="flex items-center gap-3">
                <div class="stat-icon" style="background:linear-gradient(135deg,rgba(16,185,129,0.12),rgba(16,185,129,0.06));color:#10b981;width:36px;height:36px;border-radius:10px;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <p class="text-sm font-medium" style="color:#059669;">{{ session('success') }}</p>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="dashboard-card animate-card" style="border-left:4px solid #ef4444;">
            <div class="flex items-center gap-3">
                <div class="stat-icon" style="background:linear-gradient(135deg,rgba(239,68,68,0.12),rgba(239,68,68,0.06));color:#ef4444;width:36px;height:36px;border-radius:10px;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <p class="text-sm font-medium" style="color:#dc2626;">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    @if(!$session)
        <div class="dashboard-card animate-card p-12 text-center" style="border-left:4px solid #f59e0b;">
            <div class="stat-icon mx-auto mb-4" style="width:80px;height:80px;border-radius:20px;background:linear-gradient(135deg,rgba(245,158,11,0.12),rgba(245,158,11,0.06));color:#f59e0b;">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold" style="color:var(--text-primary);">No Active Session</h3>
            <p class="mt-2" style="color:var(--text-muted);">Start a session first before adding patients to the queue.</p>
            <a href="{{ route('assistant.smart-serial.index', ['doctor_id' => $doctorId]) }}" class="inline-block mt-6 px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">Go to Queue</a>
        </div>
    @else
        <form method="POST" action="{{ route('assistant.smart-serial.add-patient') }}">
            @csrf
            <input type="hidden" name="doctor_id" value="{{ $doctorId }}">

            {{-- Patient Search --}}
            <div class="dashboard-card animate-card mb-4" style="border-left:4px solid #6366f1;position:relative;z-index:10;">
                <label class="block text-sm font-bold uppercase tracking-wider mb-3" style="color:var(--text-muted);">Patient <span style="color:#ef4444;">*</span></label>
                <div class="flex gap-3">
                    <div class="relative flex-1">
                        <input type="text" x-model="searchQuery" @input.debounce.300ms="searchPatients()" @focus="showDropdown = results.length > 0" placeholder="Search by name, phone, or patient number..."
                            class="w-full border rounded-lg px-4 py-2.5 pr-10 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                            style="border-color:rgba(148,163,184,0.2);background:rgba(255,255,255,0.7);color:var(--text-primary);">

                        <div x-show="showDropdown && results.length > 0" x-cloak @click.away="showDropdown = false"
                             class="absolute z-[999] w-full mt-1 bg-white border rounded-lg shadow-lg max-h-60 overflow-y-auto">
                            <template x-for="patient in results" :key="patient.id">
                                <div class="px-4 py-3 cursor-pointer transition-all border-b last:border-0 hover:bg-indigo-50" @click="selectPatient(patient)">
                                    <div class="font-semibold text-sm" style="color:var(--text-primary);" x-text="patient.name"></div>
                                    <div class="text-xs mt-0.5" style="color:var(--text-muted);">
                                        <span x-text="patient.phone || 'No phone'"></span>
                                        <span x-show="patient.patient_number"> &bull; <span x-text="patient.patient_number"></span></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                    <button type="button" @click="showCreateModal = true"
                        class="px-4 py-2.5 rounded-lg text-sm font-semibold text-white whitespace-nowrap transition-all"
                        style="background:linear-gradient(135deg,#10b981,#059669);box-shadow:0 4px 14px rgba(16,185,129,0.25);" title="Add new patient">
                        + New Patient
                    </button>
                </div>

                <input type="hidden" name="patient_id" :value="selectedPatient?.id" x-ref="patientIdInput">

                <div x-show="selectedPatient" x-cloak class="mt-3 p-3 rounded-lg flex items-center justify-between" style="background:rgba(99,102,241,0.08);border:1px solid rgba(99,102,241,0.15);">
                    <div class="flex items-center gap-3">
                        <div class="stat-icon" style="background:linear-gradient(135deg,rgba(99,102,241,0.15),rgba(99,102,241,0.08));color:#6366f1;width:36px;height:36px;border-radius:10px;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <div>
                            <span class="font-semibold text-sm" style="color:#4f46e5;" x-text="selectedPatient?.name"></span>
                            <span class="text-sm ml-2" style="color:#6366f1;" x-text="selectedPatient?.phone || ''"></span>
                            <span class="text-xs ml-2" style="color:#818cf8;" x-text="selectedPatient?.patient_number || ''"></span>
                        </div>
                    </div>
                    <button type="button" @click="clearPatient()" class="text-indigo-400 hover:text-indigo-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                @error('patient_id') <p class="text-xs mt-1" style="color:#dc2626;">{{ $message }}</p> @enderror
            </div>

            {{-- Patient Name & Phone (read-only) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div class="dashboard-card animate-card" style="border-left:4px solid #06b6d4;">
                    <label class="block text-xs font-bold uppercase tracking-wider mb-2" style="color:var(--text-muted);">Patient Name</label>
                    <input type="text" :value="selectedPatient?.name || ''" readonly placeholder="Auto-filled from patient"
                        class="w-full border-0 rounded-lg px-3 py-2 text-sm cursor-not-allowed"
                        style="background:rgba(6,182,212,0.06);color:var(--text-muted);">
                </div>
                <div class="dashboard-card animate-card" style="border-left:4px solid #06b6d4;">
                    <label class="block text-xs font-bold uppercase tracking-wider mb-2" style="color:var(--text-muted);">Phone</label>
                    <input type="text" :value="selectedPatient?.phone || ''" readonly placeholder="Auto-filled from patient"
                        class="w-full border-0 rounded-lg px-3 py-2 text-sm cursor-not-allowed"
                        style="background:rgba(6,182,212,0.06);color:var(--text-muted);">
                </div>
            </div>

            {{-- Doctor & Chamber --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div class="dashboard-card animate-card" style="border-left:4px solid #8b5cf6;">
                    <label class="block text-xs font-bold uppercase tracking-wider mb-2" style="color:var(--text-muted);">Doctor</label>
                    <div class="flex items-center gap-2">
                        <div class="stat-icon" style="background:linear-gradient(135deg,rgba(139,92,246,0.12),rgba(139,92,246,0.06));color:#8b5cf6;width:32px;height:32px;border-radius:8px;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <span class="text-sm font-semibold" style="color:var(--text-primary);">{{ $doctor->name }}</span>
                    </div>
                </div>
                <div class="dashboard-card animate-card" style="border-left:4px solid #8b5cf6;">
                    <label class="block text-xs font-bold uppercase tracking-wider mb-2" style="color:var(--text-muted);">Chamber</label>
                    <div class="flex items-center gap-2">
                        <div class="stat-icon" style="background:linear-gradient(135deg,rgba(139,92,246,0.12),rgba(139,92,246,0.06));color:#8b5cf6;width:32px;height:32px;border-radius:8px;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        @if($session->chamber)
                            <span class="text-sm font-semibold" style="color:var(--text-primary);">{{ $session->chamber->name }}</span>
                        @else
                            <span class="text-sm" style="color:var(--text-muted);">No chamber</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Date & Serial Number --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div class="dashboard-card animate-card" style="border-left:4px solid #f59e0b;">
                    <label class="block text-xs font-bold uppercase tracking-wider mb-2" style="color:var(--text-muted);">Date</label>
                    <div class="flex items-center gap-2">
                        <div class="stat-icon" style="background:linear-gradient(135deg,rgba(245,158,11,0.12),rgba(245,158,11,0.06));color:#f59e0b;width:32px;height:32px;border-radius:8px;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <span class="text-sm font-semibold" style="color:var(--text-primary);">{{ now()->format('d M, Y') }}</span>
                    </div>
                </div>
                <div class="dashboard-card animate-card" style="border-left:4px solid #06b6d4;">
                    <label class="block text-xs font-bold uppercase tracking-wider mb-2" style="color:var(--text-muted);">Serial Number</label>
                    <div class="flex items-center gap-2">
                        <div class="stat-icon" style="background:linear-gradient(135deg,rgba(6,182,212,0.12),rgba(6,182,212,0.06));color:#06b6d4;width:32px;height:32px;border-radius:8px;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                        </div>
                        <span class="text-2xl font-extrabold" style="color:#0891b2;">#{{ $formattedPreview ?? str_pad($nextSerial, 3, '0', STR_PAD_LEFT) }}</span>
                    </div>
                </div>
            </div>

            {{-- Serial Type (Priority) --}}
            <div class="dashboard-card animate-card mb-4" style="border-left:4px solid #a855f7;">
                <label class="block text-sm font-bold uppercase tracking-wider mb-3" style="color:var(--text-muted);">Serial Type</label>
                <div class="flex gap-3">
                    <label class="flex items-center gap-2 px-5 py-3 rounded-lg cursor-pointer transition-all border"
                        :class="priority === 'normal' ? 'shadow-md' : ''"
                        :style="priority === 'normal' ? 'background:rgba(59,130,246,0.1);border-color:rgba(59,130,246,0.4);' : 'border-color:rgba(148,163,184,0.2);background:rgba(255,255,255,0.5);'">
                        <input type="radio" name="priority" value="normal" x-model="priority" class="hidden">
                        <span class="w-3 h-3 rounded-full" style="background:#3b82f6;"></span>
                        <span class="text-sm font-semibold" :style="priority === 'normal' ? 'color:#1d4ed8;' : 'color:var(--text-muted);'">Normal</span>
                    </label>
                    <label class="flex items-center gap-2 px-5 py-3 rounded-lg cursor-pointer transition-all border"
                        :class="priority === 'urgent' ? 'shadow-md' : ''"
                        :style="priority === 'urgent' ? 'background:rgba(249,115,22,0.1);border-color:rgba(249,115,22,0.4);' : 'border-color:rgba(148,163,184,0.2);background:rgba(255,255,255,0.5);'">
                        <input type="radio" name="priority" value="urgent" x-model="priority" class="hidden">
                        <span class="w-3 h-3 rounded-full" style="background:#f97316;"></span>
                        <span class="text-sm font-semibold" :style="priority === 'urgent' ? 'color:#c2410c;' : 'color:var(--text-muted);'">Urgent</span>
                    </label>
                    <label class="flex items-center gap-2 px-5 py-3 rounded-lg cursor-pointer transition-all border"
                        :class="priority === 'vip' ? 'shadow-md' : ''"
                        :style="priority === 'vip' ? 'background:rgba(168,85,247,0.1);border-color:rgba(168,85,247,0.4);' : 'border-color:rgba(148,163,184,0.2);background:rgba(255,255,255,0.5);'">
                        <input type="radio" name="priority" value="vip" x-model="priority" class="hidden">
                        <span class="w-3 h-3 rounded-full" style="background:#a855f7;"></span>
                        <span class="text-sm font-semibold" :style="priority === 'vip' ? 'color:#7c3aed;' : 'color:var(--text-muted);'">VIP</span>
                    </label>
                </div>
            </div>

            {{-- Notes --}}
            <div class="dashboard-card animate-card mb-4" style="border-left:4px solid #6b7280;">
                <label class="block text-sm font-bold uppercase tracking-wider mb-3" style="color:var(--text-muted);">Notes</label>
                <textarea name="notes" rows="2" maxlength="500" placeholder="Optional notes about this patient..."
                    class="w-full border rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                    style="border-color:rgba(148,163,184,0.2);background:rgba(255,255,255,0.7);color:var(--text-primary);"></textarea>
            </div>

            {{-- Submit --}}
            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('assistant.smart-serial.index', ['doctor_id' => $doctorId]) }}"
                    class="px-6 py-2.5 rounded-lg text-sm font-semibold transition-all border"
                    style="border-color:rgba(148,163,184,0.3);color:var(--text-muted);background:rgba(255,255,255,0.5);">
                    Cancel
                </a>
                <button type="submit" :disabled="!selectedPatient"
                    class="px-8 py-2.5 rounded-lg text-sm font-bold text-white transition-all"
                    :class="selectedPatient ? '' : 'cursor-not-allowed'"
                    :style="selectedPatient ? 'background:linear-gradient(135deg,#6366f1,#4f46e5);box-shadow:0 4px 14px rgba(99,102,241,0.25);' : 'background:#9ca3af;'">
                    Add to Queue
                </button>
            </div>
        </form>
    @endif

    {{-- Create Patient Modal --}}
    <div x-show="showCreateModal" x-cloak style="display:none;" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0" style="background:rgba(0,0,0,0.4);backdrop-filter:blur(4px);" @click="showCreateModal = false"></div>
            <div class="relative rounded-2xl max-w-md w-full p-6 space-y-4" style="background:rgba(255,255,255,0.95);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,0.5);box-shadow:0 25px 60px rgba(0,0,0,0.15);">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold" style="color:var(--text-primary);">Add New Patient</h3>
                    <button @click="showCreateModal = false" class="w-8 h-8 rounded-full flex items-center justify-center transition-all"
                        style="background:rgba(107,114,128,0.1);color:var(--text-muted);"
                        onmouseover="this.style.background='rgba(107,114,128,0.2)'" onmouseout="this.style.background='rgba(107,114,128,0.1)'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider mb-1.5" style="color:var(--text-muted);">Full Name <span style="color:#ef4444;">*</span></label>
                        <input type="text" x-model="newPatient.name" placeholder="Patient full name"
                            class="w-full border rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                            style="border-color:rgba(148,163,184,0.2);background:rgba(255,255,255,0.7);color:var(--text-primary);">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider mb-1.5" style="color:var(--text-muted);">Phone</label>
                            <input type="text" x-model="newPatient.phone" placeholder="Phone number"
                                class="w-full border rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                style="border-color:rgba(148,163,184,0.2);background:rgba(255,255,255,0.7);color:var(--text-primary);">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider mb-1.5" style="color:var(--text-muted);">Gender</label>
                            <select x-model="newPatient.gender"
                                class="w-full border rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                style="border-color:rgba(148,163,184,0.2);background:rgba(255,255,255,0.7);color:var(--text-primary);">
                                <option value="">Select</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider mb-1.5" style="color:var(--text-muted);">Age</label>
                            <input type="number" x-model="newPatient.age" min="0" max="150" placeholder="Age"
                                class="w-full border rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                style="border-color:rgba(148,163,184,0.2);background:rgba(255,255,255,0.7);color:var(--text-primary);">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider mb-1.5" style="color:var(--text-muted);">Address</label>
                            <input type="text" x-model="newPatient.address" placeholder="Address"
                                class="w-full border rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                style="border-color:rgba(148,163,184,0.2);background:rgba(255,255,255,0.7);color:var(--text-primary);">
                        </div>
                    </div>
                </div>
                <div x-show="createError" class="text-sm p-3 rounded-lg" style="background:rgba(239,68,68,0.08);color:#dc2626;border:1px solid rgba(239,68,68,0.15);" x-text="createError"></div>
                <div class="flex justify-end gap-3 pt-2">
                    <button @click="showCreateModal = false"
                        class="px-5 py-2.5 rounded-lg text-sm font-semibold transition-all border"
                        style="border-color:rgba(148,163,184,0.3);color:var(--text-muted);background:rgba(255,255,255,0.5);">
                        Cancel
                    </button>
                    <button @click="createPatient()" :disabled="!newPatient.name || creatingPatient"
                        class="px-5 py-2.5 rounded-lg text-sm font-bold text-white transition-all disabled:opacity-50"
                        style="background:linear-gradient(135deg,#10b981,#059669);box-shadow:0 4px 14px rgba(16,185,129,0.25);">
                        <span x-show="!creatingPatient">Create & Select</span>
                        <span x-show="creatingPatient">Creating...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function addSerialApp() {
    return {
        searchQuery: '',
        results: [],
        showDropdown: false,
        selectedPatient: null,
        priority: 'normal',
        showCreateModal: false,
        creatingPatient: false,
        createError: '',
        newPatient: { name: '', phone: '', gender: '', age: '', address: '' },

        async searchPatients() {
            if (this.searchQuery.length < 1) { this.results = []; this.showDropdown = false; return; }
            try {
                const res = await fetch('{{ route("assistant.smart-serial.search-patients") }}?q=' + encodeURIComponent(this.searchQuery) + '&doctor_id={{ $doctorId }}', {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                this.results = await res.json();
                this.showDropdown = this.results.length > 0;
            } catch (e) { this.results = []; }
        },

        selectPatient(patient) {
            this.selectedPatient = patient;
            this.searchQuery = patient.name;
            this.showDropdown = false;
            this.results = [];
        },

        clearPatient() {
            this.selectedPatient = null;
            this.searchQuery = '';
            this.results = [];
        },

        async createPatient() {
            if (!this.newPatient.name) return;
            this.creatingPatient = true;
            this.createError = '';
            try {
                const data = new URLSearchParams();
                data.append('name', this.newPatient.name);
                data.append('phone', this.newPatient.phone);
                data.append('gender', this.newPatient.gender);
                data.append('age', this.newPatient.age);
                data.append('address', this.newPatient.address);
                data.append('_token', '{{ csrf_token() }}');

                const res = await fetch('{{ route("assistant.patients.quick-add") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: data
                });
                const result = await res.json();
                if (result.success && result.patient) {
                    this.selectedPatient = {
                        id: result.patient.id,
                        name: result.patient.name,
                        phone: result.patient.phone,
                        patient_number: '',
                        gender: result.patient.gender,
                        date_of_birth: ''
                    };
                    this.searchQuery = result.patient.name;
                    this.showCreateModal = false;
                    this.newPatient = { name: '', phone: '', gender: '', age: '', address: '' };
                } else {
                    this.createError = result.message || 'Failed to create patient.';
                }
            } catch (e) {
                this.createError = 'Network error. Please try again.';
            } finally {
                this.creatingPatient = false;
            }
        }
    };
}
</script>
@endsection
