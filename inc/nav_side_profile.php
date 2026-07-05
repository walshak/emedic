<li>
	<?php
	$username = $_SESSION['username'];

	// Count drafts
	$stmt2 = $db->prepare("SELECT COUNT(*) FROM mails WHERE from_username = ? AND mail_status = 'draft'");
	$stmt2->execute([$username]);
	$count_draft = $stmt2->fetchColumn();

	// Count unread inbox
	$stmt3 = $db->prepare("SELECT COUNT(*) FROM mails WHERE on_contact_username = ? AND mail_status = 'send' AND read_status = 0");
	$stmt3->execute([$username]);
	$inbox = $stmt3->fetchColumn();
	?>

	<a href="#"><i class="fa fa-envelope"></i> <span class="nav-label">Mailbox </span>
		<span class="label label-warning pull-right"><?= $inbox . '/' . $count_draft; ?></span>
	</a>
	<ul class="nav nav-second-level">
		<li><a href="../profile/mailbox.php?inbox">Inbox</a></li>
		<li><a href="../profile/mail_compose.php">Compose email</a></li>
	</ul>
</li>


<li>
	<a href=""><i class="fa fa-bell"></i> <span class="nav-label">Messages </span></a>
	<ul class="nav nav-second-level">
		<li><a href="../profile/alert.php">Send Alert / Notes</a></li>
		<li><a href="">Send SMS</a></li>
	</ul>
</li>


<?php if ($_SESSION['hr_payroll'] == '1') { ?>
	<li>
		<a href="#"><i class="fa fa-user"></i> <span class="nav-label">Staff Leave</span> </a>
		<ul class="nav nav-second-level">
			<?php if ($_SESSION['hr_payroll'] == '1') { ?>
				<li><a href="../profile/index.php?LV">Leave Category</a></li>
			<?php } ?>
			<li><a href="../profile/index.php?yLV">Apply Leave </a></li>
			<li><a href="../profile/leave_calender.php">Leave Calender </a></li>
			<li><a href="../profile/index.php?rLV">Leave Request & Approval</a></li>
		</ul>
	</li>
<?php } ?>

<?php if ($_SESSION['hr_payroll'] == '1' or $_SESSION['unit_head'] == '1') { ?>

	<li>
		<a href="#"><i class="fa fa-user"></i> <span class="nav-label">Evaluation</span> </a>
		<ul class="nav nav-second-level">
			<?php if ($_SESSION['hr_payroll'] == '1') { ?>
				<li><a href="../profile/index.php?evalCat">Evaluation Category</a></li>
			<?php } ?>
			<li><a href="../profile/index.php?evalPeriod">Evalution period</a></li>
			<li><a href="../profile/index.php?eval">Evalute</a></li>
			<li><a href="../profile/index.php?rEval">Review</a></li>
		</ul>
	</li>
<?php } ?>

<li>
	<a href=""><i class="fa fa-bell"></i> <span class="nav-label">Leave/Query</span></a>
	<ul class="nav nav-second-level">
		<li><a href="../profile/staff_leave_appl.php">Leave Application</a></li>
		<li><a href="../profile/query_history.php">Query History</a></li>
	</ul>
</li>

<li>
	<a href="../profile/invsti_rq.php"><i class="fa fa-tasks"></i> <span class="nav-label">Stock Requisition </span></a>
</li>

<?php if (in_array($_SESSION['voucher_unit'], ['1']) || in_array($_SESSION['voucher'], ['1'])): ?>
	<li>
		<a href="../profile/vchr.php"><i class="fa fa-tasks"></i> <span class="nav-label">Billing Voucher</span></a>
	</li>
<?php endif; ?>

<?php if ($_SESSION['template_setup'] == 1 or $_SESSION['rights'] === 'LB'): ?>
	<li>
		<a href="../profile/templates.php"><i class="fa fa-tasks"></i> <span class="nav-label">Templates Setting</span></a>
	</li>
<?php endif; ?>

<?php if ($_SESSION['delete_doc'] === 1): ?>
	<li>
		<a href="../admin/backup_page.php"><i class="fa fa-tasks"></i> <span class="nav-label">DB Backup</span></a>
	</li>
<?php endif; ?>