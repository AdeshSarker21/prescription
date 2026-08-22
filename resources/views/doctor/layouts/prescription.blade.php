<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Prescription') - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #818cf8;
            --secondary: #06b6d4;
            --secondary-dark: #0891b2;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --accent: #f43f5e;
            --glass-bg: rgba(255, 255, 255, 0.6);
            --glass-border: rgba(255, 255, 255, 0.45);
            --glass-shadow: 0 8px 32px rgba(0, 0, 0, 0.06);
            --glass-blur: 16px;
            --text-primary: #1e293b;
            --text-muted: #64748b;
            --radius: 14px;
            --radius-sm: 10px;
            --radius-xs: 6px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }

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
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes shimmer {
            0% { background-position: -200% center; }
            100% { background-position: 200% center; }
        }
        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 0 20px rgba(99, 102, 241, 0.2); }
            50% { box-shadow: 0 0 40px rgba(99, 102, 241, 0.4); }
        }

        body {
            min-height: 100vh;
            padding: 16px;
            font-family: figtree, system-ui, sans-serif;
            background: linear-gradient(-45deg, #e0e7ff, #fce7f3, #ccfbf1, #ddd6fe, #e0e7ff);
            background-size: 400% 400%;
            animation: gradientShift 18s ease infinite;
            position: relative;
            overflow-x: hidden;
        }

        .bg-blob {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.25;
            pointer-events: none;
            z-index: 0;
        }
        .bg-blob-1 {
            width: 500px; height: 500px;
            background: #6366f1;
            top: -150px; right: -100px;
            animation: floatBlob 22s ease-in-out infinite;
        }
        .bg-blob-2 {
            width: 450px; height: 450px;
            background: #06b6d4;
            bottom: -120px; left: -80px;
            animation: floatBlob 28s ease-in-out infinite reverse;
        }
        .bg-blob-3 {
            width: 350px; height: 350px;
            background: #f43f5e;
            top: 40%; left: 60%;
            animation: floatBlob 20s ease-in-out infinite 6s;
        }

        .glass {
            background: var(--glass-bg);
            backdrop-filter: blur(var(--glass-blur));
            -webkit-backdrop-filter: blur(var(--glass-blur));
            border: 1px solid var(--glass-border);
            box-shadow: var(--glass-shadow);
        }

        .prescription-wrap {
            display: grid;
            grid-template-columns: 170px 1fr 170px;
            max-width: 1600px;
            margin: 0 auto;
            border-radius: var(--radius);
            overflow: hidden;
            min-height: calc(100vh - 32px);
            position: relative;
            z-index: 1;
            animation: fadeInUp 0.6s ease-out;
        }

        .left-sidebar, .right-sidebar {
            padding: 16px 10px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        .left-sidebar {
            border-right: 1px solid rgba(255, 255, 255, 0.5);
            animation: slideInLeft 0.5s ease-out;
        }
        .right-sidebar {
            border-left: 1px solid rgba(255, 255, 255, 0.5);
            animation: slideInRight 0.5s ease-out;
        }
        .sidebar-spacer { flex-grow: 1; }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            padding: 10px 12px;
            color: var(--text-primary);
            font-weight: 600;
            font-size: 13px;
            text-decoration: none;
            background: rgba(255, 255, 255, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all 0.25s ease;
            text-align: left;
        }
        .sidebar-link:hover {
            background: rgba(99, 102, 241, 0.15);
            border-color: rgba(99, 102, 241, 0.3);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.15);
        }
        .sidebar-link:active {
            transform: translateY(0);
        }

        .main-content {
            background: rgba(255, 255, 255, 0.35);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            padding: 0 20px;
            display: flex;
            flex-direction: column;
            min-height: 100%;
            animation: fadeInUp 0.6s ease-out 0.15s both;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.55);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
            border-radius: var(--radius-sm);
            padding: 16px;
            transition: all 0.3s ease;
        }
        .glass-card:hover {
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.06);
        }

        .app-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 14px 0;
            position: relative;
        }
        .header-title {
            text-align: center;
            width: 100%;
        }
        .header-title h1 {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 0.5px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .header-title h2 {
            font-size: 12px;
            margin-top: 4px;
            letter-spacing: 1px;
            color: var(--text-muted);
        }
        .close-btn {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: var(--danger);
            padding: 4px 12px;
            border-radius: var(--radius-xs);
            cursor: pointer;
            font-weight: 700;
            font-size: 18px;
            text-decoration: none;
            transition: all 0.2s ease;
            position: absolute;
            right: 0;
            top: 10px;
            line-height: 1;
        }
        .close-btn:hover {
            background: rgba(239, 68, 68, 0.2);
            border-color: rgba(239, 68, 68, 0.4);
            transform: scale(1.05);
        }

        .patient-info-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px 16px;
            padding: 16px;
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: var(--radius-sm);
            margin-bottom: 12px;
            animation: fadeInUp 0.5s ease-out 0.2s both;
        }
        .form-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .form-group label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-primary);
            white-space: nowrap;
            letter-spacing: 0.3px;
        }
        .form-group input, .form-group select, .form-group textarea {
            padding: 6px 10px;
            border: 1px solid rgba(148, 163, 184, 0.3);
            border-radius: var(--radius-xs);
            background: rgba(255, 255, 255, 0.7);
            font-size: 13px;
            outline: none;
            transition: all 0.2s ease;
            color: var(--text-primary);
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            background: rgba(255, 255, 255, 0.9);
        }
        .form-group input[readonly] {
            background: rgba(241, 245, 249, 0.5);
            cursor: default;
        }
        .w-small { width: 70px; }
        .w-medium { width: 130px; }
        .w-full { flex-grow: 1; width: 100%; }
        .w-vitals { width: 55px; text-align: center; }
        .span-2 { grid-column: span 2; }
        .align-end { justify-content: flex-end; }

        .vitals-group {
            display: flex;
            gap: 24px;
            align-items: center;
            flex-wrap: wrap;
        }
        .inline-vitals {
            display: flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.4);
            padding: 4px 12px 4px 8px;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.4);
        }
        .unit { font-size: 11px; color: var(--text-muted); font-weight: 500; }
        .prev-val {
            font-size: 11px;
            color: var(--warning);
            font-weight: 600;
            margin-left: 6px;
            background: rgba(245, 158, 11, 0.1);
            padding: 1px 8px;
            border-radius: 10px;
        }

        .prescription-body {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 16px;
            margin-top: 8px;
            flex-grow: 1;
        }
        .left-pane {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .pane-section h3 {
            font-size: 13px;
            color: var(--text-primary);
            margin-bottom: 8px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .pane-section h3::before {
            content: '';
            width: 3px;
            height: 16px;
            border-radius: 2px;
            background: linear-gradient(var(--primary), var(--secondary));
        }
        .disease-text {
            width: 100%;
            height: 180px;
            border: 1px solid rgba(148, 163, 184, 0.3);
            border-radius: var(--radius-xs);
            resize: none;
            padding: 8px;
            background: rgba(255, 255, 255, 0.6);
            font-size: 13px;
            outline: none;
            transition: all 0.2s ease;
        }
        .disease-text:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        .test-inputs { display: flex; flex-direction: column; gap: 6px; }
        .test-inputs select { width: 100%; padding: 6px; border: 1px solid rgba(148, 163, 184, 0.3); border-radius: var(--radius-xs); height: 30px; background: rgba(255, 255, 255, 0.6); }

        .drug-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: var(--radius-sm);
            overflow: visible;
            margin-bottom: 12px;
            position: relative;
            z-index: 10;
        }
        .drug-table th, .drug-table td {
            padding: 0;
            border-bottom: 1px solid rgba(148, 163, 184, 0.15);
        }
        .drug-table th {
            background: rgba(99, 102, 241, 0.08);
            font-size: 12px;
            font-weight: 700;
            padding: 10px 8px;
            color: var(--text-primary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .drug-table td {
            background: rgba(255, 255, 255, 0.3);
        }
        .drug-table tr:last-child td {
            border-bottom: none;
        }
        .drug-table td input, .drug-table td select {
            width: 100%;
            border: none;
            padding: 8px 10px;
            font-size: 12px;
            outline: none;
            background: transparent;
            transition: all 0.2s ease;
        }
        .drug-table td input:focus, .drug-table td select:focus {
            background: rgba(99, 102, 241, 0.05);
        }
        .drug-table td .remove-row {
            background: rgba(239, 68, 68, 0.08);
            border: none;
            color: var(--danger);
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            padding: 4px 10px;
            border-radius: var(--radius-xs);
            transition: all 0.2s ease;
        }
        .drug-table td .remove-row:hover {
            background: rgba(239, 68, 68, 0.2);
            color: #dc2626;
        }

        .add-row-btn {
            padding: 8px 18px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.25s ease;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.25);
        }
        .add-row-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.35);
        }
        .add-row-btn:active {
            transform: translateY(0);
        }

        .submit-bar {
            display: flex;
            gap: 10px;
            align-items: center;
            padding: 8px 0;
            flex-wrap: wrap;
        }
        .submit-bar button, .submit-bar a button {
            padding: 8px 20px;
            font-size: 13px;
            font-weight: 600;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all 0.25s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.25);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.35);
        }

        .btn-secondary {
            background: rgba(100, 116, 139, 0.15);
            color: var(--text-primary);
            border: 1px solid rgba(148, 163, 184, 0.3) !important;
        }
        .btn-secondary:hover {
            background: rgba(100, 116, 139, 0.25);
            transform: translateY(-2px);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success), #059669);
            color: white;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.25);
        }
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.35);
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--warning), #d97706);
            color: white;
            box-shadow: 0 4px 14px rgba(245, 158, 11, 0.25);
        }
        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.35);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger), #dc2626);
            color: white;
            box-shadow: 0 4px 14px rgba(239, 68, 68, 0.25);
        }
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.35);
        }

        .btn-ghost {
            background: rgba(255, 255, 255, 0.5);
            color: var(--text-primary);
            border: 1px solid rgba(148, 163, 184, 0.2) !important;
        }
        .btn-ghost:hover {
            background: rgba(255, 255, 255, 0.7);
            transform: translateY(-2px);
        }

        .btn-action {
            width: 100%;
            padding: 11px 12px;
            color: white;
            font-weight: 600;
            font-size: 12px;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            gap: 6px;
            justify-content: center;
        }
        .btn-action:hover {
            transform: translateY(-2px);
        }
        .btn-action:active {
            transform: translateY(0);
        }

        .advice-section h3 {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .advice-section h3::before {
            content: '';
            width: 3px;
            height: 16px;
            border-radius: 2px;
            background: linear-gradient(var(--success), var(--secondary));
        }

        .record-footer {
            margin: 20px -20px 0 -20px;
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-top: 1px solid rgba(255, 255, 255, 0.3);
        }

        .nav-controls {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
        }
        .nav-controls button {
            background: rgba(255, 255, 255, 0.5);
            border: 1px solid rgba(148, 163, 184, 0.2);
            padding: 4px 10px;
            border-radius: var(--radius-xs);
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .nav-controls button:hover {
            background: rgba(255, 255, 255, 0.8);
        }
        .record-count { padding: 0 8px; font-family: monospace; }
        .btn-filter { margin-left: 10px; }
        .footer-search {
            border: 1px solid rgba(148, 163, 184, 0.3);
            padding: 4px 8px;
            width: 120px;
            border-radius: var(--radius-xs);
            background: rgba(255, 255, 255, 0.5);
        }

        .quick-remark-btns button, .preset-btn {
            font-size: 10px;
            padding: 4px 10px;
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: all 0.2s ease;
            color: var(--text-primary);
        }
        .quick-remark-btns button:hover, .preset-btn:hover {
            background: rgba(99, 102, 241, 0.1);
            border-color: rgba(99, 102, 241, 0.3);
        }

        .tag-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 12px;
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.2);
            border-radius: 20px;
            font-size: 12px;
            color: var(--text-primary);
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .tag-pill:hover {
            background: rgba(99, 102, 241, 0.15);
        }
        .tag-pill .remove-tag {
            border: none;
            background: none;
            cursor: pointer;
            font-size: 14px;
            line-height: 1;
            color: var(--text-muted);
            padding: 0 2px;
            transition: color 0.2s;
        }
        .tag-pill .remove-tag:hover {
            color: var(--danger);
        }

        .complain-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
            padding: 6px 10px;
            background: rgba(99, 102, 241, 0.08);
            border: 1px solid rgba(99, 102, 241, 0.15);
            border-radius: var(--radius-xs);
            font-size: 12px;
        }

        .test-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
            padding: 6px 10px;
            background: rgba(6, 182, 212, 0.08);
            border: 1px solid rgba(6, 182, 212, 0.15);
            border-radius: var(--radius-xs);
            font-size: 12px;
        }

        .ts-wrapper {
            position: relative;
        }
        .ts-wrapper .ts-control {
            border: 1px solid rgba(148, 163, 184, 0.3) !important;
            border-radius: var(--radius-xs) !important;
            background: rgba(255, 255, 255, 0.7) !important;
            box-shadow: none !important;
            min-height: 32px !important;
        }
        .ts-wrapper .ts-control:focus {
            border-color: var(--primary-light) !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1) !important;
        }
        .ts-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            z-index: 10;
            margin: 0.25rem 0 0;
            box-sizing: border-box;
            border: 1px solid rgba(148, 163, 184, 0.2) !important;
            border-radius: var(--radius-xs) !important;
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
        }
        .ts-dropdown .active {
            background: rgba(99, 102, 241, 0.08) !important;
        }
        .ts-dropdown [data-selectable].option {
            padding: 8px 10px;
            font-size: 13px;
        }
        .ts-dropdown .create:hover,
        .ts-dropdown .option:hover {
            background: rgba(99, 102, 241, 0.06) !important;
        }

        .select2-container--default .select2-selection--single {
            border: 1px solid rgba(148, 163, 184, 0.3) !important;
            border-radius: var(--radius-xs) !important;
            background: rgba(255, 255, 255, 0.7) !important;
            height: 34px !important;
            transition: all 0.2s ease;
        }
        .select2-container--default .select2-selection--single:focus,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: var(--primary-light) !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1) !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 34px !important;
            color: var(--text-primary) !important;
        }
        .select2-dropdown {
            border: 1px solid rgba(148, 163, 184, 0.2) !important;
            border-radius: var(--radius-xs) !important;
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(12px);
        }

        @media print {
            .left-sidebar, .right-sidebar, .record-footer, .close-btn, .submit-bar, .add-row-btn, .remove-row,
            .bg-blob, .btn-action { display: none !important; }
            body {
                background: white !important;
                padding: 0 !important;
                animation: none !important;
            }
            .prescription-wrap {
                grid-template-columns: 1fr;
                border: none;
                border-radius: 0;
                min-height: auto;
                box-shadow: none;
                background: white !important;
                animation: none !important;
            }
            .main-content {
                background: white !important;
                padding: 0 15px !important;
                animation: none !important;
            }
            .patient-info-grid {
                background: white !important;
                border: 1px solid #e2e8f0 !important;
                box-shadow: none !important;
            }
            .drug-table {
                background: white !important;
                border: 1px solid #e2e8f0 !important;
            }
            .drug-table th { background: #f8fafc !important; }
            .drug-table td { background: white !important; }
            .glass-card { background: white !important; border: 1px solid #e2e8f0 !important; }
            .app-header { padding: 8px 0 !important; }
            .header-title h1 { -webkit-text-fill-color: #1e293b !important; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="bg-blob bg-blob-1"></div>
    <div class="bg-blob bg-blob-2"></div>
    <div class="bg-blob bg-blob-3"></div>

    <div class="prescription-wrap">
        <aside class="left-sidebar">
            <a href="{{ route('doctor.dashboard') }}" class="sidebar-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                Dashboard
            </a>
            <a href="{{ route('doctor.patients.index') }}" class="sidebar-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Patient
            </a>
            <a href="{{ route('doctor.prescriptions.index') }}" class="sidebar-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                Prescription
            </a>
            <button type="button" class="sidebar-link" onclick="document.querySelector('.add-row-btn')?.click()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                Medicine
            </button>
        </aside>

        <main class="main-content">
            @yield('prescription-content')

            <footer class="record-footer">
                <div class="text-center text-xs w-full py-2" style="color:var(--text-muted);">
                    &copy; {{ date('Y') }} <strong>Happy Codding IT</strong>. All rights reserved. Design &amp; Developed by <strong>Happy Codding IT</strong>.
                </div>
            </footer>
        </main>

        <aside class="right-sidebar">
            @yield('right-sidebar-buttons')
            <div class="sidebar-spacer"></div>
            <button type="button" class="btn-action btn-primary" onclick="handlePrint()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Print
            </button>
        </aside>
    </div>
    @stack('scripts')
    <script>
        function handlePrint() {
            Swal.fire({
                title: 'Save & Print?',
                text: 'Save this prescription and open the print view.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Save & Print',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#6366f1',
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('prescription-form')?.insertAdjacentHTML('beforeend', '<input type="hidden" name="_print" value="1">');
                    submitForm();
                }
            });
            return false;
        }
    </script>

    {{-- Item Search Modal Global Styles --}}
    <style>
        /* Ensure item search modals are always on top of everything */
        [x-ref="portalRoot"] {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            z-index: 999999 !important;
            pointer-events: auto !important;
        }
        [x-ref="portalRoot"] > div {
            position: fixed !important;
            z-index: 999999 !important;
        }
        /* Remove overflow restrictions from body when modal is open */
        body.modal-open {
            overflow: hidden !important;
            position: relative !important;
        }
    </style>
</body>
</html>
