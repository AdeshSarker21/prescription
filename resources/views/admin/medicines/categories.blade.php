@extends('admin.layouts.app')

@section('title', 'Medicine Categories - Admin')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white/90">Medicine Categories</h1>
            <p class="text-sm text-white/50 mt-1">Manage medicine categories.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('admin.medicines.index') }}" class="btn-outline-glass">
                Back to Medicines
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert-glass mb-6">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Add Form --}}
        <div class="glass-card-static p-6">
            <h3 class="text-lg font-semibold text-white/90 mb-4">Add Category</h3>
            <form method="POST" action="{{ route('admin.medicines.categories.store') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-white/70 mb-1">Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full glass-input">
                    @error('name') <p class="text-sm text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-white/70 mb-1">Description</label>
                    <textarea name="description" rows="2" class="w-full glass-input">{{ old('description') }}</textarea>
                </div>
                <button type="submit" class="w-full px-4 py-2 btn-gradient">Create</button>
            </form>
        </div>

        {{-- List --}}
        <div class="lg:col-span-2 glass-card-static">
            <div class="px-5 py-4 border-b border-white/5">
                <h3 class="text-lg font-semibold text-white/90">All Categories</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr>
                            <th class="text-left px-5 py-3 font-medium text-white/50 uppercase text-xs">Name</th>
                            <th class="text-left px-5 py-3 font-medium text-white/50 uppercase text-xs">Medicines</th>
                            <th class="text-left px-5 py-3 font-medium text-white/50 uppercase text-xs">Status</th>
                            <th class="text-right px-5 py-3 font-medium text-white/50 uppercase text-xs">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse ($categories as $cat)
                            <tr class="hover:bg-white/5">
                                <td class="px-5 py-4">
                                    <p class="font-medium text-white/90">{{ $cat->name }}</p>
                                    @if ($cat->description)
                                        <p class="text-xs text-white/50 mt-0.5">{{ $cat->description }}</p>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-white/60">{{ $cat->medicines_count }}</td>
                                <td class="px-5 py-4">
                                    <span class="status-badge {{ $cat->is_active ? 'bg-emerald-500/20 text-emerald-400' : 'bg-white/10 text-white/50' }}">
                                        {{ $cat->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.medicines.categories.edit', $cat) }}" class="text-indigo-400 hover:text-indigo-300 text-sm font-medium">Edit</a>
                                        <form action="{{ route('admin.medicines.categories.destroy', $cat) }}" method="POST"
                                            data-confirm="Delete {{ $cat->name }}?"
                                            data-title="Delete Category"
                                            data-confirm-text="Yes, delete"
                                            data-cancel-text="Cancel">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-400 hover:text-red-300 text-sm font-medium">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-12 text-center text-white/50">No categories yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 border-t border-white/5">{{ $categories->links() }}</div>
        </div>
    </div>
@endsection
