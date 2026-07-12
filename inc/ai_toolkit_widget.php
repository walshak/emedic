<?php
/**
 * AI Clinical Toolkit Widget - Portable Include
 * 
 * Usage: <?php include('../inc/ai_toolkit_widget.php'); ?>
 * 
 * Requires: window.currentPatientId or window.currentHospitalNo to be set
 * on the page before this widget is included.
 * 
 * This file outputs:
 * 1. The floating dictation toolbar
 * 2. The patient summary overlay
 * 3. Required CSS
 * 4. The ai-toolkit.js script include
 */

// Load LLM config to check if AI is enabled
if (!isset($db)) {
    // If $db is not available in scope, try to include
    if (file_exists(__DIR__ . '/../Connections/Conn.php')) {
        require_once(__DIR__ . '/../Connections/Conn.php');
    }
}

$_ai_enabled = false;
$_ai_voice_enabled = true;
if (isset($db)) {
    try {
        $stmt = $db->query("SELECT llm_config FROM hospital_details LIMIT 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!empty($row['llm_config'])) {
            $_ai_config = json_decode($row['llm_config'], true);
            $_ai_enabled = !empty($_ai_config['enabled']) && !empty($_ai_config['active_provider']);
            $_ai_voice_enabled = isset($_ai_config['summary_voice_enabled']) ? (bool)$_ai_config['summary_voice_enabled'] : true;
        }
    } catch (Exception $e) {
        $_ai_enabled = false;
    }
}

if (!$_ai_enabled) return; // Don't render anything if AI is not configured
?>

<!-- AI Clinical Toolkit Styles -->
<style>
/* ─── Dictation Toolbar ─────────────────────────────────── */
#ai-toolbar {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9990;
    display: flex;
    align-items: center;
    gap: 6px;
    background: rgba(44, 62, 80, 0.95);
    backdrop-filter: blur(12px);
    padding: 8px 14px;
    border-radius: 40px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.3);
    transition: all 0.3s ease;
}
#ai-toolbar:hover {
    box-shadow: 0 12px 40px rgba(0,0,0,0.4);
    transform: translateY(-2px);
}
#ai-toolbar .btn {
    border-radius: 20px;
    font-size: 12px;
    padding: 5px 12px;
    border: none;
    font-weight: 600;
    transition: all 0.2s ease;
}
#ai-toolbar .btn:hover { transform: scale(1.05); }
#ai-toolbar select {
    background: rgba(255,255,255,0.15);
    color: #fff;
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 12px;
    padding: 3px 6px;
    font-size: 11px;
    outline: none;
}
#ai-toolbar select option { background: #2c3e50; color: #fff; }

/* ─── Dictation Preview Bubble ──────────────────────────── */
#ai-dictate-preview {
    display: none;
    position: fixed;
    bottom: 72px;
    right: 20px;
    z-index: 9991;
    background: rgba(41, 128, 185, 0.95);
    backdrop-filter: blur(8px);
    color: #fff;
    padding: 10px 16px;
    border-radius: 16px 16px 4px 16px;
    max-width: 400px;
    font-size: 13px;
    box-shadow: 0 4px 20px rgba(41, 128, 185, 0.4);
    animation: ai-pulse 2s infinite;
}
@keyframes ai-pulse {
    0%, 100% { box-shadow: 0 4px 20px rgba(41, 128, 185, 0.4); }
    50% { box-shadow: 0 4px 30px rgba(41, 128, 185, 0.7); }
}

/* ─── Summary Overlay ───────────────────────────────────── */
#ai-summary-overlay {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    z-index: 10000;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(6px);
    justify-content: center;
    align-items: center;
    animation: ai-fadeIn 0.3s ease;
}
@keyframes ai-fadeIn { from { opacity: 0; } to { opacity: 1; } }

#ai-summary-panel {
    background: #fff;
    width: 90%;
    max-width: 720px;
    max-height: 85vh;
    border-radius: 16px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.4);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: ai-slideUp 0.4s ease;
}
@keyframes ai-slideUp { from { transform: translateY(40px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

#ai-summary-panel .summary-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    padding: 16px 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
#ai-summary-panel .summary-header h4 {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
}
#ai-summary-panel .summary-header .close-btn {
    background: rgba(255,255,255,0.2);
    border: none;
    color: #fff;
    width: 32px; height: 32px;
    border-radius: 50%;
    font-size: 18px;
    cursor: pointer;
    transition: background 0.2s;
}
#ai-summary-panel .summary-header .close-btn:hover { background: rgba(255,255,255,0.35); }

#ai-summary-panel .summary-body {
    flex: 1;
    overflow-y: auto;
    padding: 20px 24px;
    font-size: 14px;
    line-height: 1.7;
    color: #333;
}

