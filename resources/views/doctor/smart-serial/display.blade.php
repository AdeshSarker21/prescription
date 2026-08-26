<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $doctor->name ?? 'Doctor' }} - Patient Display</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --bg-dark: #050a15;
            --bg-panel: #0a1020;
            --bg-card: #0d1528;
            --bg-row: rgba(13,21,40,0.7);
            --border: rgba(255,255,255,0.05);
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --green: #00e676;
            --green-bright: #00e676;
            --green-glow: rgba(0,230,118,0.5);
            --green-dark: #15803d;
            --cyan: #00e5ff;
            --cyan-glow: rgba(0,229,255,0.4);
            --blue: #448aff;
            --blue-glow: rgba(68,138,255,0.4);
            --purple: #b388ff;
            --purple-glow: rgba(179,136,255,0.4);
            --orange: #ff9100;
            --orange-glow: rgba(255,145,0,0.3);
            --pink: #ff4081;
            --pink-glow: rgba(255,64,129,0.3);
            --red: #ef4444;
            --yellow: #ffd740;
        }
        body {
            font-family: 'Noto Sans Bengali', 'Segoe UI', sans-serif;
            background: var(--bg-dark);
            background-image:
                radial-gradient(ellipse at 15% 50%, rgba(0,229,255,0.04) 0%, transparent 50%),
                radial-gradient(ellipse at 85% 20%, rgba(0,230,118,0.03) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 90%, rgba(179,136,255,0.03) 0%, transparent 50%);
            color: var(--text-primary);
            height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .main-layout {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

        /* ==================== LEFT SIDEBAR ==================== */
        .left-sidebar {
            width: 300px;
            min-width: 280px;
            background: linear-gradient(180deg, #080e1e 0%, #0a1225 100%);
            border-right: 1px solid rgba(0,229,255,0.08);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 24px 20px;
            overflow-y: auto;
            position: relative;
            z-index: 10;
        }
        .left-sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 1px;
            height: 100%;
            background: linear-gradient(180deg, transparent, rgba(0,229,255,0.2), transparent);
        }

        /* Logo */
        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 28px;
            width: 100%;
        }
        .sidebar-logo .logo-icon {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: var(--cyan);
            filter: drop-shadow(0 0 8px var(--cyan-glow));
        }
        .sidebar-logo .logo-text {
            display: flex;
            flex-direction: column;
        }
        .sidebar-logo .logo-title {
            font-size: 0.85rem;
            font-weight: 800;
            color: var(--cyan);
            letter-spacing: 1px;
            text-transform: uppercase;
            text-shadow: 0 0 10px var(--cyan-glow);
        }
        .sidebar-logo .logo-subtitle {
            font-size: 0.6rem;
            color: var(--text-muted);
            letter-spacing: 0.5px;
        }

        /* Doctor Avatar */
        .avatar-wrapper {
            position: relative;
            width: 160px;
            height: 160px;
            margin-bottom: 18px;
        }
        .avatar-ring {
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            border: 3px solid transparent;
            border-top-color: var(--cyan);
            border-right-color: var(--blue);
            border-bottom-color: var(--purple);
            border-left-color: var(--cyan);
            animation: avatar-ring-spin 4s linear infinite;
            filter: drop-shadow(0 0 12px var(--cyan-glow));
        }
        @keyframes avatar-ring-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .avatar-glow {
            position: absolute;
            inset: -8px;
            border-radius: 50%;
            background: conic-gradient(from 0deg, var(--cyan-glow), transparent, var(--blue-glow), transparent, var(--purple-glow), transparent, var(--cyan-glow));
            filter: blur(15px);
            opacity: 0.5;
            animation: avatar-glow-pulse 3s ease-in-out infinite;
        }
        @keyframes avatar-glow-pulse {
            0%, 100% { opacity: 0.3; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(1.05); }
        }
        .doctor-avatar {
            width: 160px;
            height: 160px;
            border-radius: 50%;
            object-fit: cover;
            position: relative;
            z-index: 2;
            background: linear-gradient(135deg, rgba(0,229,255,0.15), rgba(68,138,255,0.1));
        }

        /* Doctor Info */
        .doctor-name {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
            text-align: center;
            margin-bottom: 4px;
        }
        .doctor-degrees {
            font-size: 0.8rem;
            color: var(--text-muted);
            text-align: center;
            margin-bottom: 10px;
        }
        .doctor-specialty {
            display: inline-block;
            background: linear-gradient(135deg, rgba(0,229,255,0.15), rgba(0,229,255,0.05));
            color: var(--cyan);
            padding: 5px 22px;
            border-radius: 20px;
            font-size: 0.82rem;
            font-weight: 600;
            border: 1px solid rgba(0,229,255,0.2);
            margin-bottom: 16px;
        }

        /* Chamber */
        .chamber-section {
            text-align: center;
            margin-bottom: 18px;
        }
        .chamber-label {
            font-size: 0.6rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--text-muted);
            margin-bottom: 4px;
            opacity: 0.7;
        }
        .chamber-name {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .chamber-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--green);
            box-shadow: 0 0 8px var(--green-glow);
            animation: dot-pulse 2s ease-in-out infinite;
        }
        @keyframes dot-pulse {
            0%, 100% { box-shadow: 0 0 6px var(--green-glow); }
            50% { box-shadow: 0 0 14px var(--green-glow); }
        }

        /* Sidebar Buttons */
        .sidebar-buttons {
            display: flex;
            flex-direction: column;
            gap: 6px;
            width: 100%;
            margin-bottom: 16px;
        }
        .sidebar-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 10px 14px;
            background: rgba(13,21,40,0.6);
            border: 1px solid rgba(255,255,255,0.04);
            border-radius: 10px;
            color: var(--text-secondary);
            font-size: 0.82rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: inherit;
            position: relative;
            overflow: hidden;
        }
        .sidebar-btn::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 3px;
            height: 100%;
            border-radius: 10px 0 0 10px;
        }
        .sidebar-btn:hover {
            background: rgba(13,21,40,0.9);
            transform: translateX(2px);
        }
        .sidebar-btn i {
            font-size: 0.9rem;
            width: 20px;
            text-align: center;
        }
        .btn-fullscreen::before { background: var(--cyan); }
        .btn-fullscreen i { color: var(--cyan); }
        .btn-voice::before { background: var(--green); }
        .btn-voice i { color: var(--green); }
        .btn-refresh::before { background: var(--blue); }
        .btn-refresh i { color: var(--blue); }
        .btn-settings::before { background: var(--purple); }
        .btn-settings i { color: var(--purple); }

        /* Notice Card */
        .notice-card {
            width: 100%;
            background: linear-gradient(135deg, rgba(255,64,129,0.08), rgba(255,64,129,0.02));
            border: 1px solid rgba(255,64,129,0.2);
            border-radius: 12px;
            padding: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: auto;
        }
        .notice-icon {
            font-size: 1.6rem;
            color: var(--pink);
            filter: drop-shadow(0 0 8px var(--pink-glow));
            flex-shrink: 0;
        }
        .notice-text {
            font-size: 0.78rem;
            color: var(--text-secondary);
            line-height: 1.5;
        }
        .notice-text .highlight {
            color: var(--pink);
            font-weight: 600;
        }

        /* ==================== RIGHT PANEL ==================== */
        .right-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* Top Header Bar */
        .top-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 28px;
            background: rgba(10,16,32,0.8);
            border-bottom: 1px solid var(--border);
            position: relative;
        }
        .top-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(0,229,255,0.15), transparent);
        }
        .header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .header-title {
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--text-primary);
            letter-spacing: 0.5px;
        }
        .live-badge {
            display: flex;
            align-items: center;
            gap: 6px;
            background: rgba(0,230,118,0.12);
            border: 1px solid rgba(0,230,118,0.25);
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--green);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .live-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--green);
            animation: live-pulse 1.5s ease-in-out infinite;
            box-shadow: 0 0 6px var(--green-glow);
        }
        @keyframes live-pulse {
            0%, 100% { opacity: 1; transform: scale(1); box-shadow: 0 0 6px var(--green-glow); }
            50% { opacity: 0.5; transform: scale(1.3); box-shadow: 0 0 12px var(--green-glow); }
        }
        .header-right {
            text-align: right;
        }
        .header-clock {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text-primary);
            font-variant-numeric: tabular-nums;
            letter-spacing: 1px;
            line-height: 1;
        }
        .header-date {
            font-size: 0.78rem;
            color: var(--text-muted);
            margin-top: 2px;
        }

        /* ==================== NOW CALLING + NEXT PATIENT ==================== */
        .calling-section {
            display: flex;
            gap: 16px;
            padding: 16px 28px;
            min-height: 180px;
        }

        /* Now Calling Card */
        .now-calling-card {
            flex: 2.5;
            background: linear-gradient(135deg, rgba(0,230,118,0.06), rgba(0,230,118,0.01));
            border: 2px solid rgba(0,230,118,0.35);
            border-radius: 16px;
            padding: 20px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
            animation: calling-border-glow 3s ease-in-out infinite;
        }
        @keyframes calling-border-glow {
            0%, 100% {
                border-color: rgba(0,230,118,0.35);
                box-shadow: 0 0 20px rgba(0,230,118,0.1), inset 0 0 20px rgba(0,230,118,0.02);
            }
            50% {
                border-color: rgba(0,230,118,0.6);
                box-shadow: 0 0 40px rgba(0,230,118,0.15), inset 0 0 30px rgba(0,230,118,0.04);
            }
        }
        .now-calling-card::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            border-radius: 16px;
            background: conic-gradient(from 0deg, rgba(0,230,118,0.4), transparent 30%, transparent 70%, rgba(0,230,118,0.4));
            animation: calling-rotate 4s linear infinite;
            z-index: -1;
            opacity: 0.5;
        }
        @keyframes calling-rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .calling-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .calling-bell {
            font-size: 2.2rem;
            color: var(--cyan);
            filter: drop-shadow(0 0 10px var(--cyan-glow));
            animation: bell-shake 2s ease-in-out infinite;
        }
        @keyframes bell-shake {
            0%, 100% { transform: rotate(0); }
            10% { transform: rotate(14deg); }
            20% { transform: rotate(-12deg); }
            30% { transform: rotate(10deg); }
            40% { transform: rotate(-8deg); }
            50% { transform: rotate(0); }
        }
        .calling-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .calling-label {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: var(--green);
        }
        .calling-serial {
            font-size: 2.8rem;
            font-weight: 900;
            color: var(--green);
            text-shadow: 0 0 20px rgba(0,230,118,0.4);
            line-height: 1.1;
            animation: serial-text-glow 2s ease-in-out infinite;
        }
        @keyframes serial-text-glow {
            0%, 100% { text-shadow: 0 0 20px rgba(0,230,118,0.3); }
            50% { text-shadow: 0 0 35px rgba(0,230,118,0.5), 0 0 60px rgba(0,230,118,0.15); }
        }
        .calling-patient-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: #fff;
        }
        .calling-instruction {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 2px;
        }

        /* Timer Ring */
        .timer-ring-wrapper {
            position: relative;
            width: 120px;
            height: 120px;
            flex-shrink: 0;
        }
        .timer-ring-bg {
            fill: none;
            stroke: rgba(0,230,118,0.1);
            stroke-width: 4;
        }
        .timer-ring-progress {
            fill: none;
            stroke: var(--green);
            stroke-width: 4;
            stroke-linecap: round;
            stroke-dasharray: 314;
            stroke-dashoffset: 0;
            transform: rotate(-90deg);
            transform-origin: center;
            filter: drop-shadow(0 0 6px var(--green-glow));
            animation: timer-ring-pulse 2s ease-in-out infinite;
        }
        @keyframes timer-ring-pulse {
            0%, 100% { filter: drop-shadow(0 0 4px var(--green-glow)); }
            50% { filter: drop-shadow(0 0 10px var(--green-glow)); }
        }
        .timer-ring-glow {
            fill: none;
            stroke: rgba(0,230,118,0.2);
            stroke-width: 8;
            stroke-dasharray: 314;
            stroke-dashoffset: 0;
            transform: rotate(-90deg);
            transform-origin: center;
            filter: blur(4px);
        }
        .timer-content {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .timer-label {
            font-size: 0.55rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--green);
            opacity: 0.8;
        }
        .timer-value {
            font-size: 2rem;
            font-weight: 900;
            color: var(--green);
            text-shadow: 0 0 15px var(--green-glow);
            line-height: 1;
        }
        .timer-unit {
            font-size: 0.6rem;
            color: var(--text-muted);
        }

        /* No Calling */
        .no-calling-card {
            flex: 2.5;
            background: rgba(13,21,40,0.5);
            border: 1px solid var(--border);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            font-size: 1rem;
            min-height: 160px;
        }

        /* Next Patient Card */
        .next-patient-card {
            flex: 1;
            background: linear-gradient(135deg, rgba(68,138,255,0.06), rgba(68,138,255,0.01));
            border: 2px solid rgba(68,138,255,0.3);
            border-radius: 16px;
            padding: 18px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            position: relative;
            animation: next-border-glow 3s ease-in-out infinite;
        }
        @keyframes next-border-glow {
            0%, 100% {
                border-color: rgba(68,138,255,0.3);
                box-shadow: 0 0 15px rgba(68,138,255,0.08);
            }
            50% {
                border-color: rgba(68,138,255,0.5);
                box-shadow: 0 0 30px rgba(68,138,255,0.12);
            }
        }
        .next-label {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--blue);
            margin-bottom: 6px;
        }
        .next-serial {
            font-size: 1.8rem;
            font-weight: 900;
            color: var(--blue);
            text-shadow: 0 0 15px var(--blue-glow);
            line-height: 1.1;
        }
        .next-patient-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: #fff;
            margin-top: 4px;
        }
        .next-patient-status {
            font-size: 0.72rem;
            color: var(--text-muted);
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .next-patient-status .hourglass {
            color: var(--cyan);
            font-size: 0.85rem;
        }

        /* ==================== QUEUE TABLE ==================== */
        .queue-area {
            flex: 1;
            overflow-y: auto;
            padding: 0 28px 10px;
        }
        .queue-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 5px;
        }
        .queue-table thead th {
            padding: 10px 16px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--text-muted);
            text-align: left;
            border-bottom: 1px solid rgba(255,255,255,0.04);
        }
        .queue-table tbody tr {
            border-radius: 10px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            animation: row-slide-in 0.5s ease-out;
        }
        @keyframes row-slide-in {
            from { opacity: 0; transform: translateX(-10px); }
            to { opacity: 1; transform: translateX(0); }
        }
        .queue-table tbody td {
            padding: 12px 16px;
            font-size: 1rem;
            vertical-align: middle;
            background: var(--bg-row);
            transition: all 0.4s;
        }
        .queue-table tbody td:first-child { border-radius: 10px 0 0 10px; }
        .queue-table tbody td:last-child { border-radius: 0 10px 10px 0; }

        /* Row color accents (left border) */
        .queue-table tbody tr td:first-child {
            position: relative;
        }

        /* RUNNING ROW */
        .queue-table tbody tr.row-running {
            position: relative;
            animation: running-row-enter 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }
        @keyframes running-row-enter {
            from { opacity: 0; transform: scale(0.97); }
            to { opacity: 1; transform: scale(1); }
        }
        .queue-table tbody tr.row-running td {
            background: rgba(0,230,118,0.06);
        }
        .queue-table tbody tr.row-running td:first-child::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 4px;
            height: 100%;
            background: var(--green);
            border-radius: 10px 0 0 10px;
            box-shadow: 0 0 12px var(--green-glow);
            animation: running-bar-glow 1.5s ease-in-out infinite;
        }
        @keyframes running-bar-glow {
            0%, 100% { box-shadow: 0 0 8px var(--green-glow); }
            50% { box-shadow: 0 0 20px var(--green-glow), 0 0 40px rgba(0,230,118,0.15); }
        }

        /* Animated border for running row */
        .queue-table tbody tr.row-running::before {
            content: '';
            position: absolute;
            inset: -1px;
            border-radius: 10px;
            background: linear-gradient(90deg, rgba(0,230,118,0.5), rgba(0,230,118,0.05), rgba(0,230,118,0.5));
            background-size: 200% 100%;
            animation: running-border-flow 2s linear infinite;
            z-index: -1;
        }
        @keyframes running-border-flow {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Running row zoom pulse */
        .queue-table tbody tr.row-running .col-serial {
            animation: running-serial-zoom 2s ease-in-out infinite;
        }
        @keyframes running-serial-zoom {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        /* Running particles */
        .running-particles {
            position: absolute;
            inset: 0;
            pointer-events: none;
            overflow: hidden;
        }
        .running-particles .particle {
            position: absolute;
            width: 3px;
            height: 3px;
            background: var(--green);
            border-radius: 50%;
            box-shadow: 0 0 6px var(--green-glow);
            animation: particle-float 3s ease-in-out infinite;
        }
        .running-particles .particle:nth-child(1) { left: 10%; top: 20%; animation-delay: 0s; animation-duration: 2.5s; }
        .running-particles .particle:nth-child(2) { left: 30%; top: 70%; animation-delay: 0.5s; animation-duration: 3s; }
        .running-particles .particle:nth-child(3) { left: 60%; top: 30%; animation-delay: 1s; animation-duration: 2.8s; }
        .running-particles .particle:nth-child(4) { left: 80%; top: 60%; animation-delay: 1.5s; animation-duration: 3.2s; }
        .running-particles .particle:nth-child(5) { left: 50%; top: 80%; animation-delay: 0.8s; animation-duration: 2.6s; }
        @keyframes particle-float {
            0%, 100% { opacity: 0; transform: translateY(0) scale(0.5); }
            50% { opacity: 1; transform: translateY(-15px) scale(1); }
        }

        /* NEXT ROW */
        .queue-table tbody tr.row-next td {
            background: rgba(68,138,255,0.05);
        }
        .queue-table tbody tr.row-next td:first-child::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 4px;
            height: 100%;
            background: var(--blue);
            border-radius: 10px 0 0 10px;
            box-shadow: 0 0 8px var(--blue-glow);
        }

        /* WAITING ROWS - color coded */
        .queue-table tbody tr.row-waiting-0 td:first-child::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 4px;
            height: 100%;
            background: var(--orange);
            border-radius: 10px 0 0 10px;
            box-shadow: 0 0 6px var(--orange-glow);
        }
        .queue-table tbody tr.row-waiting-1 td:first-child::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 4px;
            height: 100%;
            background: var(--pink);
            border-radius: 10px 0 0 10px;
            box-shadow: 0 0 6px var(--pink-glow);
        }
        .queue-table tbody tr.row-waiting-2 td:first-child::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 4px;
            height: 100%;
            background: var(--green);
            border-radius: 10px 0 0 10px;
            box-shadow: 0 0 6px var(--green-glow);
        }
        .queue-table tbody tr.row-waiting-3 td:first-child::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 4px;
            height: 100%;
            background: var(--cyan);
            border-radius: 10px 0 0 10px;
            box-shadow: 0 0 6px var(--cyan-glow);
        }

        /* EMERGENCY ROW */
        .queue-table tbody tr.row-emergency td {
            background: rgba(239,68,68,0.06);
        }
        .queue-table tbody tr.row-emergency td:first-child::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 4px;
            height: 100%;
            background: var(--red);
            border-radius: 10px 0 0 10px;
            box-shadow: 0 0 8px rgba(239,68,68,0.5);
        }

        /* Column styles */
        .col-serial {
            font-weight: 800;
            font-size: 1.1rem;
            color: var(--text-primary);
            width: 160px;
            display: flex;
            align-items: center;
            gap: 8px;
            position: relative;
        }
        .col-serial .serial-text {
            white-space: nowrap;
        }
        .col-name {
            font-weight: 600;
            font-size: 1.05rem;
            color: var(--text-primary);
        }
        .col-status { width: 160px; }
        .col-wait {
            width: 160px;
            color: var(--text-muted);
            font-size: 0.9rem;
            text-align: right;
        }

        /* Running arrow icon */
        .running-arrow {
            color: var(--green);
            font-size: 1rem;
            animation: arrow-bounce 1s ease-in-out infinite;
            filter: drop-shadow(0 0 4px var(--green-glow));
        }
        @keyframes arrow-bounce {
            0%, 100% { transform: translateX(0); opacity: 1; }
            50% { transform: translateX(4px); opacity: 0.7; }
        }

        /* ECG / Heartbeat animation */
        .ecg-line {
            display: inline-flex;
            align-items: center;
            margin-left: 8px;
            height: 20px;
            overflow: hidden;
        }
        .ecg-line svg {
            width: 60px;
            height: 20px;
        }
        .ecg-path {
            fill: none;
            stroke: var(--green);
            stroke-width: 1.5;
            stroke-linecap: round;
            stroke-dasharray: 120;
            stroke-dashoffset: 120;
            animation: ecg-draw 1.5s linear infinite;
            filter: drop-shadow(0 0 3px var(--green-glow));
        }
        @keyframes ecg-draw {
            0% { stroke-dashoffset: 120; }
            100% { stroke-dashoffset: 0; }
        }

        /* Status Badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }
        .badge-running {
            background: rgba(0,230,118,0.15);
            color: var(--green);
            border: 1px solid rgba(0,230,118,0.3);
            animation: badge-running-glow 2s ease-in-out infinite;
        }
        @keyframes badge-running-glow {
            0%, 100% { box-shadow: 0 0 0 rgba(0,230,118,0); }
            50% { box-shadow: 0 0 12px rgba(0,230,118,0.3); }
        }
        .badge-waiting {
            background: rgba(148,163,184,0.08);
            color: var(--text-secondary);
            border: 1px solid rgba(148,163,184,0.12);
        }
        .badge-next {
            background: rgba(68,138,255,0.12);
            color: var(--blue);
            border: 1px solid rgba(68,138,255,0.25);
        }
        .badge-emergency {
            background: rgba(239,68,68,0.15);
            color: #fca5a5;
            border: 1px solid rgba(239,68,68,0.25);
            animation: badge-emergency-flash 1s ease-in-out infinite;
        }
        @keyframes badge-emergency-flash {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        .badge-inside {
            background: rgba(179,136,255,0.12);
            color: var(--purple);
            border: 1px solid rgba(179,136,255,0.25);
        }
        .badge-completed {
            background: rgba(0,230,118,0.08);
            color: rgba(0,230,118,0.5);
            border: 1px solid rgba(0,230,118,0.15);
        }

        .hourglass-icon {
            font-size: 0.8rem;
            color: var(--cyan);
            filter: drop-shadow(0 0 3px var(--cyan-glow));
        }

        /* Running pulse dot */
        .running-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--green);
            display: inline-block;
            margin-right: 6px;
            animation: running-dot-pulse 1.2s ease-out infinite;
            box-shadow: 0 0 8px var(--green-glow);
            vertical-align: middle;
        }
        @keyframes running-dot-pulse {
            0% { box-shadow: 0 0 0 0 rgba(0,230,118,0.6); }
            70% { box-shadow: 0 0 0 10px rgba(0,230,118,0); }
            100% { box-shadow: 0 0 0 0 rgba(0,230,118,0); }
        }

        /* ==================== BOTTOM MARQUEE ==================== */
        .marquee-bar {
            background: linear-gradient(90deg, rgba(10,16,32,0.95), rgba(13,21,40,0.95));
            border-top: 1px solid rgba(255,64,129,0.15);
            padding: 10px 0;
            overflow: hidden;
            white-space: nowrap;
            position: relative;
            display: flex;
            align-items: center;
        }
        .marquee-bar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,64,129,0.2), transparent);
        }
        .marquee-bell {
            flex-shrink: 0;
            padding: 0 14px;
            font-size: 1.1rem;
            color: var(--yellow);
            filter: drop-shadow(0 0 6px rgba(255,215,64,0.4));
            animation: marquee-bell-ring 2s ease-in-out infinite;
        }
        @keyframes marquee-bell-ring {
            0%, 100% { transform: rotate(0); }
            10% { transform: rotate(12deg); }
            20% { transform: rotate(-10deg); }
            30% { transform: rotate(8deg); }
            40% { transform: rotate(0); }
        }
        .marquee-arrows-left {
            flex-shrink: 0;
            padding: 0 8px 0 0;
            color: var(--cyan);
            font-size: 0.8rem;
            letter-spacing: 2px;
            animation: arrows-blink 1.5s ease-in-out infinite;
        }
        .marquee-arrows-right {
            flex-shrink: 0;
            padding: 0 0 0 8px;
            color: var(--cyan);
            font-size: 0.8rem;
            letter-spacing: 2px;
            animation: arrows-blink 1.5s ease-in-out infinite 0.75s;
        }
        @keyframes arrows-blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
        .marquee-content {
            display: inline-block;
            animation: marquee-scroll 40s linear infinite;
            font-size: 0.88rem;
            color: var(--yellow);
            font-weight: 500;
            padding-left: 100%;
        }
        .marquee-content .notice-highlight {
            color: var(--yellow);
            font-weight: 600;
        }
        .marquee-content .notice-separator {
            margin: 0 24px;
            color: var(--text-muted);
            opacity: 0.5;
        }
        @keyframes marquee-scroll {
            0% { transform: translateX(0); }
            100% { transform: translateX(-100%); }
        }

        /* ==================== SESSION ENDED ==================== */
        .session-ended {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            background: radial-gradient(ellipse at center, rgba(239,68,68,0.05) 0%, transparent 70%);
        }
        .session-ended h2 { font-size: 2.5rem; color: var(--red); margin-bottom: 14px; font-weight: 800; }
        .session-ended p { color: var(--text-muted); font-size: 1.2rem; }

        /* ==================== SCROLLBAR ==================== */
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(148,163,184,0.12); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(148,163,184,0.2); }

        /* ==================== RESPONSIVE ==================== */
        @media (min-width: 1920px) {
            .left-sidebar { width: 340px; padding: 30px 24px; }
            .avatar-wrapper, .doctor-avatar { width: 180px; height: 180px; }
            .doctor-name { font-size: 1.4rem; }
            .calling-serial { font-size: 3.4rem; }
            .calling-patient-name { font-size: 1.7rem; }
            .timer-ring-wrapper { width: 140px; height: 140px; }
            .timer-value { font-size: 2.4rem; }
            .next-serial { font-size: 2.1rem; }
            .col-serial { font-size: 1.2rem; width: 180px; }
            .col-name { font-size: 1.15rem; }
            .queue-table tbody td { padding: 14px 18px; }
            .header-clock { font-size: 2.3rem; }
        }
        @media (min-width: 2560px) {
            .left-sidebar { width: 400px; padding: 36px 28px; }
            .avatar-wrapper, .doctor-avatar { width: 200px; height: 200px; }
            .doctor-name { font-size: 1.6rem; }
            .calling-serial { font-size: 4rem; }
            .calling-patient-name { font-size: 2rem; }
            .timer-ring-wrapper { width: 160px; height: 160px; }
            .timer-value { font-size: 2.8rem; }
            .next-serial { font-size: 2.4rem; }
            .col-serial { font-size: 1.35rem; width: 200px; }
            .col-name { font-size: 1.3rem; }
            .queue-table tbody td { padding: 18px 22px; font-size: 1.15rem; }
            .header-clock { font-size: 2.6rem; }
        }
    </style>
