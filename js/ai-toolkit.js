/**
 * AI Clinical Toolkit for eMedic
 * 
 * Portable widget combining:
 * 1. Speech Dictation (Web Speech API SpeechRecognition)
 * 2. Note Polishing (LLM-powered)
 * 3. Patient Summary (LLM + SpeechSynthesis TTS)
 * 
 * Ported from corehealth's SpeechDictationKit + PatientSummaryManager.
 */

// ─── Utils ─────────────────────────────────────────────────────────────
function getActiveHospitalNo() {
    if (window.currentHospitalNo) return window.currentHospitalNo;
    if (window.currentPatientId) return window.currentPatientId;
    const params = new URLSearchParams(window.location.search);
    if (params.get('hosp_no')) return params.get('hosp_no');
    if (params.get('hospital_no')) return params.get('hospital_no');
    const mgtNotes = document.getElementById('mgt_notes');
    if (mgtNotes && mgtNotes.dataset.hospital_no) return mgtNotes.dataset.hospital_no;
    return '';
}

// ─── AI Dictation Kit ────────────────────────────────────────────────

class AIDictationKit {
    constructor(options = {}) {
        this.targetSelector = options.targetSelector || '.summernote, textarea';
        this.editorType = options.editorType || 'auto';
        this.defaultLang = options.defaultLang || 'en-US';

        // DOM refs (set by widget PHP)
        this.button = document.getElementById('ai-dictate-btn');
        this.previewBubble = document.getElementById('ai-dictate-preview');
        this.previewText = document.getElementById('ai-dictate-preview-text');
        this.formatButton = document.getElementById('ai-polish-btn');
        this.summaryButton = document.getElementById('ai-summary-btn');
        this.langSelect = document.getElementById('ai-lang-select');
        this.overlayStopBtn = document.getElementById('ai-dictate-stop');

        this.recognition = null;
        this.isListening = false;
        this.ignoreNextStart = false;

        if (!this.button) return;
        this.init();
    }

    init() {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SpeechRecognition) {
            this.button.disabled = true;
            this.button.innerHTML = '<i class="fa fa-microphone-slash"></i> Unsupported';
            this.button.title = 'Speech recognition is not supported (use Chrome or Edge).';
            return;
        }

        this.recognition = new SpeechRecognition();
        this.recognition.continuous = true;
        this.recognition.interimResults = true;
        this.recognition.lang = this.getLanguage();

