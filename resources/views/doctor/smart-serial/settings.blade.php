@extends('doctor.layouts.app')
@section('title', 'Smart Serial Settings')
@section('header', 'Smart Serial Settings')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Smart Serial Settings</h1>
        <p class="text-gray-500 mt-1">Configure queue behavior, serial numbering, and display options</p>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('doctor.smart-serial.settings.update') }}" class="space-y-6">
        @csrf

        {{-- Serial Numbering --}}
        <div class="bg-white rounded-xl shadow p-6 space-y-5">
            <h3 class="text-lg font-semibold text-gray-900 border-b pb-3">Serial Numbering</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Starting Serial Number</label>
                    <input type="number" name="starting_serial_number" value="{{ old('starting_serial_number', $settings->starting_serial_number) }}" min="1" class="mt-1 block w-full border rounded-lg px-3 py-2">
                    <p class="text-xs text-gray-400 mt-1">Default start for new sessions. Chambers can override this.</p>
                    @error('starting_serial_number') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Maximum Serial</label>
                    <input type="number" name="max_serial" value="{{ old('max_serial', $settings->max_serial) }}" min="1" class="mt-1 block w-full border rounded-lg px-3 py-2">
                    <p class="text-xs text-gray-400 mt-1">Maximum serial number allowed per session.</p>
                    @error('max_serial') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <label class="font-medium text-gray-900">Auto Increment</label>
                        <p class="text-xs text-gray-500">Automatically increment serial for each new patient</p>
                    </div>
                    <input type="hidden" name="auto_increment" value="0">
                    <input type="checkbox" name="auto_increment" value="1" {{ $settings->auto_increment ? 'checked' : '' }} class="w-5 h-5 rounded border-gray-300">
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <label class="font-medium text-gray-900">Daily Reset</label>
                        <p class="text-xs text-gray-500">Reset serial numbers to starting value each day</p>
                    </div>
                    <input type="hidden" name="serial_reset_daily" value="0">
                    <input type="checkbox" name="serial_reset_daily" value="1" {{ $settings->serial_reset_daily ? 'checked' : '' }} class="w-5 h-5 rounded border-gray-300">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Serial Prefix</label>
                <input type="text" name="prefix" value="{{ old('prefix', $settings->prefix) }}" maxlength="20" class="mt-1 block w-full border rounded-lg px-3 py-2" placeholder="e.g. A, VIP, EMG">
                <p class="text-xs text-gray-400 mt-1">Prepended to serial numbers (e.g. A-001). Chambers can override this.</p>
                @error('prefix') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Queue Behavior --}}
        <div class="bg-white rounded-xl shadow p-6 space-y-5">
            <h3 class="text-lg font-semibold text-gray-900 border-b pb-3">Queue Behavior</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Queue Mode</label>
                    <select name="queue_mode" class="mt-1 block w-full border rounded-lg px-3 py-2">
                        <option value="serial" {{ $settings->queue_mode === 'serial' ? 'selected' : '' }}>Serial (1, 2, 3...)</option>
                        <option value="token" {{ $settings->queue_mode === 'token' ? 'selected' : '' }}>Token (A-01, A-02...)</option>
                        <option value="appointment" {{ $settings->queue_mode === 'appointment' ? 'selected' : '' }}>Appointment-based</option>
                    </select>
                    <p class="text-xs text-gray-400 mt-1">How patients are numbered in the queue.</p>
                    @error('queue_mode') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Max Queue Size</label>
                    <input type="number" name="max_queue_size" value="{{ old('max_queue_size', $settings->max_queue_size) }}" min="1" max="500" class="mt-1 block w-full border rounded-lg px-3 py-2">
                    <p class="text-xs text-gray-400 mt-1">Maximum patients allowed in queue per session.</p>
                    @error('max_queue_size') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <label class="font-medium text-gray-900">Auto Call Next</label>
                        <p class="text-xs text-gray-500">Call next patient when current consultation ends</p>
                    </div>
                    <input type="hidden" name="auto_call_next" value="0">
                    <input type="checkbox" name="auto_call_next" value="1" {{ $settings->auto_call_next ? 'checked' : '' }} class="w-5 h-5 rounded border-gray-300">
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <label class="font-medium text-gray-900">Allow Priority</label>
                        <p class="text-xs text-gray-500">Allow Urgent, VIP, Emergency priority levels</p>
                    </div>
                    <input type="hidden" name="allow_priority" value="0">
                    <input type="checkbox" name="allow_priority" value="1" {{ $settings->allow_priority ? 'checked' : '' }} class="w-5 h-5 rounded border-gray-300">
                </div>
            </div>

            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div>
                    <label class="font-medium text-gray-900">Emergency Priority</label>
                    <p class="text-xs text-gray-500">Allow emergency override — pushes current patient back to waiting</p>
                </div>
                <input type="hidden" name="emergency_priority" value="0">
                <input type="checkbox" name="emergency_priority" value="1" {{ $settings->emergency_priority ? 'checked' : '' }} class="w-5 h-5 rounded border-gray-300">
            </div>
        </div>

        {{-- Display & Notifications --}}
        <div class="bg-white rounded-xl shadow p-6 space-y-5">
            <h3 class="text-lg font-semibold text-gray-900 border-b pb-3">Display & Notifications</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <label class="font-medium text-gray-900">Voice Announcements</label>
                        <p class="text-xs text-gray-500">Play voice when calling patients</p>
                    </div>
                    <input type="hidden" name="voice_enabled" value="0">
                    <input type="checkbox" name="voice_enabled" value="1" {{ $settings->voice_enabled ? 'checked' : '' }} class="w-5 h-5 rounded border-gray-300">
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <label class="font-medium text-gray-900">Public Display</label>
                        <p class="text-xs text-gray-500">Show queue on public display screen</p>
                    </div>
                    <input type="hidden" name="display_enabled" value="0">
                    <input type="checkbox" name="display_enabled" value="1" {{ $settings->display_enabled ? 'checked' : '' }} class="w-5 h-5 rounded border-gray-300">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <label class="font-medium text-gray-900">Notifications</label>
                        <p class="text-xs text-gray-500">Send notifications when patients are called</p>
                    </div>
                    <input type="hidden" name="notification_enabled" value="0">
                    <input type="checkbox" name="notification_enabled" value="1" {{ $settings->notification_enabled ? 'checked' : '' }} class="w-5 h-5 rounded border-gray-300">
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <label class="font-medium text-gray-900">Show in Appointments</label>
                        <p class="text-xs text-gray-500">Show queue info on appointment pages</p>
                    </div>
                    <input type="hidden" name="show_in_appointment" value="0">
                    <input type="checkbox" name="show_in_appointment" value="1" {{ $settings->show_in_appointment ? 'checked' : '' }} class="w-5 h-5 rounded border-gray-300">
                </div>
            </div>
        </div>

        {{-- Chamber Overrides --}}
        @if($chambers->count() > 0)
        <div class="bg-white rounded-xl shadow p-6 space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 border-b pb-3">Chamber Overrides</h3>
            <p class="text-sm text-gray-500">Each chamber can override the default prefix and starting serial number set above.</p>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Chamber</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Prefix</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Starting #</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($chambers as $chamber)
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $chamber->name }}</td>
                            <td class="px-4 py-3"><code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ $chamber->serial_prefix ?: '—' }}</code></td>
                            <td class="px-4 py-3 text-gray-700">{{ $chamber->daily_starting_number }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full {{ $chamber->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $chamber->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="text-xs text-gray-400">Edit chamber settings from <a href="{{ route('admin.chambers.index') }}" class="text-blue-600 hover:underline">Admin &rarr; Chambers</a>.</p>
        </div>
        @endif

        <div class="flex justify-end">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">Save Settings</button>
        </div>
    </form>

    <a href="{{ route('doctor.smart-serial.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Back to Queue</a>
</div>
@endsection
