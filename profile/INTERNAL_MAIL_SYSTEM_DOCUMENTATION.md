# Internal Mail System Documentation
## WebMedic Internal Messaging System

**Document Version:** 1.0  
**Date:** December 17, 2025  
**Location:** `/profile` directory

---

## Overview

The WebMedic internal mail system is a complete messaging solution that enables users within the system to communicate with each other. This is NOT the SMTP external email system, but an internal messaging platform similar to enterprise messaging systems.

---

## System Architecture

### Core Components

The internal mail system consists of 4 main PHP files:

1. **mailbox.php** - Mail listing and inbox management
2. **mail_compose.php** - Compose, send, and draft messages
3. **mail_detail.php** - View individual messages
4. **mail_icons.php** - Navigation sidebar for mail folders

### Database Structure

**Table Name:** `mails`

**SQL Schema:**
```sql
CREATE TABLE `mails` (
  `sn` int(11) NOT NULL,
  `on_contact_name` varchar(100) NOT NULL,
  `on_contact_username` text DEFAULT NULL,
  `from_name` varchar(100) DEFAULT NULL,
  `from_username` varchar(50) NOT NULL,
  `msg` text NOT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `mail_status` varchar(20) NOT NULL,
  `mail_date` datetime NOT NULL,
  `read_status` int(1) DEFAULT 0,
  `attachment_status` int(1) NOT NULL DEFAULT 0,
  `attachments` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
```

**Field Details:**
- `sn` - INT(11), NOT NULL - Serial number (Primary Key)
- `on_contact_name` - VARCHAR(100), NOT NULL - Recipient's full name
- `on_contact_username` - TEXT, DEFAULT NULL - Recipient's username (or semicolon-separated list for drafts)
- `from_name` - VARCHAR(100), DEFAULT NULL - Sender's full name
- `from_username` - VARCHAR(50), NOT NULL - Sender's username
- `msg` - TEXT, NOT NULL - Message body content
- `subject` - VARCHAR(100), DEFAULT NULL - Mail subject line
- `mail_status` - VARCHAR(20), NOT NULL - Status: 'send', 'draft', or 'trash'
- `mail_date` - DATETIME, NOT NULL - Timestamp of mail creation
- `read_status` - INT(1), DEFAULT 0 - Read indicator: 0 (unread) or 1 (read)
- `attachment_status` - INT(1), NOT NULL DEFAULT 0 - Attachment indicator: 0 (no attachments) or 1 (has attachments)
- `attachments` - TEXT, DEFAULT NULL - Semicolon-separated file paths

---

## Feature Breakdown

### 1. Mailbox (mailbox.php)

#### Purpose
Central hub for viewing all mail folders (Inbox, Sent, Drafts, Trash)

#### Key Features

**Folder Views:**
- **Inbox** - Messages received by the current user
  - Query: `WHERE on_contact_username='$username' AND mail_status='send'`
  
- **Sent** - Messages sent by the current user
  - Query: `WHERE from_username='$username' AND mail_status='send'`
  
- **Drafts** - Unsent messages saved by the current user
  - Query: `WHERE from_username='$username' AND mail_status='draft'`
  
- **Trash** - Deleted messages
  - Query: `WHERE (from_username='$username' OR on_contact_username='$username') AND mail_status='trash'`

**Display Features:**
- Unread mail indicator (highlighted rows)
- Sender information with designation and unit head status
- Attachment indicator icon
- Subject preview (truncated to 50 characters)
- Date/time display using custom date functions
- Checkbox selection for bulk operations (currently UI only)

**UI Elements:**
- Search functionality (form present but needs backend implementation)
- Refresh button
- Pagination arrows (UI present)
- Mail count badges on folders

---

### 2. Mail Compose (mail_compose.php)

#### Purpose
Create, edit, and send internal messages

#### Key Features

**Recipient Selection:**
- Multi-select dropdown using Chosen.js plugin
- Shows all active users (`status='1'`) from `admin_users` table
- Displays: Full name, designation, and unit head status
- Format: `fullname (Designation, Unit Head)`
- Special feature for unit heads: "Select All Users" and "Deselect All Users" buttons

