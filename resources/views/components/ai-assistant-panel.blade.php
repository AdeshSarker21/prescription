@props(['patientId' => null, 'compact' => false])

<div
    x-data="aiAssistantPanel({{ $patientId ? json_encode($patientId) : 'null' }})"
    x-init="init()"
    @patient-selected.window="patientId = $event.detail.patientId"
    style="position:fixed;bottom:20px;left:20px;z-index:9999;"
>
    {{-- Toggle Button --}}
    <button
        @click="open = !open"
        x-show="!open"
        class="flex items-center gap-2 px-4 py-3 rounded-full shadow-lg transition-all duration-300 hover:scale-105"
        style="background:linear-gradient(135deg,#6366f1,#8b5cf6);color:white;font-weight:600;font-size:14px;"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
        AI Assistant
    </button>

    {{-- Panel --}}
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-95"
        @click.away="open = false"
        class="bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden flex flex-col"
        style="width:420px;max-height:600px;bottom:80px;left:0;position:absolute;"
    >
        {{-- Header --}}
        <div class="px-4 py-3 flex items-center justify-between" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);color:white;">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <span class="font-semibold text-sm">AI Medical Assistant</span>
            </div>
            <button @click="open = false" class="text-white/80 hover:text-white">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Patient Context --}}
        <div class="px-4 py-2 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
            <span class="text-xs text-gray-500">Patient:</span>
            <span x-show="patientId" class="text-xs font-medium text-indigo-600" x-text="patientName || 'Loading...'"></span>
            <span x-show="!patientId" class="text-xs text-gray-400">None selected</span>
        </div>

        {{-- Quick Actions --}}
        <div class="px-4 py-2 border-b border-gray-100 flex flex-wrap gap-1">
            <button @click="quickAction('diagnosis')" :disabled="loading" class="px-2 py-1 text-xs rounded-md bg-indigo-50 text-indigo-700 hover:bg-indigo-100 disabled:opacity-50 transition-colors">
                Diagnosis
            </button>
            <button @click="quickAction('interactions')" :disabled="loading" class="px-2 py-1 text-xs rounded-md bg-amber-50 text-amber-700 hover:bg-amber-100 disabled:opacity-50 transition-colors">
                Drug Check
            </button>
            <button @click="quickAction('medicines')" :disabled="loading" class="px-2 py-1 text-xs rounded-md bg-green-50 text-green-700 hover:bg-green-100 disabled:opacity-50 transition-colors">
                Suggest Meds
            </button>
            <button @click="quickAction('tests')" :disabled="loading" class="px-2 py-1 text-xs rounded-md bg-blue-50 text-blue-700 hover:bg-blue-100 disabled:opacity-50 transition-colors">
                Suggest Tests
            </button>
            <button x-show="patientId" @click="quickAction('analyze')" :disabled="loading" class="px-2 py-1 text-xs rounded-md bg-purple-50 text-purple-700 hover:bg-purple-100 disabled:opacity-50 transition-colors">
                Analyze Patient
            </button>
        </div>

        {{-- Chat Messages --}}
        <div class="flex-1 overflow-y-auto p-4 space-y-3" style="max-height:350px;min-height:200px;" x-ref="chatContainer">
            <template x-for="(msg, idx) in messages" :key="idx">
                <div class="flex" :class="msg.role === 'user' ? 'justify-end' : 'justify-start'">
                    <div
                        class="max-w-[85%] rounded-xl px-3 py-2 text-sm leading-relaxed"
                        :class="msg.role === 'user'
                            ? 'bg-indigo-600 text-white'
                            : 'bg-gray-100 text-gray-800'"
                    >
                        <template x-if="msg.role === 'ai' && msg.data">
                            <div>
                                <p class="whitespace-pre-wrap" x-html="formatResponse(msg.content)"></p>

                                {{-- Warnings --}}
                                <template x-if="msg.data.warnings && msg.data.warnings.length > 0">
                                    <div class="mt-2 p-2 rounded-lg bg-red-50 border border-red-200">
                                        <p class="text-xs font-semibold text-red-700 mb-1">Warnings:</p>
                                        <template x-for="(warn, wi) in msg.data.warnings" :key="wi">
                                            <p class="text-xs text-red-600" x-text="warn"></p>
                                        </template>
                                    </div>
                                </template>

                                {{-- Drug Interactions --}}
                                <template x-if="msg.data.drug_interactions && msg.data.drug_interactions.length > 0">
                                    <div class="mt-2 p-2 rounded-lg bg-amber-50 border border-amber-200">
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

                                {{-- Suggestions --}}
                                <template x-if="msg.data.suggestions">
                                    <div class="mt-2 space-y-1">
                                        <template x-if="msg.data.suggestions.medicines && msg.data.suggestions.medicines.length > 0">
                                            <div class="p-2 rounded-lg bg-green-50 border border-green-200">
                                                <p class="text-xs font-semibold text-green-700 mb-1">Suggested Medicines:</p>
                                                <template x-for="(med, mi) in msg.data.suggestions.medicines" :key="mi">
                                                    <div class="text-xs text-green-800 mb-1">
                                                        <span class="font-medium" x-text="med.name || med"></span>
                                                        <template x-if="med.dosage">
                                                            <span x-text="' - ' + med.dosage"></span>
                                                        </template>
                                                        <template x-if="med.frequency">
                                                            <span x-text="' | ' + med.frequency"></span>
                                                        </template>
                                                        <template x-if="med.duration">
                                                            <span x-text="' | ' + med.duration"></span>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>

                                        <template x-if="msg.data.suggestions.diagnosis && msg.data.suggestions.diagnosis.length > 0">
                                            <div class="p-2 rounded-lg bg-indigo-50 border border-indigo-200">
                                                <p class="text-xs font-semibold text-indigo-700 mb-1">Possible Diagnoses:</p>
                                                <template x-for="(diag, di) in msg.data.suggestions.diagnosis" :key="di">
                                                    <p class="text-xs text-indigo-800" x-text="(di+1) + '. ' + diag"></p>
                                                </template>
                                            </div>
                                        </template>

                                        <template x-if="msg.data.suggestions.tests && msg.data.suggestions.tests.length > 0">
                                            <div class="p-2 rounded-lg bg-blue-50 border border-blue-200">
                                                <p class="text-xs font-semibold text-blue-700 mb-1">Suggested Tests:</p>
                                                <template x-for="(test, ti) in msg.data.suggestions.tests" :key="ti">
                                                    <p class="text-xs text-blue-800" x-text="(ti+1) + '. ' + test"></p>
                                                </template>
                                            </div>
                                        </template>

                                        <template x-if="msg.data.suggestions.advice && msg.data.suggestions.advice.length > 0">
                                            <div class="p-2 rounded-lg bg-teal-50 border border-teal-200">
                                                <p class="text-xs font-semibold text-teal-700 mb-1">Advice:</p>
                                                <template x-for="(adv, ai) in msg.data.suggestions.advice" :key="ai">
                                                    <p class="text-xs text-teal-800" x-text="(ai+1) + '. ' + adv"></p>
                                                </template>
                                            </div>
                                        </template>

                                        <template x-if="msg.data.suggestions.follow_up">
                                            <div class="p-2 rounded-lg bg-purple-50 border border-purple-200">
                                                <p class="text-xs font-semibold text-purple-700">Follow-up:</p>
                                                <p class="text-xs text-purple-800" x-text="msg.data.suggestions.follow_up"></p>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                                {{-- Disclaimer --}}
                                <p class="mt-2 text-[10px] text-gray-400 italic" x-text="msg.data.disclaimer || 'Clinical decision support tool. Final decisions rest with the physician.'"></p>
                            </div>
                        </template>
                        <template x-if="!msg.data">
                            <p class="whitespace-pre-wrap" x-text="msg.content"></p>
                        </template>
                    </div>
                </div>
            </template>

            {{-- Loading --}}
            <div x-show="loading" class="flex justify-start">
                <div class="bg-gray-100 rounded-xl px-3 py-2 text-sm text-gray-500 flex items-center gap-2">
                    <div class="flex gap-1">
                        <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay:0ms"></span>
                        <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay:150ms"></span>
                        <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay:300ms"></span>
                    </div>
                    <span class="text-xs">Analyzing...</span>
                </div>
            </div>
        </div>

        {{-- Input --}}
        <div class="p-3 border-t border-gray-200 bg-gray-50">
            <form @submit.prevent="sendMessage" class="flex gap-2">
                <input
                    type="text"
                    x-model="newMessage"
                    :placeholder="patientId ? 'Ask about this patient...' : 'Describe symptoms or ask a question...'"
                    class="flex-1 border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm px-3 py-2"
                    :disabled="loading"
                >
                <button
                    type="submit"
                    :disabled="loading || !newMessage.trim()"
                    class="px-3 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19V5m0 0l-7 7m7-7l7 7"/>
                    </svg>
                </button>
            </form>
            <p class="mt-1 text-[10px] text-gray-400 text-center">AI is a decision support tool. Final decisions rest with you.</p>
        </div>
    </div>
