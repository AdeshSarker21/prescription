@extends('admin.layouts.app')

@section('title', 'SMS Templates')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white/90">SMS Templates</h1>
            <p class="text-sm text-white/50 mt-1">Manage default SMS templates for all doctors</p>
        </div>
        <a href="{{ route('admin.sms-settings.index') }}" class="btn-outline-glass px-4 py-2 text-sm">Back</a>
    </div>

    @if(session('success'))
        <div data-flash-success="{{ session('success') }}"></div>
    @endif
    @if(session('error'))
        <div data-flash-error="{{ session('error') }}"></div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Create New Template --}}
        <div class="glass-card-static p-6">
            <h2 class="text-lg font-semibold text-white/90 mb-4">Create New Template</h2>
            <form method="POST" action="{{ route('admin.sms-settings.templates.store') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-white/70 mb-1">Template Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-white/90 text-sm focus:outline-none focus:border-indigo-500"
                               placeholder="e.g. Welcome Message">
                        @error('name') <span class="text-xs text-red-400 mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-white/70 mb-1">Type</label>
                        <select name="type" required
                                class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-white/90 text-sm focus:outline-none focus:border-indigo-500">
                            <option value="welcome">Welcome</option>
                            <option value="follow_up">Follow-up</option>
                            <option value="appointment">Appointment</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-white/70 mb-1">Body</label>
                        <textarea name="body" rows="4" required
                                  class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-white/90 text-sm focus:outline-none focus:border-indigo-500 font-mono"
                                  placeholder="{{patient_name}}, {{doctor_name}}, {{clinic_name}}">{{ old('body') }}</textarea>
                        <p class="text-xs text-white/40 mt-1">Available: {{patient_name}}, {{doctor_name}}, {{clinic_name}}, {{appointment_date}}, {{follow_up_reason}}</p>
                        @error('body') <span class="text-xs text-red-400 mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" checked
                               class="w-4 h-4 text-indigo-500 bg-white/5 border-white/10 rounded focus:ring-indigo-500">
                        <label class="text-sm text-white/70">Active</label>
                    </div>

                    <button type="submit" class="btn-primary-glass px-4 py-2 text-sm">Create Template</button>
                </div>
            </form>
        </div>

        {{-- Existing Templates --}}
        <div class="glass-card-static p-6">
            <h2 class="text-lg font-semibold text-white/90 mb-4">Existing Templates</h2>

            @forelse($templates as $template)
                <div class="p-4 bg-white/5 rounded-lg border border-white/10 mb-3" x-data="{ editMode: false }">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded-full text-xs bg-blue-500/10 text-blue-400">{{ $template->type }}</span>
                            <span class="text-sm font-medium text-white/90">{{ $template->name }}</span>
                            @if(!$template->is_active)
                                <span class="px-2 py-0.5 rounded-full text-xs bg-red-500/10 text-red-400">Inactive</span>
                            @endif
                        </div>
                        <div class="flex gap-2">
                            <button @click="editMode = !editMode" class="text-xs text-indigo-400 hover:text-indigo-300">Edit</button>
                            <form method="POST" action="{{ route('admin.sms-settings.templates.destroy', $template->id) }}" class="inline"
                                  data-confirm="Delete this template?">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-400 hover:text-red-300">Delete</button>
                            </form>
                        </div>
                    </div>

                    {{-- Display Mode --}}
                    <div x-show="!editMode">
                        <p class="text-xs text-white/50 font-mono whitespace-pre-wrap">{{ $template->body }}</p>
                    </div>

                    {{-- Edit Mode --}}
                    <div x-show="editMode" x-cloak>
                        <form method="POST" action="{{ route('admin.sms-settings.templates.update', $template->id) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="name" value="{{ $template->name }}">
                            <input type="hidden" name="type" value="{{ $template->type }}">
                            <div class="space-y-3">
                                <textarea name="body" rows="4" required
                                          class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-white/90 text-sm focus:outline-none focus:border-indigo-500 font-mono text-xs">{{ $template->body }}</textarea>
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" name="is_active" value="1" {{ $template->is_active ? 'checked' : '' }}
                                           class="w-4 h-4 text-indigo-500 bg-white/5 border-white/10 rounded focus:ring-indigo-500">
                                    <label class="text-xs text-white/70">Active</label>
                                </div>
                                <button type="submit" class="btn-primary-glass px-3 py-1.5 text-xs">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-sm text-white/40 text-center py-8">No templates created yet.</p>
            @endforelse
        </div>
    </div>
@endsection