#ai-summary-panel .summary-footer {
    border-top: 1px solid #eee;
    padding: 12px 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    background: #f8f9fa;
}
#ai-summary-panel .summary-footer .btn {
    border-radius: 20px;
    font-size: 12px;
    padding: 5px 14px;
    font-weight: 600;
}
#ai-summary-panel .summary-footer .voice-controls {
    display: flex;
    align-items: center;
    gap: 8px;
}
#ai-summary-panel .summary-footer select {
    border-radius: 12px;
    padding: 3px 6px;
    font-size: 11px;
    border: 1px solid #ddd;
}
#ai-summary-meta {
    font-size: 11px;
    color: #888;
    width: 100%;
    text-align: center;
    margin-top: 4px;
}

/* Loading State */
.ai-loading-spinner {
    text-align: center;
    padding: 60px 20px;
}
.ai-loading-spinner .spinner-icon {
    font-size: 48px;
    color: #667eea;
    animation: ai-spin 1.2s linear infinite;
}
@keyframes ai-spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
.ai-loading-spinner p {
    margin-top: 16px;
    color: #666;
    font-size: 14px;
}

/* Error State */
.ai-error-state {
    text-align: center;
    padding: 40px 20px;
    color: #c0392b;
}
</style>

<!-- ─── Dictation Toolbar ─────────────────────────────────── -->
<div id="ai-toolbar">
    <button id="ai-dictate-btn" class="btn btn-info btn-sm" title="Voice Dictation">
        <i class="fa fa-microphone"></i> Dictate
    </button>
    <button id="ai-polish-btn" class="btn btn-success btn-sm" title="AI Polish Note">
        <i class="fa fa-magic"></i> Polish
    </button>
    <button id="ai-summary-btn" class="btn btn-primary btn-sm" title="AI Patient Summary">
        <i class="fa fa-user-md"></i> Summary
    </button>
    <select id="ai-lang-select" title="Dictation Language">
        <option value="en-US">English (US)</option>
        <option value="en-GB">English (UK)</option>
        <option value="fr-FR">French</option>
        <option value="es-ES">Spanish</option>
        <option value="ar-SA">Arabic</option>
        <option value="ha-NG">Hausa</option>
        <option value="yo-NG">Yoruba</option>
        <option value="ig-NG">Igbo</option>
    </select>
</div>

<!-- ─── Dictation Preview Bubble ──────────────────────────── -->
<div id="ai-dictate-preview">
    <span id="ai-dictate-preview-text">Listening...</span>
    <button id="ai-dictate-stop" style="background:none;border:none;color:#fff;margin-left:8px;cursor:pointer;font-size:14px" title="Stop"><i class="fa fa-times"></i></button>
</div>

<!-- ─── Patient Summary Overlay ───────────────────────────── -->
<div id="ai-summary-overlay" data-voice-enabled="<?= $_ai_voice_enabled ? '1' : '0' ?>">
    <div id="ai-summary-panel">
        <div class="summary-header">
            <h4><i class="fa fa-robot"></i> AI Patient Summary</h4>
            <button class="close-btn ai-summary-close" title="Close">&times;</button>
        </div>
        <div class="summary-body">
            <!-- Loading -->
            <div id="ai-summary-loading" class="ai-loading-spinner">
                <div class="spinner-icon"><i class="fa fa-spinner fa-spin"></i></div>
                <p>Generating clinical summary...</p>
                <small style="color:#999">This may take a few seconds depending on your LLM provider.</small>
            </div>
            <!-- Error -->
            <div id="ai-summary-error" class="ai-error-state" style="display:none">
                <i class="fa fa-exclamation-triangle" style="font-size:36px"></i>
                <p id="ai-summary-error-text">An error occurred.</p>
                <button class="btn btn-default btn-sm ai-summary-close">Close</button>
            </div>
            <!-- Content -->
            <div id="ai-summary-content" style="display:none">
                <div id="ai-summary-text"></div>
            </div>
        </div>
        <div class="summary-footer">
            <div class="voice-controls">
                <?php if ($_ai_voice_enabled): ?>
                <button id="ai-summary-play" class="btn btn-default btn-sm" title="Play / Pause"><i class="fa fa-play"></i></button>
                <button id="ai-summary-stop" class="btn btn-default btn-sm" title="Stop"><i class="fa fa-stop"></i></button>
                <select id="ai-summary-rate" title="Speech Rate">
                    <option value="0.7">0.7x</option>
                    <option value="0.8">0.8x</option>
                    <option value="1" selected>1x</option>
                    <option value="1.2">1.2x</option>
                    <option value="1.5">1.5x</option>
                    <option value="2">2x</option>
                </select>
                <span id="ai-summary-voice-status" style="font-size:11px;color:#888">Ready.</span>
                <?php endif; ?>
            </div>
            <div>
                <button id="ai-summary-regenerate" class="btn btn-warning btn-sm"><i class="fa fa-refresh"></i> Regenerate</button>
                <button id="ai-summary-copy" class="btn btn-default btn-sm"><i class="fa fa-clipboard"></i> Copy</button>
            </div>
            <div id="ai-summary-meta"></div>
        </div>
    </div>
</div>

<!-- ─── Script ────────────────────────────────────────────── -->
<script src="/js/ai-toolkit.js"></script>
