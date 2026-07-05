<?php
// DB setup
$host = 'localhost';
$dbname = 'mluth3';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
);

try {
    $db = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}


if (isset($_POST['delete']) && isset($_POST['sn'])) {
    $snToDelete = (int)$_POST['sn'];
    $deleteStmt = $db->prepare("DELETE FROM patient_ap_services WHERE sn = :sn");
    $deleteStmt->bindValue(':sn', $snToDelete, PDO::PARAM_INT);
    $deleteStmt->execute();

    // Redirect after deletion to avoid resubmission and refresh listing
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}


// Get filter inputs
$app_no = isset($_POST['app_no']) ? trim($_POST['app_no']) : '';
$hospital_no = isset($_POST['hospital_no']) ? trim($_POST['hospital_no']) : '';
$serv_group = isset($_POST['serv_group']) ? trim($_POST['serv_group']) : '';
$cat_type = isset($_POST['cat_type']) ? trim($_POST['cat_type']) : '';
$dept_id = isset($_POST['dept_id']) ? trim($_POST['dept_id']) : '';
$invoice_by = isset($_POST['invoice_by']) ? trim($_POST['invoice_by']) : '';
$prepared_by = isset($_POST['prepared_by']) ? trim($_POST['prepared_by']) : '';
$dsp_by = isset($_POST['dsp_by']) ? trim($_POST['dsp_by']) : '';
$date_start = isset($_POST['date_start']) ? trim($_POST['date_start']) : '';
$date_end = isset($_POST['date_end']) ? trim($_POST['date_end']) : '';
$pay_mode = isset($_POST['pay_mode']) ? trim($_POST['pay_mode']) : '';
$paystatus = isset($_POST['paystatus']) ? trim($_POST['paystatus']) : '';
$process_claim = isset($_POST['process_claim']) ? trim($_POST['process_claim']) : '';
$acct_billed_staff = isset($_POST['acct_billed_staff']) ? trim($_POST['acct_billed_staff']) : '';
$who_process_paystatus = isset($_POST['who_process_paystatus']) ? trim($_POST['who_process_paystatus']) : '';
$date_field = isset($_POST['date_field']) ? trim($_POST['date_field']) : '';

