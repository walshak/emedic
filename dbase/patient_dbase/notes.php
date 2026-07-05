<?php

include("../../Connections/Conn.php");
session_start();
$username = $_SESSION['username'];
$stmt2 = $db->query("SELECT * FROM admin_users_rights WHERE username='$username' AND see_med_rpt=1");
if ($stmt2->rowCount() == 0) {
    exit;
}



///$table_name = "notes_services";
//$table_name = "notes";

// Get filters
$hospital_no = isset($_GET['hospital_no']) ? trim($_GET['hospital_no']) : '';
$notes_search = isset($_GET['notes_search']) ? trim($_GET['notes_search']) : '';
$filter_rights = isset($_GET['rights']) ? trim($_GET['rights']) : '';
$filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';
$filter_preparer = isset($_GET['prepared_by']) ? trim($_GET['prepared_by']) : '';
$table_name = isset($_GET['table_name']) ? trim($_GET['table_name']) : 'notes';

$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1) $page = 1;

$notes_per_page = 20;
$offset = ($page - 1) * $notes_per_page;

$notes = array();
$keyword_occurrences = 0;
$totalNotes = 0;
$all_preparers = array();
$all_rights = array();

$patient = null; // Patient details array or null

if ($hospital_no !== '') {
    // Fetch patient details
    $patient_sql = "SELECT hospital_no, surname, fname, oname, dob, age, gender FROM enrollee WHERE hospital_no = :hospital_no";
    $stmt_patient = $db->prepare($patient_sql);
    $stmt_patient->execute([':hospital_no' => $hospital_no]);
    $patient = $stmt_patient->fetch();
}

// Build WHERE clause
$where = array();
$params = array();

if ($hospital_no !== '') {
    $where[] = "n.hospital_no = :hospital_no";
    $params[':hospital_no'] = $hospital_no;
}
if ($notes_search !== '') {
    $where[] = "n.notes LIKE :notes_search";
    $params[':notes_search'] = '%' . $notes_search . '%';
}
if ($filter_rights !== '') {
    $where[] = "a.rights = :rights";
    $params[':rights'] = $filter_rights;
}
if ($filter_status !== '') {
    $where[] = "n.status = :status";
    $params[':status'] = $filter_status;
}
if ($filter_preparer !== '') {
    $where[] = "a.fullname = :prepared_by";
    $params[':prepared_by'] = $filter_preparer;
}

$where_clause = '';
if (!empty($where)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where);
}

// Total count
$count_sql = "SELECT COUNT(*) FROM $table_name n
              INNER JOIN admin_users a ON a.id = n.created_by
              $where_clause";
$stmt = $db->prepare($count_sql);
$stmt->execute($params);
$totalNotes = (int) $stmt->fetchColumn();

// Fetch paginated notes
if ($table_name == "notes_services") {
    $notes = " AND notes!=''";
    $sql = "SELECT n.*,e.Designation, a.rights, a.fullname
    FROM $table_name n
    INNER JOIN admin_users a ON a.id = n.created_by
    LEFT JOIN hremp e ON a.EmployeeCode = e.EmployeeCode
    $where_clause  $notes 
    ORDER BY n.created_at DESC
    LIMIT :offset, :limit";
} else {
    $sql = "SELECT n.*, d.department, e.Designation, a.rights, a.fullname
        FROM $table_name n
        INNER JOIN admin_users a ON a.id = n.created_by
        LEFT JOIN department d ON n.dept_id = d.sn
        LEFT JOIN hremp e ON a.EmployeeCode = e.EmployeeCode
        $where_clause
        ORDER BY n.date_entry DESC
        LIMIT :offset, :limit";
}

$stmt = $db->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $notes_per_page, PDO::PARAM_INT);
$stmt->execute();
$notes = $stmt->fetchAll();

