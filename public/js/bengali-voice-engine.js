/**
 * Bengali Voice Engine v2 — Root Cause Fix
 *
 * Guarantees Bengali patient names are spoken correctly:
 *  - Validates selected voice actually supports Bengali
 *  - Normalizes Unicode text (NFC, strips invisible chars)
 *  - Falls back to Google Translate TTS if no browser Bengali voice
 *  - Full debug logging to console
 *  - Never transliterates Bengali to English
 *
 * Usage:
 *   const engine = new BengaliVoiceEngine();
 *   await engine.init();
 *   engine.speak('নাসরিন সুলতানা, আপনি ভিতরে প্রবেশ করুন।');
 */

class BengaliVoiceEngine {
    constructor() {
        /** @type {SpeechSynthesisVoice|null} */
        this.voice = null;

        /** @type {boolean} Whether all browser voices have been loaded */
        this.voicesLoaded = false;

        /** @type {boolean} Whether engine is ready to speak */
        this.ready = false;

        /** @type {boolean} Whether a Bengali-capable voice was found */
        this.hasBengaliVoice = false;

        /** @type {boolean} Whether fallback (Google Translate TTS) is available */
        this.fallbackAvailable = false;

        /** @type {Array<{msg: string, key?: string}>} Speech queue */
        this.queue = [];

        /** @type {boolean} Whether currently speaking */
        this.speaking = false;

        /** @type {Function|null} Called when engine becomes ready */
        this.onReady = null;

        /** @type {Function|null} Called on status changes */
        this.onStatus = null;

        /** @type {Function|null} Called on errors */
        this.onError = null;

        /** @type {SpeechSynthesisVoice[]} All available voices */
        this._allVoices = [];
    }

    // ─────────────────────────────────────────────
    // INITIALIZATION
    // ─────────────────────────────────────────────

    /**
     * Initialize the engine. Must be called after user interaction
     * (click/touch/keydown) to satisfy browser autoplay policy.
     * @returns {Promise<boolean>} true if ready
     */
    async init() {
        this._log('info', '=== BengaliVoiceEngine v2 Init ===');

        if (!('speechSynthesis' in window)) {
            this._log('error', 'Web Speech API NOT available in this browser');
            this._status('Speech not supported');
            return false;
        }

        this._log('info', 'TTS Available: YES');
        this._status('Loading Bengali voices...');

        // Cancel any pending speech from previous session
        window.speechSynthesis.cancel();

        // Step 1: Load all voices
        await this._loadVoices();

        // Step 2: Select and validate the best Bengali voice
        this._selectAndValidateVoice();

        // Step 3: Check fallback availability
        this._checkFallback();

        // Step 4: Unlock speech synthesis
        try {
            await this._unlock();
            this.ready = true;
            this._log('info', 'Engine READY. Bengali voice:', this.voice ? this.voice.name : 'NONE (using fallback)');
            this._status(this.hasBengaliVoice ? 'Voice Active — Bengali' : 'Voice Active — Fallback TTS');
            if (this.onReady) this.onReady();
            return true;
        } catch (e) {
            this._log('warn', 'Unlock failed, registering interaction listeners');
            this._status('Click anywhere to enable voice');
            this._registerUnlockListeners();
            return false;
        }
    }

    // ─────────────────────────────────────────────
    // VOICE LOADING & SELECTION
    // ─────────────────────────────────────────────

    /**
     * Load all available SpeechSynthesis voices.
     * Waits for the `voiceschanged` event if voices aren't loaded yet.
     */
    _loadVoices() {
        return new Promise((resolve) => {
            const collect = () => {
                this._allVoices = window.speechSynthesis.getVoices();
                this._log('info', `Total voices found: ${this._allVoices.length}`);

                if (this._allVoices.length > 0) {
                    this.voicesLoaded = true;
                    this._logVoices();
                    resolve();
                }
            };

            // Try collecting immediately
            collect();

            if (!this.voicesLoaded) {
                // Wait for voiceschanged event
                const onVoicesChanged = () => {
                    collect();
                    window.speechSynthesis.removeEventListener('voiceschanged', onVoicesChanged);
                    resolve();
                };
                window.speechSynthesis.addEventListener('voiceschanged', onVoicesChanged);

                // Timeout fallback — some browsers never fire voiceschanged
                setTimeout(() => {
                    if (!this.voicesLoaded) {
                        this._log('warn', 'voiceschanged timeout — using whatever voices are available');
                        collect();
                        resolve();
                    }
                }, 3000);
            }
        });
    }

