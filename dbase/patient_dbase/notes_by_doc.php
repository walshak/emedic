<?php
include("../../Connections/Conn.php");
session_start();

$username = $_SESSION['username'];
$stmt2 = $db->query("SELECT * FROM admin_users_rights WHERE username='$username' AND see_med_rpt=1 AND see_statistic_rpt=1");
if ($stmt2->rowCount() == 0) {
    exit;
}

// Get filter inputs
$filter_preparer = isset($_GET['prepared_by']) ? trim($_GET['prepared_by']) : '';
$notes_search = isset($_GET['notes_search']) ? trim($_GET['notes_search']) : '';
$filter_notes_type = isset($_GET['notes_type']) ? trim($_GET['notes_type']) : '';
$filter_dept_id = isset($_GET['dept_id']) ? trim($_GET['dept_id']) : '';
$filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';
$date_start = isset($_GET['date_start']) ? trim($_GET['date_start']) : '';
$date_end = isset($_GET['date_end']) ? trim($_GET['date_end']) : '';
$table_name = isset($_GET['table_name']) ? trim($_GET['table_name']) : 'notes';



$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1) $page = 1;

$notes_per_page = 20;
$offset = ($page - 1) * $notes_per_page;

$notes = array();
$totalNotes = 0;

$whereClauses = [];
$params = [];

if ($filter_preparer !== '') {
    $whereClauses[] = "a.fullname = :prepared_by";
    $params[':prepared_by'] = $filter_preparer;
}
if ($notes_search !== '') {
    $whereClauses[] = "n.notes LIKE :notes_search";
    $params[':notes_search'] = '%' . $notes_search . '%';
}
if ($filter_notes_type !== '') {
    $whereClauses[] = "n.notes_type = :notes_type";
    $params[':notes_type'] = $filter_notes_type;
}
if ($filter_dept_id !== '') {
    $whereClauses[] = "n.dept_id = :dept_id";
    $params[':dept_id'] = $filter_dept_id;
}
if ($filter_status !== '') {
    $whereClauses[] = "n.status = :status";
    $params[':status'] = $filter_status;
}

if ($table_name == "notes_services") {
    if ($date_start !== '') {
        $whereClauses[] = "n.created_at >= :date_start";
        $params[':date_start'] = $date_start . ' 00:00:00';
    }
    if ($date_end !== '') {
        $whereClauses[] = "n.created_at <= :date_end";
        $params[':date_end'] = $date_end . ' 23:59:59';
    }
} else {

    if ($date_start !== '') {
        $whereClauses[] = "n.date_entry >= :date_start";
        $params[':date_start'] = $date_start . ' 00:00:00';
    }
    if ($date_end !== '') {
        $whereClauses[] = "n.date_entry <= :date_end";
        $params[':date_end'] = $date_end . ' 23:59:59';
    }
}

$whereSql = '';
if (!empty($whereClauses)) {
    $whereSql = 'WHERE ' . implode(' AND ', $whereClauses);
}

// Total count

if ($table_name == "notes_services") {
    $count_sql = "SELECT COUNT(*) FROM notes_services n
              INNER JOIN admin_users a ON a.id = n.created_by
              $whereSql";
} else {
    $count_sql = "SELECT COUNT(*) FROM notes n
    INNER JOIN admin_users a ON a.id = n.created_by
    $whereSql";
}

$stmt = $db->prepare($count_sql);
$stmt->execute($params);
$totalNotes = (int) $stmt->fetchColumn();

