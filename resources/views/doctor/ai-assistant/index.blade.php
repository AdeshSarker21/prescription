@extends('doctor.layouts.app')

@section('title', 'AI Medical Assistant')

@section('header', 'AI Medical Assistant')

@section('content')
<div class="max-w-6xl mx-auto" x-data="{
    messages: [{ role: 'ai', content: 'Hello! I am your AI Medical Assistant. I can help with diagnosis, medicine suggestions, drug interaction checks, patient analysis, and more. Select a patient for context-aware responses, or ask general medical questions.', data: null }],
    newMessage: '',
    loading: false,
    selectedPatient: '{{ old('patient_id') }}',
    patientName: '',
    activePanel: null,

    sendMessage() {
        if (!this.newMessage.trim() || this.loading) return;
        const userMsg = this.newMessage;
        this.messages.push({ role: 'user', content: userMsg });
        this.newMessage = '';
        this.loading = true;
        this.scrollToBottom();

        fetch('{{ route('doctor.ai-assistant.chat') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ message: userMsg, patient_id: this.selectedPatient || null })
        })
        .then(res => res.json())
        .then(data => {
            this.messages.push({ role: 'ai', content: data.reply || data.message || 'I received your query.', data: data });
        })
        .catch(() => {
            this.messages.push({ role: 'ai', content: 'Sorry, an error occurred. Please try again.', data: null });
        })
        .finally(() => {
            this.loading = false;
            this.scrollToBottom();
        });
    },

    scrollToBottom() {
        this.$nextTick(() => {
            const el = document.getElementById('chat-messages');
            if (el) el.scrollTop = el.scrollHeight;
        });
    },

    quickDiagnosis() {
        this.newMessage = 'What are the possible diagnoses for the current symptoms?';
        this.sendMessage();
    },
    quickInteractions() {
        this.newMessage = 'Check for drug interactions between medicines I am considering.';
        this.sendMessage();
    },
    quickMeds() {
        this.newMessage = 'Suggest appropriate medicines for the diagnosis.';
        this.sendMessage();
    },
    quickTests() {
        this.newMessage = 'What laboratory tests should be ordered?';
        this.sendMessage();
    },

    // Drug Interaction Checker
    interactionMeds: [''],
    interactionResults: null,
    interactionLoading: false,
    addMedField() { this.interactionMeds.push(''); },
    removeMedField(i) { if (this.interactionMeds.length > 1) this.interactionMeds.splice(i, 1); },
    checkInteractions() {
        const meds = this.interactionMeds.filter(m => m.trim());
        if (meds.length < 2) return;
        this.interactionLoading = true;
        this.interactionResults = null;
        fetch('{{ route('doctor.ai-assistant.checkInteractions') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ medicines: meds, patient_id: this.selectedPatient || null })
        })
        .then(res => res.json())
        .then(data => { this.interactionResults = data; })
        .catch(() => { this.interactionResults = { reply: 'Error checking interactions.', warnings: [] }; })
        .finally(() => { this.interactionLoading = false; });
    },

    // Patient Analysis
    analysisResults: null,
    analysisLoading: false,
    analyzePatient() {
        if (!this.selectedPatient) return;
        this.analysisLoading = true;
        this.analysisResults = null;
        fetch('{{ route('doctor.ai-assistant.analyzePatient') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ patient_id: this.selectedPatient })
        })
        .then(res => res.json())
        .then(data => { this.analysisResults = data; })
        .catch(() => { this.analysisResults = { reply: 'Error analyzing patient.' }; })
        .finally(() => { this.analysisLoading = false; });
    },

    formatResponse(text) {
        if (!text) return '';
        return text.replace(/\\*\\*(.*?)\\*\\*/g, '<strong>$1</strong>').replace(/\\n/g, '<br>');
    }
}" x-init="
    $watch('selectedPatient', (val) => {
        if (val) {
            fetch('{{ route('doctor.prescriptions.patient-data', ':id') }}'.replace(':id', val))
                .then(r => r.json())
                .then(d => { patientName = d.name || ''; })
                .catch(() => { patientName = ''; });
        } else {
            patientName = '';
        }
    });
