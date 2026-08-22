@extends('doctor.layouts.app')

@section('title', 'Prescriptions')

@section('header', 'Prescriptions')

@section('content')
<div class="space-y-6">
    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center gap-3">
                <div class="p-3 rounded-lg bg-indigo-50 text-indigo-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Total Prescriptions</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $prescriptions->total() }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center gap-3">
                <div class="p-3 rounded-lg bg-green-50 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500">This Month</p>
                    <p class="text-2xl font-bold text-gray-900">
                        @php
                            $monthCount = \App\Models\Prescription::where('doctor_id', auth()->id())
                                ->whereMonth('created_at', now()->month)
                                ->whereYear('created_at', now()->year)
                                ->count();
                        @endphp
                        {{ $monthCount }}
                    </p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center gap-3">
                <div class="p-3 rounded-lg bg-amber-50 text-amber-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Today</p>
                    <p class="text-2xl font-bold text-gray-900">
                        @php
                            $todayCount = \App\Models\Prescription::where('doctor_id', auth()->id())
                                ->whereDate('created_at', today())
                                ->count();
                        @endphp
                        {{ $todayCount }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Status Filter --}}
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('doctor.prescriptions.index') }}" class="px-3 py-1.5 rounded-full text-xs font-semibold transition-all {{ !request('status') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">All</a>
        <a href="{{ route('doctor.prescriptions.index', ['status' => 'investigation_pending']) }}" class="px-3 py-1.5 rounded-full text-xs font-semibold transition-all {{ request('status') == 'investigation_pending' ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Pending Investigation</a>
        <a href="{{ route('doctor.prescriptions.index', ['status' => 'report_received']) }}" class="px-3 py-1.5 rounded-full text-xs font-semibold transition-all {{ request('status') == 'report_received' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Report Received</a>
        <a href="{{ route('doctor.prescriptions.index', ['status' => 'treatment_started']) }}" class="px-3 py-1.5 rounded-full text-xs font-semibold transition-all {{ request('status') == 'treatment_started' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Treatment Started</a>
        <a href="{{ route('doctor.prescriptions.index', ['status' => 'follow_up']) }}" class="px-3 py-1.5 rounded-full text-xs font-semibold transition-all {{ request('status') == 'follow_up' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Follow Up</a>
        <a href="{{ route('doctor.prescriptions.index', ['status' => 'completed']) }}" class="px-3 py-1.5 rounded-full text-xs font-semibold transition-all {{ request('status') == 'completed' ? 'bg-gray-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Completed</a>
    </div>

    {{-- Search & Actions --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <form method="GET" action="{{ route('doctor.prescriptions.index') }}" class="relative flex-1 max-w-md">
            @if(request('status'))
            <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by patient name, phone or prescription number..." class="w-full pl-10 pr-4 py-2.5 border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
        </form>
        <a href="{{ route('doctor.prescriptions.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 active:scale-95 transition-all shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Create Prescription
        </a>
    </div>

    {{-- Table --}}
    <div id="prescriptions-table" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Prescription</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Patient</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Items</th>
                        <th class="px-6 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($prescriptions as $prescription)
                    <tr class="hover:bg-indigo-50/40 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-semibold text-indigo-600">#{{ $prescription->prescription_number }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm font-semibold">
                                    {{ strtoupper(substr($prescription->patient->name, 0, 1)) }}
                                </div>
                                <span class="text-sm font-medium text-gray-900">{{ $prescription->patient->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $prescription->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-medium {{ ($prescription->items_count ?? $prescription->items->count()) > 0 ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $prescription->items_count ?? $prescription->items->count() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @php $statusClasses = \App\Models\Prescription::colorClasses($prescription->status); @endphp
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold {{ $statusClasses['bg'] }} {{ $statusClasses['text'] }} {{ $statusClasses['border'] }} border">
                                <span class="w-1.5 h-1.5 rounded-full {{ $statusClasses['dot'] }}"></span>
                                {{ $prescription->statusLabel() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('doctor.prescriptions.show', $prescription) }}" class="p-2 text-indigo-600 hover:text-white hover:bg-indigo-600 rounded-lg transition-all" title="View">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('doctor.prescriptions.edit', $prescription) }}" class="p-2 text-amber-600 hover:text-white hover:bg-amber-600 rounded-lg transition-all" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <a href="{{ route('doctor.prescriptions.print', $prescription) }}" class="p-2 text-gray-600 hover:text-white hover:bg-gray-600 rounded-lg transition-all" title="Print">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                </a>
                                <button type="button" class="p-2 text-red-600 hover:text-white hover:bg-red-600 rounded-lg transition-all" title="Delete"
                                    onclick="confirmDelete('{{ route('doctor.prescriptions.destroy', $prescription) }}')">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <p class="text-gray-500 text-sm font-medium">No prescriptions found</p>
                                <a href="{{ route('doctor.prescriptions.create') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Create your first prescription</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($prescriptions->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $prescriptions->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    function confirmDelete(url) {
        Swal.fire({
            title: 'Delete Prescription?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, delete',
            cancelButtonText: 'Cancel',
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = url;
                form.style.display = 'none';
                const token = document.querySelector('meta[name="csrf-token"]')?.content;
                if (token) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = '_token';
                    input.value = token;
                    form.appendChild(input);
                }
                const method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                method.value = 'DELETE';
                form.appendChild(method);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    const searchForm = document.querySelector('form[action*="prescriptions"]');
    const searchInput = searchForm?.querySelector('input[name="search"]');
    const tableContainer = document.getElementById('prescriptions-table');
    let searchTimer;

    function buildSearchUrl() {
        const params = new URLSearchParams();
        if (searchInput?.value) params.set('search', searchInput.value);
        const statusInput = searchForm?.querySelector('input[name="status"]');
        if (statusInput?.value) params.set('status', statusInput.value);
        return window.location.pathname + '?' + params.toString();
    }

    async function fetchPrescriptions(url) {
        try {
            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) return;
            const html = await res.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newTable = doc.getElementById('prescriptions-table');
            if (newTable && tableContainer) {
                tableContainer.innerHTML = newTable.innerHTML;
                history.replaceState(null, '', url);
            }
        } catch (e) {}
    }

    searchInput?.addEventListener('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => fetchPrescriptions(buildSearchUrl()), 350);
    });

    document.addEventListener('click', function(e) {
        const link = e.target.closest('.pagination a, a[href*="page="]');
        if (!link || !tableContainer?.contains(link)) return;
        e.preventDefault();
        fetchPrescriptions(link.href);
    });
</script>
@endpush
@endsection