        this.bindEvents();
    }

    getLanguage() {
        return this.langSelect ? this.langSelect.value : this.defaultLang;
    }

    getActiveTarget() {
        // Try to find the first visible Summernote or textarea
        const targets = document.querySelectorAll(this.targetSelector);
        for (let t of targets) {
            if (t.offsetParent !== null) return t;
        }
        return targets[0] || null;
    }

    bindEvents() {
        // Track the last focused input element
        document.addEventListener('focusin', (e) => {
            const el = e.target;
            if (el.matches('textarea, input[type="text"]') || el.classList.contains('trumbowyg-editor') || el.classList.contains('note-editable')) {
                this.lastFocusedInput = el;
            }
        });

        this.button.addEventListener('click', (e) => {
            e.preventDefault();
            this.isListening ? this.stop() : this.start();
        });

        if (this.overlayStopBtn) {
            this.overlayStopBtn.addEventListener('click', (e) => { e.preventDefault(); this.stop(); });
        }

        if (this.formatButton) {
            this.formatButton.addEventListener('click', (e) => { e.preventDefault(); this.polishNote(); });
        }

        if (this.summaryButton) {
            this.summaryButton.addEventListener('click', (e) => {
                e.preventDefault();
                const hospNo = getActiveHospitalNo();
                if (window.aiPatientSummary && hospNo) {
                    window.aiPatientSummary.hospitalNo = hospNo;
                    window.aiPatientSummary.openAndLoad();
                } else {
                    alert('Please select a patient first to generate a clinical summary.');
                }
            });
        }

        if (this.langSelect) {
            this.langSelect.addEventListener('change', () => {
                if (this.recognition) {
                    this.recognition.lang = this.getLanguage();
                    if (this.isListening) { this.ignoreNextStart = true; this.recognition.stop(); }
                }
            });
        }

        // Speech Recognition Lifecycle
        this.recognition.onstart = () => {
            this.isListening = true;
            this.updateUI('listening');
        };

        this.recognition.onend = () => {
            if (this.ignoreNextStart) {
                this.ignoreNextStart = false;
                this.recognition.start();
                return;
            }
            this.isListening = false;
            this.updateUI('idle');
        };

        this.recognition.onerror = (event) => {
            console.error('Speech recognition error:', event.error);
            if (this.previewText) {
                let msg = 'Speech error occurred.';
                if (event.error === 'not-allowed') msg = 'Microphone access denied.';
                if (event.error === 'no-speech') msg = 'No speech detected.';
                this.previewText.innerHTML = '<span style="color:#c0392b"><i class="fa fa-exclamation-circle"></i> ' + msg + '</span>';
            }
            this.isListening = false;
            this.updateUI('idle');
        };

        this.recognition.onresult = (event) => {
            let interimTranscript = '';
            let finalTranscript = '';

            for (let i = event.resultIndex; i < event.results.length; ++i) {
                const piece = event.results[i][0].transcript;
                if (event.results[i].isFinal) {
                    finalTranscript += piece;
                } else {
                    interimTranscript += piece;
                }
            }

            if (interimTranscript && this.previewText) {
                this.previewText.innerHTML = '<em>' + interimTranscript + '</em>';
                if (this.previewBubble) this.previewBubble.style.display = 'block';
            }

            if (finalTranscript) {
                const formatted = this.formatSpeech(finalTranscript);
                if (formatted) this.insertText(formatted);
                if (this.previewText) this.previewText.textContent = 'Listening...';
            }
        };
    }

    start() {
        if (!this.lastFocusedInput) {
            alert('Please click inside a text box (like the consultation notes) before dictating.');
            return;
        }
        if (this.recognition && !this.isListening) {
            this.recognition.lang = this.getLanguage();
            this.updateUI('connecting');
            try { this.recognition.start(); } catch (e) { console.error(e); }
        }
    }

    stop() {
        if (this.recognition && this.isListening) this.recognition.stop();
    }

    updateUI(state) {
        if (state === 'listening') {
            this.button.className = 'btn btn-danger btn-sm';
            this.button.innerHTML = '<i class="fa fa-microphone"></i> Stop Dictation';
            if (this.previewBubble) this.previewBubble.style.display = 'block';
            if (this.previewText) this.previewText.textContent = 'Listening...';
        } else if (state === 'connecting') {
            this.button.className = 'btn btn-warning btn-sm';
            this.button.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Connecting...';
        } else {
            this.button.className = 'btn btn-info btn-sm';
            this.button.innerHTML = '<i class="fa fa-microphone"></i> Dictate';
            if (this.previewBubble) this.previewBubble.style.display = 'none';
        }
    }

    formatSpeech(text) {
        let result = text;
        const commands = [
            { pattern: /\b(period|full stop)\b/gi, replacement: '.' },
            { pattern: /\b(comma)\b/gi, replacement: ',' },
            { pattern: /\b(question mark)\b/gi, replacement: '?' },
            { pattern: /\b(exclamation mark)\b/gi, replacement: '!' },
            { pattern: /\b(new line|next line)\b/gi, replacement: '\n' },
            { pattern: /\b(new paragraph)\b/gi, replacement: '\n\n' },
            { pattern: /\b(colon)\b/gi, replacement: ':' },
            { pattern: /\b(semicolon)\b/gi, replacement: ';' },
            { pattern: /\b(hyphen|dash)\b/gi, replacement: '-' },
        ];
        commands.forEach(cmd => { result = result.replace(cmd.pattern, cmd.replacement); });
        result = result.replace(/\s+/g, ' ').replace(/\s+([.,?!:;])/g, '$1');
        return result.trim();
    }

    insertText(text) {
        const target = this.lastFocusedInput;
        if (!target) return;

        // Trumbowyg Support
        if (target.classList.contains('trumbowyg-editor')) {
            target.innerHTML = target.innerHTML + ' ' + text;
            const textarea = target.parentElement.querySelector('textarea.trumbowyg-textarea');
            if (textarea) textarea.value = target.innerHTML;
            $(target).trigger('tbwchange');
            $(target).trigger('input');
            return;
        }

        // Summernote Support
        if (target.classList.contains('note-editable')) {
            const originalTextarea = target.parentElement.parentElement.previousElementSibling;
            if (originalTextarea && $(originalTextarea).data('summernote')) {
                $(originalTextarea).summernote('insertText', ' ' + text);
                return;
            }
        }

        // Standard Textarea / Input
        if (target.value !== undefined) {
            const startPos = target.selectionStart || 0;
            const endPos = target.selectionEnd || 0;
            target.value = target.value.substring(0, startPos) + ' ' + text + target.value.substring(endPos);
            target.selectionStart = target.selectionEnd = startPos + text.length + 1;
            target.dispatchEvent(new Event('change', { bubbles: true }));
        } else {
            // Contenteditable fallback
            target.innerHTML += ' ' + text;
        }
        target.focus();
    }

    async polishNote() {
        if (!this.formatButton) return;

        if (!this.lastFocusedInput) {
            alert('Please click inside a text box to select the note you want to polish.');
            return;
        }

        const originalHTML = this.formatButton.innerHTML;
        this.formatButton.disabled = true;
        this.formatButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Polishing...';

        try {
            let text = '';
            const target = this.lastFocusedInput;

            if (target.classList.contains('trumbowyg-editor')) {
                text = target.innerHTML;
            } else if (target.classList.contains('note-editable')) {
                const originalTextarea = target.parentElement.parentElement.previousElementSibling;
                if (originalTextarea) text = $(originalTextarea).summernote('code');
            } else if (target.value !== undefined) {
                text = target.value;
            } else {
                text = target.innerHTML;
            }

            if (!text || text.trim() === '' || text.trim() === '<p><br></p>') {
                alert('Clinical note is empty. Add some text first!');
                return;
            }

            const hospitalNo = getActiveHospitalNo();
            const formData = new FormData();
            formData.append('action', 'polish_note');
            formData.append('note_content', text);
            formData.append('hospital_no', hospitalNo);

            const response = await fetch('/api/llm.php', { method: 'POST', body: formData });
            const data = await response.json();

            if (data.success && data.polished_content) {
                if (target.classList.contains('trumbowyg-editor')) {
                    target.innerHTML = data.polished_content;
                    const textarea = target.parentElement.querySelector('textarea.trumbowyg-textarea');
                    if (textarea) textarea.value = data.polished_content;
                    $(target).trigger('tbwchange');
                    $(target).trigger('input');
                } else if (target.classList.contains('note-editable')) {
                    const originalTextarea = target.parentElement.parentElement.previousElementSibling;
                    if (originalTextarea) $(originalTextarea).summernote('code', data.polished_content);
                } else if (target.value !== undefined) {
                    target.value = data.polished_content;
                } else {
                    target.innerHTML = data.polished_content;
                }
                if (window.toastr) toastr.success('✨ Clinical Note AI-polished successfully!');
            } else {
                throw new Error(data.message || 'Polishing failed');
            }
        } catch (e) {
            console.error('Polish error:', e);
            if (window.toastr) toastr.error('Failed to polish note: ' + e.message);
            else alert('Failed to polish note: ' + e.message);
        } finally {
            this.formatButton.disabled = false;
            this.formatButton.innerHTML = originalHTML;
        }
    }
}