// Count keyword occurrences
if ($notes_search !== '') {
    $keyword = mb_strtolower($notes_search);
    foreach ($notes as $note) {
        $note_text = mb_strtolower(strip_tags($note['notes']));
        $keyword_occurrences += substr_count($note_text, $keyword);
    }
}

// Get all unique preparers and rights for filters
$filter_query = "SELECT DISTINCT a.fullname, a.rights
                 FROM $table_name n
                 INNER JOIN admin_users a ON a.id = n.created_by
                 WHERE n.hospital_no = :hospital_no";
$stmt = $db->prepare($filter_query);
$stmt->execute(array(':hospital_no' => $hospital_no));
while ($row = $stmt->fetch()) {
    if (!in_array($row['fullname'], $all_preparers)) {
        $all_preparers[] = $row['fullname'];
    }
    if (!in_array($row['rights'], $all_rights)) {
        $all_rights[] = $row['rights'];
    }
}

// Stats
$total_visits = count($notes);
$seen_doctor = $seen_nurse = $seen_physician = $hidden_notes = $visible_notes = 0;
$notes_type_count = array();
$prepared_by_count = array();

foreach ($notes as $note) {
    if (!empty($note['prepared_by'])) $seen_doctor++;
    if ($note['status'] == '0') $hidden_notes++;
    if ($note['status'] == '1') $visible_notes++;

    $rights = strtoupper($note['rights']);
    if (strpos($rights, 'NS') !== false) $seen_nurse++;
    if (strpos($rights, 'MD') !== false || strpos($rights, 'AD') || strpos($rights, 'DR') !== false) $seen_physician++;

    /// service /// edit here  /// 
    $type = !empty($note['notes_type']) ? $note['notes_type'] : '(none)';
    if (!isset($notes_type_count[$type])) $notes_type_count[$type] = 0;
    $notes_type_count[$type]++;

    $name = !empty($note['fullname']) ? $note['fullname'] : '(Unknown)';
    if (!isset($prepared_by_count[$name])) $prepared_by_count[$name] = 0;
    $prepared_by_count[$name]++;
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>PATIENT MEDICAL HISTORY</title>
    <style>
        body {
            font-family: Arial;
            margin: 20px;
        }

        form {
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
        }

        input,
        select,
        button,
        a.download-csv {
            padding: 5px 10px;
            margin: 5px 10px 10px 0;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }

        button:hover,
        a.download-csv:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
            vertical-align: top;
        }

        th {
            background: #007bff;
            color: white;
            text-align: left;
        }

        .pagination a,
        .pagination span {
            padding: 5px 10px;
            margin-right: 5px;
            text-decoration: none;
            background: #007bff;
            color: #fff;
            border-radius: 3px;
        }

        .pagination span {
            background: #0056b3;
        }

        .patient-details {
            background: #f3f6f9;
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .patient-details h3 {
            margin-top: 0;
        }

        .patient-details dl {
            margin: 0;
            display: flex;
            flex-wrap: wrap;
        }

        .patient-details dt,
        .patient-details dd {
            width: 50%;
            margin: 0;
            padding: 4px 0;
        }

        .patient-details dt {
            font-weight: bold;
        }
    </style>
</head>

<body>

    <h2>PATIENT MEDICAL HISTORY</h2>

    <form method="get" action="">
        <label>Hospital No:</label>
        <input type="text" name="hospital_no" value="<?php echo htmlspecialchars($hospital_no); ?>" required>

        <label>Keyword:</label>
        <input type="text" name="notes_search" value="<?php echo htmlspecialchars($notes_search); ?>">

        <label>Rights:</label>
        <select name="rights">
            <option value="">-- All --</option>
            <?php foreach ($all_rights as $r) : ?>
                <option value="<?php echo htmlspecialchars($r); ?>" <?php if ($filter_rights == $r) echo 'selected'; ?>><?php echo htmlspecialchars($r); ?></option>
            <?php endforeach; ?>
        </select>

        <label>Status:</label>
        <select name="status">
            <option value="">-- All --</option>
            <option value="1" <?php if ($filter_status === '1') echo 'selected'; ?>>Visible</option>
            <option value="0" <?php if ($filter_status === '0') echo 'selected'; ?>>Hidden</option>
        </select>

        <label>Prepared By:</label>
        <select name="prepared_by">
            <option value="">-- All --</option>
            <?php foreach ($all_preparers as $name) : ?>
                <option value="<?php echo htmlspecialchars($name); ?>" <?php if ($filter_preparer == $name) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($name); ?>
                </option>
            <?php endforeach; ?>
        </select>


        <label for="status">Choose Table:</label>
        <select name="table_name" id="table_name">
            <option value="notes">-- All --</option>
            <option value="notes" <?php if ($table_name === 'notes') echo 'selected'; ?>>Medical HX</option>
            <option value="notes_services" <?php if ($table_name === 'notes_services') echo 'selected'; ?>>Medical HX (Others)</option>
        </select>


        <button type="submit">Search</button> &nbsp;:&nbsp;
        <a href="notes.php">Refresh</a>
        &nbsp;:&nbsp;
        <a href="index.php" style="font-size: 16px;;">Close</a>
    </form>

    <?php if ($hospital_no) : ?>
        <?php if ($patient) : ?>
            <div id="printPatientDetails" class="patient-details">
                <h3>Patient Details</h3>
                <dl>
                    <dt>Hospital No:</dt>
                    <dd><?php echo htmlspecialchars($patient['hospital_no']); ?></dd>
                    <dt>Full Name:</dt>
                    <dd><?php echo htmlspecialchars(trim($patient['surname'] . ' ' . $patient['fname'] . ' ' . $patient['oname'])); ?></dd>
                    <dt>Date of Birth:</dt>
                    <dd><?php echo date('jS M Y', strtotime($patient['dob']));  ?></dd>
                    <dt>Age:</dt>
                    <dd><?php echo htmlspecialchars($patient['age']); ?></dd>
                    <dt>Gender:</dt>
                    <dd><?php echo htmlspecialchars($patient['gender']); ?></dd>
                </dl>
            </div>
        <?php else : ?>
            <p><strong>Patient not found for Hospital No: <?php echo htmlspecialchars($hospital_no); ?></strong></p>
        <?php endif; ?>

        <div id="printSummary">
            <h3>Summary</h3>
            <ul>
                <li>Total Visits: <?php echo $total_visits; ?></li>
                <li>Seen by Doctor: <?php echo $seen_doctor; ?></li>
                <li>Seen by Nurse: <?php echo $seen_nurse; ?></li>
                <li>Seen by Physician: <?php echo $seen_physician; ?></li>
                <li>Visible Notes: <?php echo $visible_notes; ?></li>
                <li>Hidden Notes: <?php echo $hidden_notes; ?></li>
                <?php if ($notes_search !== '') : ?>
                    <li>Occurrences of "<?php echo htmlspecialchars($notes_search); ?>": <?php echo $keyword_occurrences; ?></li>
                <?php endif; ?>
            </ul>

            <h3>Prepared By Count</h3>
            <table id="printPreparedByCount">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($prepared_by_count as $name => $count) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($name); ?></td>
                            <td><?php echo $count; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h3>Notes Type Count</h3>
            <table id="printNotesTypeCount">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($notes_type_count as $type => $count) : ?>
                        <tr>
                            <td>

                                <?php
                                $typeLabels = array(
                                    'D' => 'Diagnosis',
                                    'C' => 'Presenting Complaints',
                                    'note' => 'Notes/Nursing Reports',
                                    'PHY' => 'Physiotherapy',
                                    'ward_round' => 'Doctors Wardround',
                                    'pre_opt_notes' => 'Pre Operation Notes',
                                    'post_opt_notes' => 'Post Operation Notes',
                                    'treatment' => 'General Treatment',
                                    'Pharm' => 'Pharmacy Notes',
                                    'dialysis' => 'Dialysis',
                                    'plan' => 'Doctors Plan'
                                );

                                echo isset($typeLabels[$type]) ? $typeLabels[$type] : htmlspecialchars($type);
                                ?>
                            </td>
                            <td><?php echo $count; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <h3>Notes</h3>
        <button onclick="printTable()">Print Notes</button>
        <a href="download.php?hospital_no=<?php echo urlencode($hospital_no); ?>&notes_search=<?php echo urlencode($notes_search); ?>&rights=<?php echo urlencode($filter_rights); ?>&status=<?php echo urlencode($filter_status); ?>&prepared_by=<?php echo urlencode($filter_preparer); ?>&table_name=<?php echo urlencode($table_name); ?>" class="download-csv" target="_blank" rel="noopener noreferrer">Download CSV</a>
        <table id="notesTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>App No</th>
                    <th>Notes</th>
                    <th>Prepared By</th>
                    <th>Department</th>
                    <th>Rights</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notes as $i => $note) : ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td><?php echo htmlspecialchars($note['app_no']); ?></td>
                        <td><?php echo $note['notes']; ?></td>
                        <td><?php echo htmlspecialchars($note['fullname']); ?></td>
                        <td><?php if ($table_name == "notes") {
                                echo htmlspecialchars($note['department']);
                            } ?></td>
                        <td><?php echo htmlspecialchars($note['rights']); ?></td>
                        <td><?php if ($table_name == "notes") {
                                echo date('jS M Y', strtotime($note['date_entry']));
                            } else {
                                echo date('jS M Y', strtotime($note['created_at']));
                            } ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="pagination">
            <?php
            $totalPages = ceil($totalNotes / $notes_per_page);
            $queryParams = $_GET;
            unset($queryParams['page']);
            $queryStr = http_build_query($queryParams);

            if ($totalPages > 1) {
                if ($page > 1)
                    echo '<a href="?' . $queryStr . '&page=' . ($page - 1) . '">« Prev</a>';
                for ($i = 1; $i <= $totalPages; $i++) {
                    if ($i == $page)
                        echo '<span>' . $i . '</span>';
                    else
                        echo '<a href="?' . $queryStr . '&page=' . $i . '">' . $i . '</a>';
                }
                if ($page < $totalPages)
                    echo '<a href="?' . $queryStr . '&page=' . ($page + 1) . '">Next »</a>';
            }
            ?>
        </div>

    <?php endif; ?>

    <script>
        function printTable() {
            var patientDetails = document.getElementById('printPatientDetails');
            var summary = document.getElementById('printSummary');
            var notesTable = document.getElementById('notesTable');

            var styles = `<style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                h3 { color: #007bff; margin-bottom: 10px; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                th, td { border: 1px solid #ccc; padding: 8px; text-align: left; vertical-align: top; }
                th { background: #007bff; color: white; }
                ul { list-style-type: none; padding-left: 0; }
                ul li { margin-bottom: 5px; }
                .patient-details { background: #f3f6f9; border: 1px solid #ccc; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
                dl { margin: 0; display: flex; flex-wrap: wrap; }
                dt, dd { width: 50%; margin: 0; padding: 4px 0; }
                dt { font-weight: bold; }
            </style>`;

            var printContents = '';
            if (patientDetails)
                printContents += patientDetails.outerHTML;
            if (summary)
                printContents += summary.outerHTML;
            if (notesTable)
                printContents += notesTable.outerHTML;

            var newWindow = window.open('', '', 'height=700,width=900');
            newWindow.document.write('<html><head><title>Print Notes and Patient Details</title>');
            newWindow.document.write(styles);
            newWindow.document.write('</head><body>');
            newWindow.document.write(printContents);
            newWindow.document.write('</body></html>');
            newWindow.document.close();
            newWindow.focus();
            newWindow.print();
            setTimeout(() => newWindow.close(), 100);
        }
    </script>

</body>

</html>