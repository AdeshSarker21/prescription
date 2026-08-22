<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Assistant Portal') - {{ config('app.name') }}</title>
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
            position: fixed; border-radius: 50%; filter: blur(90px); opacity: 0.2; pointer-events: none; z-index: 0;
        }
        .bg-blob-1 { width: 550px; height: 550px; background: #6366f1; top: -150px; right: -80px; animation: floatBlob 25s ease-in-out infinite; }
        .bg-blob-2 { width: 450px; height: 450px; background: #06b6d4; bottom: -120px; left: -60px; animation: floatBlob 30s ease-in-out infinite reverse; }
        .bg-blob-3 { width: 350px; height: 350px; background: #f43f5e; top: 30%; left: 70%; animation: floatBlob 22s ease-in-out infinite 8s; }
        .glass { background: var(--glass-bg); backdrop-filter: blur(var(--glass-blur)); border: 1px solid var(--glass-border); box-shadow: var(--glass-shadow); }
        .glass-strong { background: rgba(255,255,255,0.72); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.5); box-shadow: 0 8px 32px rgba(0,0,0,0.06); }
        .dashboard-card {
            background: rgba(255,255,255,0.6); backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,0.45);
            box-shadow: 0 4px 24px rgba(0,0,0,0.04); border-radius: var(--radius); padding: 1.5rem;
            transition: all 0.35s cubic-bezier(0.4,0,0.2,1);
        }
        .dashboard-card:hover { transform: translateY(-4px); box-shadow: 0 12px 40px rgba(0,0,0,0.08); background: rgba(255,255,255,0.7); }
        .stat-icon { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease; }
        .dashboard-card:hover .stat-icon { transform: scale(1.08) rotate(-3deg); }
        .glass-sidebar { background: rgba(255,255,255,0.5); backdrop-filter: blur(24px); border-right: 1px solid rgba(255,255,255,0.5); }
        .glass-header { background: rgba(255,255,255,0.6); backdrop-filter: blur(16px); border-bottom: 1px solid rgba(255,255,255,0.4); }
        .glass-footer { background: rgba(255,255,255,0.4); backdrop-filter: blur(12px); border-top: 1px solid rgba(255,255,255,0.3); }
        .glass-table { background: rgba(255,255,255,0.5); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.4); border-radius: var(--radius); overflow: hidden; }
        .glass-table table { width: 100%; border-collapse: collapse; }
        .glass-table th { padding: 12px 16px; text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); background: rgba(99,102,241,0.06); border-bottom: 1px solid rgba(148,163,184,0.15); }
        .glass-table td { padding: 12px 16px; font-size: 13px; border-bottom: 1px solid rgba(148,163,184,0.1); color: var(--text-primary); }
        .glass-table tbody tr { transition: all 0.2s ease; }
        .glass-table tbody tr:hover { background: rgba(99,102,241,0.04); }
        .glass-table tbody tr:last-child td { border-bottom: none; }
        .status-badge { display: inline-flex; align-items: center; padding: 3px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; letter-spacing: 0.3px; }
        .animate-card { animation: cardAppear 0.5s ease-out both; }
        .animate-card:nth-child(1) { animation-delay: 0.05s; }
        .animate-card:nth-child(2) { animation-delay: 0.12s; }
        .animate-card:nth-child(3) { animation-delay: 0.19s; }
        .animate-card:nth-child(4) { animation-delay: 0.26s; }
        .glass-dropdown { background: rgba(255,255,255,0.88); backdrop-filter: blur(24px); border: 1px solid rgba(255,255,255,0.5); box-shadow: 0 16px 48px rgba(0,0,0,0.08); border-radius: var(--radius-sm); overflow: visible; }
        .stat-value { font-size: 2rem; font-weight: 800; letter-spacing: -0.5px; line-height: 1.1; }
        .btn-gradient { background: linear-gradient(135deg, #6366f1, #4f46e5); color: white; border: none; padding: 10px 24px; border-radius: var(--radius-sm); font-weight: 600; font-size: 13px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 14px rgba(99,102,241,0.25); }
        .btn-gradient:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(99,102,241,0.35); }
        .nav-item { position: relative; }
        .nav-item.active { background: rgba(99,102,241,0.08); color: #4f46e5; font-weight: 600; }
        .nav-item.active::before { content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%); width: 3px; height: 60%; background: linear-gradient(180deg, #6366f1, #4f46e5); border-radius: 0 4px 4px 0; }
        input, select, textarea { font-family: 'Figtree', sans-serif; }
    </style>
</head>
<body class="font-sans antialiased">
    <div class="bg-blob bg-blob-1"></div>
    <div class="bg-blob bg-blob-2"></div>
    <div class="bg-blob bg-blob-3"></div>

    <div x-data="{ sidebarOpen: false }" class="min-h-screen flex relative z-10">
        <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-20 bg-black/20 backdrop-blur-sm lg:hidden"></div>

        <aside class="fixed inset-y-0 left-0 z-30 w-64 glass-sidebar text-gray-800 transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-auto print:hidden"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            <div class="flex items-center justify-between h-16 px-4 border-b border-white/40">
                <a href="{{ route('assistant.dashboard') }}" class="flex items-center gap-3">
                    <div class="w-100 h-100 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center shadow-lg shadow-emerald-100 mt-4">
                        <img src="{{ asset('image/user-dashboard/logo.png') }}" alt="Logo" class="w-full h-full object-cover">
                    </div>
                    {{-- <span class="text-lg font-bold text-gray-800">Assistant</span> --}}
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                <a href="{{ route('assistant.dashboard') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('assistant.dashboard') ? 'active bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:text-gray-800 hover:bg-white/40' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('assistant.appointments.index') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('assistant.appointments.*') ? 'active bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:text-gray-800 hover:bg-white/40' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Appointments</span>
                    @php
                        $doctorIds = auth()->user()->getAccessibleDoctorIds();
                        $todayCount = \App\Models\Appointment::whereIn('doctor_id', $doctorIds)
                            ->whereDate('appointment_date', now()->toDateString())
                            ->where('status', 'scheduled')
                            ->count();
                    @endphp
                    @if($todayCount > 0)
                        <span class="ml-auto px-2 py-0.5 text-xs font-bold text-white bg-indigo-500 rounded-full">{{ $todayCount }}</span>
                    @endif
                </a>

                <a href="{{ route('assistant.patients.create') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('assistant.patients.*') ? 'active bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:text-gray-800 hover:bg-white/40' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    <span>New Patient</span>
                </a>

                <div class="border-t border-gray-200/50 my-3"></div>

                <a href="{{ route('assistant.profile') }}" class="nav-item flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('assistant.profile') ? 'active bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:text-gray-800 hover:bg-white/40' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Profile</span>
                </a>
            </nav>
        </aside>

        <div class="flex-1 flex flex-col min-h-screen lg:pl-0">
            <header class="glass-header print:hidden">
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
                        @if(auth()->user()->assignedDoctors()->count() > 0)
                        <div class="hidden sm:flex items-center gap-2 text-sm text-gray-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span>{{ auth()->user()->assignedDoctors()->pluck('name')->implode(', ') }}</span>
                        </div>
                        @endif

                        {{-- Notifications --}}
                        @php
                            $unreadCount = auth()->user()->unreadNotifications->count();
                        @endphp
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="relative p-2 text-gray-500 hover:text-gray-700 hover:bg-white/40 rounded-xl transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                @if($unreadCount > 0)
                                    <span class="absolute -top-0.5 -right-0.5 px-1.5 py-0.5 text-xs font-bold text-white bg-red-500 rounded-full leading-none">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                                @endif
                            </button>
                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-80 glass-dropdown py-1 z-50" x-cloak>
                                <div class="px-4 py-2 border-b border-white/30">
                                    <p class="text-sm font-semibold text-gray-800">Notifications</p>
                                </div>
                                @if($unreadCount > 0)
                                    <div class="max-h-64 overflow-y-auto">
                                        @foreach(auth()->user()->unreadNotifications->take(5) as $notification)
                                            <div class="px-4 py-3 hover:bg-white/40 transition-colors border-b border-white/20 last:border-0">
                                                <p class="text-xs text-gray-700">{{ $notification->data['message'] ?? 'New notification' }}</p>
                                                <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="px-4 py-2 border-t border-white/30">
                                        <form method="POST" action="{{ route('assistant.notifications.markAllRead') }}">
                                            @csrf
                                            <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Mark all as read</button>
                                        </form>
                                    </div>
                                @else
                                    <div class="px-4 py-6 text-center">
                                        <p class="text-sm text-gray-400">No new notifications</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center gap-2 text-sm text-gray-700 hover:text-gray-900 p-1.5 rounded-xl glass-strong transition-all">
                                @if(auth()->user()->avatar)
                                    <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-full object-cover shadow-lg shadow-emerald-200">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center text-white font-bold text-xs shadow-lg shadow-emerald-200">
                                        {{ substr(auth()->user()->name, 0, 2) }}
                                    </div>
                                @endif
                                <span class="hidden sm:block font-medium">{{ auth()->user()->name }}</span>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 glass-dropdown py-1 z-50" x-cloak>
                                <a href="{{ route('assistant.profile') }}" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-white/40 transition-colors">Profile</a>
                                <div class="border-t border-white/30 my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-white/40 transition-colors">Logout</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm flex items-center gap-2">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('success') }}
                </div>
                @endif
                @if(session('error'))
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm flex items-center gap-2">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    {{ session('error') }}
                </div>
                @endif
                @yield('content')
            </main>

            <footer class="glass-footer px-6 py-4 print:hidden">
                <div class="flex flex-col sm:flex-row justify-between items-center text-sm text-gray-500">
                    <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                    <p>Assistant Portal v1.0</p>
                </div>
            </footer>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