// Fetch paginated notes with patient name and hospital number
if ($table_name == "notes_services") {
    $notesh = " AND notes!=''";
    $sql = "SELECT n.*, a.fullname,
               CONCAT(p.surname, ', ', p.fname, ' ', p.oname) AS patient_name, 
               p.hospital_no as patient_hospital_no
        FROM notes_services n
        INNER JOIN admin_users a ON a.id = n.created_by
        LEFT JOIN enrollee p ON n.hospital_no = p.hospital_no
        $whereSql $notesh
        ORDER BY n.created_at DESC
        LIMIT :offset, :limit";
} else {

    $sql = "SELECT n.*, d.department, a.fullname,
                   CONCAT(p.surname, ', ', p.fname, ' ', p.oname) AS patient_name, 
                   p.hospital_no as patient_hospital_no
            FROM notes n
            INNER JOIN admin_users a ON a.id = n.created_by
            LEFT JOIN department d ON n.dept_id = d.sn
            LEFT JOIN enrollee p ON n.hospital_no = p.hospital_no
            $whereSql
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

// Summary calculations
$distinctAppNoSql = "SELECT COUNT(DISTINCT n.app_no) FROM $table_name n
                     INNER JOIN admin_users a ON a.id = n.created_by
                     $whereSql";
$stmt = $db->prepare($distinctAppNoSql);
$stmt->execute($params);
$totalDistinctAppNo = (int)$stmt->fetchColumn();

$distinctPatientSql = "SELECT COUNT(DISTINCT n.hospital_no) FROM $table_name n
                       INNER JOIN admin_users a ON a.id = n.created_by
                       $whereSql";
$stmt = $db->prepare($distinctPatientSql);
$stmt->execute($params);
$totalDistinctPatients = (int)$stmt->fetchColumn();


if ($table_name == 'notes_services') {
    $notesTypeCountSql = "SELECT n.service, COUNT(*) AS cnt FROM notes_services n
    INNER JOIN admin_users a ON a.id = n.created_by
    $whereSql
    GROUP BY n.service
    ORDER BY cnt DESC";
    $stmt = $db->prepare($notesTypeCountSql);
    $stmt->execute($params);
    $notes_type_counts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $notesTypeCountSql = "SELECT n.notes_type, COUNT(*) AS cnt FROM notes n
    INNER JOIN admin_users a ON a.id = n.created_by
    $whereSql
    GROUP BY n.notes_type
    ORDER BY cnt DESC";
    $stmt = $db->prepare($notesTypeCountSql);
    $stmt->execute($params);
    $notes_type_counts = $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// Fetch distinct values for filters for dropdowns
$notes_type_list = [];
$departments_list = [];
$status_list = ['0' => 'Hidden', '1' => 'Visible'];

// Notes types for dropdown
if ($table_name == 'notes') {
    $notesTypeQuery = "SELECT DISTINCT notes_type FROM notes WHERE notes_type IS NOT NULL AND notes_type != '' ORDER BY notes_type";
    $notesTypeStmt = $db->query($notesTypeQuery);
    $notes_type_list = $notesTypeStmt->fetchAll(PDO::FETCH_COLUMN);
} else {
    $notesTypeQuery = "SELECT DISTINCT service FROM notes_services WHERE service IS NOT NULL AND service != '' ORDER BY service";
    $notesTypeStmt = $db->query($notesTypeQuery);
    $notes_type_list = $notesTypeStmt->fetchAll(PDO::FETCH_COLUMN);
}
// Departments for dropdown
$departmentsQuery = "SELECT sn, department FROM department ORDER BY department";
$departmentsStmt = $db->query($departmentsQuery);
$departments_list = $departmentsStmt->fetchAll(PDO::FETCH_ASSOC);

// Preparers for dropdown
$preparersQuery = "SELECT DISTINCT fullname FROM admin_users where rights in ('NS','DR','MD','GM','AD') ORDER BY fullname";
$preparersStmt = $db->query($preparersQuery);
$all_preparers = $preparersStmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Search Notes by Doctor/Nurse</title>
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
            margin-right: 10px;
        }

        select,
        input[type=text],
        input[type=date],
        button {
            padding: 6px 12px;
            font-size: 14px;
            margin-right: 10px;
            margin-bottom: 10px;
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
            text-align: left;
        }

        th {
            background-color: #007bff;
            color: white;
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

        .summary-section {
            margin-bottom: 20px;
            border: 1px solid #ccc;
            padding: 10px 15px;
            border-radius: 5px;
            background: #f9f9f9;
        }

        .summary-section h3 {
            margin-top: 0;
            color: #007bff;
        }

        .notes-type-summary table {
            width: auto;
            margin-top: 5px;
        }

        .notes-type-summary th,
        .notes-type-summary td {
            border: 1px solid #ccc;
            padding: 5px 10px;
        }
    </style>
</head>

<body>
    <h2>Search Patient Notes by Doctor/Nurse</h2>

    <form method="get" action="">
        <label for="prepared_by">Doctor/Nurse:</label>
        <select name="prepared_by" id="prepared_by" required>
            <option value="">-- Select --</option>
            <?php foreach ($all_preparers as $name): ?>
                <option value="<?php echo htmlspecialchars($name); ?>" <?php if ($filter_preparer == $name) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($name); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="notes_search">Keyword (optional):</label>
        <input type="text" name="notes_search" id="notes_search" value="<?php echo htmlspecialchars($notes_search); ?>">

        <label for="notes_type">Notes Type:</label>
        <select name="notes_type" id="notes_type">
            <option value="">-- All --</option>
            <?php foreach ($notes_type_list as $type): ?>
                <option value="<?php echo htmlspecialchars($type); ?>" <?php if ($filter_notes_type == $type) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($type); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="dept_id">Department:</label>
        <select name="dept_id" id="dept_id">
            <option value="">-- All --</option>
            <?php foreach ($departments_list as $dept): ?>
                <option value="<?php echo htmlspecialchars($dept['sn']); ?>" <?php if ($filter_dept_id == $dept['sn']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($dept['department']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="status">Status:</label>
        <select name="status" id="status">
            <option value="">-- All --</option>
            <option value="1" <?php if ($filter_status === '1') echo 'selected'; ?>>Visible</option>
            <option value="0" <?php if ($filter_status === '0') echo 'selected'; ?>>Hidden</option>
        </select>



        <label for="status">Choose Table:</label>
        <select name="table_name" id="table_name">
            <option value="notes">-- All --</option>
            <option value="notes" <?php if ($table_name === 'notes') echo 'selected'; ?>>Medical HX</option>
            <option value="notes_services" <?php if ($table_name === 'notes_services') echo 'selected'; ?>>Medical HX (Others)</option>
        </select>

        <label for="date_start">Date Range From:</label>
        <input type="date" name="date_start" id="date_start" value="<?php echo htmlspecialchars($date_start); ?>" required>

        <label for="date_end">To:</label>
        <input type="date" name="date_end" id="date_end" value="<?php echo htmlspecialchars($date_end); ?>" required>

        <button type="submit">Search</button>
        &nbsp;:&nbsp;
        <a href="notes_by_doc.php">Refresh</a>
        &nbsp;:&nbsp;
        <a href="index.php">CLOSE</a>
    </form>

    <?php if ($filter_preparer !== ''): ?>

        <div class="summary-section">
            <h3>Summary: &nbsp; <?= $filter_preparer; ?></h3>
            <p><strong>Total Documentations:</strong> <?php echo $totalNotes; ?></p>
            <p><strong>Total Documentation By Patient Appointment Number:</strong> <?php echo $totalDistinctAppNo; ?></p>
            <p><strong>Total Patients Seen:</strong> <?php echo $totalDistinctPatients; ?></p>
            <?php if ($table_name == 'notes') { ?>
                <div class="notes-type-summary">
                    <strong>Notes Type Counts:</strong>
                    <table>
                        <thead>
                            <tr>
                                <th>Notes Type</th>
                                <th>Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($notes_type_counts as $typeCount): ?>
                                <tr>
                                    <td><?php

                                        $type = $typeCount['notes_type'];
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

                                        ///echo htmlspecialchars($typeCount['notes_type'] ?: '(none)'); 
                                        ?></td>
                                    <td><?php echo (int)$typeCount['cnt']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php } ?>
        </div>

        <?php if (count($notes) === 0): ?>
            <p>No notes found for this preparer with the selected filters.</p>
        <?php else: ?>
            <button onclick="printTable()">Print</button>
            <a href="download_csv.php?<?php echo http_build_query($_GET); ?>&table_name=<?= $table_name; ?>" class="download-csv">Download CSV</a>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Patient Name</th>
                        <th>Hospital Number</th>
                        <th>Notes</th>
                        <th>Department</th>
                        <th>Date and Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($notes as $index => $note): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($note['patient_name']); ?></td>
                            <td><?php echo htmlspecialchars($note['patient_hospital_no']); ?></td>
                            <td><?php echo $note['notes']; ?></td>
                            <td>
                                <?php if ($table_name == "notes") {
                                    echo htmlspecialchars($note['department']);
                                } ?></td>
                            <td><?php if ($table_name == "notes") {
                                    echo date('jS M Y, H:i:s', strtotime($note['date_entry']));
                                } else {
                                    echo date('jS M Y, H:i:s', strtotime($note['created_at']));
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
    <?php else: ?>
        <p>Please select a doctor or nurse to search for patient notes.</p>
    <?php endif; ?>

    <script>
        function printTable() {
            var printContents = document.body.innerHTML;
            var newWindow = window.open('', '', 'height=700,width=900');
            newWindow.document.write('<html><head><title>Print Notes</title>');
            newWindow.document.write('</head><body>');
            newWindow.document.write(printContents);
            newWindow.document.write('</body></html>');
            newWindow.document.close();
            newWindow.focus();
            newWindow.print();
            newWindow.close();
        }
    </script>

</body>

</html>