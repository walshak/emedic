<?php
// DB setup
include("../../Connections/Conn.php");
session_start();

// Get filter inputs
$patient_search = isset($_GET['patient_search']) ? trim($_GET['patient_search']) : '';
$date_start = isset($_GET['date_start']) ? trim($_GET['date_start']) : '';
$date_end = isset($_GET['date_end']) ? trim($_GET['date_end']) : '';
$specimen_collected = isset($_GET['specimen_collected']) ? trim($_GET['specimen_collected']) : '';
$request_by = isset($_GET['request_by']) ? trim($_GET['request_by']) : '';
$approved_by = isset($_GET['approved_by']) ? trim($_GET['approved_by']) : '';
$lab_sci_name = isset($_GET['lab_sci_name']) ? trim($_GET['lab_sci_name']) : '';
$lab_sci_speciality = isset($_GET['lab_sci_speciality']) ? trim($_GET['lab_sci_speciality']) : '';
$lab_cat = isset($_GET['lab_cat']) ? trim($_GET['lab_cat']) : '';
$lab_result_tbl = isset($_GET['lab_result_tbl']) ? trim($_GET['lab_result_tbl']) : 'lab_result';

if ($lab_result_tbl == '') {
    $lab_result_tbl = 'lab_result';
}

// SECTION FILTER
$section = isset($_GET['section']) ? trim($_GET['section']) : '';

$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$rows_per_page = 20;
$offset = ($page - 1) * $rows_per_page;

$whereClauses = [];
$params = [];

if ($date_start !== '' && $date_end !== '') {
    $whereClauses[] = "r.result_date BETWEEN :date_start AND :date_end";
    $params[':date_start'] = $date_start . ' 00:00:00';
    $params[':date_end'] = $date_end . ' 23:59:59';
}

if ($patient_search !== '') {
    $whereClauses[] = "l.patient LIKE :patient_search";
    $params[':patient_search'] = '%' . $patient_search . '%';
}
if ($specimen_collected !== '') {
    $whereClauses[] = "r.specimen_collected = :specimen_collected";
    $params[':specimen_collected'] = $specimen_collected;
}
if ($request_by !== '') {
    $whereClauses[] = "l.request_by = :request_by";
    $params[':request_by'] = $request_by;
}
if ($approved_by !== '') {
    $whereClauses[] = "l.approved_by = :approved_by";
    $params[':approved_by'] = $approved_by;
}
if ($lab_sci_name !== '') {
    $whereClauses[] = "r.lab_sci_name = :lab_sci_name";
    $params[':lab_sci_name'] = $lab_sci_name;
}
if ($lab_sci_speciality !== '') {
    $whereClauses[] = "r.lab_sci_speciality = :lab_sci_speciality";
    $params[':lab_sci_speciality'] = $lab_sci_speciality;
}
if ($lab_cat !== '') {
    $whereClauses[] = "l.lab_cat = :lab_cat";
    $params[':lab_cat'] = $lab_cat;
}

// SECTION filter condition
if ($section !== '') {
    $whereClauses[] = "l.section = :section";
    $params[':section'] = $section;
}

$whereSql = '';
if (!empty($whereClauses)) {
    $whereSql = 'WHERE ' . implode(' AND ', $whereClauses);
}

// Main Query

