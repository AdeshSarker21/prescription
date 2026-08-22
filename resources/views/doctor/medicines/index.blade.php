@extends('doctor.layouts.app')

@section('title', 'Medicines')

@section('header', 'Medicines')

@section('content')
<div class="space-y-6" x-data="{
    search: '{{ request('search') }}',
    category: '{{ request('category') }}',
    html: '',
    pagination: '',
    loading: false,
    init() {
        this.html = document.getElementById('cards-wrap')?.innerHTML || '';
        this.pagination = document.getElementById('pagination-wrap')?.innerHTML || '';
    },
    fetchData() {
        this.loading = true;
        let url = '{{ route('doctor.medicines.index') }}?search=' + encodeURIComponent(this.search) + '&category=' + encodeURIComponent(this.category);
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                this.html = data.html;
                this.pagination = data.pagination;
                this.loading = false;
            })
            .catch(() => { this.loading = false; });
    }
}" x-init="$watch('search', () => { clearTimeout(window._srch); window._srch = setTimeout(() => fetchData(), 400) })">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex-1 flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1 max-w-md">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" x-model="search" placeholder="Search medicines..." class="w-full pl-10 pr-4 py-2 border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            </div>
            <select x-model="category" @change="fetchData()" class="border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                <option value="">All Categories</option>
                @foreach($categories ?? [] as $id => $name)
                <option value="{{ $name }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <a href="{{ route('doctor.medicines.suggest') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Suggest Medicine
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4" id="cards-wrap" x-html="html">
        @include('doctor.medicines._cards')
    </div>

    <div id="pagination-wrap" x-html="pagination" x-show="pagination">
        @if($medicines->hasPages())
        <div class="mt-6">
            {{ $medicines->appends(['search' => request('search'), 'category' => request('category')])->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
