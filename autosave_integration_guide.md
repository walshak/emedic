# eMedic Autosave Drafts Integration Guide

This guide details how the Autosave Drafts Widget integrates into the eMedic application, how it functions under the hood, and how to enable these features on any page containing a text editor.

---

## 1. How to Enable the Autosave Widget on Any Page

To use the Autosave widget on any page containing a text editor (like Trumbowyg or a standard `<textarea>`), follow these steps:

### Step 1: Ensure the Editor has the Correct ID
The autosave JavaScript is configured to look for an editor with the ID `#mgt_notes`.
```html
<div name="mgt_notes" id="mgt_notes" class="trumbowygEditor"></div>
```

### Step 2: Provide Patient Context
The autosave widget needs to know which patient the draft belongs to, as well as the current appointment cycle (if any). The widget attempts to find this context in three ways, prioritizing in this order:

1. **HTML Dataset on the Editor:** By reading `data-hospital_no`, `data-app_no`, and `data-doctor` directly from the editor element. This is the most reliable method.
   ```html
   <div id="mgt_notes" 
        data-hospital_no="<?= $_GET['hosp_no'] ?>" 
        data-app_no="<?= $_GET['app'] ?>" 
        data-doctor="<?= $_SESSION['fullname'] ?>">
   </div>
   ```
2. **URL Parameters:** By reading `?hosp_no=XYZ` and `?app=123` from the browser's address bar.
3. **Global Variables:** By falling back to global javascript variables set on the page (e.g., `window.currentHospitalNo`), which are also used by the AI Toolkit.

### Step 3: Include the Widget
Include the portable widget PHP file at the bottom of the page, ideally at the end of the module or container where the editor resides.
```php
<?php include_once('../inc/autosave_widget.php'); ?>
```
*Note: The widget contains a self-executing script that automatically breaks out of any CSS transforms (like animations) by appending itself directly to the `<body>` element. This guarantees it will always float correctly at `bottom: 80px`.*

---

## 2. How the Autosave Feature Works

The Autosave widget runs silently in the background with robust UI feedback and safety checks.

1. The script (`js/autosave-widget.js`) uses a `setInterval` timer that ticks every **10 seconds**.
2. It fetches the current text from the editor, safely stripping any hidden HTML tags injected by WYSIWYG editors.
3. If the content is **truly empty** or **unchanged** since the last successful save, it aborts silently to save network bandwidth.
4. If there are changes, it pushes a `POST` request to `/api/autosave_drafts.php`.
5. The widget actively tracks the exact timestamp of the last successful save, updating the UI every second to display exactly how long it has been (e.g. `Last autosave 15 sec ago`).
6. **Error Handling:** If the network fails, the widget and inline labels flash red with an explicit warning (e.g. `Autosave Connection Error. Please save manually. (Last autosave 45 sec ago)`) and the timer continues to track the time since the last successful backup.

---

## 3. Draft Management & Document Cycles

The widget provides an MS Word-style auto-recovery system with a rolling historical limit:

- **Creating Drafts:** A new draft is created whenever a user types into an editor during a fresh session (i.e. after a page load).
- **Updating Drafts:** The active draft is seamlessly updated every 10 seconds.
- **Restoring Drafts:** When a user clicks "Restore" on an old draft, the text is loaded into the editor, but the historical draft is left perfectly intact as a permanent backup. The user's next save will automatically spawn a new draft session.
- **Rolling 10-Draft Limit:** To keep the database clean, the system enforces a strict rolling limit. Whenever a draft is saved, it automatically deletes any drafts beyond the 10 most recent ones for that specific patient.
- **Draft Cleanup:** When the user formally clicks the "Save Documentation" button, the JS automatically triggers a cleanup function that tells the server to delete the active draft.

---

## 4. API Routes and Endpoints

All backend operations for the widget are securely bound to the specific `doctor` making the request to ensure complete privacy across concurrent sessions.

**Endpoint Route:** `/api/autosave_drafts.php`

| Action Payload | Request Type | Description |
| :--- | :--- | :--- |
| `action=save` | POST | Inserts a new draft or updates an existing draft. Automatically generates a human-readable title. Enforces the 10-draft rolling limit. |
| `action=list` | GET | Fetches all saved drafts belonging to a specific `hospital_no` and `doctor`. |
| `action=get` | GET | Fetches the full HTML content of a specific `draft_id`. Strictly enforces the `doctor` check to guarantee privacy. |
| `action=delete` | POST | Discards a specific draft manually. |
| `action=cleanup` | POST | Deletes drafts tied to a specific `app_no` or active session after the formal note is saved. |

---

## 5. File Modifications for Autosave Compatibility

The following files were modified to transition from the legacy, hard-coded autosave to the new widget-based system:

- **`doctor/_appointment_mgt_2.php` & `nursing/_appointment_mgt_2.php`**
  Removed any legacy autosave logic blocks. Injected the `<?php include_once('../inc/autosave_widget.php'); ?>` include specifically into the consultation tabs. Added the `data-hospital_no`, `data-app_no`, and `data-doctor` attributes to the `#mgt_notes` element. Restored legacy inline `<div id="autosave-status">` feedback elements for better UX.

- **`js/autosave-widget.js` (Created)**
  Contains all frontend logic. Handles empty-content validation, real-time "time ago" UI tracking, and explicit connection error management.

- **`api/autosave_drafts.php` (Created)**
  Handles secure PDO-based CRUD operations for the `autosave` table, strictly locked to the user's active session and enforcing the rolling 10-draft limit.

- **`inc/autosave_widget.php` (Created)**
  Contains the HTML layout and CSS styling for the floating toolbar and the "Drafts" overlay panel.

---

## 6. Database Migrations

To support multiple drafts and allow doctors to easily identify their drafts in the overlay UI, a `title` column was required on the existing `autosave` table. 

Run the following SQL query to ensure the database is prepared for the Autosave Toolkit:

```sql
ALTER TABLE `autosave` 
ADD COLUMN `title` VARCHAR(255) NULL DEFAULT NULL AFTER `doctor`;
```
