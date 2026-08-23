@extends('doctor.layouts.app')

@section('title', 'Smart Serial Settings')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Smart Serial Settings</h1>
        <p class="text-gray-500 mt-1">Configure your queue preferences</p>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('doctor.smart-serial.settings.update') }}" class="bg-white rounded-xl shadow p-6 space-y-6">
        @csrf

        <div class="flex items-center justify-between">
            <div>
                <label class="font-medium text-gray-900">Auto Call Next</label>
                <p class="text-sm text-gray-500">Automatically call next patient when current consultation ends</p>
            </div>
            <input type="hidden" name="auto_call_next" value="0">
            <input type="checkbox" name="auto_call_next" value="1" {{ $settings->auto_call_next ? 'checked' : '' }} class="w-5 h-5 rounded border-gray-300">
        </div>

        <div class="flex items-center justify-between">
            <div>
                <label class="font-medium text-gray-900">Show in Appointments</label>
                <p class="text-sm text-gray-500">Show queue info on appointment pages</p>
            </div>
            <input type="hidden" name="show_in_appointment" value="0">
            <input type="checkbox" name="show_in_appointment" value="1" {{ $settings->show_in_appointment ? 'checked' : '' }} class="w-5 h-5 rounded border-gray-300">
        </div>

        <div class="flex items-center justify-between">
            <div>
                <label class="font-medium text-gray-900">Allow Priority</label>
                <p class="text-sm text-gray-500">Allow setting priority levels (Urgent, VIP, Emergency)</p>
            </div>
            <input type="hidden" name="allow_priority" value="0">
            <input type="checkbox" name="allow_priority" value="1" {{ $settings->allow_priority ? 'checked' : '' }} class="w-5 h-5 rounded border-gray-300">
        </div>

        <div class="flex items-center justify-between">
            <div>
                <label class="font-medium text-gray-900">Reset Daily</label>
                <p class="text-sm text-gray-500">Auto-reset serial numbers each day</p>
            </div>
            <input type="hidden" name="serial_reset_daily" value="0">
            <input type="checkbox" name="serial_reset_daily" value="1" {{ $settings->serial_reset_daily ? 'checked' : '' }} class="w-5 h-5 rounded border-gray-300">
        </div>

        <div class="flex items-center justify-between">
            <div>
                <label class="font-medium text-gray-900">Notifications</label>
                <p class="text-sm text-gray-500">Send notifications when patients are called</p>
            </div>
            <input type="hidden" name="notification_enabled" value="0">
            <input type="checkbox" name="notification_enabled" value="1" {{ $settings->notification_enabled ? 'checked' : '' }} class="w-5 h-5 rounded border-gray-300">
        </div>

        <div>
            <label class="font-medium text-gray-900">Max Queue Size</label>
            <input type="number" name="max_queue_size" value="{{ $settings->max_queue_size }}" min="1" max="200" class="mt-1 block w-32 border rounded-lg px-3 py-2">
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Settings</button>
        </div>
    </form>

    <a href="{{ route('doctor.smart-serial.index') }}" class="text-blue-600 hover:underline">Back to Queue</a>
</div>
@endsection