**Message Composition:**
- Rich text editor using Summernote plugin
- Subject line field
- Character limit: 160 characters for message
- Hidden fields for tracking:
  - `compose_type` - 'normal' or 'draft'
  - `draft_sn` - Serial number if editing existing draft
  - `name_sender` - Sender's full name from session
  - `username_sender` - Sender's username from session

**File Attachments:**
- Multiple file upload support
- Maximum file size: 20MB per file
- Upload directory: `../uploads/attachments/`
- Unique filename generation using `uniqid()` prefix
- Security: Filename sanitization (removes special characters)
- Storage format: Semicolon-separated paths in database
- Dynamic add/remove attachment rows with JavaScript

**Actions:**

1. **Send Mail:**
   - Validates recipient selection and message content
   - Prevents duplicate sends (checks existing on_contact_name + msg)
   - Sends to multiple recipients (loops through selected users)
   - Triggers notification via `global_notify_()` function
   - Notification message: "New mail From [sender] with subject: [subject]"
   - Redirects to: `mail_compose.php?send` on success
   - Auto-deletes draft if converting draft to sent message

2. **Save Draft:**
   - Saves message with `mail_status='draft'`
   - Stores recipient list as semicolon-separated values in `on_contact_username`
   - Sets `on_contact_name` to "Unknown Draft @ [timestamp]"
   - Deletes previous draft if editing existing one

**Special Modes:**

1. **Reply Mode** (`?reply=[message_id]`)
   - Pre-fills recipient with original sender
   - Adds "RE:" prefix to subject
   - Includes original message with separator line

2. **Forward Mode** (`?fwd=[message_id]`)
   - Pre-fills subject with "FORWARD:" prefix
   - Includes original message content with separator line
   - Recipient field left empty for user selection

3. **Edit Draft Mode** (`?m=[draft_id]`)
   - Loads draft data
   - Pre-selects recipients
   - Populates subject and message
   - Replaces draft on send

**Error Handling:**
- Validates file upload errors
- Checks file size limits
- Displays error messages in alert boxes
- Reports file upload failures individually

---

### 3. Mail Detail (mail_detail.php)

#### Purpose
Display full message content with actions

#### Key Features

**Message Display:**
- Full subject line
- Sender information with designation and unit head status
- Formatted timestamp (e.g., "10:30 AM 15 Dec 2025")
- Message body with preserved line breaks (`nl2br()`)
- Automatic read status update when viewing (`read_status=1`)

**Attachment Handling:**
- Lists all attached files
- File type icons:
  - `fa-file-word-o` - Word documents (.doc, .docx)
  - `fa-file-pdf-o` - PDF files (.pdf)
  - `fa-file-excel-o` - Excel files (.xls, .xlsx)
  - `fa-file-image-o` - Images (.jpg, .jpeg, .png, .gif)
  - `fa-file-o` - Default for other files
- Download links for each attachment
- Attachment count display

**Actions:**
- **Reply** - Opens composer with reply pre-filled
- **Forward** - Opens composer with forward pre-filled
- **Print** - JavaScript function opens print-friendly popup window
- **Delete/Move to Trash** - Updates `mail_status='trash'`

**Print Functionality:**
- Opens new popup window
- Includes Bootstrap CSS styling
- Displays message without action buttons
- Auto-triggers print dialog

**Security:**
- Uses `htmlspecialchars()` for output escaping
- Prevents XSS attacks on user-generated content

---

### 4. Mail Icons (mail_icons.php)

#### Purpose
Sidebar navigation for mail folders

#### Features

**Folder List:**
- Inbox (with unread count badge)
- Sent Mails
- Drafts (with draft count badge)
- Trash

**Badge Counters:**
- `$inbox` - Count of unread inbox messages
  - Query: `WHERE on_contact_username='$username' AND mail_status='send' AND read_status='0'`
  
- `$count_draft` - Count of draft messages
  - Query: `WHERE from_username='$username' AND mail_status='draft'`

**UI Elements:**
- Uses Font Awesome icons
- Label badges for counts (warning for inbox, danger for drafts)
- Folder-style list design

---

## Integration with Other Systems

### 1. Global Notification System

**Function:** `global_notify_($db, $type, $pattern, $title, $message, $sender)`

**Location:** `/Connections/global_notify_.php`

**Usage in Mail System:**
```php
global_notify_(
    $db,
    'user',
    $on_contact_username . '%',
    'New mail From ' . $on_contact_name . ' with subject : ' . $_POST['subject'],
    'Proceed to Your Webmedic Mailbox to read the mail',
    $_POST['username_sender']
);
```

**Purpose:**
- Sends system notifications when new mail arrives
- Notifies recipient to check their mailbox
- Type: 'user' notification
- Pattern matching on username

### 2. User Authentication

**Integration Points:**
- Session management via `../inc/session.php`
- Current user: `$_SESSION['username']`
- User full name: `$_SESSION['fullname']`
- Unit head status: `$_SESSION['unit_head']`

### 3. Employee Data

**Tables Joined:**
- `admin_users` - System user accounts and permissions
- `hremp` - HR employee data for designations

**Join Query Pattern:**
```php
SELECT admin_users.*, hremp.Designation 
FROM admin_users 
LEFT JOIN hremp ON hremp.EmployeeCode = admin_users.EmployeeCode
WHERE admin_users.username = :username
```

### 4. Navigation Header

**File:** `nav_header.php`

**Mail Integration:**
- Shows unread mail count in navigation bar
- Displays latest 5 unread messages in dropdown
- Badge indicator for new messages
- Quick preview with:
  - Subject (truncated to 30 chars)
  - Relative time display (e.g., "2 hours ago")
  - Mail icon thumbnail

---

## Security Features

### 1. SQL Injection Prevention
- Uses PDO prepared statements throughout
- Parameter binding with type specification
- Example:
  ```php
  $stmt = $db->prepare("SELECT * FROM mails WHERE sn = :sn");
  $stmt->bindParam(':sn', $sn, PDO::PARAM_STR);
  $stmt->execute();
  ```

### 2. XSS Protection
- `htmlspecialchars()` on all user output
- HTML entity encoding for subject lines, names, and message content

### 3. File Upload Security
- File size validation (20MB limit)
- Upload error checking
- Filename sanitization: `preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName)`
- Unique filename generation to prevent overwrites
- Uploads stored outside web root access with relative paths

### 4. Session Management
- Session validation via `session.php` include
- Username verification on all operations
- Access control based on authenticated user

### 5. Duplicate Prevention
- Checks for duplicate messages before sending
- Query: `WHERE on_contact_name=:on_contact_name AND msg=:msg`
- Prevents accidental double-sends

---

## User Interface Components

### 1. JavaScript Libraries

**jQuery** - v2.1.1
- DOM manipulation
- AJAX (potential future use)

**Bootstrap** - v3.x
- Responsive layout
- Modal components
- Buttons and forms

**Chosen.js**
- Multi-select dropdown enhancement
- Search functionality in recipient selection
- Configuration:
  ```javascript
  $(".chosen-select").chosen({
      allow_single_deselect: true,
      enable_search_threshold: 10,
      no_results_text: 'Oops, nothing found!',
      width: "100%"
  });
  ```

**Summernote**
- WYSIWYG rich text editor
- Formatting options for message body

**iCheck**
- Checkbox styling
- Used for mail selection (UI only currently)

### 2. Custom JavaScript Functions

**select_all_u()** - Select all users in recipient dropdown (unit heads only)
**deselect_all_u()** - Deselect all users
**printDiv()** - Print message content in popup window
**Attachment Management** - Dynamic add/remove file input rows

### 3. CSS Styling

**Framework:** Inspinia Admin Theme
- `.mail-box` - Main mail container
- `.mail-box-header` - Header section with actions
- `.mail-body` - Message content area
- `.mail-text` - Compose text area
- `.mail-attachment` - Attachment display area
- `.unread` / `.read` - Row styling for message status

---

## Workflow Examples

### Sending a New Message

1. User clicks "Compose" or navigates to `mail_compose.php`
2. Selects recipient(s) from dropdown
3. Enters subject and message body
4. (Optional) Adds file attachments
5. Clicks "Send" button
6. System:
   - Validates input (recipient selected, message not empty)
   - Uploads and processes attachments
   - Checks for duplicates
   - Inserts record into `mails` table for each recipient
   - Calls `global_notify_()` for each recipient
   - Redirects to success page

### Reading a Message

1. User sees unread count in navigation or mailbox
2. Clicks on message in inbox
3. System:
   - Retrieves message by serial number (`sn`)
   - Updates `read_status` to '1'
   - Fetches sender information from joined tables
   - Displays message with attachments
4. User can Reply, Forward, Print, or Delete

### Managing Drafts

1. User composes message
2. Clicks "Draft" instead of "Send"
3. System saves with `mail_status='draft'`
4. Draft appears in Drafts folder
5. User can later:
   - Edit draft by clicking on it in Drafts view
   - Complete and send (converts to sent message)
   - Delete draft

### Deleting Messages

1. User clicks delete/trash button
2. System updates `mail_status='trash'`
3. Message moves to Trash folder
4. Message remains in database (soft delete)
5. Visible to both sender and recipient in Trash view

---

## Database Queries Reference

### Key Queries Used

**Get Inbox Messages:**
```sql
SELECT * FROM mails 
WHERE on_contact_username='[username]' 
AND mail_status='send' 
ORDER BY sn DESC
```

**Get Sent Messages:**
```sql
SELECT * FROM mails 
WHERE from_username='[username]' 
AND mail_status='send' 
ORDER BY sn DESC
```

**Get Drafts:**
```sql
SELECT * FROM mails 
WHERE from_username='[username]' 
AND mail_status='draft' 
ORDER BY sn DESC
```

**Get Trash:**
```sql
SELECT * FROM mails 
WHERE (from_username='[username]' OR on_contact_username='[username]') 
AND mail_status='trash' 
ORDER BY sn DESC
```

**Get Unread Count:**
```sql
SELECT COUNT(*) FROM mails 
WHERE on_contact_username='[username]' 
AND mail_status='send' 
AND read_status='0'
```

**Mark as Read:**
```sql
UPDATE mails 
SET read_status=1 
WHERE sn='[message_id]'
```

**Send New Mail:**
```sql
INSERT INTO mails 
(on_contact_name, on_contact_username, from_name, from_username, 
 msg, subject, mail_status, mail_date, attachments, attachment_status) 
VALUES 
(:on_contact_name, :on_contact_username, :from_name, :from_username, 
 :msg, :subject, :mail_status, :mail_date, :attachments, :attachment_status)
```

**Get Active Users for Recipients:**
```sql
SELECT admin_users.*, hremp.Designation 
FROM admin_users 
LEFT JOIN hremp ON hremp.EmployeeCode = admin_users.EmployeeCode
WHERE admin_users.status = '1'
```

---

## Configuration and Settings

### File Upload Configuration

- **Upload Directory:** `../uploads/attachments/`
- **Max File Size:** 20MB (20 * 1024 * 1024 bytes)
- **Directory Permissions:** 0755 (created if not exists)
- **Filename Format:** `{uniqid}_{sanitized_original_name}`
- **Storage in DB:** Semicolon-separated relative paths

### Message Constraints

- **Message Length:** 160 characters (enforced by `maxlength` attribute)
- **Subject:** No hard limit (text field)
- **Recipients:** Multiple selection allowed (no hard limit)

### Session Dependencies

Required session variables:
- `$_SESSION['username']` - Current user's username
- `$_SESSION['fullname']` - Current user's full name
- `$_SESSION['unit_head']` - Unit head status (boolean/int)
- `$_SESSION['dept_name']` - Department name (for display)

---

## Future Enhancement Opportunities

### Current Limitations & Potential Improvements

1. **Search Functionality**
   - UI present but not implemented
   - Could search by sender, subject, or message content

2. **Bulk Operations**
   - Checkboxes present but no bulk actions
   - Could implement: Bulk delete, mark as read, move to trash

3. **Pagination**
   - Navigation arrows present but not functional
   - Large mailboxes could benefit from pagination

4. **Trash Management**
   - No permanent delete option
   - Could add "Empty Trash" functionality
   - Could add auto-cleanup after X days

5. **Read Receipts**
   - Currently only tracks if opened
   - Could add "seen by" timestamps

6. **Message Threading**
   - No conversation grouping
   - Reply/forward creates separate messages
   - Could implement thread views

7. **Attachment Improvements**
   - No file type restrictions currently
   - Could add virus scanning
   - Could implement inline image preview

8. **Message Drafts Auto-save**
   - Manual save only
   - Could add auto-save every X seconds

9. **Message Importance/Priority**
   - No priority flags
   - Could add high/normal/low priority markers

10. **Carbon Copy (CC) / Blind Carbon Copy (BCC)**
    - Only direct recipients currently
    - Could add CC/BCC fields

---

## Troubleshooting Guide

### Common Issues

**Issue:** Attachments not uploading
- Check directory permissions on `/uploads/attachments/`
- Verify PHP `upload_max_filesize` setting
- Check `post_max_size` in php.ini
- Ensure file is under 20MB limit

**Issue:** Users not receiving notifications
- Verify `global_notify_()` function is included
- Check notification table/system is working
- Confirm recipient username is correct

**Issue:** Duplicate messages sent
- System checks for duplicates by name + message
- If message content is identical, only first is sent
- Change message slightly if resending needed

**Issue:** Draft not loading for edit
- Verify draft `sn` parameter in URL
- Check `mail_status='draft'` in database
- Ensure user is the sender (`from_username`)

**Issue:** Can't see sent messages
- Check `mail_status='send'` in database
- Verify `from_username` matches current user
- Ensure message wasn't moved to trash

---

## API Reference (Internal Functions)

### uploadAttachments($db)

**Purpose:** Handle multiple file uploads for mail attachments

**Parameters:**
- `$db` (PDO) - Database connection

**Returns:**
```php
[
    'attachments' => ['path1', 'path2', ...],  // Array of uploaded file paths
    'errors' => ['error1', 'error2', ...]      // Array of error messages
]
```

**Logic:**
1. Creates upload directory if not exists
2. Loops through $_FILES['attachments']
3. Validates each file (errors, size limit)
4. Sanitizes filename
5. Generates unique filename with uniqid()
6. Moves file to destination
7. Returns paths and any errors

---

## File Structure Summary

```
profile/
├── mailbox.php              # Main inbox/listing view
├── mail_compose.php         # Compose/send/draft messages
├── mail_detail.php          # View individual message
├── mail_icons.php           # Folder navigation sidebar
├── nav_header.php           # Top nav with mail notifications
├── nav_side.php             # Side navigation
└── [other profile files]

uploads/
└── attachments/             # Mail attachment storage
    └── {uniqid}_{filename}  # Uploaded files

Connections/
├── Conn.php                 # Database connection
└── global_notify_.php       # Notification system integration

inc/
├── session.php              # Session management
├── header.php               # HTML head includes
├── footer.php               # HTML footer
└── dd.php                   # Date/time helper functions
```

---

## Dependencies

### PHP Extensions Required
- PDO (MySQL)
- FileInfo (for file type detection)
- Standard PHP functions

### JavaScript Libraries
- jQuery 2.1.1
- Bootstrap 3.x
- Chosen.js (multi-select)
- Summernote (WYSIWYG editor)
- iCheck (checkbox styling)
- Pace.js (loading indicator)

### CSS Frameworks
- Bootstrap 3.x
- Font Awesome (icons)
- Inspinia Admin Theme

---

## Database Tables Used

### Primary Tables

**mails** - Main message storage
- All mail records
- Statuses: send, draft, trash
- Read tracking

**admin_users** - User accounts
- User authentication
- Username, fullname
- Status, rights, unit_head flag

**hremp** - HR employee data
- Employee codes
- Designations
- Department information

---

## Conclusion

The WebMedic internal mail system is a fully-featured internal messaging platform designed for secure communication between system users. It provides essential email-like functionality including:

- Multi-user messaging
- File attachments
- Draft management
- Read/unread tracking
- Reply and forward capabilities
- Integration with the notification system
- Print functionality
- Trash/delete management

The system is built with security in mind, using prepared statements, input validation, and output escaping. It integrates seamlessly with the existing user management and HR systems to provide context-rich recipient information.

---

**End of Documentation**