// ─── AI Patient Summary ──────────────────────────────────────────────

class AIPatientSummary {
    constructor(config = {}) {
        this.hospitalNo = config.hospitalNo || '';
        this.voiceEnabled = config.voiceEnabled !== undefined ? config.voiceEnabled : true;
        this.defaultRate = config.voiceRate || 1.0;

        // DOM
        this.overlay = document.getElementById('ai-summary-overlay');
        this.loadingState = document.getElementById('ai-summary-loading');
        this.errorState = document.getElementById('ai-summary-error');
        this.errorText = document.getElementById('ai-summary-error-text');
        this.contentArea = document.getElementById('ai-summary-content');
        this.textContent = document.getElementById('ai-summary-text');
        this.metaInfo = document.getElementById('ai-summary-meta');
        this.btnPlay = document.getElementById('ai-summary-play');
        this.btnStop = document.getElementById('ai-summary-stop');
        this.rateSelect = document.getElementById('ai-summary-rate');
        this.statusText = document.getElementById('ai-summary-voice-status');
        this.btnRegenerate = document.getElementById('ai-summary-regenerate');
        this.btnCopy = document.getElementById('ai-summary-copy');

        this.synth = window.speechSynthesis;
        this.utterance = null;
        this.isPlaying = false;
        this.summaryText = '';
        this.hasLoaded = false;

        if (!this.overlay) return;
        this.init();
    }

