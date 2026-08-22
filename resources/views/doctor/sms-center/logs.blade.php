@extends('doctor.layouts.app')

@section('title', 'SMS Logs')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--text-primary);">SMS Logs</h1>
            <p class="text-sm mt-1" style="color:var(--text-muted);">View all sent, failed, and pending SMS messages</p>
        </div>
        <a href="{{ route('doctor.sms-center.index') }}" class="btn-outline-glass px-4 py-2 text-sm">Back</a>
    </div>

    {{-- Filters --}}
    <div class="glass-card-static p-4 mb-6">
        <form method="GET" action="{{ route('doctor.sms-center.logs') }}">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="w-full sm:w-40">
                    <select name="status" class="w-full glass-input-light border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg text-sm">
                        <option value="">All Status</option>
                        <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>
                <div class="w-full sm:w-40">
                    <select name="type" class="w-full glass-input-light border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg text-sm">
                        <option value="">All Types</option>
                        <option value="follow_up" {{ request('type') === 'follow_up' ? 'selected' : '' }}>Follow-up</option>
                        <option value="welcome" {{ request('type') === 'welcome' ? 'selected' : '' }}>Welcome</option>
                        <option value="appointment" {{ request('type') === 'appointment' ? 'selected' : '' }}>Appointment</option>
                        <option value="custom" {{ request('type') === 'custom' ? 'selected' : '' }}>Custom</option>
                    </select>
                </div>
                <button type="submit" class="btn-gradient px-4 py-2 text-sm">Filter</button>
                @if(request('status') || request('type'))
                    <a href="{{ route('doctor.sms-center.logs') }}" class="btn-outline-glass px-4 py-2 text-sm">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <div class="glass-card-static overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-indigo-50/50">
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">#</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Patient</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Phone</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Sent At</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Message</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $index => $log)
                        <tr class="border-b border-gray-100 hover:bg-indigo-50/30 transition-colors">
                            <td class="px-4 py-3" style="color:var(--text-muted);">{{ $logs->firstItem() + $index }}</td>
                            <td class="px-4 py-3 text-xs font-medium" style="color:var(--text-primary);">{{ $log->patient->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-xs font-mono" style="color:var(--text-muted);">{{ $log->recipient_phone }}</td>
                            <td class="px-4 py-3 text-xs">
                                <span class="px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700 font-medium">{{ $log->type }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @if($log->status === 'sent')
                                    <span class="px-2 py-0.5 rounded-full text-xs bg-emerald-100 text-emerald-700 font-medium">Sent</span>
                                @elseif($log->status === 'failed')
                                    <span class="px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-700 font-medium" title="{{ $log->error_message }}">Failed</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-xs bg-amber-100 text-amber-700 font-medium">Pending</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs" style="color:var(--text-muted);">{{ $log->sent_at?->format('d M Y, g:i A') ?? '—' }}</td>
                            <td class="px-4 py-3 text-xs max-w-[200px] truncate" style="color:var(--text-muted);" title="{{ $log->message }}">{{ Str::limit($log->message, 60) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12" style="color:var(--text-muted);">No SMS logs found.</td>
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