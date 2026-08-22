@extends('doctor.layouts.app')

@section('title', 'SMS Templates')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--text-primary);">SMS Templates</h1>
            <p class="text-sm mt-1" style="color:var(--text-muted);">Manage reusable SMS templates</p>
        </div>
        <a href="{{ route('doctor.sms-center.index') }}" class="btn-outline-glass px-4 py-2 text-sm">Back</a>
    </div>

    @if(session('success'))
        <div data-flash-success="{{ session('success') }}"></div>
    @endif

    {{-- Create Form --}}
    <div class="glass-card-static p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4" style="color:var(--text-primary);">Add New Template</h2>
        <form method="POST" action="{{ route('doctor.sms-center.templates.store') }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium mb-1" style="color:var(--text-primary);">Template Name</label>
                    <input type="text" name="name" required placeholder="e.g., Welcome New Patient"
                        class="w-full glass-input-light border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg text-sm @error('name') border-red-500 @enderror">
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1" style="color:var(--text-primary);">Type</label>
                    <select name="type" class="w-full glass-input-light border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg text-sm">
                        @foreach(\App\Models\SmsTemplate::getTypes() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1" style="color:var(--text-primary);">Message</label>
                <p class="text-xs mb-1" style="color:var(--text-muted);">Placeholders: &#123;&#123;patient_name&#125;&#125; &#123;&#123;doctor_name&#125;&#125; &#123;&#123;followup_date&#125;&#125; &#123;&#123;followup_time&#125;&#125;</p>
                <textarea name="message" rows="4" required class="w-full glass-input-light border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg text-sm font-mono">{{ old('message') }}</textarea>
            </div>
            <button type="submit" class="btn-gradient">Create Template</button>
        </form>
    </div>

    {{-- Existing Templates --}}
    <div class="glass-card-static overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-indigo-50/50">
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">#</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Message</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide" style="color:var(--text-muted);">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($templates as $index => $template)
                        <tr class="border-b border-gray-100 hover:bg-indigo-50/30 transition-colors">
                            <td class="px-4 py-3" style="color:var(--text-muted);">{{ $templates->firstItem() + $index }}</td>
                            <td class="px-4 py-3 font-medium" style="color:var(--text-primary);">{{ $template->name }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700 font-medium">{{ \App\Models\SmsTemplate::getTypes()[$template->type] ?? $template->type }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @if($template->is_active)
                                    <span class="px-2 py-0.5 rounded-full text-xs bg-emerald-100 text-emerald-700 font-medium">Active</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-700 font-medium">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs max-w-xs truncate" style="color:var(--text-muted);">{{ Str::limit($template->message, 60) }}</td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <button onclick="document.getElementById('edit-{{ $template->id }}').classList.toggle('hidden')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-indigo-700 bg-indigo-100 border border-indigo-200 rounded-lg hover:bg-indigo-200 transition-colors">Edit</button>
                                <form method="POST" action="{{ route('doctor.sms-center.templates.destroy', $template->id) }}" class="inline" data-confirm="Delete this template?" data-icon="warning">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-700 bg-red-100 border border-red-200 rounded-lg hover:bg-red-200 transition-colors">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <tr id="edit-{{ $template->id }}" class="hidden">
                            <td colspan="6" class="px-4 py-4 bg-indigo-50/40">
                                <form method="POST" action="{{ route('doctor.sms-center.templates.update', $template->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                                        <div>
                                            <label class="block text-xs font-medium mb-1" style="color:var(--text-muted);">Name</label>
                                            <input type="text" name="name" value="{{ $template->name }}" required class="w-full glass-input-light border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium mb-1" style="color:var(--text-muted);">Type</label>
                                            <select name="type" class="w-full glass-input-light border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg text-sm">
                                                @foreach(\App\Models\SmsTemplate::getTypes() as $value => $label)
                                                    <option value="{{ $value }}" {{ $template->type === $value ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="block text-xs font-medium mb-1" style="color:var(--text-muted);">Message</label>
                                        <textarea name="message" rows="3" required class="w-full glass-input-light border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg text-sm font-mono">{{ $template->message }}</textarea>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <label class="flex items-center gap-2 text-xs font-medium" style="color:var(--text-muted);">
                                            <input type="checkbox" name="is_active" value="1" {{ $template->is_active ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"> Active
                                        </label>
                                        <button type="submit" class="btn-gradient text-xs">Update</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12" style="color:var(--text-muted);">No templates found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($templates->hasPages())
        <div class="mt-4 flex justify-center">
            {{ $templates->links() }}
        </div>
    @endif
@endsection