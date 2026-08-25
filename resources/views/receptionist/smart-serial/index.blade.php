@extends('assistant.layouts.app')

@section('title', 'Receptionist - Smart Serial')
@section('header', 'Smart Serial Queue')

@section('content')
<div class="space-y-6" x-data="receptionistQueue()" x-init="init()">

    {{-- Chamber Tabs --}}
    @if($chambers->count() > 0)
    <div class="flex items-center gap-2 flex-wrap">
        <span class="text-sm font-semibold text-gray-500">Chambers:</span>
        @foreach($chambers as $chamber)
            <a href="{{ route('assistant.receptionist.smart-serial.index', ['chamber_id' => $chamber->id]) }}"
               class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ ($activeChamberId == $chamber->id || (!$activeChamberId && $loop->first)) ? 'bg-blue-600 text-white shadow-md' : 'bg-white/50 text-gray-600 hover:bg-white/70' }}">
                {{ $chamber->serial_prefix ? $chamber->serial_prefix . ' - ' : '' }}{{ $chamber->name }}
            </a>
        @endforeach
    </div>
    @endif

    {{-- Session Info --}}
    @if($session)
    <div class="bg-white rounded-xl shadow p-4 flex items-center justify-between flex-wrap gap-4" style="border-left:4px solid #6366f1;">
        <div class="flex items-center gap-4">
            <div>
                <p class="text-sm font-medium text-gray-500">Session Active</p>
                <p class="text-lg font-bold text-gray-900">Dr. {{ $doctorName }} | {{ $chamberName }} | Started {{ $session->started_at->format('h:i A') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $session->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                {{ ucfirst($session->status) }}
            </span>
        </div>
    </div>
    @endif

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ session('error') }}</div>
    @endif

    @if($session)
        {{-- Stats Row --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl shadow p-4 text-center" style="border-left:4px solid #6366f1;">
                <div class="text-3xl font-bold text-indigo-600">{{ $stats['total'] }}</div>
                <div class="text-sm text-gray-500">Total</div>
            </div>
            <div class="bg-white rounded-xl shadow p-4 text-center" style="border-left:4px solid #f59e0b;">
                <div class="text-3xl font-bold text-yellow-500">{{ $stats['waiting'] }}</div>
                <div class="text-sm text-gray-500">Waiting</div>
            </div>
            <div class="bg-white rounded-xl shadow p-4 text-center" style="border-left:4px solid #f97316;">
                <div class="text-3xl font-bold text-orange-500">{{ $stats['calling'] + $stats['inside'] }}</div>
                <div class="text-sm text-gray-500">In Progress</div>
            </div>
            <div class="bg-white rounded-xl shadow p-4 text-center" style="border-left:4px solid #22c55e;">
                <div class="text-3xl font-bold text-green-600">{{ $stats['completed'] }}</div>
                <div class="text-sm text-gray-500">Completed</div>
            </div>
        </div>

        {{-- Add Patient Card --}}
        <div class="bg-white rounded-xl shadow p-5" x-show="showAddForm" x-transition>
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-900">Add Patient to Queue</h3>
                <button @click="showAddForm = false" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <form method="POST" action="{{ route('assistant.receptionist.smart-serial.add-patient') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search Patient</label>
                        <div class="relative">
                            <input type="text" x-model="searchQuery" @input.debounce.300ms="searchPatients()" placeholder="Search by name, phone, or ID..."
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <div x-show="searchResults.length > 0" class="absolute z-10 w-full bg-white border border-gray-200 rounded-lg mt-1 shadow-lg max-h-60 overflow-y-auto">
                                <template x-for="patient in searchResults" :key="patient.id">
                                    <div @click="selectPatient(patient)" class="px-4 py-3 hover:bg-indigo-50 cursor-pointer border-b border-gray-100 last:border-0">
                                        <div class="font-medium text-gray-900" x-text="patient.name"></div>
                                        <div class="text-sm text-gray-500" x-text="patient.phone || patient.patient_id || ''"></div>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <input type="hidden" name="patient_id" :value="selectedPatientId">
                        <div x-show="selectedPatientName" class="mt-2 text-sm text-indigo-600 font-medium">
                            Selected: <span x-text="selectedPatientName"></span>
                            <button type="button" @click="clearPatient()" class="ml-2 text-red-500 hover:text-red-700">&times;</button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                        <select name="priority" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500">
                            <option value="normal">Normal</option>
                            <option value="urgent">Urgent</option>
                            <option value="vip">VIP</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes (optional)</label>
                    <input type="text" name="notes" maxlength="500" placeholder="Any notes..."
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex gap-2">
                    <button type="submit" :disabled="!selectedPatientId"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium">
                        Add to Queue
                    </button>
                    <button type="button" @click="showAddForm = false" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium">
                        Cancel
                    </button>
                </div>
            </form>
        </div>

        {{-- Queue Table --}}
        <div class="bg-white rounded-xl shadow">
            <div class="p-4 border-b flex items-center justify-between">
                <h3 class="font-semibold text-gray-900">Queue ({{ $queue->count() }} patients)</h3>
                <button @click="showAddForm = !showAddForm" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                    + Add Patient
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Serial</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Patient</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Priority</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($queue as $item)
                        <tr class="{{ $item->status === 'calling' ? 'bg-yellow-50' : ($item->status === 'inside' ? 'bg-blue-50' : ($item->status === 'preparing' ? 'bg-purple-50' : '')) }}">
                            <td class="px-4 py-3 font-bold text-lg text-gray-900">
                                {{ $item->formatted_serial ?? str_pad($item->serial_number, 3, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $item->patient->name ?? 'N/A' }}</div>
                                @if($item->patient && $item->patient->phone)
                                    <div class="text-xs text-gray-500">{{ $item->patient->phone }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($item->priority === 'emergency')
                                    <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-bold">EMERGENCY</span>
                                @elseif($item->priority === 'urgent')
                                    <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded-full text-xs font-bold">Urgent</span>
                                @elseif($item->priority === 'vip')
                                    <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-bold">VIP</span>
                                @else
                                    <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-full text-xs">Normal</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($item->status === 'waiting')
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs">Waiting</span>
                                @elseif($item->status === 'preparing')
                                    <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded-full text-xs">Preparing</span>
                                @elseif($item->status === 'calling')
                                    <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded-full text-xs font-bold">Calling</span>
                                @elseif($item->status === 'inside')
                                    <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs">Inside</span>
                                @elseif($item->status === 'completed')
                                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs">Completed</span>
                                @elseif($item->status === 'skipped')
                                    <span class="px-2 py-1 bg-gray-200 text-gray-500 rounded-full text-xs">Skipped</span>
                                @elseif($item->status === 'cancelled')
                                    <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs">Cancelled</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-1 flex-wrap">
                                    @if(!in_array($item->status, ['completed','cancelled','skipped']))
                                        <button @click="openEditModal({{ $item->id }}, '{{ $item->priority }}', '{{ addslashes($item->notes ?? '') }}')"
                                                class="px-2 py-1 bg-indigo-500 text-white rounded text-xs hover:bg-indigo-600" title="Edit">
                                            Edit
                                        </button>
                                    @endif
                                    @if(!in_array($item->status, ['completed','cancelled','skipped']))
                                        <form method="POST" action="{{ route('assistant.receptionist.smart-serial.cancel', $item->id) }}" onsubmit="return confirm('Cancel this queue entry?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="px-2 py-1 bg-red-500 text-white rounded text-xs hover:bg-red-600" title="Cancel">Cancel</button>
                                        </form>
                                    @endif
                                    @if(!in_array($item->status, ['cancelled','skipped']))
                                        <a href="{{ route('assistant.receptionist.smart-serial.print-token', $item->id) }}" target="_blank"
                                           class="px-2 py-1 bg-teal-500 text-white rounded text-xs hover:bg-teal-600 inline-block" title="Print Token">&#128424; Print</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-gray-400">
                                <p class="text-lg">No patients in queue</p>
                                <p class="text-sm mt-1">Click "Add Patient" to add a patient to the queue.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Edit Modal --}}
        <div x-show="editModalOpen" x-transition class="fixed inset-0 z-50 overflow-y-auto" style="display:none;">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="editModalOpen = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Edit Queue Entry</h3>
                    <form :action="'{{ url('assistant/receptionist/smart-serial/queue') }}/' + editId + '/update'" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                                <select name="priority" x-model="editPriority" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500">
                                    <option value="normal">Normal</option>
                                    <option value="urgent">Urgent</option>
                                    <option value="vip">VIP</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                <input type="text" name="notes" x-model="editNotes" maxlength="500" placeholder="Notes..."
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>
                        <div class="flex gap-2 mt-6">
                            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium">Save Changes</button>
                            <button type="button" @click="editModalOpen = false" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    @else
        <div class="bg-white rounded-xl shadow p-12 text-center text-gray-400">
            <p class="text-lg">No active session found.</p>
            <p class="text-sm mt-1">Please ask the doctor to start a session first.</p>
        </div>
    @endif
</div>

@push('scripts')
<script>
function receptionistQueue() {
    return {
        showAddForm: false,
        searchQuery: '',
        searchResults: [],
        selectedPatientId: null,
        selectedPatientName: '',
        editModalOpen: false,
        editId: null,
        editPriority: 'normal',
        editNotes: '',

        init() {},

        async searchPatients() {
            if (this.searchQuery.length < 2) {
                this.searchResults = [];
                return;
            }
            try {
                const res = await fetch('{{ route("assistant.receptionist.smart-serial.search-patients") }}?q=' + encodeURIComponent(this.searchQuery));
                this.searchResults = await res.json();
            } catch(e) {
                this.searchResults = [];
            }
        },

        selectPatient(patient) {
            this.selectedPatientId = patient.id;
            this.selectedPatientName = patient.name + (patient.phone ? ' (' + patient.phone + ')' : '');
            this.searchQuery = '';
            this.searchResults = [];
        },

        clearPatient() {
            this.selectedPatientId = null;
            this.selectedPatientName = '';
        },

        openEditModal(id, priority, notes) {
            this.editId = id;
            this.editPriority = priority;
            this.editNotes = notes;
            this.editModalOpen = true;
        }
    };
}
</script>
@endpush
@endsection
