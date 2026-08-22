@extends('admin.layouts.app')

@section('title', 'SMS Logs')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white/90">SMS Logs</h1>
            <p class="text-sm text-white/50 mt-1">View all sent, failed, and pending SMS messages</p>
        </div>
        <a href="{{ route('admin.sms-settings.index') }}" class="btn-outline-glass px-4 py-2 text-sm">Back to Settings</a>
    </div>

    @if(session('success'))
        <div data-flash-success="{{ session('success') }}"></div>
    @endif

    {{-- Filters --}}
    <div class="glass-card-static p-4 mb-6">
        <form method="GET" action="{{ route('admin.sms-settings.logs') }}">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <select name="doctor_id" class="w-full glass-input">
                        <option value="">All Doctors</option>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>{{ $doctor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full sm:w-40">
                    <select name="status" class="w-full glass-input">
                        <option value="">All Status</option>
                        <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>
                <button type="submit" class="btn-outline-glass px-4 py-2 text-sm">Filter</button>
                @if(request('doctor_id') || request('status'))
                    <a href="{{ route('admin.sms-settings.logs') }}" class="btn-outline-glass px-4 py-2 text-sm">Clear</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        @php
            $totalSent = \App\Models\SmsLog::sent()->count();
            $totalFailed = \App\Models\SmsLog::failed()->count();
            $totalPending = \App\Models\SmsLog::pending()->count();
            $totalAll = \App\Models\SmsLog::count();
        @endphp
        <div class="glass-card-static p-4 text-center">
            <div class="text-2xl font-bold text-white/90">{{ $totalAll }}</div>
            <div class="text-xs text-white/50">Total SMS</div>
        </div>
        <div class="glass-card-static p-4 text-center">
            <div class="text-2xl font-bold text-emerald-400">{{ $totalSent }}</div>
            <div class="text-xs text-white/50">Sent</div>
        </div>
        <div class="glass-card-static p-4 text-center">
            <div class="text-2xl font-bold text-red-400">{{ $totalFailed }}</div>
            <div class="text-xs text-white/50">Failed</div>
        </div>
        <div class="glass-card-static p-4 text-center">
            <div class="text-2xl font-bold text-yellow-400">{{ $totalPending }}</div>
            <div class="text-xs text-white/50">Pending</div>
        </div>
    </div>

    {{-- Logs Table --}}
    <div class="glass-card-static overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/10">
                        <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase">#</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase">Doctor</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase">Patient</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase">Phone</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase">Sent At</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase">Message</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $index => $log)
                        <tr class="border-b border-white/5 hover:bg-white/5">
                            <td class="px-4 py-3 text-white/40">{{ $logs->firstItem() + $index }}</td>
                            <td class="px-4 py-3 text-white/90 text-xs">{{ $log->doctor->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-white/70 text-xs">{{ $log->patient->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-white/50 text-xs font-mono">{{ $log->recipient_phone }}</td>
                            <td class="px-4 py-3 text-xs">
                                <span class="px-2 py-0.5 rounded-full text-xs bg-blue-500/10 text-blue-400 border border-blue-500/20">{{ $log->type }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @if($log->status === 'sent')
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Sent</span>
                                @elseif($log->status === 'failed')
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-500/10 text-red-400 border border-red-500/20" title="{{ $log->error_message }}">Failed</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-500/10 text-yellow-400 border border-yellow-500/20">Pending</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-white/50 text-xs">{{ $log->sent_at?->format('d M Y, g:i A') ?? '—' }}</td>
                            <td class="px-4 py-3 text-white/50 text-xs max-w-[200px] truncate" title="{{ $log->message }}">{{ Str::limit($log->message, 60) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-12 text-white/40">No SMS logs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($logs->hasPages())
        <div class="mt-4 flex justify-center">
            {{ $logs->links() }}
        </div>
    @endif
@endsection