if ($whereSql != '') {


    $sql = "SELECT r.field_value, r.test_name, r.specimen_collected, r.result_date, r.lab_sci_name, 
               r.lab_sci_speciality, r.entered_by, l.patient, l.patient_name, l.section, 
               l.request_date2, l.request_by, l.approved_by, d.department as lab_cat_name
        FROM $lab_result_tbl r
        INNER JOIN lab_manage l ON l.labrequest_no = r.lab_no
        LEFT JOIN department d ON l.lab_cat = d.sn
        $whereSql
        ORDER BY r.result_date DESC
        LIMIT :offset, :limit";
    $stmt = $db->prepare($sql);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $rows_per_page, PDO::PARAM_INT);
    $stmt->execute();
    $results = $stmt->fetchAll();

    // Total count for pagination
    $totalSql = "SELECT COUNT(*) FROM $lab_result_tbl r
             INNER JOIN lab_manage l ON l.labrequest_no = r.lab_no
             LEFT JOIN department d ON l.lab_cat = d.sn
             $whereSql";
    $totalStmt = $db->prepare($totalSql);
    foreach ($params as $key => $val) {
        $totalStmt->bindValue($key, $val);
    }
    $totalStmt->execute();
    $totalRecords = $totalStmt->fetchColumn();
    $totalPages = ceil($totalRecords / $rows_per_page);

    // Summary
    $summary = [];
    $total_tests = 0;
    if ($patient_search !== '') {
        $summarySql = "SELECT r.test_name, COUNT(*) AS test_count
                   FROM $lab_result_tbl r
                   INNER JOIN lab_manage l ON l.labrequest_no = r.lab_no
                   WHERE l.patient LIKE :patient_search AND r.result_date BETWEEN :date_start AND :date_end
                   GROUP BY r.test_name";
        $summaryStmt = $db->prepare($summarySql);
        $summaryStmt->bindValue(':patient_search', '%' . $patient_search . '%');
        $summaryStmt->bindValue(':date_start', $params[':date_start']);
        $summaryStmt->bindValue(':date_end', $params[':date_end']);
        $summaryStmt->execute();
        $summary = $summaryStmt->fetchAll(PDO::FETCH_ASSOC);
        $total_tests = array_sum(array_column($summary, 'test_count'));
    } else {
        $summarySql = "SELECT 
                      COUNT(DISTINCT l.section) AS distinct_sections,
                      COUNT(DISTINCT l.approved_by) AS distinct_approved_by,
                      COUNT(DISTINCT r.lab_sci_name) AS distinct_lab_sci_name,
                      COUNT(DISTINCT r.lab_sci_speciality) AS distinct_lab_sci_speciality,
                      COUNT(DISTINCT l.lab_cat) AS distinct_lab_cat,
                      COUNT(DISTINCT r.entered_by) AS distinct_entered_by
                   FROM $lab_result_tbl r
                   INNER JOIN lab_manage l ON l.labrequest_no = r.lab_no
                   WHERE r.result_date BETWEEN :date_start AND :date_end";
        $summaryStmt = $db->prepare($summarySql);
        $summaryStmt->bindValue(':date_start', $params[':date_start']);
        $summaryStmt->bindValue(':date_end', $params[':date_end']);
        $summaryStmt->execute();
        $summary = $summaryStmt->fetch(PDO::FETCH_ASSOC);
    }
}