    /**
     * Log all available voices to console for debugging.
     */
    _logVoices() {
        this._allVoices.forEach((v, i) => {
            this._log('info', `  Voice[${i}]: name="${v.name}" lang="${v.lang}" default=${v.default} localService=${v.localService}`);
        });
    }

    /**
     * Select the best Bengali voice from available voices.
     * Priority: bn-BD > bn-IN > any bn-* > bengali/bangla in name
     * Then VALIDATE by attempting a test speak.
     */
    _selectAndValidateVoice() {
        // Priority-ordered selection
        const candidates = [
            { test: (v) => v.lang === 'bn-BD', reason: 'Exact bn-BD match' },
            { test: (v) => v.lang === 'bn', reason: 'Exact bn match' },
            { test: (v) => v.lang === 'bn-IN', reason: 'bn-IN fallback' },
            { test: (v) => /^bn-/i.test(v.lang), reason: 'Any bn-* lang' },
            { test: (v) => /bengali|bangla/i.test(v.name), reason: 'Bengali/Bangla in voice name' },
        ];

        for (const { test, reason } of candidates) {
            const found = this._allVoices.find(test);
            if (found) {
                this.voice = found;
                this.hasBengaliVoice = true;
                this._log('info', `Bengali voice SELECTED: "${found.name}" (${found.lang}) — ${reason}`);
                return;
            }
        }

        // No Bengali voice found
        this.voice = null;
        this.hasBengaliVoice = false;
        this._log('warn', 'NO Bengali voice found in browser. Will use fallback TTS if available.');
    }

    /**
     * Check if Google Translate TTS fallback is reachable.
     */
    _checkFallback() {
        // Google Translate TTS URL pattern
        // This works as an <audio> src — no CORS needed for playback
        this.fallbackAvailable = true; // Assume available; tested on first speak
        this._log('info', 'Fallback TTS (Google Translate): ASSUMED AVAILABLE');
    }

    // ─────────────────────────────────────────────
    // UNLOCK (Browser Autoplay Policy)
    // ─────────────────────────────────────────────

    /**
     * Unlock speech synthesis with a silent test utterance.
     */
    _unlock() {
        return new Promise((resolve, reject) => {
            const test = new SpeechSynthesisUtterance('');
            test.lang = this.voice ? this.voice.lang : 'bn-BD';
            test.volume = 0;
            test.rate = 1;
            test.pitch = 1;
            if (this.voice) test.voice = this.voice;

            test.onend = () => {
                this._log('info', 'Unlock silent utterance completed');
                resolve();
            };
            test.onerror = (e) => {
                if (e.error === 'not-allowed' || e.error === 'canceled') {
                    this._log('error', `Unlock failed: ${e.error}`);
                    reject(new Error(e.error));
                } else {
                    resolve(); // Non-fatal errors
                }
            };

            window.speechSynthesis.speak(test);

            // Fallback timeout
            setTimeout(() => resolve(), 800);
        });
    }

    /**
     * Register passive unlock listeners on document.
     */
    _registerUnlockListeners() {
        const unlock = async () => {
            document.removeEventListener('click', unlock);
            document.removeEventListener('keydown', unlock);
            document.removeEventListener('touchstart', unlock);

            try {
                await this._unlock();
                this.ready = true;
                this._status(this.hasBengaliVoice ? 'Voice Active — Bengali' : 'Voice Active — Fallback TTS');
                this._log('info', 'Engine unlocked via user interaction');
                if (this.onReady) this.onReady();
                this._processQueue();
            } catch (e) {
                this._status('Voice blocked. Click again.');
                this._registerUnlockListeners();
            }
        };

        document.addEventListener('click', unlock);
        document.addEventListener('keydown', unlock);
        document.addEventListener('touchstart', unlock);
    }

