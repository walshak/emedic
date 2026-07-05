<?php
// DB setup
$host = 'localhost';
$dbname = 'mluth3';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $db = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}

// Get the SN to edit from GET parameter
$sn = isset($_GET['sn']) ? (int)$_GET['sn'] : 0;

if ($sn <= 0) {
    die("Invalid record specified.");
}

// Fetch the record by SN
$stmt = $db->prepare("SELECT * FROM patient_ap_services WHERE sn = :sn");
$stmt->bindValue(':sn', $sn, PDO::PARAM_INT);
$stmt->execute();
$record = $stmt->fetch();

if (!$record) {
    die("Record not found.");
}

// Fetch distinct values for dropdowns for form selects
function fetchDistinctValues($db, $sql)
{
    $stmt = $db->query($sql);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

$distinctServGroups = fetchDistinctValues($db, "SELECT DISTINCT serv_group FROM patient_ap_services WHERE serv_group IS NOT NULL AND serv_group != '' ORDER BY serv_group");
$distinctCatTypes = fetchDistinctValues($db, "SELECT DISTINCT cat_type FROM patient_ap_services WHERE cat_type IS NOT NULL AND cat_type != '' ORDER BY cat_type");
$departmentsListStmt = $db->query("SELECT sn, department FROM department ORDER BY department");
$distinctDepts = $departmentsListStmt->fetchAll(PDO::FETCH_ASSOC);
$staffListStmt = $db->query("SELECT fullname, EmployeeCode FROM admin_users ORDER BY fullname");
$staffList = $staffListStmt->fetchAll(PDO::FETCH_ASSOC);
$distinctPayModes = fetchDistinctValues($db, "SELECT DISTINCT pay_mode FROM patient_ap_services WHERE pay_mode IS NOT NULL AND pay_mode != '' ORDER BY pay_mode");
$distinctPayStatuses = fetchDistinctValues($db, "SELECT DISTINCT paystatus FROM patient_ap_services WHERE paystatus IS NOT NULL ORDER BY paystatus");
$distinctProcessClaims = fetchDistinctValues($db, "SELECT DISTINCT process_claim FROM patient_ap_services WHERE process_claim IS NOT NULL ORDER BY process_claim");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Edit Patient AP Service #<?= htmlspecialchars($record['sn']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 text-gray-700 max-w-6xl mx-auto p-8 font-sans">
    <h1 class="text-4xl font-extrabold mb-8">Edit Patient AP Service</h1>

    <form action="patient_ap_services_update.php" method="post" class="bg-white p-8 rounded-lg shadow-lg space-y-6" novalidate>
        <input type="hidden" name="sn" value="<?= htmlspecialchars($record['sn']) ?>" />

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block font-semibold mb-1" for="app_no">App No</label>
                <input type="text" id="app_no" name="app_no" value="<?= htmlspecialchars($record['app_no']) ?>" class="w-full border border-gray-300 rounded p-2" />
            </div>
            <div>
                <label class="block font-semibold mb-1" for="hospital_no">Hospital No</label>
                <input type="text" id="hospital_no" name="hospital_no" value="<?= htmlspecialchars($record['hospital_no']) ?>" class="w-full border border-gray-300 rounded p-2" />
            </div>
            <div>
                <label class="block font-semibold mb-1" for="serv_group">Service Group</label>
                <select id="serv_group" name="serv_group" class="w-full border border-gray-300 rounded p-2">
                    <option value="">-- Select --</option>
                    <?php foreach ($distinctServGroups as $sg): ?>
                        <option value="<?= htmlspecialchars($sg) ?>" <?= $record['serv_group'] === $sg ? 'selected' : '' ?>><?= htmlspecialchars($sg) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block font-semibold mb-1" for="cat_type">Category Type</label>
                <select id="cat_type" name="cat_type" class="w-full border border-gray-300 rounded p-2">
                    <option value="">-- Select --</option>
                    <?php foreach ($distinctCatTypes as $ct): ?>
                        <option value="<?= htmlspecialchars($ct) ?>" <?= $record['cat_type'] === $ct ? 'selected' : '' ?>><?= htmlspecialchars($ct) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block font-semibold mb-1" for="dept_id">Department</label>
                <select id="dept_id" name="dept_id" class="w-full border border-gray-300 rounded p-2">
                    <option value="">-- Select --</option>
                    <?php foreach ($distinctDepts as $dept): ?>
                        <option value="<?= htmlspecialchars($dept['sn']) ?>" <?= $record['dept_id'] == $dept['sn'] ? 'selected' : '' ?>><?= htmlspecialchars($dept['department']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block font-semibold mb-1" for="dept_id">dept_dispensory_id</label>
                <select id="dept_dispensory_id" name="dept_dispensory_id" class="w-full border border-gray-300 rounded p-2">
                    <option value="">-- Select --</option>
                    <?php foreach ($distinctDepts as $dept): ?>
                        <option value="<?= htmlspecialchars($dept['sn']) ?>" <?= $record['dept_dispensory_id'] == $dept['sn'] ? 'selected' : '' ?>><?= htmlspecialchars($dept['department']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>



            <div>
                <label class="block font-semibold mb-1" for="invoice_by">drug_sn</label>
                <input type="text" id="drug_sn" name="drug_sn" value="<?= htmlspecialchars($record['drug_sn']) ?>" class="w-full border border-gray-300 rounded p-2" />
            </div>
            <div>
                <label class="block font-semibold mb-1" for="item_services">item_services</label>
                <input type="text" id="item_services" name="item_services" value="<?= htmlspecialchars($record['item_services']) ?>" class="w-full border border-gray-300 rounded p-2" />
            </div>

            <div>
                <label class="block font-semibold mb-1" for="invoice_by">Invoice By</label>
                <input type="text" id="invoice_by" name="invoice_by" value="<?= htmlspecialchars($record['invoice_by']) ?>" class="w-full border border-gray-300 rounded p-2" />
            </div>
            <div>
                <label class="block font-semibold mb-1" for="created_by">created_by By</label>
                <input type="text" id="created_by" name="created_by" value="<?= htmlspecialchars($record['created_by']) ?>" class="w-full border border-gray-300 rounded p-2" />
            </div>

            <div>
                <label class="block font-semibold mb-1" for="prepared_by">Prepared By</label>
                <input type="text" id="prepared_by" name="prepared_by" value="<?= htmlspecialchars($record['prepared_by']) ?>" class="w-full border border-gray-300 rounded p-2" />
            </div>
            <div>
                <label class="block font-semibold mb-1" for="dsp_by">Dispensed By</label>
                <input type="text" id="dsp_by" name="dsp_by" value="<?= htmlspecialchars($record['dsp_by']) ?>" class="w-full border border-gray-300 rounded p-2" />
            </div>
            <div>
                <label class="block font-semibold mb-1" for="hosp_price">Hospital Price</label>
                <input type="number" id="hosp_price" name="hosp_price" value="<?= htmlspecialchars($record['hosp_price']) ?>" class="w-full border border-gray-300 rounded p-2" />
            </div>
            <div>
                <label class="block font-semibold mb-1" for="claim_amt">Claim Amount</label>
                <input type="number" id="claim_amt" name="claim_amt" value="<?= htmlspecialchars($record['claim_amt']) ?>" class="w-full border border-gray-300 rounded p-2" />
            </div>
            <div>
                <label class="block font-semibold mb-1" for="claim_amt">pay</label>
                <input type="number" id="pay" name="pay" value="<?= htmlspecialchars($record['pay']) ?>" class="w-full border border-gray-300 rounded p-2" />
            </div>
            <div>
                <label class="block font-semibold mb-1" for="interest">Interest</label>
                <input type="number" id="interest" name="interest" value="<?= htmlspecialchars($record['interest']) ?>" class="w-full border border-gray-300 rounded p-2" />
            </div>
            <div>
                <label class="block font-semibold mb-1" for="qty">Quantity</label>
                <input type="number" id="qty" name="qty" value="<?= htmlspecialchars($record['qty']) ?>" class="w-full border border-gray-300 rounded p-2" />
            </div>
            <div>
                <label class="block font-semibold mb-1" for="remarks">Remarks</label>
                <textarea id="remarks" name="remarks" class="w-full border border-gray-300 rounded p-2"><?= htmlspecialchars($record['remarks']) ?></textarea>
            </div>
            <div>
                <label class="block font-semibold mb-1" for="drug_status">Drug Status</label>
                <input type="number" id="drug_status" name="drug_status" value="<?= htmlspecialchars($record['drug_status']) ?>" class="w-full border border-gray-300 rounded p-2" />
            </div>
            <div>
                <label class="block font-semibold mb-1" for="invoice_status">Invoice Status</label>
                <input type="number" id="invoice_status" name="invoice_status" value="<?= htmlspecialchars($record['invoice_status']) ?>" class="w-full border border-gray-300 rounded p-2" />
            </div>
            <div>
                <label class="block font-semibold mb-1" for="invoice_no">Invoice No</label>
                <input type="text" id="invoice_no" name="invoice_no" value="<?= htmlspecialchars($record['invoice_no']) ?>" class="w-full border border-gray-300 rounded p-2" />
            </div>


            <div>
                <label class="block font-semibold mb-1" for="date_entry">date_entry</label>
                <input type="date" id="date_entry" name="date_entry" value="<?= htmlspecialchars($record['date_entry']) ?>" class="w-full border border-gray-300 rounded p-2" />
            </div>
            <div>
                <label class="block font-semibold mb-1" for="transact_date">transact_date</label>
                <input type="date" id="transact_date" name="transact_date" value="<?= htmlspecialchars($record['transact_date']) ?>" class="w-full border border-gray-300 rounded p-2" />
            </div>

            <div>
                <label class="block font-semibold mb-1" for="invoice_date">Invoice Date</label>
                <input type="date" id="invoice_date" name="invoice_date" value="<?= htmlspecialchars($record['invoice_date']) ?>" class="w-full border border-gray-300 rounded p-2" />
            </div>




            <div>
                <label class="block font-semibold mb-1" for="pay_mode">Pay Mode</label>
                <select id="pay_mode" name="pay_mode" class="w-full border border-gray-300 rounded p-2">
                    <option value="">-- Select --</option>
                    <?php foreach ($distinctPayModes as $pm): ?>
                        <option value="<?= htmlspecialchars($pm) ?>" <?= $record['pay_mode'] === $pm ? 'selected' : '' ?>><?= htmlspecialchars($pm) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block font-semibold mb-1" for="paystatus">Pay Status</label>
                <select id="paystatus" name="paystatus" class="w-full border border-gray-300 rounded p-2">
                    <option value="">-- Select --</option>
                    <?php foreach ($distinctPayStatuses as $status): ?>
                        <option value="<?= htmlspecialchars($status) ?>" <?= $record['paystatus'] == $status ? 'selected' : '' ?>><?= htmlspecialchars($status) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block font-semibold mb-1" for="process_claim">Process Claim</label>
                <select id="process_claim" name="process_claim" class="w-full border border-gray-300 rounded p-2">
                    <option value="">-- Select --</option>
                    <?php foreach ($distinctProcessClaims as $claim): ?>
                        <option value="<?= htmlspecialchars($claim) ?>" <?= $record['process_claim'] == $claim ? 'selected' : '' ?>><?= htmlspecialchars($claim) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block font-semibold mb-1" for="cr">CR</label>
                <input type="number" id="cr" name="cr" value="<?= htmlspecialchars($record['cr']) ?>" class="w-full border border-gray-300 rounded p-2" />
            </div>
            <div>
                <label class="block font-semibold mb-1" for="discount">Discount</label>
                <input type="number" id="discount" name="discount" value="<?= htmlspecialchars($record['discount']) ?>" class="w-full border border-gray-300 rounded p-2" />
            </div>
            <div>
                <label class="block font-semibold mb-1" for="add_charge">Add Charge</label>
                <input type="number" id="add_charge" name="add_charge" value="<?= htmlspecialchars($record['add_charge']) ?>" class="w-full border border-gray-300 rounded p-2" />
            </div>
            <div>
                <label class="block font-semibold mb-1" for="prescription">Prescription</label>
                <input type="text" id="prescription" name="prescription" value="<?= htmlspecialchars($record['prescription']) ?>" class="w-full border border-gray-300 rounded p-2" />
            </div>
            <div>
                <label class="block font-semibold mb-1" for="dispensory_status_at_phamcy">Dispensory Status at Pharmacy</label>
                <input type="number" id="dispensory_status_at_phamcy" name="dispensory_status_at_phamcy" value="<?= htmlspecialchars($record['dispensory_status_at_phamcy']) ?>" class="w-full border border-gray-300 rounded p-2" />
            </div>
            <div>
                <label class="block font-semibold mb-1" for="acct_billed_staff">Acct Billed Staff</label>
                <select id="acct_billed_staff" name="acct_billed_staff" class="w-full border border-gray-300 rounded p-2">
                    <option value="">-- Select --</option>
                    <?php foreach ($staffList as $staff): ?>
                        <option value="<?= htmlspecialchars($staff['EmployeeCode']) ?>" <?= $record['acct_billed_staff'] == $staff['EmployeeCode'] ? 'selected' : '' ?>><?= htmlspecialchars($staff['fullname']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block font-semibold mb-1" for="acct_billed_ack">Acct Billed Ack</label>
                <input type="number" id="acct_billed_ack" name="acct_billed_ack" value="<?= htmlspecialchars($record['acct_billed_ack']) ?>" class="w-full border border-gray-300 rounded p-2" />
            </div>
            <div>
                <label class="block font-semibold mb-1" for="claim_valid_by">Claim Valid By</label>
                <input type="text" id="claim_valid_by" name="claim_valid_by" value="<?= htmlspecialchars($record['claim_valid_by']) ?>" class="w-full border border-gray-300 rounded p-2" />
            </div>
            <div>
                <label class="block font-semibold mb-1" for="payment_remarks">Payment Remarks</label>
                <textarea id="payment_remarks" name="payment_remarks" class="w-full border border-gray-300 rounded p-2"><?= htmlspecialchars($record['payment_remarks']) ?></textarea>
            </div>
            <div>
                <label class="block font-semibold mb-1" for="ledger_TX">Ledger TX</label>
                <input type="text" id="ledger_TX" name="ledger_TX" value="<?= htmlspecialchars($record['ledger_TX']) ?>" class="w-full border border-gray-300 rounded p-2" />
            </div>
            <div>
                <label class="block font-semibold mb-1" for="wallet_payee">Wallet Payee</label>
                <input type="text" id="wallet_payee" name="wallet_payee" value="<?= htmlspecialchars($record['wallet_payee']) ?>" class="w-full border border-gray-300 rounded p-2" />
            </div>
            <div>
                <label class="block font-semibold mb-1" for="who_process_paystatus">Who Process Pay Status</label>
                <select id="who_process_paystatus" name="who_process_paystatus" class="w-full border border-gray-300 rounded p-2">
                    <option value="">-- Select --</option>
                    <?php foreach ($staffList as $staff): ?>
                        <option value="<?= htmlspecialchars($staff['EmployeeCode']) ?>" <?= $record['who_process_paystatus'] == $staff['EmployeeCode'] ? 'selected' : '' ?>><?= htmlspecialchars($staff['fullname']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block font-semibold mb-1" for="wallet_debt_bill_to_acct">Wallet Debt Bill to Acct</label>
                <input type="text" id="wallet_debt_bill_to_acct" name="wallet_debt_bill_to_acct" value="<?= htmlspecialchars($record['wallet_debt_bill_to_acct']) ?>" class="w-full border border-gray-300 rounded p-2" />
            </div>
        </div>

        <div class="mt-8">
            <button type="submit" name="update" class="bg-black text-white px-6 py-3 rounded font-semibold hover:bg-gray-800 transition">Update Record</button>
            <a href="index.php" class="ml-4 text-gray-600 hover:underline">Back to List</a>
        </div>
    </form>
</body>

</html>