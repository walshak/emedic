
<?php include("../Connections/Conn.php"); ?>

<?php
session_start();

if (isset($_POST["apply_claim"])) {
  $setdate = date("Y-m-d");
  $hosp_no = $_POST['hosp_no'];
  $app_no = $_POST['app_no'];

  $updateSQL = "UPDATE patient_ap_services SET 
                  paystatus = :paystatus,
                  transact_date = :transact_date,
                  cr = :cr 
                WHERE 
                  hospital_no = :hospital_no AND 
                  invoice_status = :invoice_status AND 
                  paystatus = :paystatus_condition AND 
                  serv_group = :serv_group AND 
                  claim_amt > 0 AND 
                  pay = 0";

  $stmt = $db->prepare($updateSQL);
  $stmt->bindParam(':paystatus', $paystatus);
  $stmt->bindParam(':transact_date', $setdate);
  $stmt->bindParam(':cr', $cr);
  $stmt->bindParam(':hospital_no', $hosp_no);
  $stmt->bindParam(':invoice_status', $invoice_status);
  $stmt->bindParam(':paystatus_condition', $paystatus_condition);
  $stmt->bindParam(':serv_group', $serv_group);

  $paystatus = '1';
  $cr = '0';
  $invoice_status = '1';
  $paystatus_condition = '0';
  $serv_group = 'Pharmacy';

  $stmt->execute();

  header("Location: index.php?presc&hos_no=$hosp_no&ss");
  exit;
}
?>