// Dropdown values
function fetchDistinctValues($db, $sql)
{
    $stmt = $db->query($sql);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

$distinctSpecimens = fetchDistinctValues($db, "SELECT DISTINCT specimen_collected FROM $lab_result_tbl WHERE specimen_collected IS NOT NULL AND specimen_collected != '' ORDER BY specimen_collected");
$distinctRequestBy = fetchDistinctValues($db, "SELECT DISTINCT request_by FROM lab_manage WHERE request_by IS NOT NULL AND request_by != '' ORDER BY request_by");
$distinctApprovedBy = fetchDistinctValues($db, "SELECT DISTINCT approved_by FROM lab_manage WHERE approved_by IS NOT NULL AND approved_by != '' ORDER BY approved_by");
$distinctLabSciName = fetchDistinctValues($db, "SELECT DISTINCT lab_sci_name FROM $lab_result_tbl WHERE lab_sci_name IS NOT NULL AND lab_sci_name != '' ORDER BY lab_sci_name");
$distinctLabSciSpeciality = fetchDistinctValues($db, "SELECT DISTINCT lab_sci_speciality FROM $lab_result_tbl WHERE lab_sci_speciality IS NOT NULL AND lab_sci_speciality != '' ORDER BY lab_sci_speciality");
$departmentsListStmt = $db->query("SELECT sn, department FROM department ORDER BY department");
$distinctLabCat = $departmentsListStmt->fetchAll(PDO::FETCH_ASSOC);
$distinctSections = fetchDistinctValues($db, "SELECT DISTINCT section FROM lab_manage WHERE section IS NOT NULL AND section != '' ORDER BY section");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Search Lab Investigations</title>
    <style>
        body {
            font-family: Arial, sans-serif;
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
            margin: 5px 10px 10px 0;
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
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #007bff;
            color: white;
        }

        .summary {
            padding: 10px;
            background: #f9f9f9;
            border: 1px solid #ccc;
            margin-bottom: 10px;
            border-radius: 5px;
        }

        .summary h3 {
            margin-top: 0;
            color: #007bff;
        }

        .action-buttons {
            margin-bottom: 10px;
        }

        button.print-btn,
        a.download-csv {
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 8px 14px;
            text-decoration: none;
            cursor: pointer;
            margin-right: 10px;
        }

        button.print-btn:hover,
        a.download-csv:hover {
            background-color: #0056b3;
        }

        .pagination {
            margin-top: 20px;
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
    </style>
</head>

<body>
    <h2>Search Lab Investigations</h2>

    <form method="get" action="">
        <label for="patient_search">Patient (optional):</label>
        <input type="text" name="patient_search" id="patient_search" value="<?= htmlspecialchars($patient_search) ?>" />

        <label for="date_start">Date Range From:</label>
        <input type="date" name="date_start" id="date_start" value="<?= htmlspecialchars($date_start) ?>" required />

        <label for="date_end">To:</label>
        <input type="date" name="date_end" id="date_end" value="<?= htmlspecialchars($date_end) ?>" required />

        <label for="specimen_collected">Specimen Collected:</label>
        <select name="specimen_collected" id="specimen_collected">
            <option value="">-- All --</option>
            <?php foreach ($distinctSpecimens as $specimen): ?>
                <option value="<?= htmlspecialchars($specimen) ?>" <?= ($specimen_collected == $specimen) ? 'selected' : '' ?>><?= htmlspecialchars($specimen) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="request_by">Request By:</label>
        <select name="request_by" id="request_by">
            <option value="">-- All --</option>
            <?php foreach ($distinctRequestBy as $reqby): ?>
                <option value="<?= htmlspecialchars($reqby) ?>" <?= ($request_by == $reqby) ? 'selected' : '' ?>><?= htmlspecialchars($reqby) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="approved_by">Approved By:</label>
        <select name="approved_by" id="approved_by">
            <option value="">-- All --</option>
            <?php foreach ($distinctApprovedBy as $apprby): ?>
                <option value="<?= htmlspecialchars($apprby) ?>" <?= ($approved_by == $apprby) ? 'selected' : '' ?>><?= htmlspecialchars($apprby) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="lab_sci_name">Lab Scientist Name:</label>
        <select name="lab_sci_name" id="lab_sci_name">
            <option value="">-- All --</option>
            <?php foreach ($distinctLabSciName as $sci): ?>
                <option value="<?= htmlspecialchars($sci) ?>" <?= ($lab_sci_name == $sci) ? 'selected' : '' ?>><?= htmlspecialchars($sci) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="lab_sci_speciality">Lab Scientist Speciality:</label>
        <select name="lab_sci_speciality" id="lab_sci_speciality">
            <option value="">-- All --</option>
            <?php foreach ($distinctLabSciSpeciality as $spec): ?>
                <option value="<?= htmlspecialchars($spec) ?>" <?= ($lab_sci_speciality == $spec) ? 'selected' : '' ?>><?= htmlspecialchars($spec) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="lab_cat">Lab Category:</label>
        <select name="lab_cat" id="lab_cat">
            <option value="">-- All --</option>
            <?php foreach ($distinctLabCat as $cat): ?>
                <option value="<?= htmlspecialchars($cat['sn']) ?>" <?= ($lab_cat == $cat['sn']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['department']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="section">Section:</label>
        <select name="section" id="section">
            <option value="">-- All --</option>
            <?php foreach ($distinctSections as $sec): ?>
                <option value="<?= htmlspecialchars($sec) ?>" <?= ($section == $sec) ? 'selected' : '' ?>><?= htmlspecialchars($sec) ?></option>
            <?php endforeach; ?>
        </select>



        <label for="section">Choose Results Table:</label>
        <select name="lab_result_tbl" id="lab_result_tbl">
            <option value="">-- All --</option>

            <option value="lab_result" <?= ($lab_result_tbl == 'lab_result') ? 'selected' : '' ?>><?= htmlspecialchars('Approved Results') ?></option>
            <option value="lab_result_old" <?= ($lab_result_tbl == 'lab_result_old') ? 'selected' : '' ?>><?= htmlspecialchars('Previous Results Discard/Deleted') ?></option>

        </select>



        <button type="submit">Search</button>
        &nbsp;&nbsp; : &nbsp;&nbsp;
        <a href="test.php">Refresh</a>
        &nbsp;&nbsp; : &nbsp;&nbsp;

        <?php if ($_SESSION['rights'] == 'LB') { ?>
            <a href="../../investigations/mgt.php">Close</a>

        <?php } else { ?>
            <a href="index.php">Close</a>


        <?php } ?>
    </form>

    <?php if ($whereSql != '') { ?>
        <div class="summary">
            <?php if ($lab_result_tbl == 'lab_result_old') { ?>
                <h2 style="color:red;">WARNING: DO NOT USE THESE RESULTS FOR TREATMENT – RESULTS HAVE BEEN DISCARDED</h2>
            <?php } ?>
            <h3>Summary</h3>
            <?php if ($patient_search !== ''): ?>
                <p><strong>Total Tests for Patient:</strong> <?= (int)$total_tests ?></p>
                <p><strong>Tests for Patient:</strong></p>
                <ul>
                    <?php foreach ($summary as $test): ?>
                        <li><?= htmlspecialchars($test['test_name']) ?>: <?= (int)$test['test_count'] ?> test(s)</li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p><strong>Distinct Sections:</strong> <?= (int)$summary['distinct_sections'] ?></p>
                <p><strong>Distinct Approved By:</strong> <?= (int)$summary['distinct_approved_by'] ?></p>
                <p><strong>Distinct Lab Scientist Names:</strong> <?= (int)$summary['distinct_lab_sci_name'] ?></p>
                <p><strong>Distinct Lab Scientist Specialities:</strong> <?= (int)$summary['distinct_lab_sci_speciality'] ?></p>
                <p><strong>Distinct Lab Categories:</strong> <?= (int)$summary['distinct_lab_cat'] ?></p>
                <p><strong>Distinct Entered By:</strong> <?= (int)$summary['distinct_entered_by'] ?></p>
            <?php endif; ?>
        </div>

        <?php if (count($results) === 0): ?>
            <p>No lab investigations found for the selected filters.</p>
        <?php else: ?>
            <div class="action-buttons">
                <button class="print-btn" onclick="printTable()">Print</button>
                <a href="download_test2.php?<?= http_build_query($_GET) ?>" class="download-csv">Download CSV</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th width="7%">Request Date</th>
                        <th width="15%">Patient</th>
                        <th width="20%">Details</th>
                        <th>Result</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $result): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($result['request_date2'])) ?></td>
                            <td><?= htmlspecialchars($result['patient_name']) ?><br>
                                <i>EMR #: <?= htmlspecialchars($result['patient']) ?></i>
                            </td>
                            <td>
                                <?= htmlspecialchars($result['test_name']) ?><br><br>
                                <b>Specimen:</b>&nbsp;<?= htmlspecialchars($result['specimen_collected']) ?><br>
                                <b>Requester:</b>&nbsp;<?= htmlspecialchars($result['request_by']) ?><br>
                                <b>Entered:</b>&nbsp;<?= htmlspecialchars($result['lab_sci_name']) ?><br>
                                <b>Approver:</b>&nbsp;<?= htmlspecialchars($result['approved_by']) ?><br>
                                <b><i>(<?= htmlspecialchars($result['lab_sci_speciality']) ?>)</i></b>
                            </td>
                            <td><?= $result['field_value'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">« Prev</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span><?= $i ?></span>
                    <?php else: ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next »</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    <?php } ?>

    <script>
        function printTable() {
            var printContents = document.body.innerHTML;
            var newWindow = window.open('', '', 'height=700,width=900');
            newWindow.document.write('<html><head><title>Print Lab Investigations</title></head><body>');
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