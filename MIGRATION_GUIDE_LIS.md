# LIS Integration Migration & Deployment Guide

## Executive Overview
This guide provides complete documentation for the External Laboratory Information System (LIS) integration built into the eMedic EMR platform. The integration supports automated bi-directional communication with External LIS systems (e.g. ClinOS LIS), enabling automated order dispatch, specimen tube barcode label printing, non-blocking result synchronization, item & order-level notes extraction, and notification management.

---

## 1. Database Schema Migrations

Two SQL migration files must be executed against the eMedic database:

### Migration File 1: `migration_2026_09_09_lis_integration.sql`
Creates the core LIS infrastructure tables:
- **`lis_config`**: Stores global enablement toggle, provider driver name, base API URL, EMR API key, Catalogue API key, sync modes, and poll pagination cursors (`last_polled_after_id`).
- **`lis_test_mappings`**: Mapping table connecting local EMR test IDs (`lab_scan.sn`) / test names to External LIS canonical codes.
- **`lis_orders`**: Tracks order status (`pending`, `sent`, `specimen_received`, `validated`, `failed`), ClinOS Order IDs, label URLs, and error logs.
- **`lis_event_logs`**: Idempotency audit log storing raw JSON payloads received during polling to prevent duplicate processing.

### Migration File 2: `migration_2026_09_10_lis_notifications_seen.sql`
Creates user-level notification tracking:
- **`lis_notifications_seen`**: Stores read/seen receipts for LIS result alerts per user and per lab request (`username`, `labrequest_no`, `event_type`).

### SQL Execution Script
To apply the migrations, execute the following shell commands or import via phpMyAdmin/MySQL Workbench:
```bash
mysql -u [db_user] -p[db_pass] [db_name] < migration_2026_09_09_lis_integration.sql
mysql -u [db_user] -p[db_pass] [db_name] < migration_2026_09_10_lis_notifications_seen.sql
```

---

## 2. Configuration & Admin Setup

1. Log into eMedic as an Administrator and navigate to **Admin -> LIS Settings** (`admin/lis_settings.php` or `admin/index.php?lis_settings`).
2. Configure the connection settings:
   - **Enable LIS Integration**: Check `Enable LIS Integration` (`is_enabled = 1`).
   - **Provider Driver**: Select `ClinOS LIS Driver` (`clinos`).
   - **Base URL**: Set API endpoint URL (e.g., `https://zmkc-livs.shares.zrok.io`).
   - **EMR API Key**: Input the secure EMR authentication key.
   - **Catalogue API Key**: Input the catalogue retrieval API key.
3. Click **"Save LIS Configuration"**.
4. Test the API connection by clicking **"Test Connection"**.
5. Use the **Test Mapping Matrix** interface on the page to map local EMR tests (`lab_scan`) to ClinOS LIS canonical codes (e.g., `LBS1000001000000260`).

---

## 3. List of Modified & Added Files

