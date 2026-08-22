@extends('admin.layouts.app')

@section('title', 'Doctor Prescription Settings')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white/90">Prescription Settings</h1>
            <p class="text-sm text-white/50 mt-1">Manage header and footer templates for prescriptions</p>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex items-center gap-1 p-1 glass-card-static mb-6 w-fit">
        <a href="{{ route('admin.prescription-settings.headers') }}" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.prescription-settings.headers*') ? 'bg-indigo-500/20 text-indigo-400' : 'text-white/60 hover:text-white/80 hover:bg-white/5' }}">
            Headers
        </a>
        <a href="{{ route('admin.prescription-settings.footers') }}" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.prescription-settings.footers*') ? 'bg-indigo-500/20 text-indigo-400' : 'text-white/60 hover:text-white/80 hover:bg-white/5' }}">
            Footers
        </a>
        <a href="{{ route('admin.prescription-settings.doctors') }}" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.prescription-settings.doctors*') ? 'bg-indigo-500/20 text-indigo-400' : 'text-white/60 hover:text-white/80 hover:bg-white/5' }}">
            Doctor Settings
        </a>
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
                        <th class="px-4 py-3 text-center text-xs font-medium text-white/50 uppercase w-32">Header</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase">Header Template</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-white/50 uppercase w-32">Footer</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase">Footer Template</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-white/50 uppercase w-24">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($doctors as $doctor)
                        @php $setting = $doctor->prescriptionSetting; @endphp
                        <tr class="border-b border-white/5 hover:bg-white/5">
                            <form method="POST" action="{{ route('admin.prescription-settings.doctors.update', $doctor->id) }}">
                                @csrf
                                @method('PATCH')
                                <td class="px-4 py-3 text-white/90 font-medium">{{ $doctor->name }}</td>
                                <td class="px-4 py-3 text-center" x-data="{ on: {{ $setting && $setting->header_enabled ? 'true' : 'false' }} }">
                                    <button type="button" @click="on = !on; $el.closest('form').querySelector('input[type=checkbox][name=header_enabled]').checked = on"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-300 ease-in-out focus:outline-none"
                                        :class="on ? 'bg-indigo-500' : 'bg-white/15'">
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow-lg transition-transform duration-300 ease-in-out"
                                            :class="on ? 'translate-x-[22px]' : 'translate-x-[3px]'"></span>
                                    </button>
                                    <input type="hidden" name="header_enabled" value="0">
                                    <input type="checkbox" name="header_enabled" value="1" {{ $setting && $setting->header_enabled ? 'checked' : '' }} class="hidden">
                                </td>
                                <td class="px-4 py-3">
                                    <select name="header_id" class="w-full glass-input text-xs">
                                        <option value="">-- None --</option>
                                        @foreach($headers as $h)
                                            <option value="{{ $h->id }}" {{ ($setting && $setting->header_id == $h->id) ? 'selected' : '' }}>{{ $h->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-3 text-center" x-data="{ on: {{ $setting && $setting->footer_enabled ? 'true' : 'false' }} }">
                                    <button type="button" @click="on = !on; $el.closest('form').querySelector('input[type=checkbox][name=footer_enabled]').checked = on"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-300 ease-in-out focus:outline-none"
                                        :class="on ? 'bg-indigo-500' : 'bg-white/15'">
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow-lg transition-transform duration-300 ease-in-out"
                                            :class="on ? 'translate-x-[22px]' : 'translate-x-[3px]'"></span>
                                    </button>
                                    <input type="hidden" name="footer_enabled" value="0">
                                    <input type="checkbox" name="footer_enabled" value="1" {{ $setting && $setting->footer_enabled ? 'checked' : '' }} class="hidden">
                                </td>
                                <td class="px-4 py-3">
                                    <select name="footer_id" class="w-full glass-input text-xs">
                                        <option value="">-- None --</option>
                                        @foreach($footers as $f)
                                            <option value="{{ $f->id }}" {{ ($setting && $setting->footer_id == $f->id) ? 'selected' : '' }}>{{ $f->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-white/70 bg-white/5 hover:bg-white/10 border border-white/10 rounded-lg transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Save
                                    </button>
                                </td>
                            </form>
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
