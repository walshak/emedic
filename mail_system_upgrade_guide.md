# Internal Mail System Upgrade

This guide outlines the recent enhancements to the corehealth internal messaging system, which evolved it from a flat, single-message chronological list into a modern, threaded enterprise email client.

## Key Enhancements

### 1. Conversational Threading
- **Consolidation**: Legacy individual messages are now smartly grouped by conversational logic using the `mail_threads` table as the parent tracker.
- **Visuals**: The `mailbox.php` UI now bundles messages, displaying a "message count" badge and unread highlighting per conversation. Clicking a thread opens the full, scrollable history in `mail_thread_view.php`.

### 2. Advanced Composition (CC/BCC)
- **Dynamic Recipients**: Integrated the `Chosen.js` library into `mail_compose.php` to enable multi-select for the standard "To" field.
- **CC/BCC Modules**: You can seamlessly swap between adding Carbon Copies (CC) and Blind Carbon Copies (BCC). The system restricts visibility of BCC recipients in the global threads, ensuring privacy.
- **Smart Replies**:
  - `Reply`: Scopes the recipient exclusively to the sender.
  - `Reply All`: Persists the sender and all visible CC'd members of the thread to your draft.

### 3. Deletion and Trash Logic
The deletion architecture operates safely without destroying relational data for other participants:
- When a user deletes a thread, the backend updates the `deleted_status` exclusively for their row in `mail_recipients`.
- Trashed threads exist in the Trash view, where they can be restored back to the Inbox.
- The system employs a **30-Day Auto-Clear** mechanism; the Trash interface naturally hides messages where `deleted_date` is >30 days. No manual data purges are required.

## Database Architecture and Migration

The upgrade involved a database schema expansion and a smart-parsing data migration script.

### New Tables
- **`mail_threads`**: Tracks the master conversation (subject, activity dates, message counts).
- **`mail_recipients`**: Normalizes recipients, extracting them from the old flat string logic. Maps each user's read/deleted status independently against a specific thread.

### Migration Script: `profile/migrate_mail_threads_properly.php`
If you deploy this feature to a fresh instance with legacy data, **you MUST run this script once**.

**How to run:**
```bash
cd profile
php migrate_mail_threads_properly.php
```

**What it does:**
1. It loops through the `mails` table and extracts every message.
2. It strips common reply indicators (`RE:`, `FWD:`) from the subject lines.
3. It creates unique "Conversation Hashes" based on the normalized subject line + the unique list of participants.
4. It creates the master `mail_threads` entries and repopulates the new `mail_recipients` table accordingly.
5. All legacy mail is fully preserved and integrated into the new UX.

## Replaced Files
- `profile/mail_detail.php`: This file was the legacy single-message viewer. It has been replaced with a hard redirect to `profile/mail_thread_view.php` to ensure zero dead-links if users had messages bookmarked.
