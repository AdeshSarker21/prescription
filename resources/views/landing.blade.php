<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'HCI Prescription') }} — Modern Prescription Management</title>
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
            --glass-bg: rgba(255, 255, 255, 0.6);
            --glass-border: rgba(255, 255, 255, 0.35);
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
            overflow-x: hidden;
            color: var(--text-primary);
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

        .landing-container {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .landing-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            max-width: 1200px;
            width: 100%;
            gap: 40px;
            align-items: center;
        }

        .hero-section {
            animation: slideInLeft 0.8s ease-out;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            border-radius: 50px;
            background: rgba(99, 102, 241, 0.15);
            border: 1px solid rgba(99, 102, 241, 0.25);
            color: #a5b4fc;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 24px;
            backdrop-filter: blur(8px);
        }
        .hero-badge .dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: #10b981;
            animation: pulseGlow 2s ease-in-out infinite;
        }

        .hero-title {
            font-size: 48px;
            font-weight: 800;
            line-height: 1.15;
            color: #fff;
            margin-bottom: 16px;
            letter-spacing: -0.02em;
        }
        .hero-title .gradient-text {
            background: linear-gradient(135deg, #818cf8, #a78bfa, #6366f1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-subtitle {
            font-size: 18px;
            line-height: 1.7;
            color: rgba(255, 255, 255, 0.65);
            margin-bottom: 40px;
            max-width: 480px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .feature-card {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 16px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: var(--radius-sm);
            backdrop-filter: blur(8px);
            transition: all 0.3s ease;
        }
        .feature-card:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(99, 102, 241, 0.2);
            transform: translateY(-2px);
        }
        .feature-icon {
            width: 40px; height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        .feature-icon.purple { background: rgba(99, 102, 241, 0.15); color: #a5b4fc; }
        .feature-icon.cyan { background: rgba(6, 182, 212, 0.15); color: #67e8f9; }
        .feature-icon.green { background: rgba(16, 185, 129, 0.15); color: #6ee7b7; }
        .feature-icon.orange { background: rgba(245, 158, 11, 0.15); color: #fcd34d; }

        .feature-text h4 {
            font-size: 14px;
            font-weight: 600;
            color: #fff;
            margin-bottom: 2px;
        }
        .feature-text p {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.5);
            line-height: 1.4;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 40px 36px;
            animation: cardAppear 0.8s ease-out;
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
            font-size: 16px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.5);
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
        .form-group .input-wrap .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.3);
            font-size: 16px;
            pointer-events: none;
        }
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group input[type="text"] {
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
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: transparent;
            accent-color: var(--primary);
            cursor: pointer;
        }
        .form-options a {
            font-size: 13px;
            color: rgba(165, 180, 252, 0.8);
            text-decoration: none;
            transition: color 0.2s;
        }
        .form-options a:hover {
            color: #a5b4fc;
        }

        .btn-login {
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
        .login-footer a:hover {
            text-decoration: underline;
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

        @media (max-width: 900px) {
            .landing-grid {
                grid-template-columns: 1fr;
                gap: 32px;
            }
            .hero-section {
                text-align: center;
            }
            .hero-title {
                font-size: 32px;
            }
            .hero-subtitle {
                max-width: 100%;
                margin-left: auto;
                margin-right: auto;
            }
            .features-grid {
                grid-template-columns: 1fr 1fr;
            }
            .login-card {
                padding: 28px 24px;
            }
        }
        @media (max-width: 500px) {
            .features-grid {
                grid-template-columns: 1fr;
            }
            .hero-title {
                font-size: 26px;
            }
            .landing-container {
                padding: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="bg-blob"></div>
    <div class="bg-blob"></div>
    <div class="bg-blob"></div>

    <div class="landing-container">
        <div class="landing-grid">
            <div class="hero-section">
                <div class="hero-badge">
                    <span class="dot"></span>
                    Trusted by 500+ healthcare professionals
                </div>

                <h1 class="hero-title">
                    Modern <span class="gradient-text">Prescription</span><br>
                    Management System
                </h1>

                <p class="hero-subtitle">
                    Streamline your practice with digital prescriptions, patient records, lab reports, and smart analytics — all in one secure platform.
                </p>

                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon purple">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                        </div>
                        <div class="feature-text">
                            <h4>Digital Prescriptions</h4>
                            <p>Create, manage, and print prescriptions instantly</p>
                        </div>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon cyan">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                        <div class="feature-text">
                            <h4>Patient Management</h4>
                            <p>Comprehensive patient history and records</p>
                        </div>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon green">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.376 3.622a1 1 0 0 1 3.002 3.002L7.368 18.635a2 2 0 0 1-.855.506l-2.872.838a.5.5 0 0 1-.62-.62l.838-2.872a2 2 0 0 1 .506-.854z"/></svg>
                        </div>
                        <div class="feature-text">
                            <h4>Lab Reports</h4>
                            <p>Integrated test results and tracking</p>
                        </div>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon orange">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                        </div>
                        <div class="feature-text">
                            <h4>Smart Analytics</h4>
                            <p>Insights, reports, and practice trends</p>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <div class="login-card">
                    <div class="login-header">
                        <div class="app-logo">
                            <svg viewBox="0 0 316 316" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M305.8 81.125C305.77 80.995 305.69 80.885 305.65 80.755C305.56 80.525 305.49 80.285 305.37 80.075C305.29 79.935 305.17 79.815 305.07 79.685C304.94 79.515 304.83 79.325 304.68 79.175C304.55 79.045 304.39 78.955 304.25 78.845C304.09 78.715 303.95 78.575 303.77 78.475L251.32 48.275C249.97 47.495 248.31 47.495 246.96 48.275L194.51 78.475C194.33 78.575 194.19 78.725 194.03 78.845C193.89 78.955 193.73 79.045 193.6 79.175C193.45 79.325 193.34 79.515 193.21 79.685C193.11 79.815 192.99 79.935 192.91 80.075C192.79 80.285 192.71 80.525 192.63 80.755C192.58 80.875 192.51 80.995 192.48 81.125C192.38 81.495 192.33 81.875 192.33 82.265V139.625L148.62 164.795V52.575C148.62 52.185 148.57 51.805 148.47 51.435C148.44 51.305 148.36 51.195 148.32 51.065C148.23 50.835 148.16 50.595 148.04 50.385C147.96 50.245 147.84 50.125 147.74 49.995C147.61 49.825 147.5 49.635 147.35 49.485C147.22 49.355 147.06 49.265 146.92 49.155C146.76 49.025 146.62 48.885 146.44 48.785L93.99 18.585C92.64 17.805 90.98 17.805 89.63 18.585L37.18 48.785C37 48.885 36.86 49.035 36.7 49.155C36.56 49.265 36.4 49.355 36.27 49.485C36.12 49.635 36.01 49.825 35.88 49.995C35.78 50.125 35.66 50.245 35.58 50.385C35.46 50.595 35.38 50.835 35.3 51.065C35.25 51.185 35.18 51.305 35.15 51.435C35.05 51.805 35 52.185 35 52.575V232.235C35 233.795 35.84 235.245 37.19 236.025L142.1 296.425C142.33 296.555 142.58 296.635 142.82 296.725C142.93 296.765 143.04 296.835 143.16 296.865C143.53 296.965 143.9 297.015 144.28 297.015C144.66 297.015 145.03 296.965 145.4 296.865C145.5 296.835 145.59 296.775 145.69 296.745C145.95 296.655 146.21 296.565 146.45 296.435L251.36 236.035C252.72 235.255 253.55 233.815 253.55 232.245V174.885L303.81 145.945C305.17 145.165 306 143.725 306 142.155V82.265C305.95 81.875 305.89 81.495 305.8 81.125Z" fill="currentColor"/>
                            </svg>
                            <span>{{ config('app.name') }}</span>
                        </div>
                        <h2>Sign in to your account</h2>
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

                        <button type="submit" class="btn-login">
                            Sign In
                        </button>
                    </form>

                    <div class="login-footer">
                        <p>
                            Don't have an account?
                            <a href="{{ route('register') }}">Create one</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
