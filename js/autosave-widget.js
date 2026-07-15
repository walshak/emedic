/**
 * Autosave Drafts Widget Javascript
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Config
    const AUTOSAVE_INTERVAL = 10000; // 10 seconds
    
    let autosaveTimer = null;
    let lastSavedContent = '';
    let currentDraftId = '';
    let lastSaveTimestamp = null;
    let currentSaveState = 'idle'; // idle, saving, error
    let lastErrorMessage = '';
    
    const uiToolbar = document.getElementById('autosave-toolbar');
    const uiStatus = document.getElementById('autosave-status-indicator');
    const uiDraftsCount = document.getElementById('autosave-drafts-count');
    const uiOverlay = document.getElementById('autosave-drafts-overlay');
    const uiDraftsList = document.getElementById('autosave-drafts-list');
    const btnViewDrafts = document.getElementById('autosave-view-drafts-btn');
    const btnCloseDrafts = document.getElementById('autosave-close-drafts');
    
    function getActiveEditor() {
        const editors = document.querySelectorAll('#mgt_notes, #mgt_notes_nurse');
        for (let i = 0; i < editors.length; i++) {
            if (editors[i].offsetParent !== null) {
                return editors[i];
            }
        }
        return document.querySelector('#mgt_notes') || null;
    }
    
    // Ensure globals are present (like AI toolkit)
    function getHospitalNo() {
        const editorEl = getActiveEditor();
        if (editorEl && editorEl.dataset.hospital_no) {
            return editorEl.dataset.hospital_no;
        }
        // Try getting from URL
        const urlParams = new URLSearchParams(window.location.search);
        let hospNo = urlParams.get('hosp_no');
        if (hospNo) return hospNo;
        
        return window.currentHospitalNo || window.currentPatientId || '';
    }
    
    function getAppNo() {
        // Try getting app_no from URL or the editor element
        const urlParams = new URLSearchParams(window.location.search);
        let appNo = urlParams.get('app') || urlParams.get('app_no');
        if (!appNo) {
            const editorEl = getActiveEditor();
            if (editorEl && editorEl.dataset.app_no) {
                appNo = editorEl.dataset.app_no;
            }
        }
        return appNo || '';
    }
    
    function getDoctorName() {
        const editorEl = getActiveEditor();
        if (editorEl && editorEl.dataset.doctor) {
            return editorEl.dataset.doctor;
        }
        // Fallback to reading from #doctor_name input if available
        const docInput = document.getElementById('doctor_name');
        return docInput ? docInput.value : 'Doctor';
    }
    
    function getEditorContent() {
        const editorEl = getActiveEditor();
        if (!editorEl) return '';
        // Handle trumbowyg or standard textarea/div
        if (typeof jQuery !== 'undefined' && typeof $(editorEl).trumbowyg === 'function') {
             return $(editorEl).trumbowyg('html');
        }
        return editorEl.value || editorEl.innerHTML || '';
    }
    
    function setEditorContent(content) {
        const editorEl = getActiveEditor();
        if (!editorEl) return;
        if (typeof jQuery !== 'undefined' && typeof $(editorEl).trumbowyg === 'function') {
             $(editorEl).trumbowyg('html', content);
        } else if (editorEl.tagName.toLowerCase() === 'textarea') {
            editorEl.value = content;
        } else {
            editorEl.innerHTML = content;
        }
    }
    
    function updateStatus(text, color) {
        if (uiStatus) {
            let icon = 'fa-check-circle';
            if (text.includes('Saving')) icon = 'fa-spinner fa-spin';
            else if (text.includes('Error') || text.includes('Failed')) icon = 'fa-exclamation-triangle';
            uiStatus.innerHTML = `<i class="fa ${icon}" style="color:${color};"></i> ${text}`;
        }
        // Also update legacy inline indicators if they exist
        const oldStatus = document.getElementById('autosave-status');
        const oldSaving = document.getElementById('autosaving-status');
        
        if (text === "Saving...") {
            if (oldStatus) oldStatus.style.display = 'none';
            if (oldSaving) {
                oldSaving.style.display = 'block';
                oldSaving.innerHTML = "Autosaving...";
                oldSaving.style.color = "orange";
            }
        } else if (text.includes('Error') || text.includes('Failed')) {
            if (oldStatus) oldStatus.style.display = 'none';
            if (oldSaving) {
                oldSaving.style.display = 'block';
                oldSaving.innerHTML = `<i class="fa fa-exclamation-triangle"></i> ${text}`;
                oldSaving.style.color = "red";
            }
        } else {
            if (oldStatus) {
                oldStatus.style.display = 'block';
                oldStatus.innerHTML = `<i class="fa fa-check-circle" style="color:${color};"></i> ${text}`;
                oldStatus.style.color = "gray"; // matches old style
            }
            if (oldSaving) oldSaving.style.display = 'none';
        }
    }
    
    function updateTimeAgo() {
        if (currentSaveState === 'saving') return;
        
        let timeStr = "(Not saved yet)";
        if (lastSaveTimestamp) {
            const diffSecs = Math.floor((Date.now() - lastSaveTimestamp) / 1000);
            timeStr = diffSecs < 60 ? `Last autosave ${diffSecs} sec ago` : `Last autosave ${Math.floor(diffSecs/60)} min ago`;
        }
        
        if (currentSaveState === 'error') {
            updateStatus(`${lastErrorMessage} (${timeStr})`, "#e74c3c");
        } else {
            updateStatus(timeStr, "#2ecc71");
        }
    }
    
    setInterval(updateTimeAgo, 1000);
    
    // API Calls
    function loadDrafts() {
        const hospNo = getHospitalNo();
        const docName = getDoctorName();
        
        if (!hospNo) return;
        
        fetch(`../api/autosave_drafts.php?action=list&hospital_no=${encodeURIComponent(hospNo)}&doctor=${encodeURIComponent(docName)}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    const drafts = data.data;
                    uiDraftsCount.textContent = drafts.length;
                    renderDraftsList(drafts);
                }
            })
            .catch(err => console.error("Error loading drafts:", err));
    }
    
    function renderDraftsList(drafts) {
        if (drafts.length === 0) {
            uiDraftsList.innerHTML = '<div style="text-align:center; padding: 20px; font-size: 12px; color: #888;">No saved drafts found.</div>';
            return;
        }
        
        uiDraftsList.innerHTML = drafts.map(draft => `
            <div class="draft-item" data-id="${draft.id}">
                <h6>${draft.title || 'Draft'}</h6>
                <div class="draft-meta">Saved: ${draft.saved_at} ${draft.app_no ? `(Appt: ${draft.app_no})` : ''}</div>
                <div class="draft-actions">
                    <button class="btn btn-primary btn-sm restore-draft-btn" data-id="${draft.id}">Restore</button>
                    <button class="btn btn-danger btn-sm delete-draft-btn" data-id="${draft.id}">Discard</button>
                </div>
            </div>
        `).join('');
        
        // Attach events
        document.querySelectorAll('.restore-draft-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const draftId = this.dataset.id;
                restoreDraft(draftId);
            });
        });
        
        document.querySelectorAll('.delete-draft-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const draftId = this.dataset.id;
                deleteDraft(draftId);
            });
        });
    }
    
    function isEmptyContent(html) {
        if (!html || !html.trim()) return true;
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;
        const text = tempDiv.textContent || tempDiv.innerText || '';
        // If text is empty and there are no media elements, it's truly empty
        if (text.trim() === '' && tempDiv.querySelector('img, video, audio, iframe') === null) {
            return true;
        }
        return false;
    }
    
    function saveDraft() {
        const content = getEditorContent();
        // Don't save if empty or unchanged
        if (isEmptyContent(content) || content === lastSavedContent) return;
        
        const hospNo = getHospitalNo();
        if (!hospNo) return;
        
        updateStatus("Saving...", "#f39c12");
        currentSaveState = 'saving';
        
        const formData = new URLSearchParams();
        formData.append('action', 'save');
        formData.append('hospital_no', hospNo);
        formData.append('app_no', getAppNo());
        formData.append('doctor', getDoctorName());
        formData.append('content', content);
        if (currentDraftId) {
            formData.append('draft_id', currentDraftId);
        }
        
        fetch('../api/autosave_drafts.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                lastSavedContent = content;
                if (data.draft_id) {
                    currentDraftId = data.draft_id;
                }
                lastSaveTimestamp = Date.now();
                currentSaveState = 'idle';
                updateTimeAgo();
                loadDrafts(); // Refresh list silently
            } else {
                currentSaveState = 'error';
                lastErrorMessage = data.message || "Autosave Failed. Please save manually.";
                updateTimeAgo();
            }
        })
        .catch(err => {
            console.error("Save error:", err);
            currentSaveState = 'error';
            lastErrorMessage = "Autosave Connection Error. Please save manually.";
            updateTimeAgo();
        });
    }
    
    function restoreDraft(draftId) {
        const docName = getDoctorName();
        fetch(`../api/autosave_drafts.php?action=get&id=${draftId}&doctor=${encodeURIComponent(docName)}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' && data.data) {
                    if (confirm("Are you sure you want to restore this draft? Your current unsaved progress will be replaced.")) {
                        setEditorContent(data.data.content);
                        lastSavedContent = data.data.content;
                        // Do not change currentDraftId so the restored draft remains intact
                        // and the current working draft gets updated with this content instead.
                        uiOverlay.style.display = 'none';
                        alert("Draft restored successfully.");
                    }
                }
            })
            .catch(err => console.error("Restore error:", err));
    }
    
    function deleteDraft(draftId) {
        if (!confirm("Are you sure you want to discard this draft?")) return;
        
        const formData = new URLSearchParams();
        formData.append('action', 'delete');
        formData.append('id', draftId);
        
        fetch('../api/autosave_drafts.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                if (currentDraftId == draftId) {
                    currentDraftId = '';
                }
                loadDrafts();
            }
        })
        .catch(err => console.error("Delete error:", err));
    }
    
    // When the user actually hits "Save Documentation" on the main page
    function cleanupDrafts() {
        const hospNo = getHospitalNo();
        if (!hospNo) return;
        
        const formData = new URLSearchParams();
        formData.append('action', 'cleanup');
        formData.append('hospital_no', hospNo);
        formData.append('app_no', getAppNo());
        formData.append('doctor', getDoctorName());
        if (currentDraftId) {
            formData.append('draft_id', currentDraftId);
        }
        
        fetch('../api/autosave_drafts.php', {
            method: 'POST',
            body: formData
        }).then(res => {
            currentDraftId = '';
            lastSavedContent = '';
        });
    }
    
    // UI Events
    btnViewDrafts.addEventListener('click', () => {
        uiOverlay.style.display = uiOverlay.style.display === 'flex' ? 'none' : 'flex';
        if (uiOverlay.style.display === 'flex') {
            loadDrafts();
        }
    });
    
    btnCloseDrafts.addEventListener('click', () => {
        uiOverlay.style.display = 'none';
    });
    
    // Start interval
    autosaveTimer = setInterval(saveDraft, AUTOSAVE_INTERVAL);
    
    // Initial load
    setTimeout(loadDrafts, 1000);
    
    // Expose cleanup method so it can be called by the page's save button
    window.autosaveDraftsCleanup = cleanupDrafts;
    
    // Hook into main save buttons
    const mainSaveBtn = document.getElementById('save-mgt-button');
    if (mainSaveBtn) {
        mainSaveBtn.addEventListener('click', cleanupDrafts);
    }
    const mainSaveBtnPhysio = document.getElementById('save-mgt-button_physio');
    if (mainSaveBtnPhysio) {
        mainSaveBtnPhysio.addEventListener('click', cleanupDrafts);
    }
});
