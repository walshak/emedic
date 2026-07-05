<?php
// Enable error reporting (development only; disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database configuration
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Optional: debug posted data
        // echo "<pre>"; print_r($_POST); echo "</pre>";

        // Sanitize and fetch posted data
        $sn = $_POST['sn'];
        $app_no = isset($_POST['app_no']) ? $_POST['app_no'] : '';
        $hospital_no = isset($_POST['hospital_no']) ? $_POST['hospital_no'] : '';
        $serv_group = isset($_POST['serv_group']) ? $_POST['serv_group'] : '';
        $cat_type = isset($_POST['cat_type']) ? $_POST['cat_type'] : '';
        $dept_id = isset($_POST['dept_id']) ? $_POST['dept_id'] : '';
        $dept_dispensory_id = isset($_POST['dept_dispensory_id']) ? $_POST['dept_dispensory_id'] : '';
        $drug_sn = isset($_POST['drug_sn']) ? $_POST['drug_sn'] : '';
        $item_services = isset($_POST['item_services']) ? $_POST['item_services'] : '';
        $hosp_price = isset($_POST['hosp_price']) ? $_POST['hosp_price'] : 0;
        $claim_amt = isset($_POST['claim_amt']) ? $_POST['claim_amt'] : 0;
        $interest = isset($_POST['interest']) ? $_POST['interest'] : 0;
        $qty = isset($_POST['qty']) ? $_POST['qty'] : 0;
        $remarks = isset($_POST['remarks']) ? $_POST['remarks'] : '';
        $drug_status = isset($_POST['drug_status']) ? $_POST['drug_status'] : 0;
        $invoice_status = isset($_POST['invoice_status']) ? $_POST['invoice_status'] : 0;
        $invoice_no = isset($_POST['invoice_no']) ? $_POST['invoice_no'] : '';
        $invoice_date = isset($_POST['invoice_date']) ? $_POST['invoice_date'] : null;
        $invoice_by = isset($_POST['invoice_by']) ? $_POST['invoice_by'] : '';
        $prepared_by = isset($_POST['prepared_by']) ? $_POST['prepared_by'] : '';
        $created_by = isset($_POST['created_by']) ? $_POST['created_by'] : '';
        $dsp_by = isset($_POST['dsp_by']) ? $_POST['dsp_by'] : '';
        $date_entry = isset($_POST['date_entry']) ? $_POST['date_entry'] : null;
        $transact_date = isset($_POST['transact_date']) ? $_POST['transact_date'] : null;
        $pay = isset($_POST['pay']) ? $_POST['pay'] : 0;
        $pay_mode = isset($_POST['pay_mode']) ? $_POST['pay_mode'] : '';
        $paystatus = isset($_POST['paystatus']) ? $_POST['paystatus'] : 0;
        $process_claim = isset($_POST['process_claim']) ? $_POST['process_claim'] : 0;
        $cr = isset($_POST['cr']) ? $_POST['cr'] : 0;
        $discount = isset($_POST['discount']) ? $_POST['discount'] : 0;
        $add_charge = isset($_POST['add_charge']) ? $_POST['add_charge'] : 0;
        $prescription = isset($_POST['prescription']) ? $_POST['prescription'] : '';
        $dispensory_status_at_phamcy = isset($_POST['dispensory_status_at_phamcy']) ? $_POST['dispensory_status_at_phamcy'] : 0;
        $acct_billed_staff = isset($_POST['acct_billed_staff']) ? $_POST['acct_billed_staff'] : '';
        $acct_billed_ack = isset($_POST['acct_billed_ack']) ? $_POST['acct_billed_ack'] : 0;
        $claim_valid_by = isset($_POST['claim_valid_by']) ? $_POST['claim_valid_by'] : '';
        $payment_remarks = isset($_POST['payment_remarks']) ? $_POST['payment_remarks'] : '';
        $ledger_TX = isset($_POST['ledger_TX']) ? $_POST['ledger_TX'] : '';
        $wallet_payee = isset($_POST['wallet_payee']) ? $_POST['wallet_payee'] : '';
        $who_process_paystatus = isset($_POST['who_process_paystatus']) ? $_POST['who_process_paystatus'] : '';
        $wallet_debt_bill_to_acct = isset($_POST['wallet_debt_bill_to_acct']) ? $_POST['wallet_debt_bill_to_acct'] : '';

        // Prepare update query
        $sql = "UPDATE patient_ap_services SET
            app_no = :app_no,
            hospital_no = :hospital_no,
            serv_group = :serv_group,
            cat_type = :cat_type,
            dept_id = :dept_id,
            dept_dispensory_id = :dept_dispensory_id,
            drug_sn = :drug_sn,
            item_services = :item_services,
            hosp_price = :hosp_price,
            claim_amt = :claim_amt,
            interest = :interest,
            qty = :qty,
            remarks = :remarks,
            drug_status = :drug_status,
            invoice_status = :invoice_status,
            invoice_no = :invoice_no,
            invoice_date = :invoice_date,
            invoice_by = :invoice_by,
            prepared_by = :prepared_by,
            created_by = :created_by,
            dsp_by = :dsp_by,
            date_entry = :date_entry,
            transact_date = :transact_date,
            pay = :pay,
            pay_mode = :pay_mode,
            paystatus = :paystatus,
            process_claim = :process_claim,
            cr = :cr,
            discount = :discount,
            add_charge = :add_charge,
            prescription = :prescription,
            dispensory_status_at_phamcy = :dispensory_status_at_phamcy,
            acct_billed_staff = :acct_billed_staff,
            acct_billed_ack = :acct_billed_ack,
            claim_valid_by = :claim_valid_by,
            payment_remarks = :payment_remarks,
            ledger_TX = :ledger_TX,
            wallet_payee = :wallet_payee,
            who_process_paystatus = :who_process_paystatus,
            wallet_debt_bill_to_acct = :wallet_debt_bill_to_acct
            WHERE sn = :sn";

        $stmt = $db->prepare($sql);

        // Bind parameters
        $stmt->bindValue(':app_no', $app_no);
        $stmt->bindValue(':hospital_no', $hospital_no);
        $stmt->bindValue(':serv_group', $serv_group);
        $stmt->bindValue(':cat_type', $cat_type);
        $stmt->bindValue(':dept_id', $dept_id);
        $stmt->bindValue(':dept_dispensory_id', $dept_dispensory_id);
        $stmt->bindValue(':drug_sn', $drug_sn);
        $stmt->bindValue(':item_services', $item_services);
        $stmt->bindValue(':hosp_price', $hosp_price);
        $stmt->bindValue(':claim_amt', $claim_amt);
        $stmt->bindValue(':interest', $interest);
        $stmt->bindValue(':qty', $qty, PDO::PARAM_INT);
        $stmt->bindValue(':remarks', $remarks);
        $stmt->bindValue(':drug_status', $drug_status, PDO::PARAM_INT);
        $stmt->bindValue(':invoice_status', $invoice_status, PDO::PARAM_INT);
        $stmt->bindValue(':invoice_no', $invoice_no);
        $stmt->bindValue(':invoice_date', $invoice_date);
        $stmt->bindValue(':invoice_by', $invoice_by);
        $stmt->bindValue(':prepared_by', $prepared_by);
        $stmt->bindValue(':created_by', $created_by);
        $stmt->bindValue(':dsp_by', $dsp_by);
        $stmt->bindValue(':date_entry', $date_entry);
        $stmt->bindValue(':transact_date', $transact_date);
        $stmt->bindValue(':pay', $pay);
        $stmt->bindValue(':pay_mode', $pay_mode);
        $stmt->bindValue(':paystatus', $paystatus, PDO::PARAM_INT);
        $stmt->bindValue(':process_claim', $process_claim, PDO::PARAM_INT);
        $stmt->bindValue(':cr', $cr, PDO::PARAM_INT);
        $stmt->bindValue(':discount', $discount);
        $stmt->bindValue(':add_charge', $add_charge);
        $stmt->bindValue(':prescription', $prescription);
        $stmt->bindValue(':dispensory_status_at_phamcy', $dispensory_status_at_phamcy, PDO::PARAM_INT);
        $stmt->bindValue(':acct_billed_staff', $acct_billed_staff);
        $stmt->bindValue(':acct_billed_ack', $acct_billed_ack, PDO::PARAM_INT);
        $stmt->bindValue(':claim_valid_by', $claim_valid_by);
        $stmt->bindValue(':payment_remarks', $payment_remarks);
        $stmt->bindValue(':ledger_TX', $ledger_TX);
        $stmt->bindValue(':wallet_payee', $wallet_payee);
        $stmt->bindValue(':who_process_paystatus', $who_process_paystatus);
        $stmt->bindValue(':wallet_debt_bill_to_acct', $wallet_debt_bill_to_acct);
        $stmt->bindValue(':sn', $sn, PDO::PARAM_INT);

        // Execute query
        $stmt->execute();

        $rowCount = $stmt->rowCount();
        if ($rowCount > 0) {
            echo "✅ Record updated successfully.";
        } else {
            echo "⚠️ No changes were made (record not found or values unchanged).";
        }

        // Optional: redirect after success
        // header("Location: success.php");
        // exit;

    } catch (PDOException $e) {
        echo "❌ PDO Error: " . $e->getMessage();
    } catch (Exception $e) {
        echo "❌ General Error: " . $e->getMessage();
    }
}