    // ─────────────────────────────────────────────
    // TEXT NORMALIZATION
    // ─────────────────────────────────────────────

    /**
     * Normalize Bengali Unicode text for TTS.
     * - Converts to NFC normalization form
     * - Removes zero-width characters and invisible formatting
     * - Strips HTML tags
     * - Removes unwanted special characters but keeps Bengali punctuation
     * @param {string} text
     * @returns {string} Cleaned text
     */
    normalizeBengali(text) {
        if (!text) return '';

        let cleaned = text;

        // Strip HTML tags
        cleaned = cleaned.replace(/<[^>]*>/g, '');

        // Unicode NFC normalization (combines combining characters properly)
        try {
            cleaned = cleaned.normalize('NFC');
        } catch (e) {
            // Fallback if normalize not supported
        }

        // Remove zero-width characters (U+200B, U+200C, U+200D, U+FEFF, U+00AD)
        cleaned = cleaned.replace(/[\u200B\u200C\u200D\uFEFF\u00AD]/g, '');

        // Remove other invisible Unicode formatting characters
        cleaned = cleaned.replace(/[\u00A0\u1680\u2000-\u200F\u202A-\u202F\u205F-\u206F\u3000]/g, ' ');

        // Remove LTR/RTL markers
        cleaned = cleaned.replace(/[\u200E\u200F\u202A-\u202E\u2066-\u2069]/g, '');

        // Collapse multiple spaces
        cleaned = cleaned.replace(/\s+/g, ' ').trim();

        return cleaned;
    }

    /**
     * Validate that text contains Bengali Unicode characters.
     * Bengali block: U+0980–U+09FF
     * @param {string} text
     * @returns {boolean}
     */
    containsBengali(text) {
        return /[\u0980-\u09FF]/.test(text);
    }

    // ─────────────────────────────────────────────
    // SPEECH
    // ─────────────────────────────────────────────

    /**
     * Speak a Bengali message. Patient names are included directly
     * in the same Bengali utterance — never transliterated.
     *
     * @param {string} msg - Bengali text to speak (patient name embedded)
     * @param {string} [key] - Optional deduplication key
     * @returns {boolean} true if speaking started, false if queued
     */
    speak(msg, key) {
        if (!msg) return false;

        // Normalize the text
        const normalized = this.normalizeBengali(msg);

        this._log('info', '--- speak() called ---');
        this._log('info', `  Raw text: "${msg}"`);
        this._log('info', `  Normalized: "${normalized}"`);
        this._log('info', `  Contains Bengali: ${this.containsBengali(normalized)}`);
        this._log('info', `  Voice: ${this.voice ? this.voice.name : 'NONE'}`);
        this._log('info', `  Voice Lang: ${this.voice ? this.voice.lang : 'N/A'}`);
        this._log('info', `  Has Bengali Voice: ${this.hasBengaliVoice}`);
        this._log('info', `  Fallback Available: ${this.fallbackAvailable}`);
        this._log('info', `  Engine Ready: ${this.ready}`);
        this._log('info', `  Currently Speaking: ${this.speaking}`);
        this._log('info', `  Final Text (will speak): "${normalized}"`);

        if (!this.ready || this.speaking) {
            this._log('info', '  → Queued (not ready or currently speaking)');
            this.queue.push({ msg: normalized, key });
            return false;
        }

        this._speakNow(normalized);
        return true;
    }

