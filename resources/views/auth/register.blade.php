<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'HCI Prescription') }} — Register</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #818cf8;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --glass-bg: rgba(255, 255, 255, 0.06);
            --glass-border: rgba(255, 255, 255, 0.1);
            --glass-shadow: 0 24px 80px rgba(0, 0, 0, 0.3);
            --glass-blur: 20px;
            --radius: 20px;
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
        @keyframes cardAppear {
            from { opacity: 0; transform: translateY(24px) scale(0.96); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 0 20px rgba(99, 102, 241, 0.15); }
            50% { box-shadow: 0 0 40px rgba(99, 102, 241, 0.3); }
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Figtree', sans-serif;
            background: linear-gradient(-45deg, #0f0c29, #1a1a4e, #1e1b4b, #0f172a);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow-x: hidden;
        }

        .bg-blob {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.15;
            pointer-events: none;
            z-index: 0;
            animation: floatBlob 18s ease-in-out infinite;
        }
        .bg-blob:nth-child(1) {
            width: 500px; height: 500px;
            background: #6366f1;
            top: -10%; left: -5%;
            animation-delay: 0s;
        }
        .bg-blob:nth-child(2) {
            width: 400px; height: 400px;
            background: #06b6d4;
            bottom: -8%; right: -4%;
            animation-delay: -6s;
        }
        .bg-blob:nth-child(3) {
            width: 350px; height: 350px;
            background: #a855f7;
            top: 40%; left: 50%;
            animation-delay: -12s;
        }

        .register-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 500px;
            animation: fadeInUp 0.6s ease-out;
        }

        .register-card {
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 40px 36px;
            animation: cardAppear 0.8s ease-out;
            box-shadow: 0 24px 80px rgba(0, 0, 0, 0.3);
        }

        .register-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .register-header .app-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 12px;
        }
        .register-header .app-logo svg {
            width: 32px; height: 32px;
            color: var(--primary-light);
        }
        .register-header .app-logo span {
            font-size: 20px;
            font-weight: 700;
            color: #fff;
        }
        .register-header h2 {
            font-size: 20px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 4px;
        }
        .register-header p {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.5);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-group {
            margin-bottom: 20px;
        }
        .form-group.full {
            grid-column: span 2;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 6px;
        }
        .form-group .input-wrap {
            position: relative;
        }
        .form-group .input-wrap .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.3);
            font-size: 16px;
            pointer-events: none;
        }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group select {
            width: 100%;
            padding: 12px 14px 12px 42px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-xs);
            color: #fff;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.2s ease;
            outline: none;
        }
        .form-group input::placeholder {
            color: rgba(255, 255, 255, 0.25);
        }
        .form-group input:focus {
            border-color: var(--primary-light);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }
        .form-group select {
            appearance: none;
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='rgba(255,255,255,0.4)' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            padding-right: 40px;
        }
        .form-group select option {
            background: #1e1b4b;
            color: #fff;
        }

        .form-group .error {
            font-size: 12px;
            color: #f87171;
            margin-top: 4px;
        }

        .btn-register {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: var(--radius-xs);
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 16px rgba(99, 102, 241, 0.25);
            margin-top: 4px;
        }
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(99, 102, 241, 0.35);
        }
        .btn-register:active {
            transform: translateY(0);
        }

        .register-footer {
            text-align: center;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
        }
        .register-footer p {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.4);
        }
        .register-footer a {
            color: #a5b4fc;
            font-weight: 600;
            text-decoration: none;
        }
        .register-footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 500px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            .form-group.full {
                grid-column: span 1;
            }
            .register-card {
                padding: 28px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="bg-blob"></div>
    <div class="bg-blob"></div>
    <div class="bg-blob"></div>

    <div class="register-wrapper">
        <div class="register-card">
            <div class="register-header">
                <a href="/" class="app-logo" style="text-decoration:none;">
                    <svg viewBox="0 0 316 316" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M305.8 81.125C305.77 80.995 305.69 80.885 305.65 80.755C305.56 80.525 305.49 80.285 305.37 80.075C305.29 79.935 305.17 79.815 305.07 79.685C304.94 79.515 304.83 79.325 304.68 79.175C304.55 79.045 304.39 78.955 304.25 78.845C304.09 78.715 303.95 78.575 303.77 78.475L251.32 48.275C249.97 47.495 248.31 47.495 246.96 48.275L194.51 78.475C194.33 78.575 194.19 78.725 194.03 78.845C193.89 78.955 193.73 79.045 193.6 79.175C193.45 79.325 193.34 79.515 193.21 79.685C193.11 79.815 192.99 79.935 192.91 80.075C192.79 80.285 192.71 80.525 192.63 80.755C192.58 80.875 192.51 80.995 192.48 81.125C192.38 81.495 192.33 81.875 192.33 82.265V139.625L148.62 164.795V52.575C148.62 52.185 148.57 51.805 148.47 51.435C148.44 51.305 148.36 51.195 148.32 51.065C148.23 50.835 148.16 50.595 148.04 50.385C147.96 50.245 147.84 50.125 147.74 49.995C147.61 49.825 147.5 49.635 147.35 49.485C147.22 49.355 147.06 49.265 146.92 49.155C146.76 49.025 146.62 48.885 146.44 48.785L93.99 18.585C92.64 17.805 90.98 17.805 89.63 18.585L37.18 48.785C37 48.885 36.86 49.035 36.7 49.155C36.56 49.265 36.4 49.355 36.27 49.485C36.12 49.635 36.01 49.825 35.88 49.995C35.78 50.125 35.66 50.245 35.58 50.385C35.46 50.595 35.38 50.835 35.3 51.065C35.25 51.185 35.18 51.305 35.15 51.435C35.05 51.805 35 52.185 35 52.575V232.235C35 233.795 35.84 235.245 37.19 236.025L142.1 296.425C142.33 296.555 142.58 296.635 142.82 296.725C142.93 296.765 143.04 296.835 143.16 296.865C143.53 296.965 143.9 297.015 144.28 297.015C144.66 297.015 145.03 296.965 145.4 296.865C145.5 296.835 145.59 296.775 145.69 296.745C145.95 296.655 146.21 296.565 146.45 296.435L251.36 236.035C252.72 235.255 253.55 233.815 253.55 232.245V174.885L303.81 145.945C305.17 145.165 306 143.725 306 142.155V82.265C305.95 81.875 305.89 81.495 305.8 81.125Z" fill="currentColor"/>
                    </svg>
                    <span>{{ config('app.name') }}</span>
                </a>
                <h2>Create an Account</h2>
                <p>Register as a doctor and start your practice</p>
            </div>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="form-row">
                    <div class="form-group full">
                        <label for="name">Full Name</label>
                        <div class="input-wrap">
                            <span class="input-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            </span>
                            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Dr. John Doe">
                        </div>
                        @error('name')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group full">
                        <label for="email">Email Address</label>
                        <div class="input-wrap">
                            <span class="input-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                            </span>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="doctor@clinic.com">
                        </div>
                        @error('email')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group full">
                        <label for="role">Role</label>
                        <div class="input-wrap" style="position:relative;">
                            <span class="input-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </span>
                            <select id="role" name="role" required>
                                <option value="doctor" selected>Doctor</option>
                            </select>
                        </div>
                        @error('role')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrap">
                            <span class="input-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </span>
                            <input id="password" type="password" name="password" required autocomplete="new-password" placeholder="Min. 8 characters">
                        </div>
                        @error('password')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Confirm Password</label>
                        <div class="input-wrap">
                            <span class="input-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/><circle cx="12" cy="16" r="1"/></svg>
                            </span>
                            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Repeat password">
                        </div>
                        @error('password_confirmation')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <button type="submit" class="btn-register">
                    Create Account
                </button>
            </form>

            <div class="register-footer">
                <p>
                    Already have an account?
                    <a href="{{ route('login') }}">Sign in</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