</head>
<body x-data="patientDisplay()" x-init="init()">

    {{-- Voice controls are in the left sidebar --}}

    {{-- Session Ended --}}
    <template x-if="sessionEnded">
        <div class="session-ended">
            <h2>সেশন সমাপ্ত</h2>
            <p>এই সেশন আর সক্রিয় নয়। দয়া করে রিসেপশনে যোগাযোগ করুন।</p>
        </div>
    </template>

    <template x-if="!sessionEnded">
        <div class="main-layout">

            {{-- ===== LEFT SIDEBAR ===== --}}
            <div class="left-sidebar">
                {{-- Logo --}}
                <div class="sidebar-logo">
                    <div class="logo-icon"><i class="fas fa-heartbeat"></i></div>
                    <div class="logo-text">
                        <div class="logo-title">Smart Serial System</div>
                        <div class="logo-subtitle">Live Queue &bull; Live Voice</div>
                    </div>
                </div>

                {{-- Doctor Avatar --}}
                <div class="avatar-wrapper">
                    <div class="avatar-glow"></div>
                    <div class="avatar-ring"></div>
                    <img class="doctor-avatar"
                         :src="doctorData.avatar || 'https://ui-avatars.com/api/?name=Doctor&color=00e5ff&background=0a1020&size=320'"
                         :alt="doctorData.name"
                         onerror="this.src='https://ui-avatars.com/api/?name=Doctor&color=00e5ff&background=0a1020&size=320'">
                </div>

                {{-- Doctor Info --}}
                <div class="doctor-name" x-text="doctorData.name"></div>
                <div class="doctor-degrees" x-show="doctorData.qualification" x-text="doctorData.qualification"></div>
                <div class="doctor-specialty" x-show="doctorData.specialization" x-text="doctorData.specialization"></div>

                {{-- Chamber --}}
                <div class="chamber-section" x-show="chamberName">
                    <div class="chamber-label">Chamber</div>
                    <div class="chamber-name">
                        <span class="chamber-dot"></span>
                        <span x-text="chamberName"></span>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="sidebar-buttons">
                    <button class="sidebar-btn btn-fullscreen" @click="toggleFullscreen()">
                        <i class="fas fa-expand"></i>
                        <span x-text="isFullscreen ? 'Exit Full Screen' : 'Full Screen'"></span>
                    </button>
                    <div class="sidebar-btn btn-voice" style="cursor:default;flex-direction:column;align-items:stretch;gap:8px;padding:12px 14px;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:8px;height:8px;border-radius:50%;flex-shrink:0;" :style="speechReady ? 'background:var(--green);box-shadow:0 0 8px var(--green-glow);' : 'background:var(--text-muted);'"></div>
                            <i class="fas fa-volume-up" style="color:var(--green);font-size:0.9rem;width:20px;text-align:center;"></i>
                            <span style="font-size:0.82rem;font-weight:500;color:var(--text-secondary);flex:1;" x-text="voiceStatusText"></span>
                        </div>
                        <template x-if="!speechReady">
                            <button class="voice-test-btn" @click="enableVoice()" style="width:100%;padding:6px 10px;border-radius:6px;border:1px solid rgba(0,230,118,0.2);background:rgba(0,230,118,0.1);color:var(--green);font-size:0.72rem;font-weight:600;cursor:pointer;font-family:inherit;transition:all 0.2s;">
                                <i class="fas fa-microphone" style="margin-right:4px;"></i> Enable Voice
                            </button>
                        </template>
                        <template x-if="speechReady">
                            <div style="display:flex;gap:4px;align-items:center;">
                                <input type="text" x-model="testPatientName" placeholder="নাসরিন সুলতানা"
                                       style="flex:1;min-width:0;padding:5px 8px;border-radius:6px;border:1px solid rgba(255,255,255,0.1);background:rgba(255,255,255,0.05);color:#fff;font-size:0.72rem;font-family:inherit;"
                                       @keydown.enter="testPatientNameVoice()">
                                <button @click="testPatientNameVoice()" style="flex-shrink:0;padding:5px 10px;border-radius:6px;border:1px solid rgba(179,136,255,0.25);background:rgba(179,136,255,0.15);color:var(--purple);font-size:0.72rem;font-weight:600;cursor:pointer;font-family:inherit;transition:all 0.2s;white-space:nowrap;">
                                    <i class="fas fa-play" style="margin-right:3px;font-size:0.6rem;"></i> Test
                                </button>
                            </div>
                        </template>
                    </div>
                    <button class="sidebar-btn btn-refresh" @click="refreshQueue()">
                        <i class="fas fa-sync-alt"></i>
                        <span>Refresh</span>
                    </button>
                    <button class="sidebar-btn btn-settings">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </button>
                </div>

                {{-- Notice Card --}}
                <div class="notice-card">
                    <div class="notice-icon"><i class="fas fa-bullhorn"></i></div>
                    <div class="notice-text">
                        <span class="highlight">দয়া করে চেয়ারে বসে থাকুন</span><br>
                        সকলে ধন্যবাদ <span style="color:var(--pink);">&#10084;</span>
                    </div>
                </div>
            </div>

            {{-- ===== RIGHT PANEL ===== --}}
            <div class="right-panel">

                {{-- Top Header --}}
                <div class="top-header">
                    <div class="header-left">
                        <div class="header-title">PATIENT QUEUE</div>
                        <div class="live-badge">
                            <span class="live-dot"></span>
                            LIVE
                        </div>
                    </div>
                    <div class="header-right">
                        <div class="header-clock" x-text="currentTime"></div>
                        <div class="header-date" x-text="currentDate"></div>
                    </div>
                </div>

                {{-- Calling Section --}}
                <div class="calling-section">
                    {{-- Now Calling --}}
                    <template x-if="currentCalled">
                        <div class="now-calling-card">
                            <div class="calling-left">
                                <div class="calling-bell"><i class="fas fa-bell"></i></div>
                                <div class="calling-info">
                                    <div class="calling-label">Now Calling</div>
                                    <div class="calling-serial" x-text="'#' + (currentCalled.formatted_serial || String(currentCalled.serial_number).padStart(3, '0'))"></div>
                                    <div class="calling-patient-name" x-text="currentCalled.patient?.name || 'Patient'"></div>
                                    <div class="calling-instruction">এবার আপনি ভিতরে প্রবেশ করুন</div>
                                </div>
                            </div>
                            <div class="timer-ring-wrapper">
                                <svg viewBox="0 0 120 120" width="100%" height="100%">
                                    <circle class="timer-ring-glow" cx="60" cy="60" r="50"/>
                                    <circle class="timer-ring-bg" cx="60" cy="60" r="50"/>
                                    <circle class="timer-ring-progress" cx="60" cy="60" r="50"
                                            :style="`stroke-dashoffset: ${314 - (314 * Math.min(runningSeconds / 60, 1))}`"/>
                                </svg>
                                <div class="timer-content">
                                    <div class="timer-label">Running</div>
                                    <div class="timer-value" x-text="runningSeconds"></div>
                                    <div class="timer-unit">সেকেন্ড</div>
                                </div>
                            </div>
                        </div>
                    </template>
                    <template x-if="!currentCalled">
                        <div class="no-calling-card">পরবর্তী রোগীর জন্য অপেক্ষা করা হচ্ছে...</div>
                    </template>

                    {{-- Next Patient --}}
                    <template x-if="nextInQueue">
                        <div class="next-patient-card">
                            <div class="next-label">Next Patient</div>
                            <div class="next-serial" x-text="'#' + (nextInQueue.formatted_serial || String(nextInQueue.serial_number).padStart(3, '0'))"></div>
                            <div class="next-patient-name" x-text="nextInQueue.patient?.name || 'Patient'"></div>
                            <div class="next-patient-status">
                                <span class="hourglass-icon"><i class="fas fa-hourglass-half"></i></span>
                                <span>প্রস্তুত থাকুন</span>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Queue Table --}}
                <div class="queue-area">
                    <table class="queue-table">
                        <thead>
                            <tr>
                                <th>সিরিয়াল</th>
                                <th>রোগীর নাম</th>
                                <th>অবস্থা</th>
                                <th style="text-align:right;">অপেক্ষার সময়</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(item, index) in queue" :key="item.id">
                                <tr :class="{
                                    'row-running': item.status === 'calling',
                                    'row-next': item.status === 'preparing',
                                    'row-emergency': item.priority === 'emergency' && item.status !== 'calling',
                                    'row-waiting-0': item.status === 'waiting' && index % 4 === 0,
                                    'row-waiting-1': item.status === 'waiting' && index % 4 === 1,
                                    'row-waiting-2': item.status === 'waiting' && index % 4 === 2,
                                    'row-waiting-3': item.status === 'waiting' && index % 4 === 3
                                }" style="position:relative;">
                                    <template x-if="item.status === 'calling'">
                                        <div class="running-particles">
                                            <div class="particle"></div>
                                            <div class="particle"></div>
                                            <div class="particle"></div>
                                            <div class="particle"></div>
                                            <div class="particle"></div>
                                        </div>
                                    </template>
                                    <td class="col-serial" style="padding:30px 15px;">
                                        <template x-if="item.status === 'calling'">
                                            <span class="running-arrow"><i class="fas fa-angle-double-right"></i></span>
                                        </template>
                                        <span class="serial-text" x-text="'#' + (item.formatted_serial || String(item.serial_number).padStart(3, '0'))"></span>
                                    </td>
                                    <td class="col-name" x-text="item.patient?.name || 'Patient'"></td>
                                    <td class="col-status">
                                        <template x-if="item.status === 'calling'">
                                            <span class="status-badge badge-running">
                                                <span class="running-dot"></span>
                                                Running
                                                <span class="ecg-line">
                                                    <svg viewBox="0 0 60 20">
                                                        <path class="ecg-path" d="M0,10 L10,10 L15,2 L20,18 L25,5 L30,15 L35,8 L40,12 L45,10 L60,10"/>
                                                    </svg>
                                                </span>
                                            </span>
                                        </template>
                                        <template x-if="item.status === 'preparing'">
                                            <span class="status-badge badge-next">Next</span>
                                        </template>
                                        <template x-if="item.status === 'waiting' && item.priority === 'emergency'">
                                            <span class="status-badge badge-emergency">
                                                <i class="fas fa-exclamation-triangle"></i> Emergency
                                            </span>
                                        </template>
                                        <template x-if="item.status === 'waiting' && item.priority !== 'emergency'">
                                            <span class="status-badge badge-waiting">
                                                Waiting
                                                <span class="hourglass-icon"><i class="fas fa-hourglass-half"></i></span>
                                            </span>
                                        </template>
                                        <template x-if="item.status === 'inside'">
                                            <span class="status-badge badge-inside">In Serial</span>
                                        </template>
                                        <template x-if="item.status === 'completed'">
                                            <span class="status-badge badge-completed">Done</span>
                                        </template>
                                    </td>
                                    <td class="col-wait" x-text="getWaitingTime(item)"></td>
                                </tr>
                            </template>
                            <template x-if="queue.length === 0">
                                <tr>
                                    <td colspan="4" style="text-align:center;padding:50px;color:var(--text-muted);font-size:1.1rem;">কোনো রোগী নেই</td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- Bottom Marquee --}}
                <div class="marquee-bar">
                    <div class="marquee-bell"><i class="fas fa-bell"></i></div>
                    <div class="marquee-arrows-left">&laquo;&laquo;&laquo;</div>
                    <div class="marquee-content" x-data>
                        <template x-if="notices.length > 0">
                            <span>
                                <template x-for="(notice, idx) in notices" :key="notice.id">
                                    <span>
                                        <span class="notice-highlight" x-text="notice.title"></span>
                                        <span> &mdash; </span>
                                        <span x-text="notice.message"></span>
                                        <span class="notice-separator">|</span>
                                    </span>
                                </template>
                            </span>
                        </template>
                        <template x-if="notices.length === 0">
                            <span>
                                <span class="notice-highlight">সবার জন্য অনুরোধ: সিরিয়াল অনুযায়ী ডাক্তার দেখান</span>
                                <span class="notice-separator">|</span>
                                <span class="notice-highlight">দয়া করে চেয়ারে বসে অপেক্ষা করুন</span>
                                <span class="notice-separator">|</span>
                                <span class="notice-highlight">মোবাইল সাইলেন্ট রাখুন</span>
                                <span class="notice-separator">|</span>
                            </span>
                        </template>
                    </div>
                    <div class="marquee-arrows-right">&raquo;&raquo;&raquo;</div>
                </div>

            </div>
        </div>
    </template>

    <script>
    function patientDisplay() {
        return {
            sessionId: @js($session->id),
            doctorId: @js($doctor->id),
            queue: @js($queue->values()->toArray()),
            currentCalled: @js($currentCalled),
            nextInQueue: @js($nextInQueue),
            doctorData: @js([
                'name' => $doctor->name ?? 'Doctor',
                'name_bn' => $doctor->name_bn ?? '',
                'avatar' => $doctor->avatar_url ?? '',
                'specialization' => $doctor->specialization ?? '',
                'specialization_bn' => $doctor->specialization_bn ?? '',
                'qualification' => $doctor->qualification ?? '',
                'qualification_bn' => $doctor->qualification_bn ?? '',
                'designation_title' => $doctor->designation_title ?? '',
                'designation_title_bn' => $doctor->designation_title_bn ?? '',
                'sub_specialties' => $doctor->sub_specialties ?? [],
                'sub_specialties_bn' => $doctor->sub_specialties_bn ?? [],
                'clinic_name' => $doctor->clinic_name ?? '',
                'clinic_name_bn' => $doctor->clinic_name_bn ?? '',
            ]),
            chamberName: @js($chamberName),
            sessionEnded: false,
            refreshTimer: null,
            clockTimer: null,
            timerInterval: null,
            currentTime: '',
            currentDate: '',
            runningSeconds: 0,
            emergencyPatient: null,
            speechReady: false,
            voiceStatusText: 'Click "Enable Voice"',
            isFullscreen: false,
            testPatientName: 'নাসরিন সুলতানা',
            ttsQueue: [],
            ttsPlaying: false,
            ttsCache: {},
            lastPendingKey: null,
            notices: @js(\App\Models\Notice::forDoctor($doctor->id)->active()->latest()->get()->map(fn($n) => ['id' => $n->id, 'title' => $n->title, 'message' => $n->message])->values()->toArray()),

            init() {
                this.updateClock();
                this.clockTimer = setInterval(() => this.updateClock(), 1000);
                this.refreshQueue();
                this.refreshTimer = setInterval(() => this.refreshQueue(), 3000);
                this.startRunningTimer();
                document.addEventListener('fullscreenchange', () => {
                    this.isFullscreen = !!document.fullscreenElement;
                });
                this.enableVoice();
            },

            enableVoice() {
                this.speechReady = true;
                this.voiceStatusText = 'Voice Active — Server TTS';
            },

            toggleFullscreen() {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen().catch(() => {});
                } else {
                    document.exitFullscreen().catch(() => {});
                }
            },

            updateClock() {
                const now = new Date();
                this.currentTime = now.toLocaleTimeString('bn-BD', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
                this.currentDate = now.toLocaleDateString('bn-BD', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            },

            startRunningTimer() {
                if (this.timerInterval) clearInterval(this.timerInterval);
                this.timerInterval = setInterval(() => {
                    if (this.currentCalled && this.currentCalled.called_at) {
                        const diff = Math.floor((Date.now() - new Date(this.currentCalled.called_at).getTime()) / 1000);
                        this.runningSeconds = Math.max(0, diff);
                    } else {
                        this.runningSeconds = 0;
                    }
                }, 1000);
                // Initial calculation
                if (this.currentCalled && this.currentCalled.called_at) {
                    const diff = Math.floor((Date.now() - new Date(this.currentCalled.called_at).getTime()) / 1000);
                    this.runningSeconds = Math.max(0, diff);
                }
            },

            getWaitingTime(item) {
                const statusTime = item.called_at || item.prepared_at || item.created_at;
                if (!statusTime) return '';
                const diff = Math.floor((Date.now() - new Date(statusTime).getTime()) / 1000);
                const m = Math.floor(diff / 60);
                const s = diff % 60;
                if (m > 0) return `${m} মিনিট ${s} সেকেন্ড`;
                return `${s} সেকেন্ড`;
            },

            buildText(type, patientName, gender) {
                const prefix = gender === 'female' ? 'জনাবা' : 'জনাব';
                const name = patientName || 'রোগী';
                const messages = {
                    preparing: `পরবর্তী সিরিয়ালের জন্য প্রস্তুত থাকুন, ${prefix} ${name}।`,
                    calling: `${prefix} ${name}, এবার আপনি ভিতরে প্রবেশ করুন।`,
                    inside: `${prefix} ${name}, এবার আপনি ভিতরে প্রবেশ করুন।`,
                    completed: `${prefix} ${name}, ধন্যবাদ।`,
                    recall: `${prefix} ${name}, আপনার সিরিয়াল আবার ডাকা হচ্ছে।`,
                    emergency: `জরুরি! ${prefix} ${name}, আপনাকে জরুরি ভিতরে প্রবেশ করুন।`,
                };
                return messages[type] || messages.calling;
            },

            async playTtsAudio(text, type, queueId) {
                const cacheKey = `${type}_${text}`;
                if (this.ttsCache[cacheKey]) {
                    this.playAudioFromUrl(this.ttsCache[cacheKey]);
                    return;
                }

                try {
                    this.voiceStatusText = 'Generating audio...';
                    const response = await fetch('{{ route("smart-serial.tts.generate") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            queue_id: queueId,
                            type: type,
                        }),
                    });

                    const result = await response.json();
                    if (result.success && result.audio_url) {
                        this.ttsCache[cacheKey] = result.audio_url;
                        this.voiceStatusText = `Voice Active — ${result.provider}`;
                        this.playAudioFromUrl(result.audio_url);
                    } else {
                        this.voiceStatusText = 'TTS Failed — ' + (result.message || 'Unavailable');
                    }
                } catch (e) {
                    this.voiceStatusText = 'TTS Network Error';
                }
            },

            playAudioFromUrl(url) {
                const audio = new Audio(url);
                audio.onended = () => {
                    this.ttsPlaying = false;
                    this.processTtsQueue();
                };
                audio.onerror = () => {
                    this.ttsPlaying = false;
                    this.processTtsQueue();
                };
                this.ttsQueue.push(audio);
                if (!this.ttsPlaying) {
                    this.processTtsQueue();
                }
            },

            processTtsQueue() {
                if (this.ttsPlaying || this.ttsQueue.length === 0) return;
                this.ttsPlaying = true;
                const audio = this.ttsQueue.shift();
                audio.play().catch(() => {
                    this.ttsPlaying = false;
                    this.processTtsQueue();
                });
            },

            testPatientNameVoice() {
                const name = this.testPatientName.trim() || 'নাসরিন সুলতানা';
                const gender = 'female';
                const fullText = this.buildText('calling', name, gender);
                this.voiceStatusText = `Testing: ${name}...`;

                const testQueueId = this.currentCalled?.id;
                if (!testQueueId) {
                    this.voiceStatusText = 'No running patient to test with';
                    return;
                }

                this.playTtsAudio(fullText, 'calling', testQueueId);
            },

            logDiagnostics() {
                console.log('[TTS] === DIAGNOSTICS ===');
                console.log('Voice Ready:', this.speechReady);
                console.log('TTS Playing:', this.ttsPlaying);
                console.log('TTS Queue:', this.ttsQueue.length);
                console.log('Cache Entries:', Object.keys(this.ttsCache).length);
                console.log('Doctor ID:', this.doctorId);
                console.log('Session ID:', this.sessionId);
                console.log('[TTS] === END DIAGNOSTICS ===');
                this.voiceStatusText = `Diagnostics: ${Object.keys(this.ttsCache).length} cached, ${this.ttsQueue.length} queued`;
            },

            async refreshQueue() {
                try {
                    const res = await fetch(`/display/${this.sessionId}/status`);
                    if (!res.ok) {
                        if (res.status === 404) {
                            this.sessionEnded = true;
                            clearInterval(this.refreshTimer);
                            clearInterval(this.clockTimer);
                            clearInterval(this.timerInterval);
                        }
                        return;
                    }
                    const data = await res.json();

                    if (data.session_status === 'closed') {
                        this.sessionEnded = true;
                        clearInterval(this.refreshTimer);
                        clearInterval(this.clockTimer);
                        clearInterval(this.timerInterval);
                        return;
                    }

                    this.queue = data.queue || [];
                    this.currentCalled = data.current_called;
                    this.nextInQueue = data.next_in_queue;
                    if (data.notices) this.notices = data.notices;

                    if (data.doctor) {
                        this.doctorData = data.doctor;
                    }

                    this.emergencyPatient = this.queue.find(q => q.priority === 'emergency' && q.status === 'calling');

                    // Restart running timer when currentCalled changes
                    this.startRunningTimer();

                    if (data.pending_announcement && data.pending_queue_id && data.pending_voice_text) {
                        const pendingKey = `${data.pending_announcement}_${data.pending_queue_id}_${data.pending_patient_id}`;
                        if (pendingKey !== this.lastPendingKey) {
                            this.lastPendingKey = pendingKey;
                            this.playTtsAudio(data.pending_voice_text, data.pending_announcement, data.pending_queue_id);
                        }
                    }
                } catch(e) {}
            }
        };
    }
    </script>
</body>
</html>
