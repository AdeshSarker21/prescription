@extends('assistant.layouts.app')

@section('title', 'Smart Serial Queue')
@section('header', 'Smart Serial Queue')

@section('content')
<div class="space-y-6" x-data="serialQueue()" x-init="init()">

    {{-- Doctor Selector --}}
    @if($doctors->count() > 1)
    <div class="dashboard-card animate-card" style="border-left:4px solid #6366f1;">
        <div class="flex items-center gap-4 flex-wrap">
            <span class="text-sm font-semibold" style="color:var(--text-muted);">Select Doctor:</span>
            @foreach($doctors as $doc)
                <a href="{{ route('assistant.smart-serial.index', ['doctor_id' => $doc->id]) }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ ($selectedDoctorId == $doc->id) ? 'bg-blue-600 text-white shadow-md' : 'bg-white/50 text-gray-600 hover:bg-white/70' }}">
                    {{ $doc->name }}@if($doc->clinic_name) - {{ $doc->clinic_name }}@endif
                </a>
            @endforeach
        </div>
    </div>
    @endif

    @if(!$selectedDoctorId)
    <div class="dashboard-card animate-card p-12 text-center">
        <div class="stat-icon mx-auto mb-4" style="width:80px;height:80px;border-radius:20px;background:linear-gradient(135deg,rgba(99,102,241,0.1),rgba(99,102,241,0.05));color:#6366f1;">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        </div>
        <h3 class="text-xl font-bold" style="color:var(--text-primary);">Select a Doctor</h3>
        <p class="mt-2" style="color:var(--text-muted);">Choose which doctor's queue you want to manage.</p>
    </div>
    @else

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Smart Serial Queue</h1>
            <p class="text-gray-500 mt-1">Manage patient queue</p>
        </div>
        <div class="flex gap-2 items-center flex-wrap">
            @if($session)
                <a href="{{ route('assistant.smart-serial.dashboard', ['doctor_id' => $selectedDoctorId]) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">Dashboard</a>
                <a href="{{ route('assistant.smart-serial.display.doctor', $session->doctor_id) }}" target="_blank" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">&#128250; Display</a>
                <a href="{{ route('assistant.smart-serial.history', ['doctor_id' => $selectedDoctorId]) }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-sm font-medium">History</a>
            @endif
            @if($session && $session->status === 'active')
                <form method="POST" action="{{ route('assistant.smart-serial.pause', $session->id) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 text-sm font-medium">Pause</button>
                </form>
                <form method="POST" action="{{ route('assistant.smart-serial.close', $session->id) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 text-sm font-medium">Close</button>
                </form>
            @elseif($session && $session->status === 'paused')
                <form method="POST" action="{{ route('assistant.smart-serial.resume', $session->id) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 text-sm font-medium">Resume</button>
                </form>
            @else
                <form method="POST" action="{{ route('assistant.smart-serial.start') }}">
                    @csrf
                    <input type="hidden" name="doctor_id" value="{{ $selectedDoctorId }}">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">Start Session</button>
                </form>
            @endif
        </div>
    </div>

    {{-- Stats --}}
    @if($session)
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-4">
            <div class="dashboard-card animate-card" style="border-left:4px solid #6366f1;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium" style="color:var(--text-muted);">Total</p>
                        <p class="stat-value text-2xl" style="color:#6366f1;" x-text="stats.total">{{ $stats['total'] }}</p>
                    </div>
                </div>
            </div>
            <div class="dashboard-card animate-card" style="border-left:4px solid #f59e0b;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium" style="color:var(--text-muted);">Waiting</p>
                        <p class="stat-value text-2xl" style="color:#d97706;" x-text="stats.waiting">{{ $stats['waiting'] }}</p>
                    </div>
                </div>
            </div>
            <div class="dashboard-card animate-card" style="border-left:4px solid #f97316;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium" style="color:var(--text-muted);">In Progress</p>
                        <p class="stat-value text-2xl" style="color:#ea580c;" x-text="stats.calling + stats.inside">{{ $stats['calling'] + $stats['inside'] }}</p>
                    </div>
                </div>
            </div>
            <div class="dashboard-card animate-card" style="border-left:4px solid #10b981;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium" style="color:var(--text-muted);">Completed</p>
                        <p class="stat-value text-2xl" style="color:#059669;" x-text="stats.completed">{{ $stats['completed'] }}</p>
                    </div>
                </div>
            </div>
            <div class="dashboard-card animate-card" style="border-left:4px solid #8b5cf6;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium" style="color:var(--text-muted);">Emergency</p>
                        <p class="stat-value text-2xl" style="color:#7c3aed;" x-text="stats.emergency">{{ $stats['emergency'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Add Patient Section --}}
        @if(in_array('create_serial', $permissions))
        <div class="dashboard-card animate-card" style="border-left:4px solid #3b82f6;">
            <h3 class="font-semibold mb-3" style="color:var(--text-primary);">Add Patient to Queue</h3>
            <form method="POST" action="{{ route('assistant.smart-serial.add-patient') }}" class="space-y-3">
                @csrf
                <input type="hidden" name="doctor_id" value="{{ $selectedDoctorId }}">
                <div class="flex gap-3 items-end flex-wrap">
                    <div class="flex-1 min-w-[200px]" x-data="{ searchQuery: '', results: [], showDropdown: false, selectedPatient: null }">
                        <label class="block text-sm font-medium text-gray-700">Search Patient</label>
                        <div class="relative">
                            <input type="text" x-model="searchQuery" @input.debounce.300ms="await searchPatients()" @focus="showDropdown = results.length > 0" placeholder="Search by name, phone, or number..."
                                class="mt-1 block w-full border rounded-lg px-3 py-2 text-sm" autocomplete="off">
                            <input type="hidden" name="patient_id" :value="selectedPatient?.id">
                            <div x-show="showDropdown && results.length > 0" @click.away="showDropdown = false" class="absolute z-50 w-full mt-1 bg-white border rounded-lg shadow-lg max-h-60 overflow-y-auto" x-cloak>
                                <template x-for="patient in results" :key="patient.id">
                                    <div @click="selectPatient(patient)" class="px-3 py-2 hover:bg-indigo-50 cursor-pointer border-b last:border-0">
                                        <p class="text-sm font-medium" x-text="patient.name"></p>
                                        <p class="text-xs text-gray-500" x-text="(patient.phone || '') + (patient.patient_number ? ' | #' + patient.patient_number : '')"></p>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <div x-show="selectedPatient" class="mt-1 text-sm text-green-600 font-medium" x-cloak>
                            Selected: <span x-text="selectedPatient?.name"></span>
                            <button type="button" @click="selectedPatient = null; searchQuery = ''" class="text-red-500 ml-2 text-xs">Clear</button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Priority</label>
                        <select name="priority" class="mt-1 border rounded-lg px-3 py-2 text-sm">
                            <option value="normal">Normal</option>
                            <option value="urgent">Urgent</option>
                            <option value="vip">VIP</option>
                        </select>
                    </div>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">Add to Queue</button>
                </div>
            </form>
        </div>
        @endif

        {{-- Queue Table --}}
        <div class="dashboard-card animate-card">
            <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
                <h3 class="font-semibold" style="color:var(--text-primary);">
                    Queue (<span x-text="queue.length">{{ $queue->count() }}</span> patients)
                </h3>
                <div class="flex gap-2 flex-wrap">
                    @if(in_array('prepare', $permissions) && $session->status === 'active')
                    <form method="POST" action="{{ route('assistant.smart-serial.prepare', $session->id) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="doctor_id" value="{{ $selectedDoctorId }}">
                        <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 text-sm font-medium flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                            Prepare Next
                        </button>
                    </form>
                    @endif
                    @if(in_array('call_next', $permissions) && $session->status === 'active')
                    <form method="POST" action="{{ route('assistant.smart-serial.call-next', $session->id) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="doctor_id" value="{{ $selectedDoctorId }}">
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/></svg>
                            Call Next
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            <div class="glass-table">
                <table>
                    <thead>
                        <tr>
                            <th>Serial</th>
                            <th>Patient</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Time</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="item in queue" :key="item.id">
                            <tr :class="{
                                'bg-yellow-50': item.status === 'calling',
                                'bg-blue-50': item.status === 'inside',
                                'bg-green-50': item.status === 'completed',
                                'bg-purple-50': item.status === 'preparing',
                                'bg-red-50': item.priority === 'emergency' && item.status !== 'completed' && item.status !== 'cancelled',
                                'opacity-50': item.status === 'cancelled' || item.status === 'skipped'
                            }">
                                <td>
                                    <span class="font-bold text-lg" style="color:var(--text-primary);" x-text="'#' + (item.formatted_serial || String(item.serial_number).padStart(3, '0'))"></span>
                                </td>
                                <td>
                                    <span class="font-medium" style="color:var(--text-primary);" x-text="item.patient?.name || 'N/A'"></span>
                                </td>
                                <td>
                                    <span class="px-2 py-1 rounded-full text-xs font-bold"
                                        :class="{
                                            'bg-red-100 text-red-700': item.priority === 'emergency',
                                            'bg-orange-100 text-orange-700': item.priority === 'urgent',
                                            'bg-purple-100 text-purple-700': item.priority === 'vip',
                                            'bg-gray-100 text-gray-700': item.priority === 'normal'
                                        }" x-text="item.priority.toUpperCase()"></span>
                                </td>
                                <td>
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold"
                                        :class="{
                                            'bg-yellow-100 text-yellow-700': item.status === 'waiting',
                                            'bg-purple-100 text-purple-700': item.status === 'preparing',
                                            'bg-orange-100 text-orange-700': item.status === 'calling',
                                            'bg-blue-100 text-blue-700': item.status === 'inside',
                                            'bg-green-100 text-green-700': item.status === 'completed',
                                            'bg-gray-200 text-gray-500': item.status === 'skipped',
                                            'bg-red-100 text-red-700': item.status === 'cancelled',
                                            'bg-red-200 text-red-800': item.status === 'emergency'
                                        }" x-text="item.status.replace('_',' ').replace(/\b\w/g, l => l.toUpperCase())"></span>
                                </td>
                                <td style="color:var(--text-muted);font-size:12px;" x-text="new Date(item.created_at).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'})"></td>
                                <td>
                                    <div class="flex gap-1 flex-wrap">
                                        <template x-if="item.status === 'waiting' && hasPermission('prepare')">
                                            <form method="POST" :action="'/assistant/smart-serial/queue/' + item.id + '/prepare'">
                                                @csrf @method('PATCH')
                                                <button class="px-2 py-1 bg-purple-500 text-white rounded text-xs hover:bg-purple-600">Prepare</button>
                                            </form>
                                        </template>
                                        <template x-if="(item.status === 'waiting' || item.status === 'preparing') && hasPermission('call_next')">
                                            <form method="POST" :action="'/assistant/smart-serial/queue/' + item.id + '/call'">
                                                @csrf @method('PATCH')
                                                <button class="px-2 py-1 bg-blue-500 text-white rounded text-xs hover:bg-blue-600">Call</button>
                                            </form>
                                        </template>
                                        <template x-if="item.status === 'calling' && hasPermission('call_next')">
                                            <form method="POST" :action="'/assistant/smart-serial/queue/' + item.id + '/start-consultation'">
                                                @csrf @method('PATCH')
                                                <button class="px-2 py-1 bg-indigo-500 text-white rounded text-xs hover:bg-indigo-600">Start</button>
                                            </form>
                                        </template>
                                        <template x-if="item.status === 'inside' && hasPermission('complete')">
                                            <form method="POST" :action="'/assistant/smart-serial/queue/' + item.id + '/complete'">
                                                @csrf @method('PATCH')
                                                <button class="px-2 py-1 bg-green-500 text-white rounded text-xs hover:bg-green-600">Done</button>
                                            </form>
                                        </template>
                                        <template x-if="item.status !== 'completed' && item.status !== 'cancelled' && item.status !== 'skipped' && hasPermission('cancel_serial')">
                                            <form method="POST" :action="'/assistant/smart-serial/queue/' + item.id + '/cancel'">
                                                @csrf @method('DELETE')
                                                <button class="px-2 py-1 bg-red-500 text-white rounded text-xs hover:bg-red-600" title="Cancel">X</button>
                                            </form>
                                        </template>
                                        <template x-if="item.status !== 'completed' && item.status !== 'cancelled' && item.status !== 'skipped' && item.priority !== 'emergency' && hasPermission('emergency')">
                                            <form method="POST" :action="'/assistant/smart-serial/queue/' + item.id + '/emergency'">
                                                @csrf @method('PATCH')
                                                <button class="px-2 py-1 bg-red-700 text-white rounded text-xs hover:bg-red-800" title="Emergency">!</button>
                                            </form>
                                        </template>
                                        <template x-if="(item.status === 'calling' || item.status === 'completed') && hasPermission('recall')">
                                            <form method="POST" :action="'/assistant/smart-serial/queue/' + item.id + '/recall'">
                                                @csrf @method('PATCH')
                                                <button class="px-2 py-1 bg-yellow-500 text-white rounded text-xs hover:bg-yellow-600">Recall</button>
                                            </form>
                                        </template>
                                        <template x-if="item.status === 'calling' && hasPermission('skip')">
                                            <form method="POST" :action="'/assistant/smart-serial/queue/' + item.id + '/skip'">
                                                @csrf @method('PATCH')
                                                <button class="px-2 py-1 bg-gray-500 text-white rounded text-xs hover:bg-gray-600">Skip</button>
                                            </form>
                                        </template>
                                        <template x-if="item.status !== 'cancelled' && item.status !== 'skipped'">
                                            <a :href="'/assistant/smart-serial/queue/' + item.id + '/print-token'" target="_blank" class="px-2 py-1 bg-teal-500 text-white rounded text-xs hover:bg-teal-600" title="Print Token">&#128424;</a>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <template x-if="queue.length === 0">
                            <tr>
                                <td colspan="6" style="text-align:center;padding:32px;color:var(--text-muted);font-size:14px;">No patients in queue</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="dashboard-card animate-card p-12 text-center">
            <div class="stat-icon mx-auto mb-4" style="width:80px;height:80px;border-radius:20px;background:linear-gradient(135deg,rgba(99,102,241,0.1),rgba(99,102,241,0.05));color:#6366f1;">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="text-xl font-bold" style="color:var(--text-primary);">No Active Session</h3>
            <p class="mt-2" style="color:var(--text-muted);">Click "Start Session" to begin managing the queue.</p>
        </div>
    @endif

    @endif
