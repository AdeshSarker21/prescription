@extends('assistant.layouts.app')

@section('title', 'Assistant Dashboard')
@section('header', 'Dashboard')

@section('content')
<div class="space-y-6">
    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="dashboard-card animate-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Today's Appointments</p>
                    <p class="stat-value text-indigo-600 mt-1">{{ $todayAppointments }}</p>
                </div>
                <div class="stat-icon bg-indigo-50 text-indigo-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>
        </div>

        <div class="dashboard-card animate-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Upcoming</p>
                    <p class="stat-value text-cyan-600 mt-1">{{ $upcomingAppointments }}</p>
                </div>
                <div class="stat-icon bg-cyan-50 text-cyan-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>

        <div class="dashboard-card animate-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Completed Today</p>
                    <p class="stat-value text-emerald-600 mt-1">{{ $completedToday }}</p>
                </div>
                <div class="stat-icon bg-emerald-50 text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>

        <div class="dashboard-card animate-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Patients</p>
                    <p class="stat-value text-rose-600 mt-1">{{ $totalPatients }}</p>
                </div>
                <div class="stat-icon bg-rose-50 text-rose-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Today's Queue --}}
        <div class="lg:col-span-2 dashboard-card animate-card">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-gray-800">Today's Queue ({{ $today->format('M d, Y') }})</h3>
                <a href="{{ route('assistant.appointments.create') }}" class="btn-gradient text-xs px-4 py-2">+ New Appointment</a>
            </div>

            @if($todayQueue->isEmpty())
                <div class="text-center py-8 text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <p class="text-sm">No appointments today</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($todayQueue as $apt)
                    <div class="flex items-center justify-between p-3 rounded-xl bg-white/40 hover:bg-white/60 transition-all border border-white/30">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center text-white text-sm font-bold shadow-md shadow-indigo-200">
                                {{ substr($apt->patient->name ?? 'N/A', 0, 1) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">{{ $apt->patient->name ?? 'N/A' }}</p>
                                <p class="text-xs text-gray-500">Dr. {{ $apt->doctor->name ?? 'N/A' }} &middot; {{ $apt->appointment_date->format('h:i A') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @php
                                $statusColors = [
                                    'scheduled' => 'bg-blue-100 text-blue-700',
                                    'completed' => 'bg-emerald-100 text-emerald-700',
                                    'cancelled' => 'bg-red-100 text-red-700',
                                    'no_show' => 'bg-amber-100 text-amber-700',
                                ];
                            @endphp
                            <span class="status-badge {{ $statusColors[$apt->status] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst(str_replace('_', ' ', $apt->status)) }}</span>
                            @if($apt->status === 'scheduled')
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open" class="p-1.5 rounded-lg hover:bg-white/50 text-gray-400 hover:text-gray-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                                </button>
                                <div x-show="open" @click.away="open = false" class="absolute right-0 mt-1 w-36 glass-dropdown py-1 z-40" x-cloak>
                                    <form method="POST" action="{{ route('assistant.appointments.complete', $apt) }}">
                                        @csrf @method('PATCH')
                                        <button class="block w-full text-left px-3 py-2 text-xs text-emerald-700 hover:bg-emerald-50">Complete</button>
                                    </form>
                                    <form method="POST" action="{{ route('assistant.appointments.cancel', $apt) }}">
                                        @csrf @method('PATCH')
                                        <button class="block w-full text-left px-3 py-2 text-xs text-red-700 hover:bg-red-50">Cancel</button>
                                    </form>
                                    <a href="{{ route('assistant.appointments.show', $apt) }}" class="block px-3 py-2 text-xs text-gray-700 hover:bg-gray-50">View</a>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Calendar Mini --}}
            <div class="dashboard-card animate-card">
                <h3 class="text-base font-bold text-gray-800 mb-3">{{ $today->format('F Y') }}</h3>
                @php
                    $daysInMonth = $today->daysInMonth;
                    $firstDayOfWeek = $today->copy()->startOfMonth()->dayOfWeek;
                    $calendarDays = [];
                    for ($i = 0; $i < $firstDayOfWeek; $i++) {
                        $calendarDays[] = null;
                    }
                    for ($d = 1; $d <= $daysInMonth; $d++) {
                        $calendarDays[] = $d;
                    }
                @endphp
                <div class="grid grid-cols-7 gap-1 text-center text-xs">
                    @foreach(['S','M','T','W','T','F','S'] as $day)
                        <div class="font-semibold text-gray-400 py-1">{{ $day }}</div>
                    @endforeach
                    @foreach($calendarDays as $day)
                        @if($day === null)
                            <div></div>
                        @else
                            @php
                                $dateStr = $today->copy()->startOfMonth()->addDays($day - 1)->toDateString();
                                $dayData = $calendarData->get($dateStr, ['total' => 0, 'scheduled' => 0, 'completed' => 0]);
                                $isToday = $day === $today->day;
                            @endphp
                            <div class="relative py-1 rounded-lg cursor-default {{ $isToday ? 'bg-indigo-600 text-white font-bold' : ($dayData['total'] > 0 ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-600 hover:bg-white/40') }}">
                                {{ $day }}
                                @if($dayData['total'] > 0 && !$isToday)
                                    <span class="absolute -bottom-0.5 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-indigo-400"></span>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- Upcoming (Next 7 Days) --}}
            <div class="dashboard-card animate-card">
                <h3 class="text-base font-bold text-gray-800 mb-3">Upcoming (7 Days)</h3>
                @if($upcoming->isEmpty())
                    <p class="text-xs text-gray-400 text-center py-4">No upcoming appointments</p>
                @else
                    <div class="space-y-2">
                        @foreach($upcoming as $apt)
                        <a href="{{ route('assistant.appointments.show', $apt) }}" class="flex items-center gap-3 p-2 rounded-lg hover:bg-white/40 transition-all">
                            <div class="w-2 h-2 rounded-full {{ $apt->status === 'scheduled' ? 'bg-blue-400' : 'bg-gray-300' }}"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold text-gray-800 truncate">{{ $apt->patient->name ?? 'N/A' }}</p>
                                <p class="text-xs text-gray-500">{{ $apt->appointment_date->format('M d, h:i A') }}</p>
                            </div>
                        </a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Recent Patients --}}
            <div class="dashboard-card animate-card">
                <h3 class="text-base font-bold text-gray-800 mb-3">Recent Patients</h3>
                @if($recentPatients->isEmpty())
                    <p class="text-xs text-gray-400 text-center py-4">No patients yet</p>
                @else
                    <div class="space-y-2">
                        @foreach($recentPatients as $patient)
                        <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-white/40 transition-all">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-rose-400 to-rose-500 flex items-center justify-center text-white text-xs font-bold">
                                {{ substr($patient->name, 0, 1) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold text-gray-800 truncate">{{ $patient->name }}</p>
                                <p class="text-xs text-gray-500">{{ $patient->phone ?? 'No phone' }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