">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        {{-- Chat Area --}}
        <div class="lg:col-span-3 bg-white rounded-xl shadow-sm border border-gray-200 flex flex-col" style="min-height:500px;">
            <div class="p-4 border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-blue-50 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        AI Medical Assistant
                    </h3>
                    <p class="text-xs text-gray-500">Clinical decision support tool</p>
                </div>
                <div class="flex items-center gap-2">
                    <select x-model="selectedPatient" class="text-xs border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-2 py-1">
                        <option value="">No patient selected</option>
                        @foreach($patients as $patient)
                        <option value="{{ $patient->id }}">{{ $patient->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div id="chat-messages" class="flex-1 p-4 overflow-y-auto" style="max-height:450px;">
                <template x-for="(msg, index) in messages" :key="index">
                    <div class="flex mb-3" :class="msg.role === 'user' ? 'justify-end' : 'justify-start'">
                        <div class="max-w-[80%] rounded-xl px-4 py-3 text-sm" :class="msg.role === 'user' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-800'">
                            <template x-if="msg.role === 'ai' && msg.data">
                                <div>
                                    <p class="whitespace-pre-wrap leading-relaxed" x-html="formatResponse(msg.content)"></p>
                                    <template x-if="msg.data.warnings && msg.data.warnings.length > 0">
                                        <div class="mt-3 p-3 rounded-lg bg-red-50 border border-red-200">
                                            <p class="text-xs font-semibold text-red-700 mb-1">Warnings:</p>
                                            <template x-for="(w, wi) in msg.data.warnings" :key="wi">
                                                <p class="text-xs text-red-600" x-text="w"></p>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="msg.data.drug_interactions && msg.data.drug_interactions.length > 0">
                                        <div class="mt-3 p-3 rounded-lg bg-amber-50 border border-amber-200">
                                            <p class="text-xs font-semibold text-amber-700 mb-1">Drug Interactions:</p>
                                            <template x-for="(inter, ii) in msg.data.drug_interactions" :key="ii">
                                                <p class="text-xs text-amber-600">
                                                    <span x-text="inter.drugs.join(' + ')"></span>
                                                    <span class="font-medium" x-text="'[' + inter.severity + ']'"></span>:
                                                    <span x-text="inter.description"></span>
                                                </p>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="msg.data.suggestions">
                                        <div class="mt-3 space-y-2">
                                            <template x-if="msg.data.suggestions.medicines && msg.data.suggestions.medicines.length > 0">
                                                <div class="p-3 rounded-lg bg-green-50 border border-green-200">
                                                    <p class="text-xs font-semibold text-green-700 mb-1">Suggested Medicines:</p>
                                                    <template x-for="(med, mi) in msg.data.suggestions.medicines" :key="mi">
                                                        <div class="text-xs text-green-800 mb-1">
                                                            <span class="font-medium" x-text="med.name || med"></span>
                                                            <template x-if="med.dosage"><span x-text="' - ' + med.dosage"></span></template>
                                                            <template x-if="med.frequency"><span x-text="' | ' + med.frequency"></span></template>
                                                            <template x-if="med.duration"><span x-text="' | ' + med.duration"></span></template>
                                                            <template x-if="med.instructions"><span x-text="' | ' + med.instructions"></span></template>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                            <template x-if="msg.data.suggestions.diagnosis && msg.data.suggestions.diagnosis.length > 0">
                                                <div class="p-3 rounded-lg bg-indigo-50 border border-indigo-200">
                                                    <p class="text-xs font-semibold text-indigo-700 mb-1">Possible Diagnoses:</p>
                                                    <template x-for="(diag, di) in msg.data.suggestions.diagnosis" :key="di">
                                                        <p class="text-xs text-indigo-800" x-text="(di+1) + '. ' + diag"></p>
                                                    </template>
                                                </div>
                                            </template>
                                            <template x-if="msg.data.suggestions.tests && msg.data.suggestions.tests.length > 0">
                                                <div class="p-3 rounded-lg bg-blue-50 border border-blue-200">
                                                    <p class="text-xs font-semibold text-blue-700 mb-1">Suggested Tests:</p>
                                                    <template x-for="(test, ti) in msg.data.suggestions.tests" :key="ti">
                                                        <p class="text-xs text-blue-800" x-text="(ti+1) + '. ' + test"></p>
                                                    </template>
                                                </div>
                                            </template>
                                            <template x-if="msg.data.suggestions.advice && msg.data.suggestions.advice.length > 0">
                                                <div class="p-3 rounded-lg bg-teal-50 border border-teal-200">
                                                    <p class="text-xs font-semibold text-teal-700 mb-1">Advice:</p>
                                                    <template x-for="(adv, ai2) in msg.data.suggestions.advice" :key="ai2">
                                                        <p class="text-xs text-teal-800" x-text="(ai2+1) + '. ' + adv"></p>
                                                    </template>
                                                </div>
                                            </template>
                                            <template x-if="msg.data.suggestions.follow_up">
                                                <div class="p-3 rounded-lg bg-purple-50 border border-purple-200">
                                                    <p class="text-xs font-semibold text-purple-700">Follow-up:</p>
                                                    <p class="text-xs text-purple-800" x-text="msg.data.suggestions.follow_up"></p>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                    <p class="mt-2 text-[10px] text-gray-400 italic">Clinical decision support tool. Final decisions rest with the physician.</p>
                                </div>
                            </template>
                            <template x-if="!msg.data">
                                <p class="whitespace-pre-wrap" x-text="msg.content"></p>
                            </template>
                        </div>
                    </div>
                </template>
                <div x-show="loading" class="flex justify-start mb-3">
                    <div class="bg-gray-100 rounded-xl px-4 py-3 text-sm text-gray-500 flex items-center gap-2">
                        <div class="flex gap-1">
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:0ms"></span>
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:150ms"></span>
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:300ms"></span>
                        </div>
                        Analyzing medical data...
                    </div>
                </div>
            </div>

            <div class="p-4 border-t border-gray-200">
                <form @submit.prevent="sendMessage" class="flex gap-2">
                    <input type="text" x-model="newMessage" placeholder="Ask about symptoms, medicines, diagnoses..." class="flex-1 border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <button type="submit" :disabled="loading || !newMessage.trim()" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19V5m0 0l-7 7m7-7l7 7"/></svg>
                    </button>
                </form>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="lg:col-span-1 space-y-4">
            {{-- Quick Actions --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <h4 class="text-sm font-semibold text-gray-900 mb-3">Quick Actions</h4>
                <div class="space-y-2">
                    <button @click="quickDiagnosis()" :disabled="loading" class="w-full text-left px-3 py-2 text-sm text-gray-700 bg-gray-50 rounded-lg hover:bg-indigo-50 hover:text-indigo-700 transition-colors disabled:opacity-50">
                        Suggest Diagnosis
                    </button>
                    <button @click="activePanel = activePanel === 'interactions' ? null : 'interactions'" class="w-full text-left px-3 py-2 text-sm text-gray-700 bg-gray-50 rounded-lg hover:bg-amber-50 hover:text-amber-700 transition-colors">
                        Check Drug Interactions
                    </button>
                    <button @click="quickMeds()" :disabled="loading" class="w-full text-left px-3 py-2 text-sm text-gray-700 bg-gray-50 rounded-lg hover:bg-green-50 hover:text-green-700 transition-colors disabled:opacity-50">
                        Suggest Medicines
                    </button>
                    <button @click="quickTests()" :disabled="loading" class="w-full text-left px-3 py-2 text-sm text-gray-700 bg-gray-50 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-colors disabled:opacity-50">
                        Suggest Tests
                    </button>
                    <button @click="activePanel = activePanel === 'analysis' ? null : 'analysis'" :disabled="!selectedPatient" class="w-full text-left px-3 py-2 text-sm text-gray-700 bg-gray-50 rounded-lg hover:bg-purple-50 hover:text-purple-700 transition-colors disabled:opacity-50">
                        Analyze Patient
                    </button>
                </div>
            </div>

            {{-- Drug Interaction Checker --}}
            <div x-show="activePanel === 'interactions'" x-cloak x-transition class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <h4 class="text-sm font-semibold text-gray-900 mb-3">Drug Interaction Checker</h4>
                <div class="space-y-2">
                    <template x-for="(med, i) in interactionMeds" :key="i">
                        <div class="flex gap-2">
                            <input type="text" x-model="interactionMeds[i]" placeholder="Medicine name..." class="flex-1 border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs px-3 py-2">
                            <button @click="removeMedField(i)" x-show="interactionMeds.length > 1" class="text-red-400 hover:text-red-600 text-xs">&times;</button>
                        </div>
                    </template>
                    <button @click="addMedField()" class="text-xs text-indigo-600 hover:text-indigo-800">+ Add medicine</button>
                    <button @click="checkInteractions()" :disabled="interactionLoading || interactionMeds.filter(m => m.trim()).length < 2" class="w-full px-3 py-2 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700 disabled:opacity-50 transition-colors">
                        <span x-show="!interactionLoading">Check Interactions</span>
                        <span x-show="interactionLoading">Checking...</span>
                    </button>
                </div>
                <div x-show="interactionResults" class="mt-3">
                    <div class="p-2 rounded text-xs" :class="interactionResults?.warnings?.length > 0 ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700'">
                        <p x-text="interactionResults?.reply || ''" class="whitespace-pre-wrap"></p>
                    </div>
                </div>
            </div>

            {{-- Patient Analysis --}}
            <div x-show="activePanel === 'analysis'" x-cloak x-transition class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <h4 class="text-sm font-semibold text-gray-900 mb-3">Patient Analysis</h4>
                <p x-show="!selectedPatient" class="text-xs text-gray-500 mb-2">Select a patient from the dropdown above first.</p>
                <button @click="analyzePatient()" :disabled="analysisLoading || !selectedPatient" class="w-full px-3 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 disabled:opacity-50 transition-colors">
                    <span x-show="!analysisLoading">Analyze Patient</span>
                    <span x-show="analysisLoading">Analyzing...</span>
                </button>
                <div x-show="analysisResults" x-cloak class="mt-3 p-3 bg-gray-50 rounded-lg text-xs text-gray-700">
                    <p class="whitespace-pre-wrap" x-text="analysisResults?.reply || ''"></p>
                    <template x-if="analysisResults?.warnings?.length > 0">
                        <div class="mt-2 p-2 bg-red-50 rounded text-red-700">
                            <template x-for="(w, wi) in analysisResults.warnings" :key="wi">
                                <p x-text="w"></p>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