</div>

<script>
function serialQueue() {
    return {
        queue: @js($queue->values()->toArray()),
        stats: @js($stats),
        permissions: @js($permissions),
        sessionId: @js($session?->id),
        doctorId: @js($selectedDoctorId),
        searchQuery: '',
        results: [],
        showDropdown: false,
        selectedPatient: null,
        refreshTimer: null,

        init() {
            this.refreshQueue();
            this.refreshTimer = setInterval(() => this.refreshQueue(), 5000);
        },

        hasPermission(perm) {
            return this.permissions.includes(perm);
        },

        async searchPatients() {
            if (this.searchQuery.length < 1) {
                this.results = [];
                this.showDropdown = false;
                return;
            }
            try {
                const res = await fetch(`/assistant/smart-serial/search-patients?q=${encodeURIComponent(this.searchQuery)}&doctor_id=${this.doctorId}`);
                this.results = await res.json();
                this.showDropdown = this.results.length > 0;
            } catch(e) {
                this.results = [];
            }
        },

        selectPatient(patient) {
            this.selectedPatient = patient;
            this.searchQuery = patient.name;
            this.results = [];
            this.showDropdown = false;
        },

        async refreshQueue() {
            if (!this.sessionId) return;
            try {
                const res = await fetch(`/assistant/smart-serial/${this.sessionId}/status`);
                const data = await res.json();
                if (data.queue) {
                    this.queue = data.queue;
                    this.stats = data.stats;
                }
            } catch(e) {}
        },

        destroy() {
            if (this.refreshTimer) clearInterval(this.refreshTimer);
        }
    }
}
</script>
@endsection
