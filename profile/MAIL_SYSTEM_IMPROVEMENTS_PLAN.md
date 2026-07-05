# Mail System Improvements - Implementation Plan

**Date:** December 17, 2025  
**Status:** Planning Phase

---

## Overview of Improvements

### 1. Fix Trash Icon (Quick Win)
**Current Issue:** Trash button in mailbox toolbar doesn't respond  
**Solution:** Implement JavaScript handlers for bulk and individual trash operations

### 2. Add CC/BCC Functionality
**Current Behavior:** Multi-recipient works like BCC (hidden from each other)  
**New Behavior:** Clear CC (visible) and BCC (hidden) options with intuitive UX

### 3. Implement Gmail-Style Threaded Conversations
**Current Behavior:** Simple append-to-end replies  
**New Behavior:** Full conversation threading with relationship tracking and selective reply capabilities

---

## Database Schema Changes

### New Tables

#### 1. `mail_threads` Table
```sql
CREATE TABLE `mail_threads` (
  `thread_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `original_mail_id` INT(11) NOT NULL,
  `subject` VARCHAR(200) DEFAULT NULL,
  `created_by` VARCHAR(50) NOT NULL,
  `created_date` DATETIME NOT NULL,
  `last_activity` DATETIME NOT NULL,
  `message_count` INT(11) DEFAULT 1,
  INDEX idx_created_by (created_by),
  INDEX idx_last_activity (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
```

**Purpose:** Groups related messages into conversation threads

**Fields:**
- `thread_id` - Unique identifier for the conversation
- `original_mail_id` - Reference to the first message that started the thread
- `subject` - Original subject (preserved through replies)
- `created_by` - Username of person who started the thread
- `created_date` - When thread was created
- `last_activity` - Updated with each new message
- `message_count` - Running count of messages in thread

#### 2. `mail_recipients` Table
```sql
CREATE TABLE `mail_recipients` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `mail_id` INT(11) NOT NULL,
  `thread_id` INT(11) NOT NULL,
  `recipient_username` VARCHAR(50) NOT NULL,
  `recipient_name` VARCHAR(100) NOT NULL,
  `recipient_type` ENUM('to', 'cc', 'bcc') NOT NULL DEFAULT 'to',
  `read_status` INT(1) DEFAULT 0,
  `read_date` DATETIME DEFAULT NULL,
  `deleted_status` INT(1) DEFAULT 0,
  INDEX idx_mail_id (mail_id),
  INDEX idx_thread_id (thread_id),
  INDEX idx_recipient (recipient_username),
  INDEX idx_thread_recipient (thread_id, recipient_username),
  FOREIGN KEY (mail_id) REFERENCES mails(sn) ON DELETE CASCADE,
  FOREIGN KEY (thread_id) REFERENCES mail_threads(thread_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
```

**Purpose:** Tracks all recipients for each message with their visibility type

**Fields:**
- `id` - Unique record identifier
- `mail_id` - Reference to specific message in `mails` table
- `thread_id` - Thread this recipient is part of
- `recipient_username` - Username of recipient
- `recipient_name` - Full name of recipient
- `recipient_type` - 'to' (direct), 'cc' (carbon copy), 'bcc' (blind carbon copy)
- `read_status` - 0 (unread) or 1 (read) - per recipient
- `read_date` - When recipient read the message
- `deleted_status` - If recipient deleted from their view

### Modified Tables

#### Update `mails` Table
```sql
ALTER TABLE `mails` 
  ADD COLUMN `thread_id` INT(11) DEFAULT NULL AFTER `sn`,
  ADD COLUMN `parent_mail_id` INT(11) DEFAULT NULL AFTER `thread_id`,
  ADD COLUMN `reply_type` ENUM('new', 'reply', 'reply_all', 'forward') DEFAULT 'new' AFTER `parent_mail_id`,
  ADD INDEX idx_thread_id (thread_id),
  ADD INDEX idx_parent_mail_id (parent_mail_id);
```

**New Fields:**
- `thread_id` - Links message to its conversation thread
- `parent_mail_id` - Links to the message being replied to (NULL for new threads)
- `reply_type` - Tracks how this message was created

**Note:** Keep existing fields for backward compatibility during migration

---

## Migration Strategy

### Phase 1: Add New Structure (Non-Breaking)
1. Create new tables (`mail_threads`, `mail_recipients`)
2. Add new columns to `mails` table
3. All existing queries continue to work

### Phase 2: Dual-Write Period
1. New messages write to both old and new structure
2. Reads prioritize new structure but fall back to old
3. Background job migrates old data

### Phase 3: Full Migration
1. Complete data migration script
2. Update all queries to use new structure
3. Deprecate old fields (don't remove for safety)

### Migration Script Pseudocode
```php
// For each existing mail:
// 1. Create thread_id if doesn't exist
// 2. Populate mail_recipients from on_contact_username (treat as 'bcc' since hidden)
// 3. Update mails.thread_id
// 4. Identify reply chains by subject patterns (RE:, FWD:) and link parent_mail_id
```

---

## Feature 1: Fix Trash Icon

### Current Problem
Line 96 in `mailbox.php`:
```php
<button class="btn btn-white btn-sm" data-toggle="tooltip" data-placement="top" 
        title="Move to trash"><i class="fa fa-trash-o"></i> </button>
```
This button has no click handler and no functionality.

### Solution A: Bulk Trash with Checkboxes
**Implementation:**
```javascript
// Add to mailbox.php JavaScript section
$(document).ready(function() {
    // Bulk trash button handler
    $('.btn-bulk-trash').on('click', function() {
        var selected = [];
        $('.mail-checkbox:checked').each(function() {
            selected.push($(this).data('mail-id'));
        });
        
        if(selected.length === 0) {
            alert('Please select at least one message');
            return;
        }
        
        if(confirm('Move ' + selected.length + ' message(s) to trash?')) {
            window.location.href = 'mailbox.php?bulk_trash=' + selected.join(',');
        }
    });
});
```

**PHP Handler:**
```php
if (isset($_GET['bulk_trash'])) {
    $mail_ids = explode(',', $_GET['bulk_trash']);
    foreach ($mail_ids as $mail_id) {
        $mail_id = intval($mail_id); // Sanitize
        $stmt = $db->prepare("UPDATE mails SET mail_status='trash' WHERE sn=:sn");
        $stmt->execute([':sn' => $mail_id]);
    }
    header("Location: mailbox.php?trash_success=" . count($mail_ids));
    exit;
}
```

### Solution B: Individual Trash Icons Per Row
**Add to each mail row in table:**
```php
<td class="text-right">
    <div class="btn-group">
        <a href="mail_detail.php?m=<?php echo $row['sn']; ?>" 
           class="btn btn-white btn-xs" title="View">
            <i class="fa fa-eye"></i>
        </a>
        <a href="mailbox.php?trash_single=<?php echo $row['sn']; ?>" 
           class="btn btn-white btn-xs" 
           title="Move to trash"
           onclick="return confirm('Move this message to trash?')">
            <i class="fa fa-trash-o"></i>
        </a>
    </div>
</td>
```

### Recommended: Implement Both
- Toolbar button for bulk operations (with checkboxes)
- Individual trash icon in each row for quick single-message trash

---

## Feature 2: CC/BCC Implementation

### UI/UX Design

#### Compose Form Layout (mail_compose.php)

**Visual Design:**
```
┌─────────────────────────────────────────────────────┐
│ To: [Multi-select dropdown........................] │
│     [+ Add Cc]  [+ Add Bcc]                         │
├─────────────────────────────────────────────────────┤
│ Cc: [Multi-select dropdown........................] │  ← Hidden by default
│     All recipients can see each other               │  ← Gray helper text
│     [Remove Cc field]                               │
├─────────────────────────────────────────────────────┤
│ Bcc:[Multi-select dropdown........................] │  ← Hidden by default
│     Recipients are hidden from each other           │  ← Gray helper text
│     [Remove Bcc field]                              │
├─────────────────────────────────────────────────────┤
│ Subject: [.......................................]  │
└─────────────────────────────────────────────────────┘
```

#### HTML Structure
```html
<div class="form-group">
    <label class="col-sm-2 control-label">To: <span class="text-danger">*</span></label>
    <div class="col-sm-10">
        <select name="to_list[]" class="chosen-select" multiple required>
            <!-- User options -->
        </select>
        <div class="m-t-xs">
            <a href="#" id="add-cc-field" class="text-primary">
                <i class="fa fa-plus-circle"></i> Add Cc
            </a>
            &nbsp;&nbsp;
            <a href="#" id="add-bcc-field" class="text-primary">
                <i class="fa fa-plus-circle"></i> Add Bcc
            </a>
        </div>
    </div>
</div>

<div class="form-group" id="cc-field-group" style="display:none;">
    <label class="col-sm-2 control-label">
        Cc: 
        <i class="fa fa-info-circle" data-toggle="tooltip" 
           title="Carbon Copy - All recipients can see each other"></i>
    </label>
    <div class="col-sm-10">
        <select name="cc_list[]" class="chosen-select" multiple>
            <!-- Same user options -->
        </select>
        <small class="text-muted">
            <i class="fa fa-users"></i> Carbon Copy - All recipients can see each other
        </small>
        <div class="m-t-xs">
            <a href="#" id="remove-cc-field" class="text-danger">
                <i class="fa fa-times-circle"></i> Remove Cc field
            </a>
        </div>
    </div>
</div>

<div class="form-group" id="bcc-field-group" style="display:none;">
    <label class="col-sm-2 control-label">
        Bcc: 
        <i class="fa fa-info-circle" data-toggle="tooltip" 
           title="Blind Carbon Copy - Recipients cannot see each other"></i>
    </label>
    <div class="col-sm-10">
        <select name="bcc_list[]" class="chosen-select" multiple>
            <!-- Same user options -->
        </select>
        <small class="text-muted">
            <i class="fa fa-eye-slash"></i> Blind Carbon Copy - Recipients are hidden from each other
        </small>
        <div class="m-t-xs">
            <a href="#" id="remove-bcc-field" class="text-danger">
                <i class="fa fa-times-circle"></i> Remove Bcc field
            </a>
        </div>
    </div>
</div>
```

#### JavaScript for Show/Hide
```javascript
$(document).ready(function() {
    // Show CC field
    $('#add-cc-field').on('click', function(e) {
        e.preventDefault();
        $('#cc-field-group').slideDown();
        $(this).hide();
        // Re-initialize Chosen on the CC select
        $('#cc-field-group .chosen-select').chosen({
            width: "100%",
            placeholder_text_multiple: "Select recipients..."
        });
    });
    
    // Hide CC field
    $('#remove-cc-field').on('click', function(e) {
        e.preventDefault();
        $('#cc-field-group').slideUp();
        $('#cc-field-group select').val('').trigger('chosen:updated');
        $('#add-cc-field').show();
    });
    
    // Show BCC field
    $('#add-bcc-field').on('click', function(e) {
        e.preventDefault();
        $('#bcc-field-group').slideDown();
        $(this).hide();
        // Re-initialize Chosen on the BCC select
        $('#bcc-field-group .chosen-select').chosen({
            width: "100%",
            placeholder_text_multiple: "Select recipients..."
        });
    });
    
    // Hide BCC field
    $('#remove-bcc-field').on('click', function(e) {
        e.preventDefault();
        $('#bcc-field-group').slideUp();
        $('#bcc-field-group select').val('').trigger('chosen:updated');
        $('#add-bcc-field').show();
    });
});
```

### Backend Processing

#### Send Logic Update
```php
if (isset($_POST["send"])) {
    $msg = $_POST['msg'];
    $subject = $_POST['subject'];
    $from_username = $_POST['username_sender'];
    $from_name = $_POST['name_sender'];
    
    // Process attachments (existing code)
    $uploadResult = uploadAttachments($db);
    
    // Collect all recipients by type
    $to_recipients = isset($_POST['to_list']) ? $_POST['to_list'] : [];
    $cc_recipients = isset($_POST['cc_list']) ? $_POST['cc_list'] : [];
    $bcc_recipients = isset($_POST['bcc_list']) ? $_POST['bcc_list'] : [];
    
    // Validation
    if (empty($to_recipients) && empty($cc_recipients) && empty($bcc_recipients)) {
        $errors[] = "Please select at least one recipient";
        // Handle error
    }
    
    $db->beginTransaction();
    
    try {
        // 1. Create thread
        $stmt = $db->prepare("
            INSERT INTO mail_threads (subject, created_by, created_date, last_activity) 
            VALUES (:subject, :created_by, :created_date, :last_activity)
        ");
        $stmt->execute([
            ':subject' => $subject,
            ':created_by' => $from_username,
            ':created_date' => $setdate,
            ':last_activity' => $setdate
        ]);
        $thread_id = $db->lastInsertId();
        
        // 2. Insert main mail record
        $stmt = $db->prepare("
            INSERT INTO mails (thread_id, parent_mail_id, from_name, from_username, 
                               msg, subject, mail_status, mail_date, attachments, 
                               attachment_status, reply_type, on_contact_name, on_contact_username) 
            VALUES (:thread_id, NULL, :from_name, :from_username, :msg, :subject, 
                    'send', :mail_date, :attachments, :attachment_status, 'new', '', '')
        ");
        $stmt->execute([
            ':thread_id' => $thread_id,
            ':from_name' => $from_name,
            ':from_username' => $from_username,
            ':msg' => $msg,
            ':subject' => $subject,
            ':mail_date' => $setdate,
            ':attachments' => $attachmentsStr,
            ':attachment_status' => $attachment_status
        ]);
        $mail_id = $db->lastInsertId();
        
        // 3. Insert recipients
        $stmt_recipient = $db->prepare("
            INSERT INTO mail_recipients (mail_id, thread_id, recipient_username, 
                                        recipient_name, recipient_type) 
            VALUES (:mail_id, :thread_id, :recipient_username, :recipient_name, :recipient_type)
        ");
        
        // Process TO recipients
        foreach ($to_recipients as $recipient) {
            $parts = explode('/', $recipient);
            $stmt_recipient->execute([
                ':mail_id' => $mail_id,
                ':thread_id' => $thread_id,
                ':recipient_username' => $parts[1],
                ':recipient_name' => $parts[0],
                ':recipient_type' => 'to'
            ]);
            
            // Send notification
            global_notify_($db, 'user', $parts[1] . '%', 
                          'New mail: ' . $subject, 
                          'From: ' . $from_name, 
                          $from_username);
        }
        
        // Process CC recipients
        foreach ($cc_recipients as $recipient) {
            $parts = explode('/', $recipient);
            $stmt_recipient->execute([
                ':mail_id' => $mail_id,
                ':thread_id' => $thread_id,
                ':recipient_username' => $parts[1],
                ':recipient_name' => $parts[0],
                ':recipient_type' => 'cc'
            ]);
            
            // Send notification
            global_notify_($db, 'user', $parts[1] . '%', 
                          'New mail (CC): ' . $subject, 
                          'From: ' . $from_name, 
                          $from_username);
        }
        
        // Process BCC recipients
        foreach ($bcc_recipients as $recipient) {
            $parts = explode('/', $recipient);
            $stmt_recipient->execute([
                ':mail_id' => $mail_id,
                ':thread_id' => $thread_id,
                ':recipient_username' => $parts[1],
                ':recipient_name' => $parts[0],
                ':recipient_type' => 'bcc'
            ]);
            
            // Send notification (don't mention BCC in notification)
            global_notify_($db, 'user', $parts[1] . '%', 
                          'New mail: ' . $subject, 
                          'From: ' . $from_name, 
                          $from_username);
        }
        
        $db->commit();
        header("location:mail_compose.php?send");
        
    } catch (Exception $e) {
        $db->rollback();
        $errors[] = "Error sending message: " . $e->getMessage();
    }
}
```

---

## Feature 3: Threaded Conversations

### Thread Display in Mailbox

#### Query Changes
```php
// New query for threaded view
$query = "
    SELECT 
        mt.thread_id,
        mt.subject,
        mt.last_activity,
        mt.message_count,
        m.from_name,
        m.from_username,
        m.msg,
        m.sn as latest_mail_id,
        COUNT(CASE WHEN mr.read_status = 0 AND mr.recipient_username = :username THEN 1 END) as unread_count,
        GROUP_CONCAT(DISTINCT mr.recipient_name SEPARATOR ', ') as all_recipients
    FROM mail_threads mt
    INNER JOIN mails m ON m.sn = (
        SELECT m2.sn 
        FROM mails m2 
        WHERE m2.thread_id = mt.thread_id 
        ORDER BY m2.mail_date DESC 
        LIMIT 1
    )
    INNER JOIN mail_recipients mr ON mr.thread_id = mt.thread_id
    WHERE mr.recipient_username = :username
        AND mr.deleted_status = 0
    GROUP BY mt.thread_id
    ORDER BY mt.last_activity DESC
";
```

#### UI for Thread List
```html
<tr class="<?php echo $unread_count > 0 ? 'unread' : 'read'; ?>">
    <td class="check-mail">
        <input type="checkbox" class="i-checks mail-checkbox" data-thread-id="<?php echo $thread_id; ?>">
    </td>
    <td class="mail-contact">
        <a href="mail_thread_view.php?thread=<?php echo $thread_id; ?>">
            <span class="font-bold"><?php echo htmlspecialchars($from_name); ?></span>
            <?php if ($message_count > 1): ?>
                <span class="label label-info">
                    <i class="fa fa-comments"></i> <?php echo $message_count; ?>
                </span>
            <?php endif; ?>
        </a>
        <br>
        <small class="text-muted">
            To: <?php echo htmlspecialchars(substr($all_recipients, 0, 50)); ?>
            <?php if (strlen($all_recipients) > 50): ?>...<?php endif; ?>
        </small>
    </td>
    <td class="mail-subject">
        <a href="mail_thread_view.php?thread=<?php echo $thread_id; ?>">
            <?php echo htmlspecialchars($subject); ?>
        </a>
        <br>
        <small class="text-muted">
            <?php echo htmlspecialchars(substr(strip_tags($msg), 0, 60)); ?>...
        </small>
    </td>
    <td class="text-center">
        <?php if ($unread_count > 0): ?>
            <span class="label label-warning"><?php echo $unread_count; ?></span>
        <?php endif; ?>
    </td>
    <td class="text-right mail-date">
        <?php echo getTheDay3($last_activity); ?>
    </td>
</tr>
```

### Thread View Page (mail_thread_view.php)

#### Structure
```
┌──────────────────────────────────────────────────────────┐
│ Subject: Project Update - Weekly Report                  │
│ Participants: John Doe, Jane Smith, Mike Johnson (+2)    │
│ ─────────────────────────────────────────────────────────│
│ ┌────────────────────────────────────────────────────┐  │
│ │ From: John Doe (Manager, Unit Head)                │  │
│ │ To: Jane Smith, Mike Johnson                        │  │
│ │ Cc: Sarah Connor                                    │  │
│ │ Date: Dec 15, 2025 10:30 AM                        │  │
│ │                                                     │  │
│ │ Here is the weekly project update...               │  │
│ │                                                     │  │
│ │ [Reply ▼] [Reply All] [Forward]                    │  │
│ └────────────────────────────────────────────────────┘  │
│                                                            │
│ ┌────────────────────────────────────────────────────┐  │
│ │ From: Jane Smith (Developer)                        │  │
│ │ To: John Doe                                        │  │
│ │ Cc: Sarah Connor                                    │  │
│ │ Date: Dec 15, 2025 2:45 PM                         │  │
│ │                                                     │  │
│ │ Thanks for the update. I have a question...        │  │
│ │                                                     │  │
│ │ ┌───────────────────────────────────────────────┐ │  │
│ │ │ On Dec 15, 2025, John Doe wrote:              │ │  │
│ │ │ > Here is the weekly project update...        │ │  │
│ │ └───────────────────────────────────────────────┘ │  │
│ │                                                     │  │
│ │ [Reply ▼] [Reply All] [Forward]                    │  │
│ └────────────────────────────────────────────────────┘  │
│                                                            │
│ [Compose Reply Box with recipient selection]              │
└──────────────────────────────────────────────────────────┘
```

#### PHP Logic for Thread View
```php
<?php
include("../inc/session.php");
include("../Connections/Conn.php");

$thread_id = isset($_GET['thread']) ? intval($_GET['thread']) : 0;
$current_user = $_SESSION['username'];

// Get thread info
$stmt_thread = $db->prepare("
    SELECT mt.*, m.from_name, m.from_username
    FROM mail_threads mt
    INNER JOIN mails m ON m.sn = mt.original_mail_id
    WHERE mt.thread_id = :thread_id
");
$stmt_thread->execute([':thread_id' => $thread_id]);
$thread = $stmt_thread->fetch(PDO::FETCH_ASSOC);

// Get all messages in thread that user can see
$stmt_messages = $db->prepare("
    SELECT 
        m.*,
        mr_current.recipient_type as my_recipient_type
    FROM mails m
    INNER JOIN mail_recipients mr_current 
        ON mr_current.mail_id = m.sn 
        AND mr_current.recipient_username = :username
    WHERE m.thread_id = :thread_id
    ORDER BY m.mail_date ASC
");
$stmt_messages->execute([
    ':thread_id' => $thread_id,
    ':username' => $current_user
]);
$messages = $stmt_messages->fetchAll(PDO::FETCH_ASSOC);

// Mark as read
$stmt_mark = $db->prepare("
    UPDATE mail_recipients 
    SET read_status = 1, read_date = NOW()
    WHERE thread_id = :thread_id 
        AND recipient_username = :username 
        AND read_status = 0
");
$stmt_mark->execute([
    ':thread_id' => $thread_id,
    ':username' => $current_user
]);

// Function to get visible recipients for a message
function getVisibleRecipientsForMessage($db, $mail_id, $current_user) {
    // Get current user's recipient type for this message
    $stmt_my_type = $db->prepare("
        SELECT recipient_type 
        FROM mail_recipients 
        WHERE mail_id = :mail_id AND recipient_username = :username
    ");
    $stmt_my_type->execute([':mail_id' => $mail_id, ':username' => $current_user]);
    $my_type = $stmt_my_type->fetchColumn();
    
    // Get recipients based on visibility rules
    if ($my_type == 'bcc') {
        // BCC users only see TO and CC recipients, not other BCC
        $stmt = $db->prepare("
            SELECT recipient_name, recipient_username, recipient_type 
            FROM mail_recipients 
            WHERE mail_id = :mail_id AND recipient_type IN ('to', 'cc')
            ORDER BY recipient_type, recipient_name
        ");
    } else {
        // TO and CC users see TO and CC, not BCC
        $stmt = $db->prepare("
            SELECT recipient_name, recipient_username, recipient_type 
            FROM mail_recipients 
            WHERE mail_id = :mail_id AND recipient_type IN ('to', 'cc')
            ORDER BY recipient_type, recipient_name
        ");
    }
    
    $stmt->execute([':mail_id' => $mail_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
```

#### HTML for Message Display
```html
<?php foreach ($messages as $message): ?>
    <?php $recipients = getVisibleRecipientsForMessage($db, $message['sn'], $current_user); ?>
    
    <div class="mail-message-card" data-mail-id="<?php echo $message['sn']; ?>">
        <div class="message-header">
            <div class="row">
                <div class="col-md-8">
                    <strong><?php echo htmlspecialchars($message['from_name']); ?></strong>
                    <?php 
                    // Get sender designation
                    $stmt_sender = $db->prepare("
                        SELECT hremp.Designation, admin_users.unit_head
                        FROM admin_users 
                        LEFT JOIN hremp ON hremp.EmployeeCode = admin_users.EmployeeCode
                        WHERE admin_users.username = :username
                    ");
                    $stmt_sender->execute([':username' => $message['from_username']]);
                    $sender_info = $stmt_sender->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <small class="text-muted">
                        (<?php echo htmlspecialchars($sender_info['Designation']); ?>
                        <?php if ($sender_info['unit_head'] == 1) echo ', Unit Head'; ?>)
                    </small>
                </div>
                <div class="col-md-4 text-right">
                    <small class="text-muted">
                        <?php echo date("M d, Y h:i A", strtotime($message['mail_date'])); ?>
                    </small>
                </div>
            </div>
            
            <div class="recipient-info">
                <?php
                $to_recipients = array_filter($recipients, function($r) { return $r['recipient_type'] == 'to'; });
                $cc_recipients = array_filter($recipients, function($r) { return $r['recipient_type'] == 'cc'; });
                ?>
                
                <?php if (!empty($to_recipients)): ?>
                    <small class="text-muted">
                        <strong>To:</strong> 
                        <?php echo implode(', ', array_map(function($r) { 
                            return htmlspecialchars($r['recipient_name']); 
                        }, $to_recipients)); ?>
                    </small>
                <?php endif; ?>
                
                <?php if (!empty($cc_recipients)): ?>
                    <br>
                    <small class="text-muted">
                        <i class="fa fa-users"></i> <strong>Cc:</strong> 
                        <?php echo implode(', ', array_map(function($r) { 
                            return htmlspecialchars($r['recipient_name']); 
                        }, $cc_recipients)); ?>
                    </small>
                <?php endif; ?>
                
                <?php if ($message['my_recipient_type'] == 'bcc'): ?>
                    <br>
                    <small class="text-muted">
                        <i class="fa fa-eye-slash"></i> 
                        <em>You received this as BCC</em>
                    </small>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="message-body">
            <?php echo nl2br(htmlspecialchars($message['msg'])); ?>
        </div>
        
        <?php if ($message['attachment_status'] == 1): ?>
            <div class="message-attachments">
                <i class="fa fa-paperclip"></i> 
                <?php 
                $attachments = explode(';', $message['attachments']);
                foreach ($attachments as $attachment): 
                    $filename = basename($attachment);
                ?>
                    <a href="../<?php echo htmlspecialchars($attachment); ?>" download>
                        <?php echo htmlspecialchars($filename); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="message-actions">
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-white dropdown-toggle" 
                        data-toggle="dropdown">
                    <i class="fa fa-reply"></i> Reply <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a href="#" class="reply-action" 
                           data-mail-id="<?php echo $message['sn']; ?>"
                           data-reply-type="reply">
                            <i class="fa fa-reply"></i> Reply to Sender
                        </a>
                    </li>
                    <li>
                        <a href="#" class="reply-action" 
                           data-mail-id="<?php echo $message['sn']; ?>"
                           data-reply-type="reply_all">
                            <i class="fa fa-reply-all"></i> Reply All
                        </a>
                    </li>
                    <li>
                        <a href="#" class="reply-action" 
                           data-mail-id="<?php echo $message['sn']; ?>"
                           data-reply-type="reply_select">
                            <i class="fa fa-users"></i> Reply to Selected...
                        </a>
                    </li>
                </ul>
            </div>
            
            <button type="button" class="btn btn-sm btn-white">
                <i class="fa fa-share"></i> Forward
            </button>
        </div>
    </div>
<?php endforeach; ?>
```

### Reply Functionality

#### Reply Modal for Recipient Selection
```html
<!-- Modal for selective reply -->
<div class="modal fade" id="replySelectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <i class="fa fa-users"></i> Select Recipients for Reply
                </h4>
            </div>
            <div class="modal-body">
                <p class="text-muted">
                    Choose who should receive your reply:
                </p>
                <div id="recipient-selection-list">
                    <!-- Dynamically populated with checkboxes -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirm-reply-selection">
                    Continue to Reply
                </button>
            </div>
        </div>
    </div>
</div>
```

#### JavaScript for Reply Actions
```javascript
$(document).ready(function() {
    // Handle reply actions
    $('.reply-action').on('click', function(e) {
        e.preventDefault();
        
        var mailId = $(this).data('mail-id');
        var replyType = $(this).data('reply-type');
        
        if (replyType === 'reply') {
            // Simple reply to sender only
            window.location.href = 'mail_thread_reply.php?reply=' + mailId + '&type=reply';
            
        } else if (replyType === 'reply_all') {
            // Reply to all recipients
            window.location.href = 'mail_thread_reply.php?reply=' + mailId + '&type=reply_all';
            
        } else if (replyType === 'reply_select') {
            // Show modal for recipient selection
            loadRecipientSelectionModal(mailId);
        }
    });
    
    function loadRecipientSelectionModal(mailId) {
        // AJAX call to get available recipients
        $.ajax({
            url: 'ajax_get_message_recipients.php',
            method: 'POST',
            data: { mail_id: mailId },
            success: function(response) {
                var recipients = JSON.parse(response);
                var html = '';
                
                recipients.forEach(function(recipient) {
                    html += '<div class="checkbox">';
                    html += '  <label>';
                    html += '    <input type="checkbox" name="reply_recipients[]" ';
                    html += '           value="' + recipient.username + '">';
                    html += '    ' + recipient.name + ' ';
                    html += '    <small class="text-muted">(' + recipient.designation + ')</small>';
                    html += '  </label>';
                    html += '</div>';
                });
                
                $('#recipient-selection-list').html(html);
                $('#replySelectModal').modal('show');
                $('#replySelectModal').data('mail-id', mailId);
            }
        });
    }
    
    $('#confirm-reply-selection').on('click', function() {
        var mailId = $('#replySelectModal').data('mail-id');
        var selected = [];
        
        $('input[name="reply_recipients[]"]:checked').each(function() {
            selected.push($(this).val());
        });
        
        if (selected.length === 0) {
            alert('Please select at least one recipient');
            return;
        }
        
        // Redirect to reply page with selected recipients
        window.location.href = 'mail_thread_reply.php?reply=' + mailId + 
                              '&type=reply_select&recipients=' + selected.join(',');
    });
});
```

---

## CSS Styling (Bootstrap 3 Compatible)

```css
/* Thread View Styles */
.mail-message-card {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 4px;
    margin-bottom: 20px;
    padding: 15px;
}

.mail-message-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.message-header {
    border-bottom: 1px solid #e7eaec;
    padding-bottom: 10px;
    margin-bottom: 15px;
}

.recipient-info {
    margin-top: 8px;
}

.message-body {
    padding: 15px 0;
    line-height: 1.6;
}

.message-attachments {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 4px;
    margin: 15px 0;
}

.message-attachments a {
    margin-right: 15px;
    color: #1ab394;
}

.message-actions {
    padding-top: 10px;
    border-top: 1px solid #e7eaec;
}

/* CC/BCC Field Styles */
#cc-field-group, #bcc-field-group {
    background: #f9f9f9;
    padding: 15px;
    border-left: 3px solid #1ab394;
    border-radius: 4px;
}

#bcc-field-group {
    border-left-color: #f8ac59;
}

/* Unread badge in threads */
.label-warning {
    background-color: #f8ac59;
}

/* Thread indicator */
.thread-indicator {
    display: inline-block;
    padding: 2px 8px;
    background: #1ab394;
    color: white;
    border-radius: 12px;
    font-size: 11px;
}
```

---

## Implementation Order

### Phase 1: Quick Wins (Week 1)
1. ✅ Fix trash icon functionality
   - Add JavaScript handlers
   - Implement bulk and individual trash
   - Test in all folders

### Phase 2: Database Setup (Week 1-2)
2. ✅ Create database migration script
3. ✅ Run migration in development environment
4. ✅ Verify data integrity
5. ✅ Create rollback plan

### Phase 3: CC/BCC (Week 2-3)
6. ✅ Update mail_compose.php UI
7. ✅ Add JavaScript for show/hide fields
8. ✅ Update send logic
9. ✅ Test with various recipient combinations

### Phase 4: Threading Part 1 (Week 3-4)
10. ✅ Update mailbox.php for thread view
11. ✅ Create mail_thread_view.php
12. ✅ Implement basic reply functionality
13. ✅ Test thread display

### Phase 5: Threading Part 2 (Week 4-5)
14. ✅ Implement recipient visibility logic
15. ✅ Add reply/reply-all/selective reply
16. ✅ Update notification system
17. ✅ Comprehensive testing

### Phase 6: Polish & Documentation (Week 5-6)
18. ✅ Add icons and visual indicators
19. ✅ CSS refinements
20. ✅ Update documentation
21. ✅ User acceptance testing
22. ✅ Deploy to production

---

## Testing Checklist

### Trash Functionality
- [ ] Bulk trash works with multiple selected messages
- [ ] Individual trash icon works per message
- [ ] Trashed messages appear in Trash folder
- [ ] Can view trashed messages
- [ ] Confirm dialog prevents accidental deletion

### CC/BCC Functionality
- [ ] Add Cc field appears and initializes properly
- [ ] Add Bcc field appears and initializes properly
- [ ] Remove Cc/Bcc fields works and clears selections
- [ ] Can send to To only
- [ ] Can send to To + Cc
- [ ] Can send to To + Bcc
- [ ] Can send to all three (To + Cc + Bcc)
- [ ] TO recipients see To and Cc recipients
- [ ] CC recipients see To and Cc recipients
- [ ] BCC recipients only see To and Cc (not other BCC)
- [ ] Sender sees all recipients including BCC

### Threading Functionality
- [ ] New message creates new thread
- [ ] Reply links to parent message
- [ ] Reply All includes all To/Cc recipients
- [ ] Selective reply shows recipient modal
- [ ] Thread view shows messages in chronological order
- [ ] Unread count badge is accurate
- [ ] Message count in thread is correct
- [ ] Quoted messages display properly
- [ ] Attachments visible in thread view
- [ ] Mark as read updates across thread

### Edge Cases
- [ ] Reply to message with only BCC (as BCC recipient)
- [ ] Forward from thread maintains context
- [ ] Draft in thread saves properly
- [ ] Delete single message vs whole thread
- [ ] Search within threads
- [ ] Very long recipient lists display properly
- [ ] Very long threads (50+ messages) perform well

---

## Rollback Plan

If issues occur:

1. **Immediate Rollback** (within 24 hours):
   - Revert to backup of mails table
   - Drop new tables (mail_threads, mail_recipients)
   - Restore original PHP files from git
   
2. **Partial Rollback** (if CC/BCC works but threading has issues):
   - Keep CC/BCC functionality
   - Disable threading display
   - Continue using mail_recipients for CC/BCC
   - Fix threading in development

3. **Data Preservation**:
   - All changes use new columns/tables
   - Original mails table data remains intact
   - Can run in "compatibility mode" reading old structure

---

## Success Metrics

- Trash functionality works 100% of the time
- CC/BCC adoption: 30% of messages use CC within first month
- Threading reduces inbox clutter by showing conversations grouped
- Average time to find message in thread: < 10 seconds
- User satisfaction survey: 4/5 stars or higher
- Zero data loss during migration
- Page load times remain under 2 seconds

---

**End of Implementation Plan**
