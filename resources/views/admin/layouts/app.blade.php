<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Laravel') . ' - Admin')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="font-sans antialiased">
    {{-- Animated Background Blobs --}}
    <div class="bg-blob bg-blob-1"></div>
    <div class="bg-blob bg-blob-2"></div>
    <div class="bg-blob bg-blob-3"></div>
    <div class="bg-blob bg-blob-4"></div>

    <div x-data="{ sidebarOpen: false }" class="min-h-screen relative z-10">
        {{-- Sidebar Overlay (mobile) --}}
        <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm lg:hidden" @click="sidebarOpen = false"></div>

        {{-- Sidebar --}}
        <aside class="fixed inset-y-0 left-0 z-50 w-64 glass-sidebar text-white transform transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0"
               :class="sidebarOpen ? 'translate-x-0' : ''">
            <div class="flex items-center justify-between h-16 px-4 border-b border-white/5">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/20">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </div>
                    <span class="text-lg font-bold text-white/90">Admin Panel</span>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden text-white/40 hover:text-white/70">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <nav class="p-4 space-y-1 overflow-y-auto nav-scroll" style="height: calc(100vh - 4rem);">
                <a href="{{ route('admin.dashboard') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.dashboard') ? 'active bg-indigo-500/10 text-indigo-400' : 'text-white/60 hover:text-white/80 hover:bg-white/5' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('admin.doctors.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.doctors.*') ? 'active bg-indigo-500/10 text-indigo-400' : 'text-white/60 hover:text-white/80 hover:bg-white/5' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Doctors</span>
                </a>
                <a href="{{ route('admin.approvals.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.approvals.*') ? 'active bg-indigo-500/10 text-indigo-400' : 'text-white/60 hover:text-white/80 hover:bg-white/5' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Approvals</span>
                    @if (\App\Models\User::where('is_approved', false)->whereHas('roles', fn($q) => $q->where('name', 'doctor'))->count() > 0)
                        <span class="ml-auto inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-500/20 text-amber-400">
                            {{ \App\Models\User::where('is_approved', false)->whereHas('roles', fn($q) => $q->where('name', 'doctor'))->count() }}
                        </span>
                    @endif
                </a>
                <a href="{{ route('admin.patients.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.patients.*') ? 'active bg-indigo-500/10 text-indigo-400' : 'text-white/60 hover:text-white/80 hover:bg-white/5' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Patients</span>
                </a>
                <a href="{{ route('admin.assistants.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('assistants.*') ? 'active bg-indigo-500/10 text-indigo-400' : 'text-white/60 hover:text-white/80 hover:bg-white/5' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Assistants</span>
                </a>
                <a href="#" class="nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200 text-white/40 hover:text-white/60 hover:bg-white/5 cursor-not-allowed">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Appointments</span>
                </a>
                <a href="#" class="nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200 text-white/40 hover:text-white/60 hover:bg-white/5 cursor-not-allowed">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Prescriptions</span>
                </a>
                <hr class="my-3 border-white/5">
                <a href="{{ route('admin.plans.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.plans.*') ? 'active bg-indigo-500/10 text-indigo-400' : 'text-white/60 hover:text-white/80 hover:bg-white/5' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <span>Packages</span>
                </a>
                <a href="{{ route('admin.subscriptions.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.subscriptions.*') ? 'active bg-indigo-500/10 text-indigo-400' : 'text-white/60 hover:text-white/80 hover:bg-white/5' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    <span>Subscriptions</span>
                </a>
                <a href="{{ route('admin.subscriptions.history') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.subscriptions.history') ? 'active bg-indigo-500/10 text-indigo-400' : 'text-white/60 hover:text-white/80 hover:bg-white/5' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>History</span>
                </a>
                <a href="{{ route('admin.medicines.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.medicines.*') ? 'active bg-indigo-500/10 text-indigo-400' : 'text-white/60 hover:text-white/80 hover:bg-white/5' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Medicines</span>
                </a>
                <a href="{{ route('admin.medicine-suggestions.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.medicine-suggestions.*') ? 'active bg-indigo-500/10 text-indigo-400' : 'text-white/60 hover:text-white/80 hover:bg-white/5' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    <span>Medicine Suggestions</span>
                    @if (\App\Models\MedicineSuggestion::where('status', 'pending')->count() > 0)
                        <span class="ml-auto inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-500/20 text-amber-400">
                            {{ \App\Models\MedicineSuggestion::where('status', 'pending')->count() }}
                        </span>
                    @endif
                </a>
                {{-- Master Data (Collapsible) --}}
                <div x-data="{ open: {{ request()->routeIs('admin.master-data.*') ? 'true' : 'false' }} }" class="rounded-lg">
                    <button @click="open = !open" class="w-full flex items-center justify-between gap-3 px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.master-data.*') ? 'bg-indigo-500/10 text-indigo-400' : 'text-white/60 hover:text-white/80 hover:bg-white/5' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                            <span>Master Data</span>
                        </div>
                        <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" x-transition.duration.200ms x-cloak class="ml-4 mt-1 space-y-1">
                        <a href="{{ route('admin.master-data.index', 'complaints') }}" class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-all duration-200 {{ request()->path() === 'admin/master-data/complaints' ? 'text-indigo-400 bg-indigo-500/10' : 'text-white/50 hover:text-white/70 hover:bg-white/5' }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-white/20 flex-shrink-0"></span>Complaints
                        </a>
                        <a href="{{ route('admin.master-data.index', 'tests') }}" class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-all duration-200 {{ request()->path() === 'admin/master-data/tests' ? 'text-indigo-400 bg-indigo-500/10' : 'text-white/50 hover:text-white/70 hover:bg-white/5' }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-white/20 flex-shrink-0"></span>Tests
                        </a>
                        <a href="{{ route('admin.master-data.index', 'medical-histories') }}" class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-all duration-200 {{ request()->path() === 'admin/master-data/medical-histories' ? 'text-indigo-400 bg-indigo-500/10' : 'text-white/50 hover:text-white/70 hover:bg-white/5' }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-white/20 flex-shrink-0"></span>Past Medical History
                        </a>
                        <a href="{{ route('admin.master-data.index', 'clinical-features') }}" class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-all duration-200 {{ request()->path() === 'admin/master-data/clinical-features' ? 'text-indigo-400 bg-indigo-500/10' : 'text-white/50 hover:text-white/70 hover:bg-white/5' }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-white/20 flex-shrink-0"></span>Clinical Features
                        </a>
                        <a href="{{ route('admin.master-data.index', 'family-histories') }}" class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-all duration-200 {{ request()->path() === 'admin/master-data/family-histories' ? 'text-indigo-400 bg-indigo-500/10' : 'text-white/50 hover:text-white/70 hover:bg-white/5' }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-white/20 flex-shrink-0"></span>Family History
                        </a>
                        <a href="{{ route('admin.master-data.index', 'menstrual-histories') }}" class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-all duration-200 {{ request()->path() === 'admin/master-data/menstrual-histories' ? 'text-indigo-400 bg-indigo-500/10' : 'text-white/50 hover:text-white/70 hover:bg-white/5' }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-white/20 flex-shrink-0"></span>Menstrual History
                        </a>
                        <a href="{{ route('admin.master-data.index', 'drug-histories') }}" class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-all duration-200 {{ request()->path() === 'admin/master-data/drug-histories' ? 'text-indigo-400 bg-indigo-500/10' : 'text-white/50 hover:text-white/70 hover:bg-white/5' }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-white/20 flex-shrink-0"></span>Drug History
                        </a>
                        <a href="{{ route('admin.master-data.index', 'ot-notes') }}" class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-all duration-200 {{ request()->path() === 'admin/master-data/ot-notes' ? 'text-indigo-400 bg-indigo-500/10' : 'text-white/50 hover:text-white/70 hover:bg-white/5' }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-white/20 flex-shrink-0"></span>OT Note / Procedure
                        </a>
                        <a href="{{ route('admin.master-data.index', 'anesthesia-records') }}" class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-all duration-200 {{ request()->path() === 'admin/master-data/anesthesia-records' ? 'text-indigo-400 bg-indigo-500/10' : 'text-white/50 hover:text-white/70 hover:bg-white/5' }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-white/20 flex-shrink-0"></span>Anesthesia
                        </a>
                        <a href="{{ route('admin.master-data.index', 'procedures') }}" class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-all duration-200 {{ request()->path() === 'admin/master-data/procedures' ? 'text-indigo-400 bg-indigo-500/10' : 'text-white/50 hover:text-white/70 hover:bg-white/5' }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-white/20 flex-shrink-0"></span>Procedure
                        </a>
                        <a href="{{ route('admin.master-data.index', 'treatment-plans') }}" class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-all duration-200 {{ request()->path() === 'admin/master-data/treatment-plans' ? 'text-indigo-400 bg-indigo-500/10' : 'text-white/50 hover:text-white/70 hover:bg-white/5' }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-white/20 flex-shrink-0"></span>Treatment Plan
                        </a>
                        <a href="{{ route('admin.master-data.index', 'advice') }}" class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-all duration-200 {{ request()->path() === 'admin/master-data/advice' ? 'text-indigo-400 bg-indigo-500/10' : 'text-white/50 hover:text-white/70 hover:bg-white/5' }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-white/20 flex-shrink-0"></span>Advice
                        </a>
                        <a href="{{ route('admin.master-data.index', 'clinical-seals') }}" class="flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-all duration-200 {{ request()->path() === 'admin/master-data/clinical-seals' ? 'text-indigo-400 bg-indigo-500/10' : 'text-white/50 hover:text-white/70 hover:bg-white/5' }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-white/20 flex-shrink-0"></span>Clinical Seals
                        </a>
                    </div>
                </div>
                <a href="{{ route('admin.settings.payment') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.settings.*') ? 'active bg-indigo-500/10 text-indigo-400' : 'text-white/60 hover:text-white/80 hover:bg-white/5' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Settings</span>
                </a>
                <a href="{{ route('admin.prescription-settings.headers') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.prescription-settings.*') ? 'active bg-indigo-500/10 text-indigo-400' : 'text-white/60 hover:text-white/80 hover:bg-white/5' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Prescription Settings</span>
                </a>
                <a href="{{ route('admin.sms-settings.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.sms-settings.*') ? 'active bg-indigo-500/10 text-indigo-400' : 'text-white/60 hover:text-white/80 hover:bg-white/5' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    <span>SMS Settings</span>
                </a>
                <a href="{{ route('admin.doctor-feature-settings.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.doctor-feature-settings.*') ? 'active bg-indigo-500/10 text-indigo-400' : 'text-white/60 hover:text-white/80 hover:bg-white/5' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 2H9v2h6V2z"/></svg>
                    <span>Doctor Features</span>
                </a>
            </nav>
        </aside>

        {{-- Main Content --}}
        <div class="flex-1 flex flex-col min-h-screen lg:pl-64">
            {{-- Top Bar --}}
            <header class="sticky top-0 z-30 glass-header">
                <div class="flex items-center justify-between h-16 px-4 sm:px-6">
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 text-white/50 rounded-lg hover:bg-white/10 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div class="flex-1 max-w-md ml-4">
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text" placeholder="{{ __('Search...') }}" class="w-full pl-10 pr-4 py-2 text-sm glass-input rounded-lg">
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        {{-- Language Switcher --}}
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" @click.outside="open = false" class="flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-white/60 hover:text-white/80 hover:bg-white/5 rounded-lg transition-all duration-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>{{ app()->getLocale() === 'bn' ? 'বাংলা' : 'English' }}</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" x-transition.origin.top.right class="absolute right-0 mt-2 w-36 glass-dropdown py-1 z-50" x-cloak>
                                <a href="{{ route('language.switch', 'en') }}" class="block px-4 py-2 text-sm {{ app()->getLocale() === 'en' ? 'text-indigo-400 font-medium' : 'text-white/70 hover:bg-white/5' }}">English</a>
                                <a href="{{ route('language.switch', 'bn') }}" class="block px-4 py-2 text-sm {{ app()->getLocale() === 'bn' ? 'text-indigo-400 font-medium' : 'text-white/70 hover:bg-white/5' }}">বাংলা</a>
                            </div>
                        </div>
                        {{-- Notifications --}}
                        <div class="relative" x-data="{
                            open: false,
                            count: 0,
                            notifications: [],
                            init() {
                                this.fetchCount();
                                this.fetchNotifications();
                            },
                            fetchCount() {
                                fetch('{{ route("admin.notifications.unread-count") }}')
                                    .then(r => r.json())
                                    .then(d => { this.count = d.count; })
                                    .catch(() => {});
                            },
                            fetchNotifications() {
                                fetch('{{ route("admin.notifications.recent") }}')
                                    .then(r => r.json())
                                    .then(d => { this.notifications = d; })
                                    .catch(() => {});
                            },
                            markRead(id) {
                                fetch('{{ url("admin/notifications") }}/' + id + '/read', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
                                    .then(() => { this.fetchCount(); this.fetchNotifications(); })
                                    .catch(() => {});
                            },
                            markAllRead() {
                                fetch('{{ route("admin.notifications.mark-all-read") }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
                                    .then(() => { this.count = 0; this.fetchNotifications(); })
                                    .catch(() => {});
                            }
                        }">
                            <button @click="open = !open; if(open){ $el.querySelector('.badge')?.classList.add('hidden'); }" class="relative p-2 text-white/50 hover:text-white/80 hover:bg-white/5 rounded-lg transition-all duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                <span x-show="count > 0" x-text="count" class="badge absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] bg-gradient-to-br from-rose-500 to-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center px-1 shadow-lg shadow-rose-500/30"></span>
                            </button>
                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-80 glass-dropdown z-50" x-cloak>
                                <div class="glass-dropdown-inner">
                                    <div class="flex items-center justify-between px-4 py-3 border-b border-white/5">
                                        <p class="text-sm font-semibold text-white/90">Notifications</p>
                                        <button x-show="count > 0" @click="markAllRead" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium">Mark all read</button>
                                    </div>
                                    <template x-if="notifications.length === 0">
                                        <p class="text-sm text-white/40 text-center py-8">No notifications</p>
                                    </template>
                                    <template x-for="n in notifications" :key="n.id">
                                        <a :href="'{{ route('admin.notifications.index') }}'" @click.prevent="markRead(n.id); open = false" class="flex items-start gap-3 px-4 py-3 hover:bg-white/5 border-b border-white/5 last:border-0 transition-colors" :class="!n.read ? 'bg-indigo-500/5' : ''">
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm text-white/80" x-text="n.message"></p>
                                                <p class="text-xs text-white/30 mt-0.5" x-text="n.created_at"></p>
                                            </div>
                                            <template x-if="!n.read">
                                                <span class="w-2 h-2 bg-indigo-400 rounded-full flex-shrink-0 mt-1.5"></span>
                                            </template>
                                        </a>
                                    </template>
                                    <a href="{{ route('admin.notifications.index') }}" class="block text-center text-sm text-indigo-400 hover:text-indigo-300 py-3 border-t border-white/5 font-medium">View All</a>
                                </div>
                            </div>
                        </div>
                        {{-- Profile Dropdown --}}
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" @click.outside="open = false" class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-white/5 transition-all duration-200">
                                @if(Auth::user()->avatar)
                                    <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="w-8 h-8 rounded-full object-cover shadow-lg">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-sm font-bold shadow-lg shadow-indigo-500/20">
                                        {{ substr(Auth::user()->name, 0, 2) }}
                                    </div>
                                @endif
                                <span class="hidden sm:block text-sm font-medium text-white/70">{{ Auth::user()->name }}</span>
                                <svg class="w-4 h-4 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" x-transition.origin.top.right class="absolute right-0 mt-2 w-48 glass-dropdown py-1 z-50" x-cloak>
                                <a href="{{ route('admin.profile') }}" class="block px-4 py-2.5 text-sm text-white/70 hover:bg-white/5 transition-colors">{{ __('Profile') }}</a>
                                <div class="border-t border-white/5 my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2.5 text-sm text-white/70 hover:bg-white/5 transition-colors">{{ __('Log Out') }}</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Page Content --}}
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                @yield('content')
            </main>

            {{-- Footer --}}
            <footer class="glass-footer px-6 py-4">
                <div class="flex flex-col sm:flex-row justify-between items-center text-sm text-white/40">
                    <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                    <p>Admin Portal v2.0</p>
                </div>
            </footer>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
