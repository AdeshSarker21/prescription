@extends('doctor.layouts.app')
@section('title', 'Add Serial')
@section('header', 'Add Serial')

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="addSerialApp()">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Add Serial</h1>
            <p class="text-gray-500 mt-1">Add a patient to the queue</p>
        </div>
        <a href="{{ route('doctor.smart-serial.index', $activeChamberId ? ['chamber_id' => $activeChamberId] : []) }}" class="text-sm text-blue-600 hover:underline">&larr; Back to Queue</a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">{{ session('error') }}</div>
    @endif

    @if(!$session)
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
            <svg class="w-12 h-12 mx-auto mb-3 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
            <p class="text-yellow-800 font-medium">No active session today</p>
            <p class="text-yellow-600 text-sm mt-1">Start a session first before adding patients to the queue.</p>
            <a href="{{ route('doctor.smart-serial.index') }}" class="mt-4 inline-block px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 text-sm font-medium">Start Session</a>
        </div>
    @else
        <form method="POST" action="{{ route('doctor.smart-serial.add-patient') }}" class="bg-white rounded-xl shadow p-6 space-y-5">
            @csrf

            {{-- Patient Search --}}
            <div class="relative">
                <label class="block text-sm font-medium text-gray-700 mb-1">Patient <span class="text-red-500">*</span></label>
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <input type="text" x-model="searchQuery" @input.debounce.300ms="searchPatients()" @focus="showDropdown = results.length > 0" placeholder="Search by name, phone, or patient number..." class="w-full border rounded-lg px-3 py-2 pr-10">
                        <svg class="w-5 h-5 text-gray-400 absolute right-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>

                        {{-- Search Results Dropdown --}}
                        <div x-show="showDropdown && results.length > 0" x-cloak @click.away="showDropdown = false"
                             class="absolute z-50 w-full mt-1 bg-white border rounded-lg shadow-lg max-h-60 overflow-y-auto">
                            <template x-for="patient in results" :key="patient.id">
                                <div class="px-3 py-2 cursor-pointer hover:bg-blue-50 border-b last:border-b-0" @click="selectPatient(patient)">
                                    <div class="font-medium text-sm text-gray-900" x-text="patient.name"></div>
                                    <div class="text-xs text-gray-500">
                                        <span x-text="patient.phone || 'No phone'"></span>
                                        <span x-show="patient.patient_number"> &bull; <span x-text="patient.patient_number"></span></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                    <button type="button" @click="showCreateModal = true" class="px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium whitespace-nowrap" title="Add new patient">
                        + New Patient
                    </button>
                </div>

                {{-- Hidden input for selected patient_id --}}
                <input type="hidden" name="patient_id" :value="selectedPatient?.id" x-ref="patientIdInput">

                {{-- Selected Patient Display --}}
                <div x-show="selectedPatient" x-cloak class="mt-2 p-3 bg-blue-50 rounded-lg flex items-center justify-between">
                    <div>
                        <span class="font-medium text-blue-900" x-text="selectedPatient?.name"></span>
                        <span class="text-sm text-blue-700 ml-2" x-text="selectedPatient?.phone || ''"></span>
                        <span class="text-xs text-blue-500 ml-2" x-text="selectedPatient?.patient_number || ''"></span>
                    </div>
                    <button type="button" @click="clearPatient()" class="text-blue-400 hover:text-blue-600">&times;</button>
                </div>
                @error('patient_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Patient Name & Phone (read-only, auto-filled from selection) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Patient Name</label>
                    <input type="text" :value="selectedPatient?.name || ''" readonly class="mt-1 block w-full border rounded-lg px-3 py-2 bg-gray-50 text-gray-600 cursor-not-allowed" placeholder="Auto-filled from patient">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Phone</label>
                    <input type="text" :value="selectedPatient?.phone || ''" readonly class="mt-1 block w-full border rounded-lg px-3 py-2 bg-gray-50 text-gray-600 cursor-not-allowed" placeholder="Auto-filled from patient">
                </div>
            </div>

            {{-- Doctor & Chamber --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Doctor</label>
                    <input type="text" value="{{ $doctor->name }}" readonly class="mt-1 block w-full border rounded-lg px-3 py-2 bg-gray-50 text-gray-600 cursor-not-allowed">
                    <input type="hidden" name="doctor_id" value="{{ $doctor->id }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Chamber</label>
                    @if($session->chamber)
                        <input type="text" value="{{ $session->chamber->name }}" readonly class="mt-1 block w-full border rounded-lg px-3 py-2 bg-gray-50 text-gray-600 cursor-not-allowed">
                    @else
                        <select name="chamber_id" class="mt-1 block w-full border rounded-lg px-3 py-2">
                            <option value="">No chamber</option>
                            @foreach($chambers as $chamber)
                                <option value="{{ $chamber->id }}" {{ $activeChamberId == $chamber->id ? 'selected' : '' }}>{{ $chamber->name }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
            </div>

            {{-- Date & Serial Number --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Date</label>
                    <input type="text" value="{{ now()->format('d M, Y') }}" readonly class="mt-1 block w-full border rounded-lg px-3 py-2 bg-gray-50 text-gray-600 cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Serial Number</label>
                    <input type="text" value="#{{ $nextSerial }}" readonly class="mt-1 block w-full border rounded-lg px-3 py-2 bg-gray-50 text-gray-600 cursor-not-allowed font-bold text-lg">
                </div>
            </div>

            {{-- Serial Type (Priority) --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Serial Type</label>
                <div class="flex gap-3">
                    <label class="flex items-center gap-2 px-4 py-2 border rounded-lg cursor-pointer transition-all" :class="priority === 'normal' ? 'bg-blue-100 border-blue-400 text-blue-800' : 'hover:bg-gray-50'">
                        <input type="radio" name="priority" value="normal" x-model="priority" class="hidden">
                        <span class="w-3 h-3 rounded-full" style="background:#3b82f6;"></span>
                        Normal
                    </label>
                    <label class="flex items-center gap-2 px-4 py-2 border rounded-lg cursor-pointer transition-all" :class="priority === 'urgent' ? 'bg-orange-100 border-orange-400 text-orange-800' : 'hover:bg-gray-50'">
                        <input type="radio" name="priority" value="urgent" x-model="priority" class="hidden">
                        <span class="w-3 h-3 rounded-full" style="background:#f97316;"></span>
                        Urgent
                    </label>
                    <label class="flex items-center gap-2 px-4 py-2 border rounded-lg cursor-pointer transition-all" :class="priority === 'vip' ? 'bg-purple-100 border-purple-400 text-purple-800' : 'hover:bg-gray-50'">
                        <input type="radio" name="priority" value="vip" x-model="priority" class="hidden">
                        <span class="w-3 h-3 rounded-full" style="background:#a855f7;"></span>
                        VIP
                    </label>
                </div>
            </div>

            {{-- Notes --}}
            <div>
                <label class="block text-sm font-medium text-gray-700">Notes</label>
                <textarea name="notes" rows="2" maxlength="500" class="mt-1 block w-full border rounded-lg px-3 py-2" placeholder="Optional notes about this patient..."></textarea>
            </div>

            {{-- Submit --}}
            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('doctor.smart-serial.index', $activeChamberId ? ['chamber_id' => $activeChamberId] : []) }}" class="px-5 py-2 border rounded-lg text-gray-700 hover:bg-gray-50 font-medium">Cancel</a>
                <button type="submit" :disabled="!selectedPatient" :class="selectedPatient ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-400 cursor-not-allowed'" class="px-6 py-2 text-white rounded-lg font-medium transition-all">
                    Add to Queue
                </button>
            </div>
        </form>
    @endif

    {{-- Create Patient Modal --}}
    <div x-show="showCreateModal" x-cloak style="display:none;" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showCreateModal = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Add New Patient</h3>
                    <button @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" x-model="newPatient.name" class="w-full border rounded-lg px-3 py-2" placeholder="Patient full name">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Phone</label>
                            <input type="text" x-model="newPatient.phone" class="w-full border rounded-lg px-3 py-2" placeholder="Phone number">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Gender</label>
                            <select x-model="newPatient.gender" class="w-full border rounded-lg px-3 py-2">
                                <option value="">Select</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Age</label>
                            <input type="number" x-model="newPatient.age" min="0" max="150" class="w-full border rounded-lg px-3 py-2" placeholder="Age">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Address</label>
                            <input type="text" x-model="newPatient.address" class="w-full border rounded-lg px-3 py-2" placeholder="Address">
                        </div>
                    </div>
                </div>
                <div x-show="createError" class="text-red-600 text-sm bg-red-50 p-2 rounded" x-text="createError"></div>
                <div class="flex justify-end gap-3 pt-2">
                    <button @click="showCreateModal = false" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50 text-sm">Cancel</button>
                    <button @click="createPatient()" :disabled="!newPatient.name || creatingPatient" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium disabled:opacity-50">
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
                const res = await fetch('{{ route("doctor.smart-serial.search-patients") }}?q=' + encodeURIComponent(this.searchQuery), {
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

                const res = await fetch('{{ route("doctor.patients.quick-add") }}', {
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