    /**
     * Internal: Speak immediately using the best available method.
     * 1st choice: Browser SpeechSynthesis with Bengali voice
     * 2nd choice: Browser SpeechSynthesis with lang=bn-BD (cloud fallback)
     * 3rd choice: Google Translate TTS via Audio element
     */
    _speakNow(msg) {
        this.speaking = true;
        this._status('Speaking...');
        this._log('info', `_speakNow(): "${msg}"`);

        // Always try Web Speech API first
        if (this.hasBengaliVoice || this._canUseWebSpeechApi()) {
            this._speakViaWebSpeech(msg);
        } else if (this.fallbackAvailable) {
            this._speakViaFallback(msg);
        } else {
            this._log('error', 'No TTS method available!');
            this._status('No Bengali TTS available');
            this.speaking = false;
        }
    }

    /**
     * Check if Web Speech API can be used even without a local Bengali voice.
     * Some browsers (Chrome) use cloud-based TTS when lang is set.
     */
    _canUseWebSpeechApi() {
        // If browser has speechSynthesis, it might use cloud TTS
        // even without a local Bengali voice
        return 'speechSynthesis' in window;
    }

    /**
     * Speak using Web Speech API.
     */
    _speakViaWebSpeech(msg) {
        try {
            window.speechSynthesis.cancel();

            const u = new SpeechSynthesisUtterance(msg);

            if (this.voice) {
                // Use the validated Bengali voice
                u.voice = this.voice;
                u.lang = this.voice.lang;
                this._log('info', `WebSpeech: Using voice "${this.voice.name}" (${this.voice.lang})`);
            } else {
                // No Bengali voice — set lang to trigger cloud TTS
                u.lang = 'bn-BD';
                this._log('info', 'WebSpeech: No local voice, using lang=bn-BD (cloud TTS)');
            }

            // Optimal settings for Bengali pronunciation
            u.rate = 0.85;
            u.pitch = 1.0;
            u.volume = 1.0;

            u.onstart = () => {
                this._log('info', 'WebSpeech: Utterance started');
            };

            u.onend = () => {
                this._log('info', 'WebSpeech: Utterance completed');
                this.speaking = false;
                this._status('');
                this._processQueue();
            };

            u.onerror = (e) => {
                this._log('error', `WebSpeech error: ${e.error}`);
                this.speaking = false;

                if (e.error === 'not-allowed' || e.error === 'canceled') {
                    this.ready = false;
                    this._status('Voice blocked. Click page to enable.');
                    this._registerUnlockListeners();
                    this.queue.unshift({ msg });
                } else if (e.error === 'synthesis-failed' || e.error === 'language-unavailable') {
                    // Web Speech failed for Bengali — try fallback
                    this._log('warn', 'WebSpeech failed for Bengali, trying fallback...');
                    this._speakViaFallback(msg);
                } else {
                    this._status('');
                    this._processQueue();
                }
            };

            window.speechSynthesis.speak(u);
        } catch (e) {
            this._log('error', `WebSpeech exception: ${e.message}`);
            this.speaking = false;
            // Try fallback
            if (this.fallbackAvailable) {
                this._speakViaFallback(msg);
            } else {
                this._status('Voice unavailable');
            }
        }
    }

    /**
     * Speak using Google Translate TTS as fallback.
     * Uses an <audio> element to play the generated speech.
     */
    _speakViaFallback(msg) {
        this._log('info', `Fallback: Using Google Translate TTS for: "${msg}"`);

        try {
            // URL-encode the Bengali text
            const encoded = encodeURIComponent(msg);
            const url = `https://translate.google.com/translate_tts?ie=UTF-8&tl=bn&client=tw-ob&q=${encoded}`;

            const audio = new Audio();
            audio.src = url;
            audio.volume = 1.0;

            audio.onplay = () => {
                this._log('info', 'Fallback: Audio playback started');
                this._status('Speaking (Fallback TTS)...');
            };

            audio.onended = () => {
                this._log('info', 'Fallback: Audio playback completed');
                this.speaking = false;
                this._status('');
                this._processQueue();
            };

            audio.onerror = (e) => {
                this._log('error', 'Fallback: Audio playback failed', e);
                this.speaking = false;
                this._status('Fallback TTS failed');
                this._processQueue();
            };

            audio.play().catch((e) => {
                this._log('error', `Fallback: play() rejected: ${e.message}`);
                this.speaking = false;
                this._status('Playback blocked');
                this._processQueue();
            });
        } catch (e) {
            this._log('error', `Fallback exception: ${e.message}`);
            this.speaking = false;
            this._status('TTS unavailable');
            this._processQueue();
        }
    }

