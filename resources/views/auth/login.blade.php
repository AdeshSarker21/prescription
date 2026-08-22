<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'HCI Prescription') }} — Sign In</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #818cf8;
            --success: #10b981;
            --danger: #ef4444;
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

        .login-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
            animation: cardAppear 0.8s ease-out;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 40px 36px;
            box-shadow: 0 24px 80px rgba(0, 0, 0, 0.3);
        }

        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .login-header .app-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 12px;
            text-decoration: none;
        }
        .login-header .app-logo svg {
            width: 36px; height: 36px;
            color: var(--primary-light);
        }
        .login-header .app-logo span {
            font-size: 22px;
            font-weight: 700;
            color: #fff;
        }
        .login-header h2 {
            font-size: 20px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 4px;
        }
        .login-header p {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.5);
        }

        .session-status {
            padding: 10px 14px;
            border-radius: var(--radius-xs);
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: #6ee7b7;
            font-size: 13px;
            margin-bottom: 20px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
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
        .form-group .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.3);
            pointer-events: none;
        }
        .form-group input[type="email"],
        .form-group input[type="password"] {
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
        .form-group .error {
            font-size: 12px;
            color: #f87171;
            margin-top: 4px;
        }

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }
        .form-options label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
            cursor: pointer;
        }
        .form-options input[type="checkbox"] {
            width: 16px; height: 16px;
            border-radius: 4px;
            accent-color: var(--primary);
            cursor: pointer;
        }
        .form-options a {
            font-size: 13px;
            color: rgba(165, 180, 252, 0.8);
            text-decoration: none;
        }
        .form-options a:hover { color: #a5b4fc; }

        .btn-login {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: var(--radius-xs);
            background: linear-gradient(135deg, var(--primary), #4f46e5);
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 16px rgba(99, 102, 241, 0.25);
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(99, 102, 241, 0.35);
        }
        .btn-login:active {
            transform: translateY(0);
        }

        .login-footer {
            text-align: center;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
        }
        .login-footer p {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.4);
        }
        .login-footer a {
            color: #a5b4fc;
            font-weight: 600;
            text-decoration: none;
        }
        .login-footer a:hover { text-decoration: underline; }

        @media (max-width: 500px) {
            .login-card { padding: 28px 20px; }
        }
    </style>
</head>
<body>
    <div class="bg-blob"></div>
    <div class="bg-blob"></div>
    <div class="bg-blob"></div>

    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <a href="/" class="app-logo">
                    <svg viewBox="0 0 316 316" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M305.8 81.125C305.77 80.995 305.69 80.885 305.65 80.755C305.56 80.525 305.49 80.285 305.37 80.075C305.29 79.935 305.17 79.815 305.07 79.685C304.94 79.515 304.83 79.325 304.68 79.175C304.55 79.045 304.39 78.955 304.25 78.845C304.09 78.715 303.95 78.575 303.77 78.475L251.32 48.275C249.97 47.495 248.31 47.495 246.96 48.275L194.51 78.475C194.33 78.575 194.19 78.725 194.03 78.845C193.89 78.955 193.73 79.045 193.6 79.175C193.45 79.325 193.34 79.515 193.21 79.685C193.11 79.815 192.99 79.935 192.91 80.075C192.79 80.285 192.71 80.525 192.63 80.755C192.58 80.875 192.51 80.995 192.48 81.125C192.38 81.495 192.33 81.875 192.33 82.265V139.625L148.62 164.795V52.575C148.62 52.185 148.57 51.805 148.47 51.435C148.44 51.305 148.36 51.195 148.32 51.065C148.23 50.835 148.16 50.595 148.04 50.385C147.96 50.245 147.84 50.125 147.74 49.995C147.61 49.825 147.5 49.635 147.35 49.485C147.22 49.355 147.06 49.265 146.92 49.155C146.76 49.025 146.62 48.885 146.44 48.785L93.99 18.585C92.64 17.805 90.98 17.805 89.63 18.585L37.18 48.785C37 48.885 36.86 49.035 36.7 49.155C36.56 49.265 36.4 49.355 36.27 49.485C36.12 49.635 36.01 49.825 35.88 49.995C35.78 50.125 35.66 50.245 35.58 50.385C35.46 50.595 35.38 50.835 35.3 51.065C35.25 51.185 35.18 51.305 35.15 51.435C35.05 51.805 35 52.185 35 52.575V232.235C35 233.795 35.84 245.245 37.19 246.025L142.1 306.425C142.33 306.555 142.58 306.635 142.82 306.725C142.93 306.765 143.04 306.835 143.16 306.865C143.53 306.965 143.9 307.015 144.28 307.015C144.66 307.015 145.03 306.965 145.4 306.865C145.5 306.835 145.59 306.775 145.69 306.745C145.95 306.655 146.21 306.565 146.45 306.435L251.36 246.035C252.72 245.255 253.55 243.815 253.55 242.245V184.885L303.81 155.945C305.17 155.165 306 153.725 306 152.155V82.265C305.95 81.875 305.89 81.495 305.8 81.125Z" fill="currentColor"/>
                    </svg>
                    <span>{{ config('app.name') }}</span>
                </a>
                <h2>Welcome Back</h2>
                <p>Sign in to manage your practice</p>
            </div>

            @if (session('status'))
                <div class="session-status">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrap">
                        <span class="input-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        </span>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="doctor@clinic.com">
                    </div>
                    @error('email')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <span class="input-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </span>
                        <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;">
                    </div>
                    @error('password')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-options">
                    <label>
                        <input type="checkbox" name="remember">
                        Remember me
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}">Forgot password?</a>
                    @endif
                </div>

                <button type="submit" class="btn-login">Sign In</button>
            </form>

            <div class="login-footer">
                <p>
                    Don't have an account?
                    <a href="{{ route('register') }}">Create one</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
