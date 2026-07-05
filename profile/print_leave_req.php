<?php
include_once('../Connections/Conn.php');

$sn = $_GET['sn'];

$leave_query = "SELECT * FROM hrlvapply WHERE sn = ?";
$stmt = $db->prepare($leave_query);
$stmt->execute([$sn]);
$leave = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch hospital details
$hospital_details = $db->query("SELECT * FROM hospital_details LIMIT 1")->fetch(PDO::FETCH_ASSOC);

$hospital_table = '<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px; text-align:left; width:100%;">';
$hospital_table .= '<tr><td width="50%" align="left"><img src="../img/logo.png" width="196" height="111"></td>';
$hospital_table .= '<td width="50%" align="right"><div style="font-size:18px; font:Verdana, Geneva, sans-serif"><strong>' . $hospital_details['name'] . '</strong></div>';
$hospital_table .= '<br><div style="font-size:14px">' . $hospital_details['address'] . '<br><br>' . $hospital_details['phones'] . '</div></td></tr>';
$hospital_table .= '</table><br>';

// Print content
echo '<html><head><title>Leave Request</title><link rel="stylesheet" type="text/css" href="print.css"></head><body>';
echo $hospital_table;

echo '<h3>Leave Request Details</h3>';

// Start of the table for general information
echo '<table border="1" cellpadding="10" cellspacing="0" style="width:100%;">';

// Display basic details in structured rows
echo '<tr><td><strong>Name:</strong></td><td>' . $leave['Name'] . '</td></tr>';
echo '<tr><td><strong>Employee Code:</strong></td><td>' . $leave['ECode'] . '</td></tr>';
echo '<tr><td><strong>Date Applied:</strong></td><td>' . date('d M Y', strtotime($leave['date_apply'])) . '</td></tr>';
echo '<tr><td><strong>Leave Start Date:</strong></td><td>' . date('d M Y', strtotime($leave['starting_date'])) . '</td></tr>';
echo '<tr><td><strong>Leave Type:</strong></td><td>' . $leave['type_leave'] . '</td></tr>';
echo '<tr><td><strong>Resumption Date:</strong></td><td>' . date('d M Y', strtotime($leave['starting_date'] . '+' . $leave['days'] . ' days')) . '</td></tr>';
echo '<tr><td><strong>Number of Leave Days:</strong></td><td>' . $leave['days'] . '</td></tr>';
echo '<tr><td><strong>Backup Staff:</strong></td><td>' . ucfirst($leave['backup_staff']) . '</td></tr>';
echo '<tr><td><strong>Status:</strong></td><td>' . ucfirst($leave['status']) . '</td></tr>';
echo '</table>';

// Leave some space for the paragraphs
echo '<br>';

// Paragraphs for reasons (approvals, rejections, and application reason)
echo '<div style="margin-top: 20px;">';

// Application reason
echo '<h4>Application Reason:</h4>';
echo '<p>' . nl2br($leave['reason']) . '</p>';

// Approver 1 Remarks
if (!empty($leave['approver1'])) {
    echo '<h4>Approver 1 Remarks:</h4>';
    echo '<p><strong>' . $leave['approver1'] . ' (' . date('d M Y', strtotime($leave['approver1_date'])) . '):</strong> </p><p>' . nl2br($leave['remarks']) . '</p>';
}

// Approver 2 Remarks
if (!empty($leave['approver2'])) {
    echo '<h4>Approver 2 Remarks:</h4>';
    echo '<p><strong>' . $leave['approver2'] . ' (' . date('d M Y', strtotime($leave['approver2_date'])) . '):</strong> </p><p>' . nl2br($leave['remarks2']) . '</p>';
}


// Rejection reason, if applicable
if ($leave['status'] == 'reject') {
    echo '<h4>Rejection Reason:</h4>';
    echo '<p>' . nl2br($leave['rejection_reason']) . '</p>';
}

// Additional remarks (if applicable)
if (!empty($leave['additional_remarks'])) {
    echo '<h4>Additional Remarks:</h4>';
    echo '<p>' . nl2br($leave['additional_remarks']) . '</p>';
}

echo '</div>';

echo '<br>';


echo '<button onclick="window.print()">Print</button>';
echo '<button onclick="window.close()">close</button>';
echo '</body></html>';
