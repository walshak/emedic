# eMedic Dictation & AI Integration Guide

This guide details how the AI Clinical Toolkit (Speech Dictation, Note Polishing, and Patient Summary) integrates into the eMedic application, how it functions under the hood, and how to enable these features on any page.

---

## 1. How to Enable the AI Toolkit on Any Page

To use the Dictation and AI tools on any page containing a text editor, follow these two simple steps:

### Step 1: Provide Patient Context
The toolkit needs to know which patient is currently active to generate summaries and attach context for note polishing. You must set global JavaScript variables before the toolkit loads:
```html
<!-- AI Clinical Toolkit Widget -->
<script>
    window.currentHospitalNo = '<?= $hospital_no ?>';
    window.currentPatientId = '<?= $hospital_no ?>';
</script>
```

> [!IMPORTANT]  
> **Relevance of this Code Snippet**  
> This snippet is the crucial first step required to make the AI features (like Patient Summary and Note Polishing) work correctly on any page. 
> 
> When a doctor clicks **"Summary"** or **"Polish"** on the floating AI toolbar, the JavaScript file (`js/ai-toolkit.js`) needs to know *which* patient is currently in view. The toolkit runs a function called `getActiveHospitalNo()` which looks specifically for these `window.currentHospitalNo` or `window.currentPatientId` variables to grab the ID.
>
> Once the JavaScript has that ID, it sends it via a `POST` request to the backend (`/api/llm.php`). The backend then queries the database for that specific patient's demographics, age, recent lab results, current medications, past clinical notes, and known allergies. The LLM uses all of this background information to either write a highly accurate Patient Summary or to intelligently expand medical abbreviations when polishing a dictated note. 
> 
> **In short:** Without this tiny script block at the top of the page, the AI widget does not know which patient's file to summarize, and the API fails to retrieve the patient's clinical history!

> [!TIP]
> Alternatively, the script automatically checks for:
> - URL query parameters (`?hosp_no=XYZ` or `?hospital_no=XYZ`)
> - The `data-hospital_no` attribute on an element with `id="mgt_notes"`

### Step 2: Include the Widget
Include the portable widget PHP file at the bottom of your page, ideally just before the closing `</body>` tag or within your footer includes.
```php
<?php include('../inc/ai_toolkit_widget.php'); ?>
```

This single include automatically handles loading the floating toolbar UI, the overlay modals, the custom CSS, and the required JavaScript (`/js/ai-toolkit.js`).

---

## 2. How the Dictation Feature Works

The AI Dictation Kit connects to the native **Web Speech API** (`SpeechRecognition`). 

