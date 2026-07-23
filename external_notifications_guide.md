# eMedic External Notifications (Email & SMS) Guide

This guide details the implementation of the new `ExternalNotification` engine, its dependencies, how to configure it in the Admin panel, and the SQL schemas required to run it.

---

## 1. Feature Overview

The external notifications system allows the hospital to dispatch automated emails and SMS text messages to patients. This includes:
1. **Welcome Messages**: Sent automatically when a new patient is registered, or when an existing patient's details are saved with the welcome toggles checked. Contains the patient's new Hospital Number.
2. **Investigation Results**: Automated dispatch of PDF lab/scan reports directly to the patient's email. Includes optional password protection (Secure PDF) and pushes data to InstantResult.ng.

### Core Architecture
- **Email Delivery**: Uses PHPMailer for robust SMTP delivery. If SMTP fails or isn't configured, it gracefully falls back to the native PHP `mail()` function.
- **SMS Delivery**: Uses the Twilio Programmable SMS API via raw cURL requests, removing the need for heavy external SDK dependencies (like Composer).
- **PDF Generation**: Uses `DomPDF` coupled with `FPDI` to generate branded, beautifully formatted, and password-protected PDF attachments.

---

## 2. Configuration & Setup

All configuration for external notifications is securely managed through the **Hospital Details** module in the Admin Panel (`admin/index.php` -> Settings Modal). 

### Required Database Schema (SQL Migration)
To store these configurations dynamically, the `hospital_details` table requires the following columns. Run this SQL query to ensure your database is up to date:

```sql
ALTER TABLE `hospital_details` 
ADD `twilio_sid` VARCHAR(255) NULL DEFAULT NULL,
ADD `twilio_auth_token` VARCHAR(255) NULL DEFAULT NULL,
ADD `twilio_phone_number` VARCHAR(50) NULL DEFAULT NULL,
ADD `welcome_email_template` TEXT NULL DEFAULT NULL,
ADD `welcome_sms_template` TEXT NULL DEFAULT NULL;

-- Ensure the legacy SMTP columns exist
ALTER TABLE `hospital_details`
ADD `smtp_host` VARCHAR(255) NULL DEFAULT NULL,
ADD `smtp_username` VARCHAR(255) NULL DEFAULT NULL,
ADD `smtp_password` VARCHAR(255) NULL DEFAULT NULL,
ADD `smtp_port` INT NULL DEFAULT 587,
ADD `smtp_encryption` VARCHAR(50) NULL DEFAULT 'tls';
```

### Setting up Custom Sender IDs (Twilio)
You can use a standard Twilio phone number (e.g., `+1234567890`) OR a custom **Alphanumeric Sender ID** (e.g., `MLUTH ABUJA`).
- Go to **Admin > Settings > Hospital Details**.
- In the "Twilio Phone Number" field, type your desired 11-character Alphanumeric ID (e.g., `MLUTH ABUJA`).
- Ensure Alphanumeric Sender IDs are toggled to "Enabled" in your Twilio Account Settings.

---

## 3. UI/UX Interactivity in Registration

The patient registration form (`admin/index.php` and `admin/fetch_set.php`) has been upgraded with intelligent UI toggles for these notifications:

1. **Separated Toggles**: "Send Welcome SMS" and "Send Welcome Email" are distinct checkboxes.
2. **Conditional Validation**: 
   - Checking "Send Welcome SMS" automatically reveals and makes the `Phone No` field **required**.
   - Checking "Send Welcome Email" reveals and makes the `Email Address` field **required**.
3. **Graceful Error Handling**: 
   - When the user clicks "Save", the button displays a spinning loader (`<i class="fa fa-spinner fa-spin"></i> Saving...`) and disables itself to prevent duplicate submissions.
   - If the patient saves successfully but the email/Twilio API crashes (e.g., a timeout), the backend catches the error. The registration succeeds, and the frontend displays a yellow warning `toastr` explaining the notification failed, rather than breaking the application flow.

---

## 4. Modified Files Reference

The following files were modified or created for this feature:

### Backend Logic
- **`inc/ExternalNotification.php`** *(NEW)*: The core class that orchestrates `sendWelcome()`, `sendEmail()`, `sendSms()`, `sendInstantResultApi()`, and `generateSecurePDF()`. Automatically embeds inline logos (`cid:hospital_logo`) into emails so they render correctly in Gmail/Outlook.
- **`admin/insert.php`**: Evaluates the separated checkboxes, extracts the email payload, inserts the email into the `enrollee` table, invokes the notification engine, and pipes any exceptions back to the frontend delimited by `|||`.
- **`doctor/helpers.php`**: Fixed a strict-mode database bug where non-medication service requests (like Laboratories) submitted empty strings (`' '`) for dosage units, causing integer mismatch errors.

### Frontend & UI
- **`admin/index.php`**: Added the HTML form fields for the interactive toggles and email input for fresh registrations. Also added the Twilio fields to the Hospital Details modal.
- **`admin/fetch_set.php`**: Mirrored the interactive toggles and Javascript logic for the "Existing Patient" registration flow.
- **`admin/fetch_dash.php`**: Added the database bindings for the Twilio and Template inputs in the settings modal.
- **`admin/admin_js_script.php`**: Upgraded the AJAX form submission for `new_patient_dash_body` to handle the spinner loading state and parse the `|||` delimited error messages returned by `insert.php`.

### Lab/Scan Dispatch
- **`investigations/send-email-instant-result.php`**: Stripped out hundreds of lines of procedural email code and refactored it to simply instantiate and call `$notifier->sendInstantResultApi()`.
- **`investigations/printlab.php` & `investigations/printscan.php`**: Upgraded the generic include paths to properly point to `ExternalNotification.php` and its dependencies.

---

## 5. Default Fallbacks & Branding

If a user enables notifications but forgets to configure the custom templates in the Hospital Details, the engine is designed to intelligently fall back:
- **Default SMS**: "Welcome [PatientName] to [HospitalName]! Your Hospital ID is [PatientID]. We're glad to have you with us."
- **Default Email**: Wraps the text in a highly stylized HTML template. It automatically pulls the hospital logo (`img/logo.png`) and injects it as an embedded image alongside the hospital's address and phone numbers in the header.

This ensures a premium user experience out of the box with zero configuration required.
