@extends('doctor.layouts.app')

@section('title', 'SMS Center')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold" style="color:var(--text-primary);">SMS Center</h1>
        <p class="text-sm mt-1" style="color:var(--text-muted);">Send SMS to patients and view logs</p>
    </div>

    @if(session('success'))
        <div data-flash-success="{{ session('success') }}"></div>
    @endif
    @if(session('error'))
        <div data-flash-error="{{ session('error') }}"></div>
    @endif

    {{-- SMS Status --}}
    @if(!$setting || !$setting->sms_enabled)
        <div class="glass-card-static p-4 mb-6 border border-amber-300 bg-amber-50/60">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                <div>
                    <p class="text-amber-700 font-medium">SMS service is not enabled</p>
                    <p class="text-xs" style="color:var(--text-muted);">Contact admin to enable SMS for your account.</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="dashboard-card p-4 text-center">
            <div class="text-2xl font-bold" style="color:var(--text-primary);">{{ $stats['total'] }}</div>
            <div class="text-xs" style="color:var(--text-muted);">Total SMS</div>
        </div>
        <div class="dashboard-card p-4 text-center">
            <div class="text-2xl font-bold text-emerald-600">{{ $stats['sent'] }}</div>
            <div class="text-xs" style="color:var(--text-muted);">Sent</div>
        </div>
        <div class="dashboard-card p-4 text-center">
            <div class="text-2xl font-bold text-red-500">{{ $stats['failed'] }}</div>
            <div class="text-xs" style="color:var(--text-muted);">Failed</div>
        </div>
        <div class="dashboard-card p-4 text-center">
            <div class="text-2xl font-bold text-amber-500">{{ $stats['pending'] }}</div>
            <div class="text-xs" style="color:var(--text-muted);">Pending</div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <a href="{{ route('doctor.sms-center.send') }}" class="dashboard-card p-6 hover:scale-[1.02] transition-transform text-center block">
            <svg class="w-8 h-8 mx-auto mb-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            <div class="font-medium" style="color:var(--text-primary);">Send SMS</div>
            <div class="text-xs" style="color:var(--text-muted);">Send to one or multiple patients</div>
        </a>
        <a href="{{ route('doctor.sms-center.templates') }}" class="dashboard-card p-6 hover:scale-[1.02] transition-transform text-center block">
            <svg class="w-8 h-8 mx-auto mb-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <div class="font-medium" style="color:var(--text-primary);">Templates</div>
            <div class="text-xs" style="color:var(--text-muted);">Manage SMS templates</div>
        </a>
        <a href="{{ route('doctor.sms-center.logs') }}" class="dashboard-card p-6 hover:scale-[1.02] transition-transform text-center block">
            <svg class="w-8 h-8 mx-auto mb-2 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <div class="font-medium" style="color:var(--text-primary);">SMS Logs</div>
            <div class="text-xs" style="color:var(--text-muted);">View sent/failed SMS</div>
        </a>
    </div>

    {{-- Recent SMS --}}
    <div class="glass-card-static overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-sm font-semibold" style="color:var(--text-primary);">Recent SMS</h2>
            <a href="{{ route('doctor.sms-center.logs') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-indigo-50/50">
                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Patient</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Phone</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Type</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Status</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentLogs as $log)
                        <tr class="border-b border-gray-100 hover:bg-indigo-50/30 transition-colors">
                            <td class="px-4 py-2 text-xs font-medium" style="color:var(--text-primary);">{{ $log->patient->name ?? '—' }}</td>
                            <td class="px-4 py-2 text-xs font-mono" style="color:var(--text-muted);">{{ $log->recipient_phone }}</td>
                            <td class="px-4 py-2 text-xs">
                                <span class="px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700 font-medium">{{ $log->type }}</span>
                            </td>
                            <td class="px-4 py-2">
                                @if($log->status === 'sent')
                                    <span class="px-2 py-0.5 rounded-full text-xs bg-emerald-100 text-emerald-700 font-medium">Sent</span>
                                @elseif($log->status === 'failed')
                                    <span class="px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-700 font-medium">Failed</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-xs bg-amber-100 text-amber-700 font-medium">Pending</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-xs" style="color:var(--text-muted);">{{ $log->created_at->format('d M, g:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-8" style="color:var(--text-muted);">No SMS sent yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection