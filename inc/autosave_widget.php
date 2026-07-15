<!-- Autosave Drafts Widget -->
<style>
/* ─── Drafts Floating Button ─────────────────────────────────── */
#autosave-toolbar {
    position: fixed;
    bottom: 80px; /* Above the AI toolkit toolbar */
    right: 20px;
    z-index: 9980;
    display: flex;
    align-items: center;
    background: rgba(44, 62, 80, 0.95);
    backdrop-filter: blur(12px);
    padding: 6px 12px;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    transition: all 0.3s ease;
}
#autosave-toolbar:hover {
    box-shadow: 0 6px 25px rgba(0,0,0,0.4);
    transform: translateY(-2px);
}
#autosave-toolbar .btn {
    border-radius: 4px;
    font-size: 12px;
    padding: 4px 10px;
    border: none;
    font-weight: 600;
}
#autosave-status-indicator {
    color: #fff;
    font-size: 11px;
    margin-right: 10px;
}

/* ─── Drafts Panel Overlay ───────────────────────────────────── */
#autosave-drafts-overlay {
    display: none;
    position: fixed;
    bottom: 120px;
    right: 20px;
    width: 320px;
    max-height: 400px;
    z-index: 9995;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
    overflow: hidden;
    flex-direction: column;
    animation: autosave-fadeIn 0.2s ease;
}
@keyframes autosave-fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

#autosave-drafts-overlay .drafts-header {
    background: #34495e;
    color: #fff;
    padding: 12px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
#autosave-drafts-overlay .drafts-header h5 {
    margin: 0;
    font-size: 14px;
    font-weight: 600;
}
#autosave-drafts-overlay .drafts-header .close-btn {
    background: none;
    border: none;
    color: #fff;
    font-size: 18px;
    cursor: pointer;
}
#autosave-drafts-overlay .drafts-body {
    flex: 1;
    overflow-y: auto;
    padding: 10px;
    background: #f8f9fa;
}
.draft-item {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 10px;
    margin-bottom: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
.draft-item h6 {
    margin: 0 0 4px 0;
    font-size: 13px;
    color: #2c3e50;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.draft-item .draft-meta {
    font-size: 11px;
    color: #7f8c8d;
    margin-bottom: 8px;
}
.draft-item .draft-actions {
    display: flex;
    gap: 6px;
}
.draft-item .btn {
    font-size: 11px;
    padding: 3px 8px;
}
</style>

<div id="autosave-toolbar">
    <span id="autosave-status-indicator"><i class="fa fa-check-circle" style="color:#2ecc71;"></i> Autosave Active</span>
    <button id="autosave-view-drafts-btn" class="btn btn-warning btn-sm" title="View Drafts">
        <i class="fa fa-folder-open"></i> Drafts (<span id="autosave-drafts-count">0</span>)
    </button>
</div>

<div id="autosave-drafts-overlay">
    <div class="drafts-header">
        <h5><i class="fa fa-file-text-o"></i> Patient Drafts</h5>
        <button class="close-btn" id="autosave-close-drafts">&times;</button>
    </div>
    <div class="drafts-body" id="autosave-drafts-list">
        <!-- Drafts will be injected here -->
        <div style="text-align:center; padding: 20px; font-size: 12px; color: #888;">Loading drafts...</div>
    </div>
</div>

<script>
    // Escape any CSS transforms by moving the fixed elements to the body
    (function() {
        const toolbar = document.getElementById('autosave-toolbar');
        const overlay = document.getElementById('autosave-drafts-overlay');
        if (toolbar && toolbar.parentElement !== document.body) {
            document.body.appendChild(toolbar);
        }
        if (overlay && overlay.parentElement !== document.body) {
            document.body.appendChild(overlay);
        }
    })();
</script>
<script src="../js/autosave-widget.js"></script>
