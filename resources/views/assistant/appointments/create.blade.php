@extends('assistant.layouts.app')

@section('title', 'Book Appointment')
@section('header', 'Book New Appointment')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-bold text-gray-800">New Appointment</h3>
        <a href="{{ route('assistant.appointments.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">&larr; Back</a>
    </div>

    <form method="POST" action="{{ route('assistant.appointments.store') }}" x-data="appointmentForm()">
        @csrf

        {{-- Doctor & Patient --}}
        <div class="dashboard-card mb-5">
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-200/50">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <span class="text-sm font-semibold text-gray-700">Doctor & Patient</span>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Doctor *</label>
                    <select name="doctor_id" x-model="doctorId" @change="loadAvailability()" required
                            class="w-full px-3 py-2.5 text-sm rounded-lg bg-white/60 border border-white/40 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                        <option value="">Select Doctor</option>
                        @foreach($doctors as $doc)
                            <option value="{{ $doc->id }}" {{ $selectedDoctor == $doc->id ? 'selected' : '' }}>{{ $doc->name }} - {{ $doc->specialization ?? 'General' }}</option>
                        @endforeach
                    </select>
                    @error('doctor_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Patient *</label>
                    <select name="patient_id" required
                            class="w-full px-3 py-2.5 text-sm rounded-lg bg-white/60 border border-white/40 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                        <option value="">Select Patient</option>
                        @foreach($patients as $pat)
                            <option value="{{ $pat->id }}" {{ old('patient_id') == $pat->id ? 'selected' : '' }}>{{ $pat->name }} ({{ $pat->phone ?? 'No phone' }})</option>
                        @endforeach
                    </select>
                    @error('patient_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Date & Time --}}
        <div class="dashboard-card mb-5">
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-200/50">
                <div class="w-8 h-8 rounded-lg bg-cyan-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <span class="text-sm font-semibold text-gray-700">Schedule</span>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Date *</label>
                    <input type="date" name="appointment_date" x-model="date" @change="loadAvailability()"
                           min="{{ now()->format('Y-m-d') }}" required
                           class="w-full px-3 py-2.5 text-sm rounded-lg bg-white/60 border border-white/40 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                    @error('appointment_date') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Time *</label>
                    <select name="time" required
                            class="w-full px-3 py-2.5 text-sm rounded-lg bg-white/60 border border-white/40 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                        <option value="">Select Time</option>
                        @foreach(range(8, 20) as $hour)
                            @foreach([0, 30] as $min)
                                @php $time = sprintf('%02d:%02d', $hour, $min); @endphp
                                <option value="{{ $time }}" {{ in_array($time, $bookedSlots) ? 'disabled style="color:#ccc"' : '' }}>
                                    {{ \Carbon\Carbon::parse($time)->format('h:i A') }}
                                    {{ in_array($time, $bookedSlots) ? '(Booked)' : '' }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Reason --}}
        <div class="dashboard-card mb-5">
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-200/50">
                <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <span class="text-sm font-semibold text-gray-700">Reason / Notes</span>
            </div>
            <div>
                <textarea name="reason" rows="3" placeholder="Reason for visit..."
                          class="w-full px-3 py-2.5 text-sm rounded-lg bg-white/60 border border-white/40 focus:ring-2 focus:ring-indigo-400 focus:outline-none resize-none">{{ old('reason') }}</textarea>
                @error('reason') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-3">
            <button type="submit" class="btn-gradient">Book Appointment</button>
            <a href="{{ route('assistant.appointments.index') }}" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 rounded-lg hover:bg-white/30 transition-colors">Cancel</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
function appointmentForm() {
    return {
        doctorId: '{{ $selectedDoctor }}',
        date: '{{ old('appointment_date', now()->format('Y-m-d')) }}',
        bookedSlots: [],
        async loadAvailability() {
            if (!this.doctorId || !this.date) return;
            try {
                const res = await fetch(`{{ url('assistant/doctor') }}/${this.doctorId}/availability?date=${this.date}`);
                const data = await res.json();
                this.bookedSlots = data.booked_slots || [];
            } catch(e) {
                this.bookedSlots = [];
            }
        },
        init() {
            this.loadAvailability();
        }
    };
}
</script>
@endpush
@endsection
