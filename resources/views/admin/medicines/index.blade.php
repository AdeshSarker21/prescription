@extends('admin.layouts.app')

@section('title', 'Medicines - Admin')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white/90">Medicines</h1>
            <p class="text-sm text-white/50 mt-1">Manage all medicines in the system.</p>
        </div>
        <div class="mt-4 sm:mt-0 flex gap-3">
            <a href="{{ route('admin.medicines.categories.index') }}" class="btn-outline-glass">
                Categories
            </a>
            <a href="{{ route('admin.medicines.create') }}" class="btn-gradient">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Medicine
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert-glass mb-6">{{ session('success') }}</div>
    @endif

    <div class="glass-card-static relative" x-data="{
        search: '{{ request('search') }}',
        html: '',
        pagination: '',
        loading: false,
        init() {
            this.html = document.getElementById('rows-wrap')?.innerHTML || '';
            this.pagination = document.getElementById('pagination-wrap')?.innerHTML || '';
        },
        fetchData() {
            this.loading = true;
            fetch('{{ route('admin.medicines.index') }}?search=' + encodeURIComponent(this.search), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(data => {
                    this.html = data.html;
                    this.pagination = data.pagination;
                    this.loading = false;
                })
                .catch(() => { this.loading = false; });
        }
    }" x-init="$watch('search', () => { clearTimeout(window._srch); window._srch = setTimeout(() => fetchData(), 400) })">
        <div class="flex items-center justify-between p-5 border-b border-white/5">
            <h3 class="text-lg font-semibold text-white/90">All Medicines</h3>
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" x-model="search" placeholder="Search medicines..." class="glass-input w-64">
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr>
                        <th class="text-left px-5 py-3 font-medium text-white/50 uppercase text-xs">Name</th>
                        <th class="text-left px-5 py-3 font-medium text-white/50 uppercase text-xs">Generic</th>
                        <th class="text-left px-5 py-3 font-medium text-white/50 uppercase text-xs">Category</th>
                        <th class="text-left px-5 py-3 font-medium text-white/50 uppercase text-xs">Company</th>
                        <th class="text-left px-5 py-3 font-medium text-white/50 uppercase text-xs">Status</th>
                        <th class="text-right px-5 py-3 font-medium text-white/50 uppercase text-xs">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5" id="rows-wrap" x-html="html">
                    @include('admin.medicines._rows')
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-white/5" id="pagination-wrap" x-html="pagination">
            {{ $medicines->appends(['search' => request('search')])->links() }}
        </div>
        <div x-show="loading" class="absolute inset-0 bg-black/10 flex items-center justify-center rounded-xl">
            <div class="w-6 h-6 border-2 border-indigo-400 border-t-transparent rounded-full animate-spin"></div>
        </div>
    </div>
@endsection
