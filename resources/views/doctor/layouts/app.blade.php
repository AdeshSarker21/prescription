<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Doctor Dashboard') - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('styles')
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #818cf8;
            --secondary: #06b6d4;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --glass-bg: rgba(255, 255, 255, 0.55);
            --glass-border: rgba(255, 255, 255, 0.4);
            --glass-shadow: 0 8px 32px rgba(0, 0, 0, 0.06);
            --glass-blur: 16px;
            --text-primary: #1e293b;
            --text-muted: #64748b;
            --radius: 14px;
            --radius-sm: 10px;
            --radius-xs: 6px;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        @keyframes floatBlob {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(40px, -40px) scale(1.08); }
            66% { transform: translate(-30px, 30px) scale(0.92); }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-30px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes cardAppear {
            from { opacity: 0; transform: translateY(24px) scale(0.96); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        [x-cloak] { display: none !important; }

        body {
            background: linear-gradient(-45deg, #e0e7ff, #fce7f3, #ccfbf1, #ddd6fe, #e0e7ff);
            background-size: 400% 400%;
            animation: gradientShift 18s ease infinite;
            min-height: 100vh;
        }

        .bg-blob {
            position: fixed;
            border-radius: 50%;
            filter: blur(90px);
            opacity: 0.2;
            pointer-events: none;
            z-index: 0;
        }
        .bg-blob-1 {
            width: 550px; height: 550px;
            background: #6366f1;
            top: -150px; right: -80px;
            animation: floatBlob 25s ease-in-out infinite;
        }
        .bg-blob-2 {
            width: 450px; height: 450px;
            background: #06b6d4;
            bottom: -120px; left: -60px;
            animation: floatBlob 30s ease-in-out infinite reverse;
        }
        .bg-blob-3 {
            width: 350px; height: 350px;
            background: #f43f5e;
            top: 30%; left: 70%;
            animation: floatBlob 22s ease-in-out infinite 8s;
        }

        .glass {
            background: var(--glass-bg);
            backdrop-filter: blur(var(--glass-blur));
            -webkit-backdrop-filter: blur(var(--glass-blur));
            border: 1px solid var(--glass-border);
            box-shadow: var(--glass-shadow);
        }

        .glass-strong {
            background: rgba(255, 255, 255, 0.72);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.06);
        }

        .dashboard-card {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.45);
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.04);
            border-radius: var(--radius);
            padding: 1.5rem;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }
        .dashboard-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.08);
            background: rgba(255, 255, 255, 0.7);
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        .dashboard-card:hover .stat-icon {
            transform: scale(1.08) rotate(-3deg);
        }

        .glass-sidebar {
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-right: 1px solid rgba(255, 255, 255, 0.5);
        }

        .glass-header {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.4);
        }

        .glass-footer {
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-top: 1px solid rgba(255, 255, 255, 0.3);
        }

        .glass-table {
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: var(--radius);
            overflow: hidden;
        }
        .glass-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .glass-table th {
            padding: 12px 16px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            background: rgba(99, 102, 241, 0.06);
            border-bottom: 1px solid rgba(148, 163, 184, 0.15);
        }
        .glass-table td {
            padding: 12px 16px;
            font-size: 13px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.1);
            color: var(--text-primary);
        }
        .glass-table tbody tr {
            transition: all 0.2s ease;
        }
        .glass-table tbody tr:hover {
            background: rgba(99, 102, 241, 0.04);
        }
        .glass-table tbody tr:last-child td {
            border-bottom: none;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        .animate-card {
            animation: cardAppear 0.5s ease-out both;
        }
        .animate-card:nth-child(1) { animation-delay: 0.05s; }
        .animate-card:nth-child(2) { animation-delay: 0.12s; }
        .animate-card:nth-child(3) { animation-delay: 0.19s; }
        .animate-card:nth-child(4) { animation-delay: 0.26s; }
        .animate-card:nth-child(5) { animation-delay: 0.33s; }
        .animate-card:nth-child(6) { animation-delay: 0.40s; }

        .glass-dropdown {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 16px 48px rgba(0, 0, 0, 0.08);
            border-radius: var(--radius-sm);
            overflow: visible;
        }
        .glass-dropdown-scroll {
            max-height: 360px;
            overflow-y: auto;
            border-radius: inherit;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            line-height: 1.1;
        }

        .btn-gradient {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.25);
        }
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.35);
        }
        .btn-gradient:active {
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <div class="bg-blob bg-blob-1"></div>
    <div class="bg-blob bg-blob-2"></div>
    <div class="bg-blob bg-blob-3"></div>

    <div x-data="{ sidebarOpen: false }" class="min-h-screen flex relative z-10">
        {{-- Sidebar --}}
        <aside class="fixed inset-y-0 left-0 z-30 w-64 glass-sidebar text-gray-800 transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-auto print:hidden"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            <div class="flex items-center justify-between h-16 px-4 border-b border-white/40">
                <a href="{{ route('doctor.dashboard') }}" class="flex items-center gap-3">
                    <div class="w-100 h-100 overflow-hidden shadow-lg shadow-indigo-100 mt-4">
                        <img src="{{ asset('image/user-dashboard/logo.png') }}" alt="Logo" class="w-full h-full object-cover">
                    </div>
                    {{-- <span class="text-lg font-bold text-gray-800">Dr. Portal</span> --}}
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                <x-doctor-nav-link href="{{ route('doctor.dashboard') }}" :active="request()->routeIs('doctor.dashboard')">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Dashboard</span>
                </x-doctor-nav-link>

                <x-doctor-nav-link href="{{ route('doctor.patients.index') }}" :active="request()->routeIs('doctor.patients.*')">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Patients</span>
                </x-doctor-nav-link>

                <x-doctor-nav-link href="{{ route('doctor.prescriptions.index') }}" :active="request()->routeIs('doctor.prescriptions.*')">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Prescriptions</span>
                </x-doctor-nav-link>

                <x-doctor-nav-link href="{{ route('doctor.medicines.index') }}" :active="request()->routeIs('doctor.medicines.*')">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                    <span>Medicines</span>
                </x-doctor-nav-link>

                <x-doctor-nav-link href="{{ route('doctor.ai-assistant') }}" :active="request()->routeIs('doctor.ai-assistant')">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>AI Medical Assistant</span>
                </x-doctor-nav-link>

                <x-doctor-nav-link href="{{ route('doctor.appointments.index') }}" :active="request()->routeIs('doctor.appointments.*')">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Appointments</span>
                    @php
                        $scheduledCount = \App\Models\Appointment::where('doctor_id', auth()->id())
                            ->where('status', 'scheduled')
                            ->count();
                    @endphp
                    @if($scheduledCount > 0)
                        <span class="ml-auto px-2 py-0.5 text-xs font-bold text-white bg-indigo-500 rounded-full">{{ $scheduledCount }}</span>
                    @endif
                </x-doctor-nav-link>

                @if(auth()->user()->hasModulePermission('smart_serial', 'view'))
                <x-doctor-nav-link href="{{ route('doctor.smart-serial.dashboard') }}" :active="request()->routeIs('doctor.smart-serial.dashboard')">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                    <span>Serial Dashboard</span>
                </x-doctor-nav-link>
                <x-doctor-nav-link href="{{ route('doctor.smart-serial.index') }}" :active="request()->routeIs('doctor.smart-serial.index')">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Smart Serial</span>
                </x-doctor-nav-link>
                @endif

                <x-doctor-nav-link href="{{ route('doctor.reports') }}" :active="request()->routeIs('doctor.reports')">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span>Reports</span>
                </x-doctor-nav-link>

                <div class="border-t border-white/30 my-3"></div>

                <x-doctor-nav-link href="{{ route('doctor.sms-center.index') }}" :active="request()->routeIs('doctor.sms-center.*')">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    <span>SMS Center</span>
                </x-doctor-nav-link>

                <x-doctor-nav-link href="{{ route('doctor.profile') }}" :active="request()->routeIs('doctor.profile')">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Profile</span>
                </x-doctor-nav-link>

                <x-doctor-nav-link href="{{ route('doctor.settings') }}" :active="request()->routeIs('doctor.settings')">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Settings</span>
                </x-doctor-nav-link>

                <x-doctor-nav-link href="{{ route('doctor.subscription') }}" :active="request()->routeIs('doctor.subscription')">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    <span>Subscription</span>
                </x-doctor-nav-link>
            </nav>
        </aside>

        {{-- Backdrop --}}
        <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-20 bg-black/20 backdrop-blur-sm lg:hidden"></div>

        {{-- Main Content --}}
        <div class="flex-1 flex flex-col min-h-screen lg:pl-0">
            {{-- Top Bar --}}
            <header class="relative z-30 glass-header print:hidden">
                <div class="flex items-center justify-between h-16 px-4 sm:px-6">
                    <button @click="sidebarOpen = true" class="lg:hidden text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <div class="flex-1 lg:flex-none">
                        <h2 class="text-lg font-semibold text-gray-800">@yield('header', 'Dashboard')</h2>
                    </div>

                    <div class="flex items-center gap-4">
                        <a href="{{ route('doctor.subscription') }}" class="text-sm font-medium px-4 py-1.5 rounded-full glass-strong text-indigo-600 hover:text-indigo-800 transition-all">
                            {{ auth()->user()->activePlan()?->name ?? 'No Plan' }}
                        </a>

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
                                fetch('{{ route("doctor.notifications.unread-count") }}')
                                    .then(r => r.json())
                                    .then(d => { this.count = d.count; })
                                    .catch(() => {});
                            },
                            fetchNotifications() {
                                fetch('{{ route("doctor.notifications.recent") }}')
                                    .then(r => r.json())
                                    .then(d => { this.notifications = d; })
                                    .catch(() => {});
                            },
                            markRead(id) {
                                fetch('{{ url("doctor/notifications") }}/' + id + '/read', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
                                    .then(() => { this.fetchCount(); this.fetchNotifications(); })
                                    .catch(() => {});
                            },
                            markAllRead() {
                                fetch('{{ route("doctor.notifications.mark-all-read") }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
                                    .then(() => { this.count = 0; this.fetchNotifications(); })
                                    .catch(() => {});
                            }
                        }">
                            <button @click="open = !open; if(open){ $el.querySelector('.badge').classList.add('hidden'); }" class="relative p-2 rounded-xl glass-strong text-gray-500 hover:text-gray-700 transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                <span class="badge absolute -top-0.5 -right-0.5 w-4 h-4 bg-gradient-to-br from-rose-500 to-red-500 text-white text-[10px] rounded-full flex items-center justify-center shadow-lg shadow-rose-200" x-show="count > 0" x-text="count"></span>
                            </button>
                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-80 glass-dropdown z-50" x-cloak>
                                <div class="glass-dropdown-scroll">
                                    <div class="flex items-center justify-between px-4 py-3 border-b border-white/30">
                                        <p class="text-sm font-semibold text-gray-900">Notifications</p>
                                        <button x-show="count > 0" @click="markAllRead" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Mark all read</button>
                                    </div>
                                    <template x-if="notifications.length === 0">
                                        <p class="text-sm text-gray-500 text-center py-8">No notifications</p>
                                    </template>
                                    <template x-for="n in notifications" :key="n.id">
                                        <a :href="'{{ route('doctor.notifications.index') }}'" @click.prevent="markRead(n.id); open = false" class="flex items-start gap-3 px-4 py-3 hover:bg-white/40 border-b border-white/20 last:border-0 transition-colors" :class="!n.read ? 'bg-indigo-50/30' : ''">
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm text-gray-900" x-text="n.message"></p>
                                                <p class="text-xs text-gray-400 mt-0.5" x-text="n.created_at"></p>
                                            </div>
                                            <template x-if="!n.read">
                                                <span class="w-2 h-2 bg-indigo-500 rounded-full flex-shrink-0 mt-1.5"></span>
                                            </template>
                                        </a>
                                    </template>
                                    <a href="{{ route('doctor.notifications.index') }}" class="block text-center text-sm text-indigo-600 hover:text-indigo-800 py-3 border-t border-white/30 font-medium">View All</a>
                                </div>
                            </div>
                        </div>

                        {{-- Profile Dropdown --}}
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center gap-2 text-sm text-gray-700 hover:text-gray-900 p-1.5 rounded-xl glass-strong transition-all">
                                @if(auth()->user()->avatar)
                                    <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-full object-cover shadow-lg shadow-indigo-200">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center text-white font-bold text-xs shadow-lg shadow-indigo-200">
                                        {{ substr(auth()->user()->name, 0, 2) }}
                                    </div>
                                @endif
                                <span class="hidden sm:block font-medium">{{ auth()->user()->name }}</span>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 glass-dropdown py-1 z-50" x-cloak>
                                <a href="{{ route('doctor.sms-center.index') }}" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 hover:pl-6 transition-all duration-200">SMS Center</a>
                                <a href="{{ route('doctor.profile') }}" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 hover:pl-6 transition-all duration-200">Profile</a>
                                <a href="{{ route('doctor.settings') }}" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 hover:pl-6 transition-all duration-200">Settings</a>
                                <div class="border-t border-white/30 my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 hover:pl-6 transition-all duration-200">Logout</button>
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
            <footer class="glass-footer px-6 py-4 print:hidden">
                <div class="flex flex-col sm:flex-row justify-between items-center text-sm text-gray-500">
                    <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                    <p>Doctor Portal v1.0</p>
                </div>
            </footer>
        </div>
    </div>

    {{-- Subscription Expiry Full-Screen Popup --}}
    @if(auth()->check() && auth()->user()->isDoctor())
    @php
        $user = auth()->user();
        $activeSubscription = $user->subscription;
        $isExpired = $activeSubscription && $activeSubscription->isExpired();
        $isExpiringSoon = $activeSubscription && $activeSubscription->status === 'active' && $activeSubscription->isExpiringSoon();
    @endphp

    @if($isExpired)
    <div x-data="{ show: true }" x-show="show" x-cloak
         class="fixed inset-0 z-[9999] flex items-center justify-center"
         style="background:rgba(0,0,0,0.7);backdrop-filter:blur(8px);">
        <div class="glass-strong rounded-2xl p-8 max-w-lg w-full mx-4 text-center" style="background:rgba(255,255,255,0.95);box-shadow:0 25px 60px rgba(0,0,0,0.3);">
            <div class="w-20 h-20 mx-auto mb-6 rounded-full flex items-center justify-center" style="background:rgba(239,68,68,0.1);">
                <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-3">Subscription Expired</h2>
            <p class="text-gray-600 mb-2">Your subscription has expired. Please renew your package to continue using the system.</p>
            <p class="text-sm text-gray-500 mb-8">Plan: <strong>{{ $activeSubscription->plan->name ?? 'N/A' }}</strong> | Expired: <strong>{{ $activeSubscription->ends_at?->format('M d, Y') ?? 'N/A' }}</strong></p>
            <a href="{{ route('doctor.subscription.plans') }}"
               class="inline-flex items-center gap-2 px-8 py-3 rounded-xl text-white font-semibold text-sm transition-all"
               style="background:linear-gradient(135deg,#6366f1,#4f46e5);box-shadow:0 4px 14px rgba(99,102,241,0.35);">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Renew Now
            </a>
        </div>
    </div>
    @elseif($isExpiringSoon)
    <div x-data="{ show: true, dismissed: false }" x-show="show && !dismissed" x-cloak
         class="fixed bottom-4 right-4 z-[9998] max-w-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4">
        <div class="rounded-xl p-4 shadow-2xl" style="background:rgba(255,255,255,0.95);backdrop-filter:blur(16px);border:1px solid rgba(245,158,11,0.3);">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full flex-shrink-0 flex items-center justify-center" style="background:rgba(245,158,11,0.1);">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-900">Subscription Expiring</p>
                    <p class="text-xs text-gray-500 mt-0.5">Your plan expires in {{ $activeSubscription->daysUntilExpiry() }} day(s). Renew to avoid interruption.</p>
                    <div class="flex items-center gap-2 mt-3">
                        <a href="{{ route('doctor.subscription.plans') }}" class="px-3 py-1.5 text-xs font-semibold text-white rounded-lg" style="background:#6366f1;">Renew Now</a>
                        <button @click="dismissed = true" class="px-3 py-1.5 text-xs font-medium text-gray-500 rounded-lg hover:bg-gray-100 transition-colors">Dismiss</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endif

    @stack('scripts')
</body>
</html>
