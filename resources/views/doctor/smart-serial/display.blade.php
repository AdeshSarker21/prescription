<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Patient Display - {{ $doctorName }}</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            color: #e2e8f0;
            min-height: 100vh;
            overflow-x: hidden;
        }
        .display-container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .header {
            text-align: center;
            padding: 30px 0 20px;
            border-bottom: 2px solid rgba(99,102,241,0.3);
            margin-bottom: 30px;
        }
        .header h1 {
            font-size: 2.2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #818cf8, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
        }
        .header .subtitle {
            font-size: 1rem;
            color: #94a3b8;
        }
        .header .session-info {
            margin-top: 12px;
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        .header .session-info span {
            font-size: 0.85rem;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .header .session-info .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            display: inline-block;
            animation: pulse-dot 2s infinite;
        }
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }

        .now-calling {
            background: linear-gradient(135deg, rgba(249,115,22,0.15), rgba(234,88,12,0.08));
            border: 2px solid rgba(249,115,22,0.4);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }
        .now-calling::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(249,115,22,0.08) 0%, transparent 70%);
            animation: glow 3s ease-in-out infinite;
        }
        @keyframes glow {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 1; }
        }
        .now-calling .label {
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: #f97316;
            margin-bottom: 12px;
            position: relative;
        }
        .now-calling .patient-name {
            font-size: 2.8rem;
            font-weight: 800;
            color: #fff;
            position: relative;
            margin-bottom: 8px;
        }
        .now-calling .serial {
            font-size: 4rem;
            font-weight: 900;
            color: #fb923c;
            position: relative;
            text-shadow: 0 0 30px rgba(249,115,22,0.4);
        }
        .now-calling .prompt {
            font-size: 1.1rem;
            color: #fbbf24;
            margin-top: 16px;
            position: relative;
            animation: blink 1.5s ease-in-out infinite;
        }
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .no-calling {
            background: rgba(30,41,59,0.6);
            border: 1px solid rgba(100,116,139,0.3);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            margin-bottom: 30px;
        }
        .no-calling .icon { font-size: 3rem; margin-bottom: 12px; opacity: 0.4; }
        .no-calling p { color: #64748b; font-size: 1.1rem; }

        .queue-section { margin-top: 10px; }
        .queue-section h2 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 16px;
            padding-left: 4px;
        }
        .queue-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 12px;
        }
        .queue-card {
            background: rgba(30,41,59,0.7);
            border: 1px solid rgba(100,116,139,0.2);
            border-radius: 14px;
            padding: 18px;
            transition: all 0.3s ease;
        }
        .queue-card.preparing {
            border-color: rgba(139,92,246,0.5);
            background: linear-gradient(135deg, rgba(139,92,246,0.1), rgba(139,92,246,0.04));
            animation: preparing-pulse 2s ease-in-out infinite;
        }
        @keyframes preparing-pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(139,92,246,0.2); }
            50% { box-shadow: 0 0 20px 4px rgba(139,92,246,0.15); }
        }
        .queue-card .serial-num {
            font-size: 1.5rem;
            font-weight: 800;
            color: #818cf8;
            margin-bottom: 4px;
        }
        .queue-card.preparing .serial-num { color: #a78bfa; }
        .queue-card .name {
            font-size: 0.95rem;
            color: #cbd5e1;
            font-weight: 500;
        }
        .queue-card .status-badge {
            margin-top: 8px;
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .status-badge.waiting { background: rgba(245,158,11,0.15); color: #fbbf24; }
        .status-badge.preparing { background: rgba(139,92,246,0.2); color: #a78bfa; }
        .status-badge.calling { background: rgba(249,115,22,0.2); color: #fb923c; }
        .status-badge.inside { background: rgba(59,130,246,0.2); color: #60a5fa; }

        .voice-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(15,23,42,0.95);
            border-top: 1px solid rgba(100,116,139,0.2);
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 100;
            backdrop-filter: blur(10px);
        }
        .voice-bar .status {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.8rem;
            color: #64748b;
        }
        .voice-bar .status .dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #64748b;
        }
        .voice-bar .status.active .dot { background: #10b981; }
        .voice-bar .status.error .dot { background: #ef4444; }
        .voice-bar .enable-btn {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .voice-bar .enable-btn:hover { transform: scale(1.05); box-shadow: 0 4px 15px rgba(99,102,241,0.4); }

        .session-ended {
            text-align: center;
            padding: 80px 20px;
        }
        .session-ended h2 { font-size: 2rem; color: #ef4444; margin-bottom: 12px; }
        .session-ended p { color: #94a3b8; font-size: 1.1rem; }

        .live-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            margin-right: 6px;
            animation: pulse-dot 2s infinite;
        }
    </style>
</head>
<body x-data="patientDisplay()" x-init="init()">

    <div class="display-container">
        {{-- Header --}}
        <div class="header">
            <h1>{{ $doctorName }}</h1>
            @if($chamberName)
                <p class="subtitle">{{ $chamberName }}</p>
            @endif
            <div class="session-info">
                <span>
                    <span class="dot"></span>
                    Session Active
                </span>
                <span>Started {{ $session->started_at->format('h:i A') }}</span>
                <span><span class="live-dot"></span> Live</span>
            </div>
        </div>

        {{-- Session Ended --}}
        <template x-if="sessionEnded">
            <div class="session-ended">
                <h2>Session Ended</h2>
                <p>This session is no longer active. Please contact the reception.</p>
            </div>
        </template>

        <template x-if="!sessionEnded">
            <div>
                {{-- Now Calling --}}
                <template x-if="currentCalled">
                    <div class="now-calling">
                        <div class="label">Now Calling</div>
                        <div class="serial" x-text="'#' + (currentCalled.formatted_serial || String(currentCalled.serial_number).padStart(3, '0'))"></div>
                        <div class="patient-name" x-text="currentCalled.patient?.name || 'Patient'"></div>
                        <div class="prompt">Please come to the chamber</div>
                    </div>
                </template>
                <template x-if="!currentCalled">
                    <div class="no-calling">
                        <div class="icon">&#128203;</div>
                        <p>Waiting for the next patient...</p>
                    </div>
                </template>

                {{-- Queue List --}}
                <div class="queue-section">
                    <h2>Queue</h2>
                    <div class="queue-grid">
                        <template x-for="item in queue" :key="item.id">
                            <div class="queue-card" :class="{ 'preparing': item.status === 'preparing' }">
                                <div class="serial-num" x-text="'#' + (item.formatted_serial || String(item.serial_number).padStart(3, '0'))"></div>
                                <div class="name" x-text="item.patient?.name || 'Patient'"></div>
                                <span class="status-badge" :class="item.status" x-text="item.status.charAt(0).toUpperCase() + item.status.slice(1)"></span>
                            </div>
                        </template>
                        <template x-if="queue.length === 0">
                            <div style="grid-column:1/-1;text-align:center;padding:40px;color:#475569;">
                                No patients in queue
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Voice Status Bar --}}
    <div class="voice-bar">
        <div class="status" :class="{ 'active': speechReady, 'error': voiceError }">
            <span class="dot"></span>
            <span x-text="voiceStatusText"></span>
        </div>
        <template x-if="!speechReady">
            <button class="enable-btn" @click="unlockSpeech()">
                Enable Voice
            </button>
        </template>
    </div>

    <script>
    function patientDisplay() {
        return {
            sessionId: @js($session->id),
            queue: @js($queue->values()->toArray()),
            currentCalled: @js($currentCalled),
            sessionEnded: false,
            refreshTimer: null,
            announcedIds: new Set(),
            lastRecalledId: null,
            speechReady: false,
            voiceError: false,
            voiceStatusText: 'Click "Enable Voice" to start announcements',
            pendingUtterance: null,

            init() {
                this.loadAnnouncedFromStorage();
                this.refreshQueue();
                this.refreshTimer = setInterval(() => this.refreshQueue(), 3000);
            },

            loadAnnouncedFromStorage() {
                try {
                    const stored = localStorage.getItem('display_announced_' + this.sessionId);
                    if (stored) {
                        JSON.parse(stored).forEach(id => this.announcedIds.add(id));
                    }
                } catch(e) {}
            },

            saveAnnouncedToStorage() {
                try {
                    localStorage.setItem(
                        'display_announced_' + this.sessionId,
                        JSON.stringify([...this.announcedIds])
                    );
                } catch(e) {}
            },

            getGenderPrefix(gender) {
                if (gender === 'male') return 'জনাব';
                if (gender === 'female') return 'জনাবা';
                return 'জনাব';
            },

            unlockSpeech() {
                if (!('speechSynthesis' in window)) {
                    this.voiceError = true;
                    this.voiceStatusText = 'Speech not supported in this browser';
                    return;
                }
                try {
                    const test = new SpeechSynthesisUtterance('');
                    test.lang = 'bn-BD';
                    test.volume = 0;
                    test.onend = () => {
                        this.speechReady = true;
                        this.voiceError = false;
                        this.voiceStatusText = 'Voice active';
                        if (this.pendingUtterance) {
                            this.speak(this.pendingUtterance);
                            this.pendingUtterance = null;
                        }
                    };
                    test.onerror = () => {
                        this.speechReady = true;
                        this.voiceError = false;
                        this.voiceStatusText = 'Voice active';
                    };
                    window.speechSynthesis.speak(test);
                    this.speechReady = true;
                    this.voiceError = false;
                    this.voiceStatusText = 'Voice active';
                } catch(e) {
                    this.voiceError = true;
                    this.voiceStatusText = 'Failed to initialize voice';
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
                        console.error('Display speech error:', e.error);
                        if (e.error === 'not-allowed') {
                            this.speechReady = false;
                            this.voiceError = true;
                            this.voiceStatusText = 'Voice blocked. Click "Enable Voice".';
                            this.pendingUtterance = msg;
                        }
                    };
                    window.speechSynthesis.speak(u);
                } catch(e) {
                    console.error('Display speech failed:', e);
                }
            },

            async refreshQueue() {
                try {
                    const res = await fetch(`/display/${this.sessionId}/status`);
                    if (!res.ok) {
                        if (res.status === 404) {
                            this.sessionEnded = true;
                            clearInterval(this.refreshTimer);
                        }
                        return;
                    }
                    const data = await res.json();

                    if (data.session_status === 'closed') {
                        this.sessionEnded = true;
                        clearInterval(this.refreshTimer);
                        return;
                    }

                    this.queue = data.queue || [];
                    this.currentCalled = data.current_called;

                    if (this.currentCalled) {
                        const calledId = this.currentCalled.id;
                        const announceKey = 'calling_' + calledId;
                        if (!this.announcedIds.has(announceKey)) {
                            this.announcedIds.add(announceKey);
                            this.saveAnnouncedToStorage();
                            const name = this.currentCalled.patient?.name || 'Patient';
                            const gender = this.currentCalled.patient?.gender || '';
                            const prefix = this.getGenderPrefix(gender);
                            this.speak(`${prefix} ${name}, আপনি এবার ভিতরে প্রবেশ করুন।`);
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
                } catch(e) {
                    console.error('Display refresh failed:', e);
                }
            }
        };
    }
    </script>
</body>
</html>