    init() {
        document.querySelectorAll('.ai-summary-close').forEach(btn => {
            btn.addEventListener('click', () => this.close());
        });

        if (this.btnRegenerate) {
            this.btnRegenerate.addEventListener('click', () => { this.hasLoaded = false; this.summaryText = ''; this.fetchSummary(); });
        }
        if (this.btnCopy) {
            this.btnCopy.addEventListener('click', () => this.copyToClipboard());
        }

        if (this.voiceEnabled && this.synth) {
            if (this.btnPlay) this.btnPlay.addEventListener('click', () => this.togglePlay());
            if (this.btnStop) this.btnStop.addEventListener('click', () => this.stopAudio());
        }
    }

    openAndLoad() {
        if (!this.overlay) return;
        this.hospitalNo = getActiveHospitalNo() || this.hospitalNo;
        document.body.style.overflow = 'hidden';
        this.overlay.style.display = 'flex';
        if (!this.hasLoaded) this.fetchSummary();
    }

    close() {
        if (this.overlay) this.overlay.style.display = 'none';
        document.body.style.overflow = '';
        this.stopAudio();
    }

    async fetchSummary() {
        this.loadingState.style.display = 'block';
        this.errorState.style.display = 'none';
        this.contentArea.style.display = 'none';

        try {
            const formData = new FormData();
            formData.append('action', 'patient_summary');
            formData.append('hospital_no', this.hospitalNo);

            const response = await fetch('/api/llm.php', { method: 'POST', body: formData });
            const data = await response.json();

            if (!data.success) throw new Error(data.message || 'Failed to generate summary');

            this.summaryText = data.summary_text;
            this.textContent.innerHTML = this.formatMarkdown(this.summaryText);

            if (data.model_used && this.metaInfo) {
                this.metaInfo.innerHTML = '<i class="fa fa-robot"></i> Generated by ' + data.model_used;
            }

            this.hasLoaded = true;
            this.loadingState.style.display = 'none';
            this.contentArea.style.display = 'block';

        } catch (error) {
            this.loadingState.style.display = 'none';
            this.errorState.style.display = 'block';
            if (this.errorText) this.errorText.textContent = error.message;
        }
    }

