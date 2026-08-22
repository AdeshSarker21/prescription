@extends('admin.layouts.app')

@section('title', 'Edit Medicine Suggestion - Admin')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white/90">Edit Medicine Suggestion</h1>
            <p class="text-sm text-white/50 mt-1">Review and edit before approving.</p>
        </div>
        <a href="{{ route('admin.medicine-suggestions.index') }}" class="btn-outline-glass mt-4 sm:mt-0">
            Back to Suggestions
        </a>
    </div>

    @if ($errors->any())
        <div class="alert-glass mb-6 bg-red-500/10 border-red-500/20 text-red-400">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <form action="{{ route('admin.medicine-suggestions.update', $suggestion) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="glass-card-static">
                    <div class="p-5 border-b border-white/5">
                        <h3 class="text-lg font-semibold text-white/90">Medicine Details</h3>
                    </div>
                    <div class="p-5 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-white/70 mb-1.5">Medicine Name <span class="text-red-400">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $suggestion->name) }}" required
                                   class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-lg text-white/90 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 placeholder-white/30">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-white/70 mb-1.5">Generic Name</label>
                                <input type="text" name="generic_name" value="{{ old('generic_name', $suggestion->generic_name) }}"
                                       class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-lg text-white/90 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 placeholder-white/30">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-white/70 mb-1.5">Strength</label>
                                <input type="text" name="strength" value="{{ old('strength', $suggestion->strength) }}" placeholder="e.g. 500mg"
                                       class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-lg text-white/90 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 placeholder-white/30">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-white/70 mb-1.5">Category</label>
                                <select name="category_id"
                                        class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-lg text-white/90 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50">
                                    <option value="">-- None --</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('category_id', $suggestion->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-white/70 mb-1.5">Company</label>
                                <input type="text" name="company_name" value="{{ old('company_name', $suggestion->company_name) }}"
                                       class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-lg text-white/90 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 placeholder-white/30">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-white/70 mb-1.5">Admin Notes</label>
                            <textarea name="admin_notes" rows="3" placeholder="Internal notes..."
                                      class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-lg text-white/90 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 placeholder-white/30 resize-none">{{ old('admin_notes', $suggestion->admin_notes) }}</textarea>
                        </div>
                    </div>
                    <div class="p-5 border-t border-white/5 flex items-center justify-end gap-3">
                        <a href="{{ route('admin.medicine-suggestions.index') }}" class="btn-outline-glass">Cancel</a>
                        <button type="submit" class="btn-gradient">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="space-y-6">
            <div class="glass-card-static">
                <div class="p-5 border-b border-white/5">
                    <h3 class="text-lg font-semibold text-white/90">Suggestion Info</h3>
                </div>
                <div class="p-5 space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-white/50">Status</span>
                        <span class="status-badge {{ $suggestion->status === 'pending' ? 'bg-amber-500/20 text-amber-400' : '' }} {{ $suggestion->status === 'approved' ? 'bg-emerald-500/20 text-emerald-400' : '' }} {{ $suggestion->status === 'rejected' ? 'bg-red-500/20 text-red-400' : '' }}">
                            {{ ucfirst($suggestion->status) }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-white/50">Submitted by</span>
                        <span class="text-white/80">{{ $suggestion->doctor->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-white/50">Date</span>
                        <span class="text-white/80">{{ $suggestion->created_at->format('M d, Y') }}</span>
                    </div>
                    @if($suggestion->reason)
                        <div class="pt-3 border-t border-white/5">
                            <span class="text-white/50 text-xs uppercase tracking-wide">Doctor's Reason</span>
                            <p class="text-white/70 mt-1">{{ $suggestion->reason }}</p>
                        </div>
                    @endif
                </div>
            </div>

            @if($suggestion->status === 'pending')
                <div class="glass-card-static">
                    <div class="p-5 space-y-3">
                        <h4 class="text-sm font-semibold text-white/80">Quick Actions</h4>
                        <form action="{{ route('admin.medicine-suggestions.approve', $suggestion) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-500/10 text-emerald-400 text-sm font-medium rounded-lg border border-emerald-500/20 hover:bg-emerald-500/20 transition-all">
                                Approve & Add to Medicines
                            </button>
                        </form>
                        <form action="{{ route('admin.medicine-suggestions.reject', $suggestion) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-red-500/10 text-red-400 text-sm font-medium rounded-lg border border-red-500/20 hover:bg-red-500/20 transition-all">
                                Reject Suggestion
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