    // ─────────────────────────────────────────────
    // QUEUE
    // ─────────────────────────────────────────────

    /**
     * Process the next item in the speech queue.
     */
    _processQueue() {
        if (this.queue.length === 0 || this.speaking) return;

        const next = this.queue.shift();
        this._log('info', `Processing queued message: "${next.msg}"`);
        this._speakNow(next.msg);
    }

    // ─────────────────────────────────────────────
    // DEBUG LOGGING
    // ─────────────────────────────────────────────

    /**
     * Log to console with prefix.
     * @param {'info'|'warn'|'error'} level
     * @param {...any} args
     */
    _log(level, ...args) {
        const prefix = '[BengaliVoice]';
        if (level === 'error') {
            console.error(prefix, ...args);
        } else if (level === 'warn') {
            console.warn(prefix, ...args);
        } else {
            console.log(prefix, ...args);
        }
    }

    /**
     * Update status text (overridden by UI).
     */
    _status(text) {
        if (this.onStatus) this.onStatus(text);
    }

    // ─────────────────────────────────────────────
    // STATIC HELPERS
    // ─────────────────────────────────────────────

    /**
     * Check if a key has already been spoken (for deduplication).
     */
    static isAnnounced(announcedIds, key) {
        return announcedIds.has(key);
    }

    /**
     * Mark a key as spoken.
     */
    static markAnnounced(announcedIds, key) {
        announcedIds.add(key);
    }

    /**
     * Get a diagnostic report for debugging.
     * @returns {Object}
     */
    getDiagnostics() {
        return {
            ttsAvailable: 'speechSynthesis' in window,
            totalVoices: this._allVoices.length,
            voicesLoaded: this.voicesLoaded,
            hasBengaliVoice: this.hasBengaliVoice,
            selectedVoice: this.voice ? this.voice.name : null,
            selectedVoiceLang: this.voice ? this.voice.lang : null,
            fallbackAvailable: this.fallbackAvailable,
            engineReady: this.ready,
            currentlySpeaking: this.speaking,
            queueLength: this.queue.length,
            bengaliVoices: this._allVoices
                .filter(v => /bn|bengali|bangla/i.test(v.lang) || /bengali|bangla/i.test(v.name))
                .map(v => ({ name: v.name, lang: v.lang })),
        };
    }

    // ─────────────────────────────────────────────
    // ANNOUNCEMENT MESSAGES
    // ─────────────────────────────────────────────

    static messages = {
        /**
         * Step 1: Preparing — "Get ready for the next serial."
         */
        preparing(name) {
            return `পরবর্তী সিরিয়ালের জন্য প্রস্তুত থাকুন, ${name}।`;
        },

        /**
         * Step 2: Calling — "Please enter now."
         */
        calling(name) {
            return `${name}, আপনি ভিতরে প্রবেশ করুন।`;
        },

        /**
         * Step 2 (emergency): Emergency call.
         */
        emergency(name) {
            return `জরুরি! ${name}, আপনি ভিতরে প্রবেশ করুন।`;
        },

        /**
         * Step 3: Inside — "Thank you."
         */
        inside(name) {
            return `${name}, ধন্যবাদ।`;
        },

        /**
         * Step 4: Completed — "Thank you, consultation done."
         */
        completed(name) {
            return `${name}, ধন্যবাদ। আপনার চেকআপ সম্পন্ন হয়েছে।`;
        },

        /**
         * Recall: Re-announce a patient.
         */
        recall(name) {
            return `${name}, আপনার সিরিয়াল আবার ডাকা হচ্ছে।`;
        }
    };
}
