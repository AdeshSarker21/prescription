@extends('doctor.layouts.app')

@section('title', $patient->name . ' - EMR Profile')

@section('header', 'EMR: ' . $patient->name)

@section('content')
<div class="space-y-6" x-data="{ tab: 'overview' }">
    {{-- Profile Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col sm:flex-row items-start gap-6">
            <div class="w-20 h-20 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-2xl flex-shrink-0">
                {{ substr($patient->name, 0, 2) }}
            </div>
            <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-medium">Patient ID</p>
                    <p class="text-sm font-semibold text-indigo-600">{{ $patient->patient_number ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase font-medium">Name</p>
                    <p class="text-sm font-semibold text-gray-900">{{ $patient->name }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase font-medium">Email</p>
                    <p class="text-sm text-gray-700">{{ $patient->email ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase font-medium">Phone</p>
                    <p class="text-sm text-gray-700">{{ $patient->phone ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase font-medium">Gender</p>
                    <p class="text-sm text-gray-700">{{ ucfirst($patient->gender ?? 'N/A') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase font-medium">Date of Birth</p>
                    <p class="text-sm text-gray-700">{{ $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->format('M d, Y') : 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase font-medium">Blood Group</p>
                    <p class="text-sm font-semibold text-gray-900">{{ $patient->blood_group ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase font-medium">Age</p>
                    <p class="text-sm text-gray-700">{{ $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->age . ' yrs' : 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase font-medium">Height / Weight</p>
                    <p class="text-sm text-gray-700">{{ $patient->height ? $patient->height . ' cm' : 'N/A' }} / {{ $patient->weight ? $patient->weight . ' kg' : 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase font-medium">Occupation</p>
                    <p class="text-sm text-gray-700">{{ $patient->occupation ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase font-medium">Marital Status</p>
                    <p class="text-sm text-gray-700">{{ $patient->marital_status ? ucfirst($patient->marital_status) : 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase font-medium">Emergency Contact</p>
                    <p class="text-sm text-gray-700">{{ $patient->emergency_contact ?? 'N/A' }}</p>
                </div>
            </div>
            <div class="flex-shrink-0 flex flex-col gap-2">
                <a href="{{ route('doctor.patients.edit', $patient) }}" class="inline-flex items-center px-3 py-1.5 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Edit</a>
                <a href="{{ route('doctor.prescriptions.create', ['patient_id' => $patient->id]) }}" class="inline-flex items-center px-3 py-1.5 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700">+ Rx</a>
            </div>
        </div>
        @if($patient->address)
        <div class="mt-3 text-sm text-gray-600"><span class="text-xs text-gray-500 uppercase font-medium">Address:</span> {{ $patient->address }}</div>
        @endif
    </div>

    {{-- EMR Tabs --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="border-b border-gray-200 overflow-x-auto">
            <nav class="flex whitespace-nowrap">
                <button @click="tab = 'overview'" :class="tab === 'overview' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-4 py-3 text-sm font-medium border-b-2 transition-colors">Overview</button>
                <button @click="tab = 'allergies'" :class="tab === 'allergies' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-4 py-3 text-sm font-medium border-b-2 transition-colors">Allergies @if($allergies->count() > 0)<span class="ml-1 text-xs bg-red-100 text-red-700 px-1.5 py-0.5 rounded-full">{{ $allergies->count() }}</span>@endif</button>
                <button @click="tab = 'medical-history'" :class="tab === 'medical-history' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-4 py-3 text-sm font-medium border-b-2 transition-colors">Medical History</button>
                <button @click="tab = 'diagnoses'" :class="tab === 'diagnoses' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-4 py-3 text-sm font-medium border-b-2 transition-colors">Diagnoses</button>
                <button @click="tab = 'prescriptions'" :class="tab === 'prescriptions' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-4 py-3 text-sm font-medium border-b-2 transition-colors">Prescriptions @if($prescriptions->count() > 0)<span class="ml-1 text-xs bg-indigo-100 text-indigo-700 px-1.5 py-0.5 rounded-full">{{ $prescriptions->count() }}</span>@endif</button>
                <button @click="tab = 'investigations'" :class="tab === 'investigations' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-4 py-3 text-sm font-medium border-b-2 transition-colors">Investigations</button>
                <button @click="tab = 'followups'" :class="tab === 'followups' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-4 py-3 text-sm font-medium border-b-2 transition-colors">Follow-ups @if($followUps->where('status', 'pending')->count() > 0)<span class="ml-1 text-xs bg-yellow-100 text-yellow-700 px-1.5 py-0.5 rounded-full">{{ $followUps->where('status', 'pending')->count() }}</span>@endif</button>
            </nav>
        </div>

        <div class="p-6">
            {{-- Overview Tab --}}
            <div x-show="tab === 'overview'">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 mb-3">Medical History</h4>
                        <p class="text-sm text-gray-700">{{ $patient->medical_history ?? 'No medical history recorded.' }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 mb-3">Recent Diagnoses</h4>
                        @forelse($diagnoses->take(5) as $diag)
                        <div class="flex items-center justify-between py-1.5 border-b border-gray-100 text-sm">
                            <span class="text-gray-900">{{ $diag->diagnosis }}</span>
                            <span class="text-xs text-gray-500">{{ $diag->diagnosed_date ? \Carbon\Carbon::parse($diag->diagnosed_date)->format('M d, Y') : '' }}</span>
                        </div>
                        @empty
                        <p class="text-sm text-gray-400">No diagnoses recorded.</p>
                        @endforelse
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 mb-3">Active Allergies</h4>
                        @forelse($allergies as $allergy)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium mr-1 mb-1
                            @if($allergy->severity === 'severe') bg-red-100 text-red-800
                            @elseif($allergy->severity === 'moderate') bg-yellow-100 text-yellow-800
                            @else bg-green-100 text-green-800 @endif">
                            {{ $allergy->allergy }}
                        </span>
                        @empty
                        <p class="text-sm text-gray-400">No allergies recorded.</p>
                        @endforelse
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 mb-3">Active Conditions</h4>
                        @forelse($medicalHistories->where('status', 'active') as $mh)
                        <div class="flex items-center gap-2 py-1 text-sm">
                            <span class="w-2 h-2 rounded-full bg-yellow-400 inline-block"></span>
                            <span class="text-gray-900">{{ $mh->condition_name }}</span>
                            <span class="text-xs text-gray-500">({{ $mh->diagnosed_date ? \Carbon\Carbon::parse($mh->diagnosed_date)->format('M Y') : '' }})</span>
                        </div>
                        @empty
                        <p class="text-sm text-gray-400">No active conditions.</p>
                        @endforelse
                    </div>
                </div>
            </div>

             {{-- Allergies Tab --}}
             <div x-show="tab === 'allergies'" x-cloak
                  x-data="{ editing: null }">
                 <div class="mb-4 flex items-center justify-between">
                     <h4 class="text-sm font-semibold text-gray-900">Allergies &amp; Reactions</h4>
                     <button @click="$refs.allergyForm.classList.toggle('hidden')" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">+ Add Allergy</button>
                 </div>
                 <form method="POST" action="{{ route('doctor.patients.allergies.store', $patient) }}" x-ref="allergyForm" class="hidden bg-gray-50 p-4 rounded-lg mb-4">
                     @csrf
                     <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                         <input type="text" name="allergy" placeholder="Allergy" required class="w-full border-gray-300 rounded-lg text-sm px-3 py-2">
                         <select name="severity" class="w-full border-gray-300 rounded-lg text-sm px-3 py-2">
                             <option value="mild">Mild</option>
                             <option value="moderate">Moderate</option>
                             <option value="severe">Severe</option>
                         </select>
                         <input type="text" name="reaction" placeholder="Reaction (e.g. rash)" class="w-full border-gray-300 rounded-lg text-sm px-3 py-2">
                         <button type="submit" class="bg-indigo-600 text-white rounded-lg text-sm px-4 py-2 hover:bg-indigo-700">Save</button>
                     </div>
                 </form>
                 @if($allergies->count() > 0)
                 <div class="overflow-x-auto">
                     <table class="min-w-full divide-y divide-gray-200">
                         <thead class="bg-gray-50">
                             <tr>
                                 <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Allergy</th>
                                 <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Severity</th>
                                 <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reaction</th>
                                 <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                                 <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
                             </tr>
                         </thead>
                         <tbody class="divide-y divide-gray-200">
                             @foreach($allergies as $allergy)
                             <tr class="hover:bg-gray-50" x-data="{ edit: false, allergy: '{{ $allergy->allergy }}', severity: '{{ $allergy->severity }}', reaction: '{{ $allergy->reaction ?? '' }}', notes: '{{ $allergy->notes ?? '' }}' }">
                                 {{-- View mode --}}
                                 <td x-show="!edit" class="px-4 py-3 text-sm font-medium text-gray-900">{{ $allergy->allergy }}</td>
                                 <td x-show="!edit" class="px-4 py-3">
                                     <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                         @if($allergy->severity === 'severe') bg-red-100 text-red-800
                                         @elseif($allergy->severity === 'moderate') bg-yellow-100 text-yellow-800
                                         @else bg-green-100 text-green-800 @endif">
                                         {{ ucfirst($allergy->severity) }}
                                     </span>
                                 </td>
                                 <td x-show="!edit" class="px-4 py-3 text-sm text-gray-600">{{ $allergy->reaction ?? '—' }}</td>
                                 <td x-show="!edit" class="px-4 py-3 text-sm text-gray-600">{{ $allergy->notes ?? '—' }}</td>
                                 <td x-show="!edit" class="px-4 py-3 text-right whitespace-nowrap">
                                     <button @click="edit = true" class="text-indigo-600 hover:text-indigo-800 text-sm mr-2">Edit</button>
                                     <form method="POST" action="{{ route('doctor.patients.allergies.destroy', [$patient, $allergy]) }}" onsubmit="return confirm('Remove this allergy?')" class="inline">
                                         @csrf @method('DELETE')
                                         <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Delete</button>
                                     </form>
                                 </td>
                                 {{-- Edit mode --}}
                                 <td x-show="edit" colspan="5" class="px-4 py-2">
                                     <form method="POST" action="{{ route('doctor.patients.allergies.update', [$patient, $allergy]) }}" class="flex items-center gap-2">
                                         @csrf @method('PATCH')
                                         <input type="text" name="allergy" x-model="allergy" required class="w-36 border-gray-300 rounded-lg text-xs px-2 py-1.5">
                                         <select name="severity" x-model="severity" class="w-24 border-gray-300 rounded-lg text-xs px-2 py-1.5">
                                             <option value="mild">Mild</option>
                                             <option value="moderate">Moderate</option>
                                             <option value="severe">Severe</option>
                                         </select>
                                         <input type="text" name="reaction" x-model="reaction" placeholder="Reaction" class="w-28 border-gray-300 rounded-lg text-xs px-2 py-1.5">
                                         <input type="text" name="notes" x-model="notes" placeholder="Notes" class="w-28 border-gray-300 rounded-lg text-xs px-2 py-1.5">
                                         <button type="submit" class="bg-indigo-600 text-white rounded text-xs px-2 py-1.5 hover:bg-indigo-700">Save</button>
                                         <button type="button" @click="edit = false" class="text-gray-500 hover:text-gray-700 text-xs">Cancel</button>
                                     </form>
                                 </td>
                             </tr>
                             @endforeach
                         </tbody>
                     </table>
                 </div>
                 @else
                 <p class="text-sm text-gray-400 py-8 text-center">No allergies recorded for this patient.</p>
                 @endif
             </div>

             {{-- Medical History Tab --}}
             <div x-show="tab === 'medical-history'" x-cloak>
                 <div class="mb-4 flex items-center justify-between">
                     <h4 class="text-sm font-semibold text-gray-900">Medical History</h4>
                     <button @click="$refs.mhForm.classList.toggle('hidden')" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">+ Add Condition</button>
                 </div>
                 <form method="POST" action="{{ route('doctor.patients.medical-histories.store', $patient) }}" x-ref="mhForm" class="hidden bg-gray-50 p-4 rounded-lg mb-4">
                     @csrf
                     <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                         <input type="text" name="condition_name" placeholder="Condition name" required class="w-full border-gray-300 rounded-lg text-sm px-3 py-2">
                         <input type="date" name="diagnosed_date" class="w-full border-gray-300 rounded-lg text-sm px-3 py-2">
                         <select name="status" class="w-full border-gray-300 rounded-lg text-sm px-3 py-2">
                             <option value="active">Active</option>
                             <option value="resolved">Resolved</option>
                         </select>
                         <button type="submit" class="bg-indigo-600 text-white rounded-lg text-sm px-4 py-2 hover:bg-indigo-700">Save</button>
                     </div>
                 </form>
                 @if($medicalHistories->count() > 0)
                 <div class="overflow-x-auto">
                     <table class="min-w-full divide-y divide-gray-200">
                         <thead class="bg-gray-50">
                             <tr>
                                 <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Condition</th>
                                 <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Diagnosed</th>
                                 <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                 <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                                 <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
                             </tr>
                         </thead>
                         <tbody class="divide-y divide-gray-200">
                             @foreach($medicalHistories as $mh)
                             <tr class="hover:bg-gray-50" x-data="{ edit: false, condition: '{{ $mh->condition_name }}', status: '{{ $mh->status }}', diagnosed_date: '{{ $mh->diagnosed_date ? \Carbon\Carbon::parse($mh->diagnosed_date)->format('Y-m-d') : '' }}', notes: '{{ $mh->notes ?? '' }}' }">
                                 {{-- View mode --}}
                                 <td x-show="!edit" class="px-4 py-3 text-sm font-medium text-gray-900">{{ $mh->condition_name }}</td>
                                 <td x-show="!edit" class="px-4 py-3 text-sm text-gray-600">{{ $mh->diagnosed_date ? \Carbon\Carbon::parse($mh->diagnosed_date)->format('M d, Y') : '—' }}</td>
                                 <td x-show="!edit" class="px-4 py-3">
                                     <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                         @if($mh->status === 'active') bg-yellow-100 text-yellow-800
                                         @else bg-green-100 text-green-800 @endif">
                                         {{ ucfirst($mh->status) }}
                                     </span>
                                 </td>
                                 <td x-show="!edit" class="px-4 py-3 text-sm text-gray-600">{{ $mh->notes ?? '—' }}</td>
                                 <td x-show="!edit" class="px-4 py-3 text-right whitespace-nowrap">
                                     <button @click="edit = true" class="text-indigo-600 hover:text-indigo-800 text-sm mr-2">Edit</button>
                                     <form method="POST" action="{{ route('doctor.patients.medical-histories.destroy', [$patient, $mh]) }}" onsubmit="return confirm('Remove this record?')" class="inline">
                                         @csrf @method('DELETE')
                                         <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Delete</button>
                                     </form>
                                 </td>
                                 {{-- Edit mode --}}
                                 <td x-show="edit" colspan="5" class="px-4 py-2">
                                     <form method="POST" action="{{ route('doctor.patients.medical-histories.update', [$patient, $mh]) }}" class="flex items-center gap-2">
                                         @csrf @method('PATCH')
                                         <input type="text" name="condition_name" x-model="condition" required class="w-40 border-gray-300 rounded-lg text-xs px-2 py-1.5">
                                         <input type="date" name="diagnosed_date" x-model="diagnosed_date" class="w-32 border-gray-300 rounded-lg text-xs px-2 py-1.5">
                                         <select name="status" x-model="status" class="w-24 border-gray-300 rounded-lg text-xs px-2 py-1.5">
                                             <option value="active">Active</option>
                                             <option value="resolved">Resolved</option>
                                         </select>
                                         <input type="text" name="notes" x-model="notes" placeholder="Notes" class="w-28 border-gray-300 rounded-lg text-xs px-2 py-1.5">
                                         <button type="submit" class="bg-indigo-600 text-white rounded text-xs px-2 py-1.5 hover:bg-indigo-700">Save</button>
                                         <button type="button" @click="edit = false" class="text-gray-500 hover:text-gray-700 text-xs">Cancel</button>
                                     </form>
                                 </td>
                             </tr>
                             @endforeach
                         </tbody>
                     </table>
                 </div>
                 @else
                 <p class="text-sm text-gray-400 py-8 text-center">No medical history recorded.</p>
                 @endif
             </div>

             {{-- Diagnoses Tab --}}
             <div x-show="tab === 'diagnoses'" x-cloak
                  x-data="{ icdQuery: '', icdResults: [], icdCodes: [], showIcd: false, selectIcd(code, desc) { this.icdQuery = code + ' - ' + desc; this.icdResults = []; this.showIcd = false; } }"
                  x-init="fetch('/icd-codes.json').then(r => r.json()).then(data => icdCodes = data)">
                 <div class="mb-4 flex items-center justify-between">
                     <h4 class="text-sm font-semibold text-gray-900">Diagnoses / Problem List</h4>
                     <button @click="$refs.diagForm.classList.toggle('hidden')" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">+ Add Diagnosis</button>
                 </div>
                 <form method="POST" action="{{ route('doctor.patients.diagnoses.store', $patient) }}" x-ref="diagForm" class="hidden bg-gray-50 p-4 rounded-lg mb-4">
                     @csrf
                     <div class="grid grid-cols-1 sm:grid-cols-5 gap-3 relative">
                         <input type="text" name="diagnosis" placeholder="Diagnosis" required class="w-full border-gray-300 rounded-lg text-sm px-3 py-2">
                         <div class="relative">
                             <input type="text" x-model="icdQuery" @input="showIcd = true; icdResults = icdCodes.filter(c => c.code.toLowerCase().includes(icdQuery.toLowerCase()) || c.description.toLowerCase().includes(icdQuery.toLowerCase())).slice(0, 8)" @focus="showIcd = true" @click.away="showIcd = false" placeholder="ICD Code (optional)" class="w-full border-gray-300 rounded-lg text-sm px-3 py-2">
                             <input type="hidden" name="icd_code" :value="icdQuery.split(' - ')[0]">
                             <div x-show="showIcd && icdResults.length > 0" x-cloak class="absolute z-10 mt-1 w-full bg-white border border-gray-300 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                                 <template x-for="item in icdResults" :key="item.code">
                                     <button type="button" @click="selectIcd(item.code, item.description)" class="w-full text-left px-3 py-2 text-sm hover:bg-indigo-50 border-b border-gray-100 last:border-0">
                                         <span class="font-medium" x-text="item.code"></span>
                                         <span class="text-gray-500 ml-1" x-text="item.description"></span>
                                     </button>
                                 </template>
                             </div>
                         </div>
                         <select name="type" class="w-full border-gray-300 rounded-lg text-sm px-3 py-2">
                             <option value="primary">Primary</option>
                             <option value="complication">Complication</option>
                             <option value="comorbidity">Comorbidity</option>
                         </select>
                         <input type="date" name="diagnosed_date" class="w-full border-gray-300 rounded-lg text-sm px-3 py-2">
                         <button type="submit" class="bg-indigo-600 text-white rounded-lg text-sm px-4 py-2 hover:bg-indigo-700">Save</button>
                     </div>
                 </form>
                @if($diagnoses->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Diagnosis</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ICD</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prescription</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($diagnoses as $diag)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $diag->diagnosis }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $diag->icd_code ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        @if($diag->type === 'primary') bg-blue-100 text-blue-800
                                        @elseif($diag->type === 'complication') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst($diag->type) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $diag->diagnosed_date ? \Carbon\Carbon::parse($diag->diagnosed_date)->format('M d, Y') : '—' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $diag->prescription ? '#' . $diag->prescription->prescription_number : '—' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $diag->notes ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-sm text-gray-400 py-8 text-center">No diagnoses recorded.</p>
                @endif
            </div>

            {{-- Prescriptions Tab --}}
            <div x-show="tab === 'prescriptions'" x-cloak>
                @if($prescriptions->count() > 0)
                <div class="space-y-4">
                    @foreach($prescriptions as $p)
                    <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <span class="text-sm font-semibold text-indigo-600">#{{ $p->prescription_number }}</span>
                                <span class="text-xs text-gray-500 ml-2">{{ $p->created_at->format('M d, Y g:i A') }}</span>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('doctor.prescriptions.show', $p) }}" class="text-xs text-indigo-600 hover:text-indigo-800">View</a>
                                <a href="{{ route('doctor.prescriptions.print', $p) }}" class="text-xs text-gray-600 hover:text-gray-800">Print</a>
                            </div>
                        </div>
                        @if($p->diagnosis)
                        <p class="text-sm text-gray-700 mb-2"><span class="font-medium">Dx:</span> {{ $p->diagnosis }}</p>
                        @endif
                        @if($p->items->count() > 0)
                        <div class="text-xs text-gray-500">
                            <span class="font-medium">Rx:</span>
                            @foreach($p->items as $item)
                            <span class="inline-block bg-gray-100 rounded px-1.5 py-0.5 mr-1 mb-1">{{ $item->medicine_name }}@if($item->dosage && !str_contains($item->medicine_name, $item->dosage)) ({{ $item->dosage }})@endif</span>
                            @endforeach
                        </div>
                        @endif
                        @if($p->follow_up_date)
                        <div class="text-xs text-gray-500 mt-1"><span class="font-medium">Follow-up:</span> {{ \Carbon\Carbon::parse($p->follow_up_date)->format('M d, Y') }}</div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-gray-400 py-8 text-center">No prescriptions found.</p>
                @endif
            </div>

            {{-- Investigations Tab --}}
            <div x-show="tab === 'investigations'" x-cloak>
                @if($investigationHistory->count() > 0)
                <div class="space-y-4">
                    @foreach($investigationHistory as $testName => $reports)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h5 class="text-sm font-semibold text-gray-900 mb-2">{{ $testName }}</h5>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-xs">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Parameter</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Value</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Unit</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Ref. Range</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Date</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($reports as $report)
                                    <tr>
                                        <td class="px-3 py-1.5 font-medium text-gray-800">{{ $report->parameter_name }}</td>
                                        <td class="px-3 py-1.5 text-gray-700">{{ $report->value ?? '-' }}</td>
                                        <td class="px-3 py-1.5 text-gray-500">{{ $report->unit ?? '' }}</td>
                                        <td class="px-3 py-1.5 text-gray-400">{{ $report->reference_range ?? '' }}</td>
                                        <td class="px-3 py-1.5 text-gray-500">{{ $report->prescription_date ? \Carbon\Carbon::parse($report->prescription_date)->format('d/m/Y') : '' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-gray-400 py-8 text-center">No investigation reports found.</p>
                @endif
            </div>

            {{-- Follow-ups Tab --}}
            <div x-show="tab === 'followups'" x-cloak>
                @if($followUps->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Instructions</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prescription</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($followUps as $fu)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm text-gray-900">{{ \Carbon\Carbon::parse($fu->follow_up_date)->format('M d, Y') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $fu->instructions ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        @if($fu->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($fu->status === 'completed') bg-green-100 text-green-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ ucfirst($fu->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-indigo-600">{{ $fu->prescription ? '#' . $fu->prescription->prescription_number : '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-sm text-gray-400 py-8 text-center">No follow-ups recorded.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<x-ai-assistant-panel :patientId="$patient->id" />
@endsection