    formatMarkdown(text) {
        return text
            .replace(/^### (.*$)/gim, '<h5 style="color:#2c3e50;margin-top:12px">$1</h5>')
            .replace(/^## (.*$)/gim, '<h4 style="color:#2c3e50;margin-top:14px">$1</h4>')
            .replace(/^# (.*$)/gim, '<h3 style="color:#2c3e50;margin-top:16px">$1</h3>')
            .replace(/\*\*(.*?)\*\*/gim, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/gim, '<em>$1</em>')
            .replace(/^- (.*$)/gim, '<ul><li>$1</li></ul>')
            .replace(/<\/ul>\n<ul>/gim, '')
            .replace(/\n/gim, '<br>');
    }

    togglePlay() {
        if (!this.synth) return;
        if (this.synth.speaking) {
            this.synth.paused ? this.synth.resume() : this.synth.pause();
        } else {
            this.playAudio();
        }
    }

    playAudio() {
        if (!this.synth || !this.summaryText) return;
        this.synth.cancel();

        const cleanText = this.summaryText.replace(/[*#_]/g, '').replace(/---/g, '').replace(/<[^>]+>/g, '');
        this.utterance = new SpeechSynthesisUtterance(cleanText);
        let rateVal = parseFloat(this.rateSelect ? this.rateSelect.value : this.defaultRate);
        if (isNaN(rateVal) || rateVal < 0.1 || rateVal > 10) rateVal = 1.0;
        this.utterance.rate = rateVal;

        const langSelect = document.getElementById('ai-lang-select');
        const targetLang = langSelect ? langSelect.value : 'en-US';
        this.utterance.lang = targetLang;

        const voices = this.synth.getVoices();
        let pref = voices.find(v => v.lang === targetLang && (v.name.includes('Google') || v.name.includes('Natural')));
        if (!pref) pref = voices.find(v => v.lang.startsWith(targetLang.split('-')[0]));
        if (pref) this.utterance.voice = pref;

        this.utterance.onstart = () => {
            this.isPlaying = true;
            if (this.btnPlay) this.btnPlay.innerHTML = '<i class="fa fa-pause"></i>';
            if (this.statusText) this.statusText.textContent = 'Reading summary...';
        };
        this.utterance.onend = () => {
            this.isPlaying = false;
            if (this.btnPlay) this.btnPlay.innerHTML = '<i class="fa fa-play"></i>';
            if (this.statusText) this.statusText.textContent = 'Finished.';
        };
        this.utterance.onpause = () => {
            this.isPlaying = false;
            if (this.btnPlay) this.btnPlay.innerHTML = '<i class="fa fa-play"></i>';
            if (this.statusText) this.statusText.textContent = 'Paused.';
        };
        this.utterance.onresume = () => {
            this.isPlaying = true;
            if (this.btnPlay) this.btnPlay.innerHTML = '<i class="fa fa-pause"></i>';
            if (this.statusText) this.statusText.textContent = 'Reading summary...';
        };

        this.synth.speak(this.utterance);
    }

    stopAudio() {
        if (!this.synth) return;
        this.synth.cancel();
        this.isPlaying = false;
        if (this.btnPlay) this.btnPlay.innerHTML = '<i class="fa fa-play"></i>';
        if (this.statusText) this.statusText.textContent = 'Ready.';
    }

    copyToClipboard() {
        if (!this.summaryText) return;
        navigator.clipboard.writeText(this.summaryText).then(() => {
            if (this.btnCopy) {
                const orig = this.btnCopy.innerHTML;
                this.btnCopy.innerHTML = '<i class="fa fa-check"></i> Copied!';
                setTimeout(() => { this.btnCopy.innerHTML = orig; }, 2000);
            }
        }).catch(err => console.error('Copy failed:', err));
    }
}

// ─── Auto-Initialize ─────────────────────────────────────────────────
function initAIToolkit() {
    // Move UI elements to body to ensure fixed positioning works relative to viewport
    const toolbar = document.getElementById('ai-toolbar');
    const preview = document.getElementById('ai-dictate-preview');
    const overlay = document.getElementById('ai-summary-overlay');

    if (toolbar && toolbar.parentElement !== document.body) document.body.appendChild(toolbar);
    if (preview && preview.parentElement !== document.body) document.body.appendChild(preview);
    if (overlay && overlay.parentElement !== document.body) document.body.appendChild(overlay);

    if (!window.aiDictationKit) {
        window.aiDictationKit = new AIDictationKit();
    }

    if (!window.aiPatientSummary) {
        const voiceEnabled = overlay ? (overlay.dataset.voiceEnabled !== '0') : true;
        window.aiPatientSummary = new AIPatientSummary({
            hospitalNo: getActiveHospitalNo(),
            voiceEnabled: voiceEnabled,
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAIToolkit);
} else {
    initAIToolkit();
}
