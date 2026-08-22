@extends('admin.layouts.app')

@section('title', 'Doctor SMS Settings')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white/90">Doctor SMS Settings</h1>
            <p class="text-sm text-white/50 mt-1">Configure MiMSMS gateway for each doctor</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.sms-settings.templates') }}" class="btn-outline-glass px-4 py-2 text-sm">Templates</a>
            <a href="{{ route('admin.sms-settings.logs') }}" class="btn-outline-glass px-4 py-2 text-sm">View SMS Logs</a>
        </div>
    </div>

    @if(session('success'))
        <div data-flash-success="{{ session('success') }}"></div>
    @endif
    @if(session('error'))
        <div data-flash-error="{{ session('error') }}"></div>
    @endif

    <div class="glass-card-static overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/10">
                        <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase">Doctor</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-white/50 uppercase w-32">SMS Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase">API URL</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase">Sender Name</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-white/50 uppercase">Reminder</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-white/50 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($doctors as $doctor)
                        @php $setting = $doctor->smsSetting; @endphp
                        <tr class="border-b border-white/5 hover:bg-white/5">
                            <td class="px-4 py-3 text-white/90 font-medium">{{ $doctor->name }}</td>
                            <td class="px-4 py-3 text-center">
                                <form method="POST" action="{{ route('admin.sms-settings.toggle', $doctor->id) }}" x-data="{ on: {{ $setting && $setting->sms_enabled ? 'true' : 'false' }} }">
                                    @csrf
                                    <button type="submit" @click="on = !on"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-300 ease-in-out focus:outline-none"
                                        :class="on ? 'bg-indigo-500' : 'bg-white/15'">
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow-lg transition-transform duration-300 ease-in-out"
                                            :class="on ? 'translate-x-[22px]' : 'translate-x-[3px]'"></span>
                                    </button>
                                </form>
                            </td>
                            <td class="px-4 py-3 text-white/50 text-xs max-w-xs truncate">{{ $setting->api_url ?? '—' }}</td>
                            <td class="px-4 py-3 text-white/50 text-xs">{{ $setting->sender_id ?? '—' }}</td>
                            <td class="px-4 py-3 text-center text-white/50 text-xs">{{ $setting->reminder_days_before ?? 1 }} day(s)</td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <a href="{{ route('admin.sms-settings.edit', $doctor->id) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-indigo-400 bg-indigo-500/10 border border-indigo-500/20 rounded-lg hover:bg-indigo-500/20 transition-colors">
                                    Configure
                                </a>
                                <form method="POST" action="{{ route('admin.sms-settings.test', $doctor->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 rounded-lg hover:bg-emerald-500/20 transition-colors">
                                        Test SMS
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-white/40">No doctors found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($doctors->hasPages())
        <div class="mt-4 flex justify-center">
            {{ $doctors->links() }}
        </div>
    @endif
@endsection
