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
- [`investigations/mgt.php`](file:///home/mrapollos/Documents/work/emedic/investigations/mgt.php) — Added non-blocking auto-sync trigger, notification alerts, sound triggers, and guarded "Sync LIS Results" button.
- [`investigations/enter_result_process.php`](file:///home/mrapollos/Documents/work/emedic/investigations/enter_result_process.php) — Added LIS badges, status indicators, retry dispatch button, barcode label print link, and guarded all LIS UI elements with `$lisEnabled`.
- [`investigations/fetch_lab_count.php`](file:///home/mrapollos/Documents/work/emedic/investigations/fetch_lab_count.php) — Ultra-fast non-blocking local DB query endpoint returning unseen LIS result counts.
- [`investigations/fetch_lab_list.php`](file:///home/mrapollos/Documents/work/emedic/investigations/fetch_lab_list.php) — Tabbed modal UI (`Pending Queue`, `New LIS Results`, `LIS Order Status`) with "Mark All as Seen" capability.
- [`investigations/insert.php`](file:///home/mrapollos/Documents/work/emedic/investigations/insert.php) — Auto-dispatches mapped orders when placed by lab personnel.
- [`doctor/controllers/_saveInvestigation.php`](file:///home/mrapollos/Documents/work/emedic/doctor/controllers/_saveInvestigation.php) — Auto-dispatches mapped orders when placed by doctors.
- [`investigations/javascripts_setup.php`](file:///home/mrapollos/Documents/work/emedic/investigations/javascripts_setup.php) — Global JS functions for LIS sync, dispatch retry, and modal tab handlers.

---

## 4. Performance & Reliability Guarantees

1. **Non-Blocking Page Load**: Database queries for LIS status and notifications are ultra-fast local indexed reads. Network requests to External LIS are performed asynchronously in the background.
2. **cURL Timeout Safeguards**: ClinOS driver configures `CURLOPT_CONNECTTIMEOUT = 10s` and `CURLOPT_TIMEOUT = 30s` to prevent network slowness or outages from hanging web requests.
3. **Graceful Fault Tolerance**: External API errors are caught and recorded in `lis_orders.error_log` without rolling back or bricking core EMR transactions.
4. **Conditional UI Safeguard**: When LIS integration is disabled (`is_enabled = 0`), zero LIS DB queries execute, and all LIS buttons, status badges, barcode label links, and modal tabs are completely hidden from the UI.

---

## 5. Deployment Verification Checklist

- [ ] Execute `migration_2026_09_09_lis_integration.sql` and `migration_2026_09_10_lis_notifications_seen.sql`.
- [ ] Configure API keys and Base URL in Admin LIS Settings (`admin/lis_settings.php`).
- [ ] Map active local lab test items (`lab_scan`) to LIS canonical codes.
- [ ] Toggle `Enable LIS Integration` = `1`.
- [ ] Place a test lab request and verify auto-dispatch or manual retry button.
- [ ] Click "Sync LIS Results" or wait for auto-sync polling to verify result ingestion, item notes extraction, notification audio trigger, and badge updates.
- [ ] Click "Print Barcode Label" on a dispatched order to verify specimen tube label print output.
- [ ] Toggle `Enable LIS Integration` = `0` in Admin Settings and verify that all LIS UI buttons and badges cleanly disappear from `mgt.php` and `enter_result_process.php`.