</div>

@once
<script>
function aiAssistantPanel(initialPatientId) {
    return {
        open: false,
        patientId: initialPatientId,
        patientName: '',
        messages: [
            { role: 'ai', content: 'Hello! I am your AI Medical Assistant. I can help with diagnosis, medicine suggestions, drug interaction checks, and patient analysis. How can I help?', data: null }
        ],
        newMessage: '',
        loading: false,

        init() {
            if (this.patientId) {
                this.loadPatientName(this.patientId);
            }
            this.$watch('patientId', (val) => {
                if (val) this.loadPatientName(val);
            });
        },

        async loadPatientName(id) {
            try {
                const res = await fetch(`{{ route('doctor.prescriptions.patient-data', ':id') }}`.replace(':id', id));
                const data = await res.json();
                this.patientName = data.name || '';
            } catch {
                this.patientName = '';
            }
        },

        async sendMessage() {
            if (!this.newMessage.trim() || this.loading) return;

            const userMsg = this.newMessage;
            this.messages.push({ role: 'user', content: userMsg });
            this.newMessage = '';
            this.loading = true;

            this.$nextTick(() => {
                if (this.$refs.chatContainer) {
                    this.$refs.chatContainer.scrollTop = this.$refs.chatContainer.scrollHeight;
                }
            });

            try {
                const res = await fetch('{{ route("doctor.ai-assistant.chat") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ message: userMsg, patient_id: this.patientId })
                });
                const data = await res.json();
                this.messages.push({
                    role: 'ai',
                    content: data.reply || data.message || 'I received your query.',
                    data: data
                });
            } catch (e) {
                this.messages.push({ role: 'ai', content: 'Sorry, an error occurred. Please try again.', data: null });
            } finally {
                this.loading = false;
                this.$nextTick(() => {
                    if (this.$refs.chatContainer) {
                        this.$refs.chatContainer.scrollTop = this.$refs.chatContainer.scrollHeight;
                    }
                });
            }
        },

        async quickAction(type) {
            let endpoint = '';
            let body = {};

            switch (type) {
                case 'diagnosis':
                    this.newMessage = 'What are possible diagnoses for the current complaints?';
                    this.sendMessage();
                    return;
                case 'interactions':
                    this.newMessage = 'Check for drug interactions between the medicines in this prescription.';
                    this.sendMessage();
                    return;
                case 'medicines':
                    this.newMessage = 'Suggest appropriate medicines for the current diagnosis.';
                    this.sendMessage();
                    return;
                case 'tests':
                    this.newMessage = 'What tests should be ordered for these symptoms?';
                    this.sendMessage();
                    return;
                case 'analyze':
                    if (!this.patientId) return;
                    endpoint = '{{ route("doctor.ai-assistant.analyzePatient") }}';
                    body = { patient_id: this.patientId };
                    this.loading = true;
                    try {
                        const res = await fetch(endpoint, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(body)
                        });
                        const data = await res.json();
                        this.messages.push({
                            role: 'ai',
                            content: data.reply || 'Patient analysis complete.',
                            data: data
                        });
                    } catch (e) {
                        this.messages.push({ role: 'ai', content: 'Error analyzing patient.', data: null });
                    } finally {
                        this.loading = false;
                        this.$nextTick(() => {
                            if (this.$refs.chatContainer) {
                                this.$refs.chatContainer.scrollTop = this.$refs.chatContainer.scrollHeight;
                            }
                        });
                    }
                    return;
            }
        },

        formatResponse(text) {
            if (!text) return '';
            return text
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\n/g, '<br>');
        }
    };
}
</script>
@endonce
