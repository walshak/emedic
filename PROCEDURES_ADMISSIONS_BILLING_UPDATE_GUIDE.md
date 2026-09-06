# Procedures, Medical Services, Admissions & Billing Updates Guide

## Overview

This guide details the implementation of feature enhancements and bug fixes for the **eMedic Hospital System**, focusing on:
1. **Procedures & Medical Services**: Role-based access control (Doctor vs. Receptionist), notes/outcome tracking (`prepared_by` vs `consultant_name`), and direct navigation to Patient Account for billing.
2. **Nurse Admission & Discharge Workflows**: Standardized checklist tracking, admission validation, and structured discharge protocols.
3. **Billing System (`billing/pacct.php`)**: Fixes for JavaScript runtime errors, input placement in DOM tables, payment method defaulting, and Bootstrap confirmation modal integration.

---

## 1. Database Migration

Run the migration script to ensure the required database tables and columns exist:
```bash
php migration_2026_09_06_procedures_admissions.php
```

### Table & Column Changes:
- **`admission_checklists`**: Stores admission checklist templates and patient completion records.
- **`discharge_checklists`**: Stores patient discharge records and checklist signatures.
- **`patient_ap_services`**:
  - Added `prepared_by` (tracks user who requested/entered the service).
  - Added `consultant_name` (tracks assigned doctor/consultant).
  - Added `can_edit_outcomes_notes` & `can_handle_billing` permission indicators.

---

## 2. Key Component Changes

### A. Medical Services & Procedures
- **`admin/fetch_patient_services_procedures.php`**:
  - Distinguishes between **Prepared By** (current user) and **Doctor/Consultant** (`consultant_name`).
  - Restricts outcome notes and clinical status updates to authorized clinical roles (`MD`, `DR`, `NS`, `SA`). Receptionists can view status but cannot modify medical notes/outcomes.
- **`admin/_proc_medserv_modal.php`**:
  - Displays **Doctor** and **Requested By** in separate columns.
  - Replaced inline Cash/Account buttons with a single **"Patient Account"** link (`billing/pacct.php?emr=...`), honoring user rights.

### B. Nurse Admission & Discharge
- **`nursing/_admit_patient_modal.php`**:
  - Integrated structured admission checklist items (vitals, consent forms, orientation).
- **`nursing/discharge.php` & `admin/admission_checklists.php`**:
  - Standardized discharge workflow including billing clearance, medication reconciliation, and follow-up notes.

### C. Billing (`billing/pacct.php`)
- **Payment Confirmation Modal (`#paynow_confirm_modal`)**:
  - Clicking **CASH PAY NOW** / **Post Now** opens a Bootstrap modal detailing Patient Name, EMR, Payment Method, Bank/Ref No, Value Date, and Item Count before processing.
  - Clicking **Confirm & Process** executes `execute_paynow_final_pay()`, submitting the POST request via AJAX.
- **DOM & Script Fixes**:
  - Fixed invalid HTML where hidden `<input name="item[]">` tags were placed directly inside `<tr>` instead of `<td>`.
  - Updated item gathering in `paynow_final_pay()` to query both `name="item[]"` and `name="SystemSelected[]"`.
  - Set **Cash Payment** as the default dropdown option on the Payment Confirmation page.
  - Resolved `UpdateCost()` JS null property errors (`#totalcost` and `$inv_count` boundary checks).

---

## 3. Files Included in Update Package

| File Path | Purpose |
| :--- | :--- |
| `admin/_proc_medserv_modal.php` | Modal view for Medical Services & Procedures |
| `admin/admission_checklists.php` | Checklist management API |
| `admin/fetch_patient_services_procedures.php` | Fetch & permission engine for procedures/services |
| `billing/pacct.php` | Patient Account billing & payment processing |
| `inc/_admission_nurse.php` | Nurse admission logic |
| `inc/_patient_post_actions.php` | Patient post actions |
| `migration_2026_09_06_procedures_admissions.php` | Database migration script |
| `nursing/_admit_patient_modal.php` | Nurse admission modal |
| `nursing/_patient_dashboard_links.php` | Dashboard links for nursing |
| `nursing/discharge.php` | Discharge checklist & processing |

---

## 4. How to Deploy

1. Extract the update zip:
   ```bash
   unzip emedic_update_procedures_admissions_billing.zip -d /path/to/emedic/
   ```
2. Execute the database migration:
   ```bash
   php migration_2026_09_06_procedures_admissions.php
   ```
3. Clear browser cache and test the workflow in the browser.
