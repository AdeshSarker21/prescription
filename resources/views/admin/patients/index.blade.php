@extends('admin.layouts.app')

@section('title', 'Patients')

@section('content')
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-semibold text-white/90">Patients</h1>
            <p class="mt-1 text-sm text-white/50">All patients across the system.</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="glass-card-static p-4 mb-6">
            <form method="GET" action="{{ route('admin.patients.index') }}" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-xs font-medium text-white/50 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, email, phone..." class="w-48 glass-input">
                </div>
                <div>
                    <label class="block text-xs font-medium text-white/50 mb-1">Doctor</label>
                    <select name="doctor_id" class="px-3 py-2 glass-input">
                        <option value="">All Doctors</option>
                        @foreach($doctors as $doc)
                        <option value="{{ $doc->id }}" {{ request('doctor_id') == $doc->id ? 'selected' : '' }}>{{ $doc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 btn-gradient">Filter</button>
                    <a href="{{ route('admin.patients.index') }}" class="px-4 py-2 bg-white/10 text-white/60 text-sm font-medium rounded-lg hover:bg-white/20 transition-colors">Reset</a>
                </div>
            </form>
        </div>

        <div class="glass-card-static overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-white/5">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white/50 uppercase">Patient</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white/50 uppercase">Doctor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white/50 uppercase">Phone</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white/50 uppercase">Gender</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white/50 uppercase">Age</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white/50 uppercase">Registered</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse($patients as $patient)
                            <tr class="hover:bg-white/5">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-indigo-500/20 rounded-full flex items-center justify-center text-xs font-semibold text-indigo-400">
                                            {{ strtoupper(substr($patient->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-white/90">{{ $patient->name }}</p>
                                            <p class="text-xs text-white/50">{{ $patient->patient_number ?? '—' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-white/70">{{ $patient->doctor?->name ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-white/70">{{ $patient->phone ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-white/70">{{ $patient->gender ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-white/70">{{ $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->age : '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-white/50">{{ $patient->created_at->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-white/50">No patients found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-white/5 flex items-center justify-between">
                <p class="text-sm text-white/50">{{ $patients->total() }} patient(s)</p>
                {{ $patients->links() }}
            </div>
        </div>
    </div>
@endsection
