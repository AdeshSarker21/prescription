@extends('admin.layouts.app')

@section('title', 'Prescription Footers')

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

    {{-- Create Form --}}
    <div class="glass-card-static p-6 mb-6">
        <h2 class="text-lg font-semibold text-white/90 mb-4">Add New Footer</h2>
        <form method="POST" action="{{ route('admin.prescription-settings.footers.store') }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-1">Template Name</label>
                    <input type="text" name="name" required placeholder="e.g., Clinic Footer - Main"
                        class="w-full glass-input @error('name') border-red-500 @enderror">
                    @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-white/70 mb-1">Footer Content (HTML)</label>
                <textarea name="content" rows="6" required placeholder="<div class='footer'>...</div>"
                    class="w-full glass-input font-mono text-xs @error('content') border-red-500 @enderror">{{ old('content') }}</textarea>
                @error('content')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="btn-gradient">Create Footer</button>
        </form>
    </div>

    {{-- Existing Footers --}}
    <div class="glass-card-static overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/10">
                        <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase">#</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase">Doctors</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase">Content Preview</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-white/50 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $index => $item)
                        <tr class="border-b border-white/5 hover:bg-white/5">
                            <td class="px-4 py-3 text-white/40">{{ $items->firstItem() + $index }}</td>
                            <td class="px-4 py-3 text-white/90 font-medium">{{ $item->name }}</td>
                            <td class="px-4 py-3">
                                @if($item->status === 'active')
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Active</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-500/10 text-red-400 border border-red-500/20">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-white/50">{{ $item->doctors()->count() }}</td>
                            <td class="px-4 py-3 text-white/50 text-xs max-w-xs truncate">{{ Str::limit(strip_tags($item->content), 80) }}</td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <button onclick="document.getElementById('edit-footer-{{ $item->id }}').classList.toggle('hidden')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-indigo-400 bg-indigo-500/10 border border-indigo-500/20 rounded-lg hover:bg-indigo-500/20 transition-colors">
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('admin.prescription-settings.footers.destroy', $item->id) }}" class="inline" data-confirm="Delete this footer template?" data-icon="warning">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg hover:bg-red-500/20 transition-colors">Delete</button>
                                </form>
                            </td>
                        </tr>
                        {{-- Edit Row --}}
                        <tr id="edit-footer-{{ $item->id }}" class="hidden">
                            <td colspan="6" class="px-4 py-4 bg-white/5">
                                <form method="POST" action="{{ route('admin.prescription-settings.footers.update', $item->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                                        <div>
                                            <label class="block text-xs text-white/50 mb-1">Name</label>
                                            <input type="text" name="name" value="{{ $item->name }}" required class="w-full glass-input text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-white/50 mb-1">Status</label>
                                            <select name="status" class="w-full glass-input text-sm">
                                                <option value="active" {{ $item->status === 'active' ? 'selected' : '' }}>Active</option>
                                                <option value="inactive" {{ $item->status !== 'active' ? 'selected' : '' }}>Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="block text-xs text-white/50 mb-1">Content (HTML)</label>
                                        <textarea name="content" rows="4" required class="w-full glass-input font-mono text-xs">{{ $item->content }}</textarea>
                                    </div>
                                    <button type="submit" class="btn-gradient text-sm">Update Footer</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-white/40">No footer templates found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($items->hasPages())
        <div class="mt-4 flex justify-center">
            {{ $items->links() }}
        </div>
    @endif
@endsection
