<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $doctor->name ?? 'Doctor' }} - Patient Display</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --bg-dark: #0a0e1a;
            --bg-panel: #111827;
            --bg-card: #1a2236;
            --border: rgba(255,255,255,0.06);
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --green-bright: #22c55e;
            --green-dark: #15803d;
            --green-bg: rgba(34,197,94,0.12);
            --orange: #f97316;
            --red: #ef4444;
            --red-bg: rgba(239,68,68,0.15);
            --purple: #a78bfa;
            --yellow: #fbbf24;
        }
        body {
            font-family: 'Noto Sans Bengali', 'Segoe UI', sans-serif;
            background: var(--bg-dark);
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

        /* ===== LEFT PANEL - Doctor Profile ===== */
        .left-panel {
            width: 340px;
            min-width: 300px;
            background: var(--bg-panel);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 30px 24px;
            overflow-y: auto;
        }
        .doctor-avatar {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid rgba(99,102,241,0.4);
            box-shadow: 0 0 30px rgba(99,102,241,0.2);
            margin-bottom: 20px;
        }
        .doctor-name {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--text-primary);
            text-align: center;
            margin-bottom: 6px;
        }
        .doctor-name-bn {
            font-size: 1.1rem;
            font-weight: 600;
            color: #c4b5fd;
            text-align: center;
            margin-bottom: 12px;
        }
        .doctor-designation {
            font-size: 0.85rem;
            color: var(--text-secondary);
            text-align: center;
            margin-bottom: 4px;
        }
        .doctor-qualification {
            font-size: 0.8rem;
            color: var(--text-muted);
            text-align: center;
            margin-bottom: 4px;
            line-height: 1.4;
        }
        .doctor-specialization {
            display: inline-block;
            background: rgba(99,102,241,0.15);
            color: #818cf8;
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-top: 10px;
            text-align: center;
        }
        .doctor-chamber {
            margin-top: 16px;
            font-size: 0.8rem;
            color: var(--text-muted);
            text-align: center;
        }
        .doctor-chamber .label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
            margin-bottom: 4px;
        }
        .sub-specialties {
            margin-top: 12px;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            justify-content: center;
        }
        .sub-specialties span {
            background: rgba(139,92,246,0.1);
            color: #a78bfa;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.7rem;
        }

        /* Emergency Banner */
        .emergency-banner {
            width: 100%;
            background: linear-gradient(135deg, rgba(239,68,68,0.2), rgba(220,38,38,0.1));
            border: 1px solid rgba(239,68,68,0.4);
            border-radius: 12px;
            padding: 16px;
            margin-top: 20px;
            text-align: center;
            animation: emergency-flash 1.5s ease-in-out infinite;
        }
        @keyframes emergency-flash {
            0%, 100% { border-color: rgba(239,68,68,0.4); box-shadow: 0 0 0 rgba(239,68,68,0); }
            50% { border-color: rgba(239,68,68,0.8); box-shadow: 0 0 20px rgba(239,68,68,0.2); }
        }
        .emergency-banner .icon { font-size: 1.6rem; margin-bottom: 4px; }
        .emergency-banner .title {
            font-size: 0.9rem;
            font-weight: 700;
            color: #fca5a5;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .emergency-banner .patient-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: #fff;
            margin-top: 6px;
        }
        .emergency-banner .serial {
            font-size: 1.3rem;
            font-weight: 800;
            color: #f87171;
        }

        /* ===== RIGHT PANEL - Queue Table ===== */
        .right-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* Top Bar */
        .top-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 24px;
            background: var(--bg-panel);
            border-bottom: 1px solid var(--border);
        }
        .top-bar .title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-primary);
        }
        .top-bar .clock {
            font-size: 1.6rem;
            font-weight: 700;
            color: #818cf8;
            font-variant-numeric: tabular-nums;
        }
        .top-bar .date {
            font-size: 0.8rem;
            color: var(--text-muted);
        }
        .top-bar .live-badge {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.75rem;
            color: var(--green-bright);
            font-weight: 600;
        }
        .top-bar .live-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: var(--green-bright);
            animation: pulse-dot 2s infinite;
        }
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.3); }
        }

        /* Now Calling Banner */
        .calling-banner {
            background: linear-gradient(135deg, rgba(34,197,94,0.15), rgba(22,163,74,0.08));
            border-bottom: 2px solid rgba(34,197,94,0.3);
            padding: 20px 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 30px;
        }
        .calling-banner .label {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: var(--green-bright);
        }
        .calling-banner .serial-num {
            font-size: 2.6rem;
            font-weight: 900;
            color: var(--green-bright);
            text-shadow: 0 0 20px rgba(34,197,94,0.3);
        }
        .calling-banner .patient-name {
            font-size: 1.8rem;
            font-weight: 700;
            color: #fff;
        }
        .calling-banner .prompt {
            font-size: 0.9rem;
            color: var(--yellow);
            animation: blink 1.5s ease-in-out infinite;
        }
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }
        .no-calling-banner {
            background: rgba(30,41,59,0.5);
            border-bottom: 1px solid var(--border);
            padding: 16px 24px;
            text-align: center;
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        /* Queue Table */
        .queue-area {
            flex: 1;
            overflow-y: auto;
            padding: 0 24px 16px;
        }
        .queue-table {
            width: 100%;
            border-collapse: collapse;
        }
        .queue-table thead th {
            padding: 12px 14px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--text-muted);
            text-align: left;
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            background: var(--bg-dark);
            z-index: 1;
        }
        .queue-table tbody tr {
            border-bottom: 1px solid var(--border);
            transition: all 0.3s ease;
        }
        .queue-table tbody tr.row-calling {
            background: linear-gradient(90deg, rgba(34,197,94,0.2), rgba(34,197,94,0.08));
            border-left: 4px solid var(--green-bright);
        }
        .queue-table tbody tr.row-next {
            background: linear-gradient(90deg, rgba(21,128,61,0.2), rgba(21,128,61,0.08));
            border-left: 4px solid var(--green-dark);
        }
        .queue-table tbody tr.row-emergency {
            background: linear-gradient(90deg, rgba(239,68,68,0.2), rgba(239,68,68,0.08));
            border-left: 4px solid var(--red);
        }
        .queue-table tbody td {
            padding: 14px 14px;
            font-size: 0.95rem;
            vertical-align: middle;
        }
        .queue-table .col-serial {
            font-weight: 800;
            font-size: 1.1rem;
            color: var(--text-primary);
            width: 100px;
        }
        .queue-table .col-name {
            font-weight: 600;
            color: var(--text-primary);
        }
        .queue-table .col-status {
            width: 120px;
        }
        .queue-table .col-wait {
            width: 110px;
            color: var(--text-muted);
            font-size: 0.85rem;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-badge.running { background: var(--green-bg); color: var(--green-bright); }
        .status-badge.next { background: rgba(21,128,61,0.15); color: #4ade80; }
        .status-badge.waiting { background: rgba(148,163,184,0.1); color: var(--text-secondary); }
        .status-badge.emergency-badge { background: var(--red-bg); color: #fca5a5; }
        .status-badge.preparing { background: rgba(139,92,246,0.15); color: var(--purple); }

        .emergency-tag {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: var(--red-bg);
            color: #fca5a5;
            padding: 3px 8px;
            border-radius: 8px;
            font-size: 0.65rem;
            font-weight: 700;
            margin-left: 8px;
        }

        /* ===== BOTTOM TICKER ===== */
        .ticker-bar {
            background: linear-gradient(90deg, #1e293b, #0f172a);
            border-top: 1px solid var(--border);
            padding: 10px 0;
            overflow: hidden;
            white-space: nowrap;
            position: relative;
        }
        .ticker-content {
            display: inline-block;
            animation: ticker-scroll 30s linear infinite;
            font-size: 0.85rem;
            color: var(--text-secondary);
            padding-left: 100%;
        }
        .ticker-content .highlight {
            color: var(--green-bright);
            font-weight: 600;
        }
        .ticker-content .separator {
            margin: 0 30px;
            color: var(--text-muted);
        }
        @keyframes ticker-scroll {
            0% { transform: translateX(0); }
            100% { transform: translateX(-100%); }
        }

        /* Voice Bar */
        .voice-bar {
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 200;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .voice-bar .status-dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: var(--text-muted);
        }
        .voice-bar .status-dot.active { background: var(--green-bright); }
        .voice-bar .enable-btn {
            background: rgba(99,102,241,0.9);
            color: white;
            border: none;
            padding: 6px 16px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
        }
        .voice-bar .enable-btn:hover { background: rgba(99,102,241,1); }
        .voice-bar .label {
            font-size: 0.7rem;
            color: var(--text-muted);
        }

        .session-ended {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }
        .session-ended h2 { font-size: 2rem; color: var(--red); margin-bottom: 12px; }
        .session-ended p { color: var(--text-muted); font-size: 1.1rem; }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(148,163,184,0.2); border-radius: 3px; }
    </style>
</head>
<body x-data="patientDisplay()" x-init="init()">

    {{-- Controls --}}
    <div class="voice-bar">
        <button class="enable-btn" @click="toggleFullscreen()" x-text="isFullscreen ? 'Exit Fullscreen' : 'Fullscreen'" style="background:rgba(16,185,129,0.9);"></button>
        <div class="status-dot" :class="{ 'active': speechReady }"></div>
        <span class="label" x-text="voiceStatusText"></span>
        <template x-if="!speechReady">
            <button class="enable-btn" @click="unlockSpeech()">Enable Voice</button>
        </template>
    </div>

    {{-- Session Ended --}}
    <template x-if="sessionEnded">
        <div class="session-ended">
            <h2>সেশন সমাপ্ত</h2>
            <p>এই সেশন আর সক্রিয় নয়। দয়া করে রিসেপশনে যোগাযোগ করুন।</p>
        </div>
    </template>

    <template x-if="!sessionEnded">
        <div class="main-layout">

            {{-- ===== LEFT PANEL: Doctor Profile ===== --}}
            <div class="left-panel">
                <img class="doctor-avatar" :src="doctorData.avatar" :alt="doctorData.name" onerror="this.src='https://ui-avatars.com/api/?name=Doctor&color=7c3aed&background=ede9fe&size=280'">
                <div class="doctor-name" x-text="doctorData.name"></div>
                <div class="doctor-name-bn" x-show="doctorData.name_bn" x-text="doctorData.name_bn"></div>
                <div class="doctor-designation" x-show="doctorData.designation_title" x-text="doctorData.designation_title"></div>
                <div class="doctor-qualification" x-show="doctorData.qualification" x-text="doctorData.qualification"></div>
                <div class="doctor-specialization" x-show="doctorData.specialization" x-text="doctorData.specialization"></div>
                <div class="sub-specialties" x-show="doctorData.sub_specialties && doctorData.sub_specialties.length">
                    <template x-for="spec in doctorData.sub_specialties" :key="spec">
                        <span x-text="spec"></span>
                    </template>
                </div>
                <div class="doctor-chamber" x-show="chamberName">
                    <div class="label">Chamber</div>
                    <div x-text="chamberName"></div>
                </div>

                {{-- Emergency Patient --}}
                <template x-if="emergencyPatient">
                    <div class="emergency-banner">
                        <div class="icon">&#128680;</div>
                        <div class="title">EMERGENCY</div>
                        <div class="serial" x-text="'#' + (emergencyPatient.formatted_serial || String(emergencyPatient.serial_number).padStart(3, '0'))"></div>
                        <div class="patient-name" x-text="emergencyPatient.patient?.name || 'Patient'"></div>
                    </div>
                </template>
            </div>

            {{-- ===== RIGHT PANEL: Queue ===== --}}
            <div class="right-panel">
                {{-- Top Bar --}}
                <div class="top-bar">
                    <div>
                        <div class="title">Patient Queue</div>
                        <div class="live-badge"><span class="live-dot"></span> LIVE</div>
                    </div>
                    <div style="text-align:right;">
                        <div class="clock" x-text="currentTime"></div>
                        <div class="date" x-text="currentDate"></div>
                    </div>
                </div>

                {{-- Now Calling --}}
                <template x-if="currentCalled">
                    <div class="calling-banner">
                        <div style="text-align:center;">
                            <div class="label">Now Calling</div>
                            <div class="serial-num" x-text="'#' + (currentCalled.formatted_serial || String(currentCalled.serial_number).padStart(3, '0'))"></div>
                            <div class="patient-name" x-text="currentCalled.patient?.name || 'Patient'"></div>
                            <div class="prompt">দয়া করে চেম্বারে প্রবেশ করুন</div>
                        </div>
                    </div>
                </template>
                <template x-if="!currentCalled">
                    <div class="no-calling-banner">পরবর্তী রোগীর জন্য অপেক্ষা করা হচ্ছে...</div>
                </template>

                {{-- Queue Table --}}
                <div class="queue-area">
                    <table class="queue-table">
                        <thead>
                            <tr>
                                <th>সিরিয়াল</th>
                                <th>রোগীর নাম</th>
                                <th>স্ট্যাটাস</th>
                                <th>অপেক্ষার সময়</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="item in queue" :key="item.id">
                                <tr :class="{
                                    'row-calling': item.status === 'calling',
                                    'row-next': item.status === 'preparing',
                                    'row-emergency': item.priority === 'emergency'
                                }">
                                    <td class="col-serial">
                                        <span x-text="'#' + (item.formatted_serial || String(item.serial_number).padStart(3, '0'))"></span>
                                        <template x-if="item.priority === 'emergency'">
                                            <span class="emergency-tag">&#128680; EMERGENCY</span>
                                        </template>
                                    </td>
                                    <td class="col-name" x-text="item.patient?.name || 'Patient'"></td>
                                    <td class="col-status">
                                        <template x-if="item.status === 'calling'">
                                            <span class="status-badge running">Running</span>
                                        </template>
                                        <template x-if="item.status === 'preparing'">
                                            <span class="status-badge next">Next</span>
                                        </template>
                                        <template x-if="item.status === 'waiting' && item.priority === 'emergency'">
                                            <span class="status-badge emergency-badge">EMERGENCY</span>
                                        </template>
                                        <template x-if="item.status === 'waiting' && item.priority !== 'emergency'">
                                            <span class="status-badge waiting">Waiting</span>
                                        </template>
                                        <template x-if="item.status === 'inside'">
                                            <span class="status-badge" style="background:rgba(59,130,246,0.15);color:#60a5fa;">In Serial</span>
                                        </template>
                                    </td>
                                    <td class="col-wait" x-text="getWaitingTime(item)"></td>
                                </tr>
                            </template>
                            <template x-if="queue.length === 0">
                                <tr>
                                    <td colspan="4" style="text-align:center;padding:40px;color:var(--text-muted);">কোনো রোগী নেই</td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- Ticker --}}
                <div class="ticker-bar">
                    <div class="ticker-content">
                        <span class="highlight">সিরিয়াল: {{ $settings->prefix ?? '' }}</span>
                        <span class="separator">|</span>
                        <span>দয়া করে আপনার পালার অপেক্ষা করুন</span>
                        <span class="separator">|</span>
                        <span class="highlight">সিরিয়াল: {{ $settings->prefix ?? '' }}</span>
                        <span class="separator">|</span>
                        <span>অনুগ্রহ করে ডাক্তারের পরামর্শ অনুসরণ করুন</span>
                        <span class="separator">|</span>
                        <span class="highlight">সিরিয়াল: {{ $settings->prefix ?? '' }}</span>
                        <span class="separator">|</span>
                        <span>জরুরি রোগীদের অগ্রাধিকার দেওয়া হবে</span>
                        <span class="separator">|</span>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <script>
    function patientDisplay() {
        return {
            sessionId: @js($session->id),
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
            currentTime: '',
            currentDate: '',
            announcedIds: new Set(),
            lastRecalledId: null,
            emergencyPatient: null,
            speechReady: false,
            voiceError: false,
            voiceStatusText: 'Click "Enable Voice"',
            pendingUtterance: null,
            isFullscreen: false,

            init() {
                this.loadAnnouncedFromStorage();
                this.updateClock();
                this.clockTimer = setInterval(() => this.updateClock(), 1000);
                this.refreshQueue();
                this.refreshTimer = setInterval(() => this.refreshQueue(), 3000);
                document.addEventListener('fullscreenchange', () => {
                    this.isFullscreen = !!document.fullscreenElement;
                });
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

            loadAnnouncedFromStorage() {
                try {
                    const stored = localStorage.getItem('display_announced_' + this.sessionId);
                    if (stored) JSON.parse(stored).forEach(id => this.announcedIds.add(id));
                } catch(e) {}
            },

            saveAnnouncedToStorage() {
                try {
                    localStorage.setItem('display_announced_' + this.sessionId, JSON.stringify([...this.announcedIds]));
                } catch(e) {}
            },

            getGenderPrefix(gender) {
                if (gender === 'male') return 'জনাব';
                if (gender === 'female') return 'জনাবা';
                return 'জনাব';
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

            unlockSpeech() {
                if (!('speechSynthesis' in window)) {
                    this.voiceError = true;
                    this.voiceStatusText = 'Speech not supported';
                    return;
                }
                try {
                    const test = new SpeechSynthesisUtterance('');
                    test.lang = 'bn-BD';
                    test.volume = 0;
                    test.onend = () => {
                        this.speechReady = true;
                        this.voiceError = false;
                        this.voiceStatusText = 'Voice Active';
                        if (this.pendingUtterance) {
                            this.speak(this.pendingUtterance);
                            this.pendingUtterance = null;
                        }
                    };
                    test.onerror = () => {
                        this.speechReady = true;
                        this.voiceError = false;
                        this.voiceStatusText = 'Voice Active';
                    };
                    window.speechSynthesis.speak(test);
                    this.speechReady = true;
                    this.voiceError = false;
                    this.voiceStatusText = 'Voice Active';
                } catch(e) {
                    this.voiceError = true;
                    this.voiceStatusText = 'Voice Failed';
                }
            },

            speak(msg) {
                if (!this.speechReady || !msg) {
                    this.pendingUtterance = msg;
                    return;
                }
                try {
                    window.speechSynthesis.cancel();
                    const u = new SpeechSynthesisUtterance(msg);
                    u.lang = 'bn-BD';
                    u.rate = 0.9;
                    u.pitch = 1;
                    u.onerror = (e) => {
                        if (e.error === 'not-allowed') {
                            this.speechReady = false;
                            this.voiceStatusText = 'Voice blocked. Click Enable.';
                            this.pendingUtterance = msg;
                        }
                    };
                    window.speechSynthesis.speak(u);
                } catch(e) {}
            },

            async refreshQueue() {
                try {
                    const res = await fetch(`/display/${this.sessionId}/status`);
                    if (!res.ok) {
                        if (res.status === 404) {
                            this.sessionEnded = true;
                            clearInterval(this.refreshTimer);
                            clearInterval(this.clockTimer);
                        }
                        return;
                    }
                    const data = await res.json();

                    if (data.session_status === 'closed') {
                        this.sessionEnded = true;
                        clearInterval(this.refreshTimer);
                        clearInterval(this.clockTimer);
                        return;
                    }

                    this.queue = data.queue || [];
                    this.currentCalled = data.current_called;
                    this.nextInQueue = data.next_in_queue;

                    if (data.doctor) {
                        this.doctorData = data.doctor;
                    }

                    this.emergencyPatient = this.queue.find(q => q.priority === 'emergency' && q.status === 'calling');

                    if (this.currentCalled) {
                        const calledId = this.currentCalled.id;
                        const announceKey = 'calling_' + calledId;
                        if (!this.announcedIds.has(announceKey)) {
                            this.announcedIds.add(announceKey);
                            this.saveAnnouncedToStorage();
                            const name = this.currentCalled.patient?.name || 'Patient';
                            const gender = this.currentCalled.patient?.gender || '';
                            const prefix = this.getGenderPrefix(gender);
                            if (this.currentCalled.priority === 'emergency') {
                                this.speak(`জরুরি! ${prefix} ${name}, আপনি এবার ভিতরে প্রবেশ করুন।`);
                            } else {
                                this.speak(`${prefix} ${name}, আপনি এবার ভিতরে প্রবেশ করুন।`);
                            }
                        }

                        if (this.currentCalled.notes && this.currentCalled.notes.includes('Recalled')) {
                            const recallKey = 'recall_' + calledId + '_' + this.currentCalled.updated_at;
                            if (!this.announcedIds.has(recallKey)) {
                                this.announcedIds.add(recallKey);
                                this.saveAnnouncedToStorage();
                                const name = this.currentCalled.patient?.name || 'Patient';
                                this.speak(`${name}, আপনার সিরিয়াল আবার ডাকা হচ্ছে।`);
                            }
                        }
                    }

                    const preparing = this.queue.find(q => q.status === 'preparing');
                    if (preparing) {
                        const prepKey = 'preparing_' + preparing.id;
                        if (!this.announcedIds.has(prepKey)) {
                            this.announcedIds.add(prepKey);
                            this.saveAnnouncedToStorage();
                            const name = preparing.patient?.name || 'Patient';
                            this.speak(`এর পরে সিরিয়াল ${name}, আপনি প্রস্তুত থাকুন।`);
                        }
                    }
                } catch(e) {}
            }
        };
    }
    </script>
</body>
</html>
