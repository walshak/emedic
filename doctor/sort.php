<?php
include("../Connections/Conn.php");

$hospital_no = $_POST['hospital_no'];

// Define full category list (value => label)
$categories = [
    'C' => 'Consultation Notes / Complaints',
    'D' => 'Diagnosis / Findings',
    'PHY' => 'Physical Examination',
    'note' => 'Progress Note',
    'ward_round' => 'Ward Round Notes',
    'plan' => 'Medication Plan',
    'pre_opt_notes' => 'Pre-Operation Notes',
    'REQ_REMINDER' => 'Add Service Request',
    'post_opt_notes' => 'Post-Operation Notes',
    'treatment' => 'Treatment Notes',
    'Pharm' => 'Pharmacy Notes',
    'Antenatal' => 'Antenatal Notes',
    'dialysis' => 'Dialysis Notes',
    'pre-adm' => 'Pre-Admission Notes',
    'serv_review' => 'Ward Review Notes'
];

// Query notes_type available for this patient
$stmt = $db->prepare("SELECT DISTINCT notes_type FROM notes WHERE hospital_no = ? AND status = '1'");
$stmt->execute([$hospital_no]);
$found_types = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Filter categories to those that exist for this patient
$filtered = array_intersect_key($categories, array_flip($found_types));

// Output options as JSON
echo json_encode($filtered);