// Fetch distinct values for dropdowns required for filters
function fetchDistinctValues($db, $sql)
{
    $stmt = $db->query($sql);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

$distinctServGroups = fetchDistinctValues($db, "SELECT DISTINCT serv_group FROM patient_ap_services WHERE serv_group IS NOT NULL AND serv_group != '' ORDER BY serv_group");
$distinctCatTypes = fetchDistinctValues($db, "SELECT DISTINCT cat_type FROM patient_ap_services WHERE cat_type IS NOT NULL AND cat_type != '' ORDER BY cat_type");
$distinctInvoiceBy = fetchDistinctValues($db, "SELECT DISTINCT invoice_by FROM patient_ap_services WHERE invoice_by IS NOT NULL AND invoice_by != '' ORDER BY invoice_by");
$distinctPreparedBy = fetchDistinctValues($db, "SELECT DISTINCT prepared_by FROM patient_ap_services WHERE prepared_by IS NOT NULL AND prepared_by != '' ORDER BY prepared_by");
$distinctDspBy = fetchDistinctValues($db, "SELECT DISTINCT dsp_by FROM patient_ap_services WHERE dsp_by IS NOT NULL AND dsp_by != '' ORDER BY dsp_by");
$distinctPayModes = fetchDistinctValues($db, "SELECT DISTINCT pay_mode FROM patient_ap_services WHERE pay_mode IS NOT NULL AND pay_mode != '' ORDER BY pay_mode");
$distinctPayStatuses = fetchDistinctValues($db, "SELECT DISTINCT paystatus FROM patient_ap_services WHERE paystatus IS NOT NULL ORDER BY paystatus");
$distinctProcessClaims = fetchDistinctValues($db, "SELECT DISTINCT process_claim FROM patient_ap_services WHERE process_claim IS NOT NULL ORDER BY process_claim");
$departmentsListStmt = $db->query("SELECT sn, department FROM department ORDER BY department");
$distinctDepts = $departmentsListStmt->fetchAll(PDO::FETCH_ASSOC);
$staffListStmt = $db->query("SELECT fullname, EmployeeCode FROM admin_users ORDER BY fullname");
$staffList = $staffListStmt->fetchAll(PDO::FETCH_ASSOC);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $whereClauses = [];
    $params = [];

    if ($app_no !== '') {
        $whereClauses[] = "app_no LIKE :app_no";
        $params[':app_no'] = '%' . $app_no . '%';
    }
    if ($hospital_no !== '') {
        $whereClauses[] = "hospital_no LIKE :hospital_no";
        $params[':hospital_no'] = '%' . $hospital_no . '%';
    }
    if ($serv_group !== '') {
        $whereClauses[] = "serv_group = :serv_group";
        $params[':serv_group'] = $serv_group;
    }
    if ($cat_type !== '') {
        $whereClauses[] = "cat_type = :cat_type";
        $params[':cat_type'] = $cat_type;
    }
    if ($dept_id !== '') {
        $whereClauses[] = "dept_id = :dept_id";
        $params[':dept_id'] = $dept_id;
    }
    if ($invoice_by !== '') {
        $whereClauses[] = "invoice_by LIKE :invoice_by";
        $params[':invoice_by'] = '%' . $invoice_by . '%';
    }
    if ($prepared_by !== '') {
        $whereClauses[] = "prepared_by LIKE :prepared_by";
        $params[':prepared_by'] = '%' . $prepared_by . '%';
    }
    if ($dsp_by !== '') {
        $whereClauses[] = "dsp_by LIKE :dsp_by";
        $params[':dsp_by'] = '%' . $dsp_by . '%';
    }
    if ($pay_mode !== '') {
        $whereClauses[] = "pay_mode = :pay_mode";
        $params[':pay_mode'] = $pay_mode;
    }
    if ($paystatus !== '') {
        $whereClauses[] = "paystatus = :paystatus";
        $params[':paystatus'] = $paystatus;
    }
    if ($process_claim !== '') {
        $whereClauses[] = "process_claim = :process_claim";
        $params[':process_claim'] = $process_claim;
    }
    if ($acct_billed_staff !== '') {
        $whereClauses[] = "acct_billed_staff = :acct_billed_staff";
        $params[':acct_billed_staff'] = $acct_billed_staff;
    }
    if ($who_process_paystatus !== '') {
        $whereClauses[] = "who_process_paystatus = :who_process_paystatus";
        $params[':who_process_paystatus'] = $who_process_paystatus;
    }
    if ($date_start !== '' && $date_end !== '') {
        // Validate date range
        $startDate = new DateTime($date_start);
        $endDate = new DateTime($date_end);
        $interval = $startDate->diff($endDate);

        if ($interval->y > 0 || $interval->m > 0 || $interval->d > 365) {
            /// die("Error: The date range must not exceed one year.");
        }

        if ($date_field === 'transact_date') {
            $whereClauses[] = "transact_date BETWEEN :date_start AND :date_end";
        } else {
            $whereClauses[] = "date_entry BETWEEN :date_start AND :date_end";
        }
        $params[':date_start'] = $date_start . ' 00:00:00';
        $params[':date_end'] = $date_end . ' 23:59:59';
    }

    $whereSql = '';
    if (!empty($whereClauses)) {
        $whereSql = 'WHERE ' . implode(' AND ', $whereClauses);
    }

    // Fetch results from the patient_ap_services table
    $sql = "SELECT * FROM patient_ap_services $whereSql";
    $stmt = $db->prepare($sql);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->execute();
    $results = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Search Patient AP Services</title>
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
    </style>
</head>

<body>
    <h2>Search Patient AP Services</h2>

    <form method="post" action="">
        <label for="app_no">App No:</label>
        <input type="text" name="app_no" id="app_no" value="<?= htmlspecialchars($app_no) ?>" />

        <label for="hospital_no">Hospital No:</label>
        <input type="text" name="hospital_no" id="hospital_no" value="<?= htmlspecialchars($hospital_no) ?>" />

        <label for="serv_group">Service Group:</label>
        <select name="serv_group" id="serv_group">
            <option value="">-- All --</option>
            <?php foreach ($distinctServGroups as $servGroup): ?>
                <option value="<?= htmlspecialchars($servGroup) ?>" <?= ($serv_group == $servGroup) ? 'selected' : '' ?>><?= htmlspecialchars($servGroup) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="cat_type">Category Type:</label>
        <select name="cat_type" id="cat_type">
            <option value="">-- All --</option>
            <?php foreach ($distinctCatTypes as $catType): ?>
                <option value="<?= htmlspecialchars($catType) ?>" <?= ($cat_type == $catType) ? 'selected' : '' ?>><?= htmlspecialchars($catType) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="dept_id">Department ID:</label>
        <select name="dept_id" id="dept_id">
            <option value="">-- All --</option>
            <?php foreach ($distinctDepts as $dept): ?>
                <option value="<?= htmlspecialchars($dept['sn']) ?>" <?= ($dept_id == $dept['sn']) ? 'selected' : '' ?>><?= htmlspecialchars($dept['department']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="invoice_by">Invoice By:</label>
        <select name="invoice_by" id="invoice_by">
            <option value="">-- All --</option>
            <?php foreach ($distinctInvoiceBy as $invoiceBy): ?>
                <option value="<?= htmlspecialchars($invoiceBy) ?>" <?= ($invoice_by == $invoiceBy) ? 'selected' : '' ?>><?= htmlspecialchars($invoiceBy) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="prepared_by">Prepared By:</label>
        <select name="prepared_by" id="prepared_by">
            <option value="">-- All --</option>
            <?php foreach ($distinctPreparedBy as $preparedBy): ?>
                <option value="<?= htmlspecialchars($preparedBy) ?>" <?= ($prepared_by == $preparedBy) ? 'selected' : '' ?>><?= htmlspecialchars($preparedBy) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="dsp_by">Dispensed By:</label>
        <select name="dsp_by" id="dsp_by">
            <option value="">-- All --</option>
            <?php foreach ($distinctDspBy as $dspBy): ?>
                <option value="<?= htmlspecialchars($dspBy) ?>" <?= ($dsp_by == $dspBy) ? 'selected' : '' ?>><?= htmlspecialchars($dspBy) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="date_field">Select Date Field:</label>
        <select name="date_field" id="date_field" required>
            <option value="date_entry" <?= ($date_field == 'date_entry') ? 'selected' : '' ?>>Date Entry</option>
            <option value="transact_date" <?= ($date_field == 'transact_date') ? 'selected' : '' ?>>Transaction Date</option>
        </select>

        <label for="date_start">Date Start:</label>
        <input type="date" name="date_start" id="date_start" value="<?= htmlspecialchars($date_start) ?>" required />

        <label for="date_end">Date End:</label>
        <input type="date" name="date_end" id="date_end" value="<?= htmlspecialchars($date_end) ?>" required />

        <label for="pay_mode">Pay Mode:</label>
        <select name="pay_mode" id="pay_mode">
            <option value="">-- All --</option>
            <?php foreach ($distinctPayModes as $payMode): ?>
                <option value="<?= htmlspecialchars($payMode) ?>" <?= ($pay_mode == $payMode) ? 'selected' : '' ?>><?= htmlspecialchars($payMode) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="paystatus">Pay Status:</label>
        <select name="paystatus" id="paystatus">
            <option value="">-- All --</option>
            <?php foreach ($distinctPayStatuses as $status): ?>
                <option value="<?= htmlspecialchars($status) ?>" <?= ($paystatus == $status) ? 'selected' : '' ?>><?php if ($status == 0) {
                                                                                                                        echo 'Pending';
                                                                                                                    } elseif ($status == 1) {
                                                                                                                        echo 'Paid';
                                                                                                                    } else {
                                                                                                                        echo 'Deleted';
                                                                                                                    } ?></option>
            <?php endforeach; ?>
        </select>

        <label for="process_claim">Process Claim:</label>
        <select name="process_claim" id="process_claim">
            <option value="">-- All --</option>
            <?php foreach ($distinctProcessClaims as $claim): ?>
                <option value="<?= htmlspecialchars($claim) ?>" <?= ($process_claim == $claim) ? 'selected' : '' ?>><?php
                                                                                                                    if ($claim == 0) {
                                                                                                                        echo 'Pending';
                                                                                                                    } elseif ($claim == 1) {
                                                                                                                        echo 'Paid';
                                                                                                                    } else {
                                                                                                                        echo 'Deleted';
                                                                                                                    }
                                                                                                                    htmlspecialchars($claim) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="acct_billed_staff">Acct Billed Staff:</label>
        <select name="acct_billed_staff" id="acct_billed_staff">
            <option value="">-- All --</option>
            <?php foreach ($staffList as $staff): ?>
                <option value="<?= htmlspecialchars($staff['EmployeeCode']) ?>" <?= ($acct_billed_staff == $staff['EmployeeCode']) ? 'selected' : '' ?>><?= htmlspecialchars($staff['fullname']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="who_process_paystatus">Who Process Pay Status:</label>
        <select name="who_process_paystatus" id="who_process_paystatus">
            <option value="">-- All --</option>
            <?php foreach ($staffList as $staff): ?>
                <option value="<?= htmlspecialchars($staff['EmployeeCode']) ?>" <?= ($who_process_paystatus == $staff['EmployeeCode']) ? 'selected' : '' ?>><?= htmlspecialchars($staff['fullname']) ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Search</button>
        &nbsp;&nbsp;
        :
        &nbsp;&nbsp;
        <a href="services.php">Refresh</a>
    </form>


    <form method="post" action="download_csv_ap_services.php" class="mb-4">
        <input type="hidden" name="app_no" value="<?= htmlspecialchars($app_no) ?>" />
        <input type="hidden" name="hospital_no" value="<?= htmlspecialchars($hospital_no) ?>" />
        <input type="hidden" name="serv_group" value="<?= htmlspecialchars($serv_group) ?>" />
        <input type="hidden" name="cat_type" value="<?= htmlspecialchars($cat_type) ?>" />
        <input type="hidden" name="dept_id" value="<?= htmlspecialchars($dept_id) ?>" />
        <input type="hidden" name="invoice_by" value="<?= htmlspecialchars($invoice_by) ?>" />
        <input type="hidden" name="prepared_by" value="<?= htmlspecialchars($prepared_by) ?>" />
        <input type="hidden" name="dsp_by" value="<?= htmlspecialchars($dsp_by) ?>" />
        <input type="hidden" name="date_start" value="<?= htmlspecialchars($date_start) ?>" />
        <input type="hidden" name="date_end" value="<?= htmlspecialchars($date_end) ?>" />
        <input type="hidden" name="pay_mode" value="<?= htmlspecialchars($pay_mode) ?>" />
        <input type="hidden" name="paystatus" value="<?= htmlspecialchars($paystatus) ?>" />
        <input type="hidden" name="process_claim" value="<?= htmlspecialchars($process_claim) ?>" />
        <input type="hidden" name="acct_billed_staff" value="<?= htmlspecialchars($acct_billed_staff) ?>" />
        <input type="hidden" name="who_process_paystatus" value="<?= htmlspecialchars($who_process_paystatus) ?>" />
        <input type="hidden" name="date_field" value="<?= htmlspecialchars($date_field) ?>" />
        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Download CSV</button>
    </form>


    <?php if (isset($results) && count($results) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>SN</th>
                    <th>App No</th>
                    <th>Hospital No</th>
                    <th>Service Group</th>
                    <th>Category Type</th>
                    <th>Department ID</th>
                    <th>Dispensory ID</th>
                    <th>Drug SN</th>
                    <th>Item Services</th>
                    <th>Hospital Price</th>
                    <th>Claim Amount</th>
                    <th>Interest</th>
                    <th>Quantity</th>
                    <th>Remarks</th>
                    <th>Drug Status</th>
                    <th>Invoice Status</th>
                    <th>Invoice No</th>
                    <th>Invoice Date</th>
                    <th>Invoice By</th>
                    <th>Prepared By</th>
                    <th>Created By</th>
                    <th>Dispensed By</th>
                    <th>Date Entry</th>
                    <th>Transact Date</th>
                    <th>Pay</th>
                    <th>Pay Mode</th>
                    <th>Pay Status</th>
                    <th>Process Claim</th>
                    <th>CR</th>
                    <th>Discount</th>
                    <th>Add Charge</th>
                    <th>Prescription</th>
                    <th>Dispensory Status at Pharmacy</th>
                    <th>Acct Billed Staff</th>
                    <th>Acct Billed Ack</th>
                    <th>Claim Valid By</th>
                    <th>Payment Remarks</th>
                    <th>Ledger TX</th>
                    <th>Wallet Payee</th>
                    <th>Who Process Pay Status</th>
                    <th>Wallet Debt Bill to Acct</th>
                    <th class="px-3 py-2 text-left text-sm font-semibold text-gray-900">Actions</th>

                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $result): ?>
                    <tr>
                        <td><?= htmlspecialchars($result['sn']) ?></td>
                        <td><?= htmlspecialchars($result['app_no']) ?></td>
                        <td><?= htmlspecialchars($result['hospital_no']) ?></td>
                        <td><?= htmlspecialchars($result['serv_group']) ?></td>
                        <td><?= htmlspecialchars($result['cat_type']) ?></td>
                        <td><?= htmlspecialchars($result['dept_id']) ?></td>
                        <td><?= htmlspecialchars($result['dept_dispensory_id']) ?></td>
                        <td><?= htmlspecialchars($result['drug_sn']) ?></td>
                        <td><?= htmlspecialchars($result['item_services']) ?></td>
                        <td><?= htmlspecialchars($result['hosp_price']) ?></td>
                        <td><?= htmlspecialchars($result['claim_amt']) ?></td>
                        <td><?= htmlspecialchars($result['interest']) ?></td>
                        <td><?= htmlspecialchars($result['qty']) ?></td>
                        <td><?= htmlspecialchars($result['remarks']) ?></td>
                        <td><?= htmlspecialchars($result['drug_status']) ?></td>
                        <td><?= htmlspecialchars($result['invoice_status']) ?></td>
                        <td><?= htmlspecialchars($result['invoice_no']) ?></td>
                        <td><?= htmlspecialchars($result['invoice_date']) ?></td>
                        <td><?= htmlspecialchars($result['invoice_by']) ?></td>
                        <td><?= htmlspecialchars($result['prepared_by']) ?></td>
                        <td><?= htmlspecialchars($result['created_by']) ?></td>
                        <td><?= htmlspecialchars($result['dsp_by']) ?></td>
                        <td><?= htmlspecialchars($result['date_entry']) ?></td>
                        <td><?= htmlspecialchars($result['transact_date']) ?></td>
                        <td><?= htmlspecialchars($result['pay']) ?></td>
                        <td><?= htmlspecialchars($result['pay_mode']) ?></td>
                        <td><?= htmlspecialchars($result['paystatus']) ?></td>
                        <td><?= htmlspecialchars($result['process_claim']) ?></td>
                        <td><?= htmlspecialchars($result['cr']) ?></td>
                        <td><?= htmlspecialchars($result['discount']) ?></td>
                        <td><?= htmlspecialchars($result['add_charge']) ?></td>
                        <td><?= htmlspecialchars($result['prescription']) ?></td>
                        <td><?= htmlspecialchars($result['dispensory_status_at_phamcy']) ?></td>
                        <td><?= htmlspecialchars($result['acct_billed_staff']) ?></td>
                        <td><?= htmlspecialchars($result['acct_billed_ack']) ?></td>
                        <td><?= htmlspecialchars($result['claim_valid_by']) ?></td>
                        <td><?= htmlspecialchars($result['payment_remarks']) ?></td>
                        <td><?= htmlspecialchars($result['ledger_TX']) ?></td>
                        <td><?= htmlspecialchars($result['wallet_payee']) ?></td>
                        <td><?= htmlspecialchars($result['who_process_paystatus']) ?></td>
                        <td><?= htmlspecialchars($result['wallet_debt_bill_to_acct']) ?></td>

                        <td class="whitespace-nowrap px-3 py-2 text-sm">
                            <a href="patient_ap_services_edit.php?sn=<?= urlencode($result['sn']) ?>" class="text-blue-600 hover:text-blue-800 font-semibold mr-3">Edit</a>
                            <form method="post" onsubmit="return confirm('Are you sure you want to delete this record?');" class="inline">
                                <input type="hidden" name="sn" value="<?= htmlspecialchars($result['sn']) ?>" />
                                <button type="submit" name="delete" class="text-red-600 hover:text-red-800 font-semibold">Delete</button>
                            </form>
                        </td>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <p>No records found for the selected filters.</p>
    <?php endif; ?>
</body>

</html>