### Added Files (New LIS Architecture)
- [`inc/lis/LisDriverInterface.php`](file:///home/mrapollos/Documents/work/emedic/inc/lis/LisDriverInterface.php) — Interface defining LIS driver methods.
- [`inc/lis/ClinOsDriver.php`](file:///home/mrapollos/Documents/work/emedic/inc/lis/ClinOsDriver.php) — ClinOS LIS API cURL driver implementation (with 10s connect timeout, 30s total timeout).
- [`inc/lis/LisDriverFactory.php`](file:///home/mrapollos/Documents/work/emedic/inc/lis/LisDriverFactory.php) — Factory for instantiating drivers and caching global LIS enablement configuration.
- [`inc/lis/LisService.php`](file:///home/mrapollos/Documents/work/emedic/inc/lis/LisService.php) — Service layer for order dispatches, result ingestion, notes sync, and notification tracking.
- [`admin/lis_settings.php`](file:///home/mrapollos/Documents/work/emedic/admin/lis_settings.php) — Administrative configuration UI and live mapping editor.
- [`investigations/lis_poll_sync.php`](file:///home/mrapollos/Documents/work/emedic/investigations/lis_poll_sync.php) — Non-blocking application-level auto-sync & auto-dispatch endpoint.
- [`investigations/lis_action.php`](file:///home/mrapollos/Documents/work/emedic/investigations/lis_action.php) — Endpoint for manual dispatch, retry, and manual sync triggers.
- [`investigations/mark_lis_seen.php`](file:///home/mrapollos/Documents/work/emedic/investigations/mark_lis_seen.php) — Endpoint for marking LIS result notifications as seen for the logged-in user.
- [`investigations/lis_label_print.php`](file:///home/mrapollos/Documents/work/emedic/investigations/lis_label_print.php) — Specimen tube barcode label printing endpoint.
- [`migration_2026_09_09_lis_integration.sql`](file:///home/mrapollos/Documents/work/emedic/migration_2026_09_09_lis_integration.sql) — Core LIS schema SQL script.
- [`migration_2026_09_10_lis_notifications_seen.sql`](file:///home/mrapollos/Documents/work/emedic/migration_2026_09_10_lis_notifications_seen.sql) — LIS notification tracking SQL script.

### Modified Files (EMR Integration Hooks & UI Safeguards)
- [`investigations/mgt.php`](file:///home/mrapollos/Documents/work/emedic/investigations/mgt.php) — Added non-blocking auto-sync trigger, notification alerts, sound triggers, dynamic LIS batch button activation handler (`toggle_check()`), and guarded "Sync LIS Results" button.
- [`investigations/enter_result_process.php`](file:///home/mrapollos/Documents/work/emedic/investigations/enter_result_process.php) — Added LIS badges with Order IDs (`LIS Sent (#ORD-...)`), batch dispatch function `sendSelectedToLis()`, multi-specimen tube selection modal (`#lisSpecimenModal`), direct print auto-bypass for single tubes, and "Print All Labels" multi-window trigger.
- [`investigations/fetch_lab_count.php`](file:///home/mrapollos/Documents/work/emedic/investigations/fetch_lab_count.php) — Ultra-fast non-blocking local DB query endpoint returning unseen LIS result counts.
- [`investigations/fetch_lab_list.php`](file:///home/mrapollos/Documents/work/emedic/investigations/fetch_lab_list.php) — Tabbed modal UI (`Pending Queue`, `New LIS Results`, `LIS Order Status`) with "Mark All as Seen" capability.
- [`investigations/insert.php`](file:///home/mrapollos/Documents/work/emedic/investigations/insert.php) — Updated manual lab requisition creation (auto-dispatch removed in favor of manual batch dispatch).
- [`doctor/controllers/_saveInvestigation.php`](file:///home/mrapollos/Documents/work/emedic/doctor/controllers/_saveInvestigation.php) — Prescribes lab investigations without auto-dispatching to LIS.
- [`investigations/javascripts_setup.php`](file:///home/mrapollos/Documents/work/emedic/investigations/javascripts_setup.php) — Global JS functions for LIS sync, dispatch retry, and modal tab handlers.

---

## 4. Manual Batch Dispatch & Multi-Specimen Tube Barcoding Workflow

1. **Manual Batch Dispatch**:
   - Lab scientists check the desired lab investigations in the Investigation Management modal (`mgt.php` / `enter_result_process.php`).
   - The **"Send Selected to LIS"** button dynamically activates (`disabled = false`).
   - Clicking **"Send Selected to LIS"** sends all selected tests as a unified ClinOS order payload, returning a single ClinOS Order ID (e.g. `ORD-20260910-0012`).

2. **Smart Specimen Tube Label Printing**:
   - ClinOS intelligently maps requested tests to physical collection tubes (e.g., `SERUM_PLASMA` for Albumin/LFT and `WHOLE_BLOOD` for Blood Group).
   - Clicking the **"Label"** button on an investigation row checks the specimen count:
     - **Single Tube**: Bypasses the modal and opens the label print window directly.
     - **Multiple Tubes**: Opens the `#lisSpecimenModal` presenting cards for each specimen tube, assigned tests, tube barcodes, and a **"Print All Labels"** action.

---

## 5. Deployment Verification Checklist

- [ ] Execute `migration_2026_09_09_lis_integration.sql` and `migration_2026_09_10_lis_notifications_seen.sql`.
- [ ] Configure API keys and Base URL in Admin LIS Settings (`admin/lis_settings.php`).
- [ ] Map active local lab test items (`lab_scan`) to LIS canonical codes.
- [ ] Toggle `Enable LIS Integration` = `1`.
- [ ] Place lab requests, select items via checkboxes, and verify dynamic activation of **"Send Selected to LIS"**.
- [ ] Dispatch a batch order and verify display of `LIS Sent (#<order_id>)` badges.
- [ ] Click "Label" button on a dispatched order:
  - If single tube: Verify print window opens directly with barcode.
  - If multiple tubes: Verify `#lisSpecimenModal` opens with individual tube cards and "Print All Labels" button.
- [ ] Click "Sync LIS Results" or wait for auto-sync polling to verify result ingestion, item notes extraction, notification audio trigger, and badge updates.
- [ ] Toggle `Enable LIS Integration` = `0` in Admin Settings and verify that all LIS UI buttons and badges cleanly disappear from `mgt.php` and `enter_result_process.php`.
