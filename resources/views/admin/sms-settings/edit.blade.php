@extends('admin.layouts.app')

@section('title', 'Configure SMS - ' . $doctor->name)

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white/90">SMS Settings: {{ $doctor->name }}</h1>
            <p class="text-sm text-white/50 mt-1">Configure MiMSMS API credentials and reminder settings</p>
        </div>
        <a href="{{ route('admin.sms-settings.index') }}" class="btn-outline-glass px-4 py-2 text-sm">Back to List</a>
    </div>

    @if(session('success'))
        <div data-flash-success="{{ session('success') }}"></div>
    @endif
    @if(session('error'))
        <div data-flash-error="{{ session('error') }}"></div>
    @endif

    <form method="POST" action="{{ route('admin.sms-settings.update', $doctor->id) }}">
        @csrf
        @method('PATCH')

        <div class="glass-card-static p-6 mb-6">
            <h2 class="text-lg font-semibold text-white/90 mb-4">API Configuration</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-1">Enable SMS</label>
                    <label class="relative inline-flex items-center cursor-pointer mt-2">
                        <input type="checkbox" name="sms_enabled" value="1" {{ $setting->sms_enabled ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-white/10 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white/50 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                        <span class="ml-2 text-sm text-white/70">Enable SMS Service</span>
                    </label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-1">Reminder Days Before Follow-up</label>
                    <select name="reminder_days_before" class="w-full glass-input">
                        <option value="1" {{ $setting->reminder_days_before == 1 ? 'selected' : '' }}>1 Day Before</option>
                        <option value="2" {{ $setting->reminder_days_before == 2 ? 'selected' : '' }}>2 Days Before</option>
                        <option value="3" {{ $setting->reminder_days_before == 3 ? 'selected' : '' }}>3 Days Before</option>
                        <option value="7" {{ $setting->reminder_days_before == 7 ? 'selected' : '' }}>7 Days Before</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-1">API URL <span class="text-red-400">*</span></label>
                    <input type="url" name="api_url" value="{{ old('api_url', $setting->api_url) }}" required
                        placeholder="https://api.mimsms.com/api/V2/SMS"
                        class="w-full glass-input @error('api_url') border-red-500 @enderror">
                    @error('api_url')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-1">API Key <span class="text-red-400">*</span></label>
                    <input type="text" name="api_key" value="{{ old('api_key', $setting->api_key) }}"
                        placeholder="Your MiMSMS API Key"
                        class="w-full glass-input">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-1">Sender Name <span class="text-red-400">*</span></label>
                    <input type="text" name="sender_id" value="{{ old('sender_id', $setting->sender_id) }}"
                        placeholder="YourSenderID"
                        class="w-full glass-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-1">User Name (Email) <span class="text-red-400">*</span></label>
                    <input type="email" name="username" value="{{ old('username', $setting->username) }}"
                        placeholder="your@email.com"
                        class="w-full glass-input">
                </div>
            </div>
        </div>

        <div class="glass-card-static p-6 mb-6">
            <h2 class="text-lg font-semibold text-white/90 mb-2">SMS Template</h2>
            <p class="text-xs text-white/40 mb-4">Use placeholders: <code class="bg-white/10 px-1 rounded">&#123;&#123;patient_name&#125;&#125;</code>, <code class="bg-white/10 px-1 rounded">&#123;&#123;doctor_name&#125;&#125;</code>, <code class="bg-white/10 px-1 rounded">&#123;&#123;followup_date&#125;&#125;</code>, <code class="bg-white/10 px-1 rounded">&#123;&#123;followup_time&#125;&#125;</code></p>

            <textarea name="sms_template" rows="8"
                class="w-full glass-input font-mono text-xs @error('sms_template') border-red-500 @enderror"
                placeholder="Type your SMS template here...">{!! old('sms_template', $setting->sms_template) !!}</textarea>
            @error('sms_template')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn-gradient">Save Settings</button>
            <a href="{{ route('admin.sms-settings.index') }}" class="btn-outline-glass px-4 py-2">Cancel</a>
        </div>
    </form>
@endsection
