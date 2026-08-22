@extends('admin.layouts.app')

@section('title', 'Pending Approvals - Admin')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white/90">Pending Approvals</h1>
            <p class="text-sm text-white/50 mt-1">Review and approve new doctor registrations.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert-glass mb-6 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="glass-card-static">
        <div class="p-5 border-b border-white/5">
            <h3 class="text-lg font-semibold text-white/90">
                Pending Doctors
                @if ($pendingUsers->count() > 0)
                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-500/20 text-amber-400">
                        {{ $pendingUsers->count() }} pending
                    </span>
                @endif
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr>
                        <th class="text-left px-5 py-3 font-medium text-white/50 uppercase tracking-wider text-xs">Name</th>
                        <th class="text-left px-5 py-3 font-medium text-white/50 uppercase tracking-wider text-xs">Email</th>
                        <th class="text-left px-5 py-3 font-medium text-white/50 uppercase tracking-wider text-xs">Role</th>
                        <th class="text-left px-5 py-3 font-medium text-white/50 uppercase tracking-wider text-xs">Registered</th>
                        <th class="text-right px-5 py-3 font-medium text-white/50 uppercase tracking-wider text-xs">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse ($pendingUsers as $user)
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 bg-amber-500/20 rounded-full flex items-center justify-center text-sm font-semibold text-amber-400">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <span class="font-medium text-white/90">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-white/70">{{ $user->email }}</td>
                            <td class="px-5 py-4">
                                <span class="status-badge bg-indigo-500/20 text-indigo-400">
                                    {{ ucfirst($user->getRoleNames()->first() ?: 'doctor') }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-white/70">{{ $user->created_at->diffForHumans() }}</td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <form action="{{ route('admin.approvals.approve', $user) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 btn-gradient text-xs">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.approvals.reject', $user) }}" method="POST"
                                        data-confirm="Reject and delete {{ $user->name }}? This action cannot be undone."
                                        data-title="Reject Doctor"
                                        data-confirm-text="Yes, reject"
                                        data-cancel-text="Cancel"
                                        data-icon="error">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-500/10 text-red-400 text-xs font-medium rounded-lg border border-red-500/20 hover:bg-red-500/20 transition-all duration-200">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            Reject
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center">
                                <svg class="w-12 h-12 text-white/20 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p class="text-white/50 font-medium">No pending approvals</p>
                                <p class="text-white/40 text-sm mt-1">All doctor registrations have been reviewed.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