1. The user clicks inside a text input (Standard `<textarea>`, Trumbowyg editor, or Summernote editor).
2. The user clicks **Dictate** on the floating toolbar.
3. The browser records the speech and transcribes it in real time, handling punctuation commands like "period", "comma", and "new line".
4. The transcription dynamically inserts into the *last focused* text editor based on its type (handling `innerHTML` for Trumbowyg, and Summernote's API if needed).

---

## 3. How Patient Information is Obtained

When generating an **AI Patient Summary** or **Polishing a Note** (which requires patient context for abbreviation expansion), the feature makes an asynchronous call to the backend.

1. The JavaScript extracts the Hospital Number using `getActiveHospitalNo()`.
2. A `POST` request sends data to `/api/llm.php`.
3. The `llm.php` script calls `buildPatientContext($db, $hospital_no)`, which aggregates data from multiple database tables:
   - **Demographics** (Age, Sex, Genotype, Blood Group) from `enrollee`
   - **Allergies** from `c_d_remarks`
   - **Latest Vitals** from `vital_sign`
   - **Recent Notes & Diagnoses** from `notes` and `diagnosis_tracking`
   - **Active Medications** from `patient_ap_services`
   - **Recent Lab Results** from `lab_manage` & `lab_result`

---

## 4. API Routes and Endpoints

All frontend AI actions route through a single endpoint which acts as a bridge to the `LlmGatewayService`.

**Endpoint Route:** `POST /api/llm.php`

| Action Payload | Description |
| :--- | :--- |
| `action=patient_summary` | Generates a markdown patient summary using the `buildPatientContext` and the LLM's system prompt. |
| `action=polish_note` | Sends the user's raw dictated text alongside the patient's context to the LLM to format, fix spelling, and expand clinical abbreviations. |

*Note: The `LlmGatewayService.php` securely handles the cURL requests to upstream providers like OpenAI, Anthropic, or Google Gemini based on the hospital's database configurations.*

---

## 5. File Modifications for AI Compatibility

The following details the exact code changes made to each file to enable the AI toolkit and clean up legacy bugs (files with purely whitespace/formatting changes have been omitted):

### AI Core & Admin Configuration

- **`admin/fetch_dash.php`**
  Added the complete HTML UI form inside the dashboard for configuring LLM settings. This form captures API keys for Gemini/OpenAI/Anthropic, toggles for Dictation/Voice, and custom system prompts.
  *Key code added:*
  ```php
  <label><input type="checkbox" name="llm_config[enabled]" <?= $llm_enabled ?>> Enable AI Features</label>
  <input type="password" name="llm_config[providers][openai][api_key]" class="form-control">
  ```

- **`admin/index.php`**
  Added the PHP backend logic to process the submitted configuration array, convert it to JSON, and execute an `UPDATE` query.
  *Key code added:*
  ```php
  if (isset($_POST['llm_config']) && is_array($_POST['llm_config'])) {
      $llmConfigJson = json_encode($_POST['llm_config']);
      $stmt = $db->prepare("UPDATE hospital_details SET llm_config = :llm_config WHERE sn = 1");
  }
  ```

### Doctor Module

- **`doctor/patient.php`**
  Appended the AI toolkit widget so the floating dictation bar appears on the patient profile. Additionally, added a null-check safeguard for a legacy timer display to prevent JS execution halts.
  *Key code added:*
  ```php
  <?php include_once('../inc/ai_toolkit_widget.php'); ?>
  ```
  ```javascript
  const el = document.getElementById('timerDisplay');
  if (el) el.textContent = display;
  ```

- **`doctor/_patient_consultations_notes.php`**
  Provided the necessary patient context globals required by the AI Javascript to summarize and fetch patient history.
  *Key code added:*
  ```html
  <script>
      window.currentHospitalNo = '<?= $hospital_no ?>';
      window.currentPatientId = '<?= $hospital_no ?>';
  </script>
  ```

- **`doctor/_new__progess_note_modal.php`**
  Provided the necessary patient context globals for progress notes.
  *Key code added:*
  ```html
  <script>
      window.currentHospitalNo = '<?= $hospital_no ?>';
  </script>
  ```

- **`doctor/_medical_hx_scripts.php`**
  Fixed a bug where empty patient alerts were triggering errors or submitting blank records by ensuring `alert_note` has content.
  *Key code changed:*
  ```javascript
  var alert_note = $('#patient-alert-notes').val();
  if (alert_note != '') {
      alert(alert_note);
      // AJAX save...
  }
  ```

### Nursing Module

- **`nursing/patient.php`**
  Appended the AI toolkit widget (`ai_toolkit_widget.php`) to the nursing dashboard. Added safe null-checks for `appointment_number`, `data_displayed_ward_round`, and `patient-alert-tbody` before updating them to prevent console errors from blocking the AI scripts.
  *Key code changed:*
  ```javascript
  var appt_el = document.getElementById('appointment_number');
  var appointment_number = appt_el ? appt_el.value : '';
  
  var el = document.getElementById("patient-alert-tbody");
  if (el) el.innerHTML = data;
  ```

### Globals, Assets & Includes

- **`inc/header.php`**
  Cleaned up unused CSS stylesheet links to improve page load speed and remove missing asset (404) console errors that could interfere with script execution.
  *Key code changed:*
  ```html
  <!-- <link href="../css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css" rel="stylesheet"> -->
  <!-- <link href="../css/plugins/clockpicker/clockpicker.css" rel="stylesheet"> -->
  <!-- <link href="../css/plugins/daterangepicker/daterangepicker-bs3.css" rel="stylesheet"> -->
  <!-- <link href="css/plugins/dataTables/datatables.min.css" rel="stylesheet"> -->
  ```

- **`inc/footer_scripts.php` & `accounts/inc/footer_scripts.php`**
  Fixed an invalid file path for the typeahead library plugin.
  *Key code changed:*
  ```html
  <!-- Changed from typeahead/typeahead.jquery.min.js -->
  <script src="../js/typeahead.jquery.min.js"></script>
  ```

- **`inc/_special_scripts.js`**
  Fixed the exact same empty patient alert submission bug as the doctor module, adding the `if (alert_note != '')` safeguard.

### Ward Management & Vitals

- **`inc/_ward_round_vitals.php`, `inc/_ward_round_vitals_edit.php`, `inc/_ward_round_vitals - Copy.php`**
  Fixed a broken script tag path that prevented charts from loading and caused console errors.
  *Key code changed:*
  ```html
  <!-- Changed from src="chart.js" -->
  <script src="../doctor/chart.js"></script>
  ```

---

## 6. Database Migrations

To support the dynamic storage of LLM Provider configurations (like API keys, selected models, and system prompts) directly from the admin panel, a new column is required in the `hospital_details` table. 

Run the following SQL query to ensure the database is prepared for the AI Toolkit:

```sql
ALTER TABLE `hospital_details` 
ADD `llm_config` TEXT NULL DEFAULT NULL;
```
