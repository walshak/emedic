<?php

if (isset($_POST["apply_set"])) {

  $hr_payroll = isset($_POST["hr_payroll"]) ? 1 : 0;
  $doc_income = isset($_POST["doc_income"]) ? 1 : 0;
  $rdc = isset($_POST["rdc"]) ? 1 : 0;
  $report_mgr = isset($_POST["report_mgr"]) ? 1 : 0;
  $price_mgr = isset($_POST["price_mgr"]) ? 1 : 0;
  $bed_mgr = isset($_POST["bed_mgr"]) ? 1 : 0;
  $accom_price = isset($_POST["accom_price"]) ? 1 : 0;
  $writeoff_discharge = isset($_POST["writeoff_discharge"]) ? 1 : 0;
  $see_statistic_rpt = isset($_POST["see_statistic_rpt"]) ? 1 : 0;
  $patient_master = isset($_POST["patient_master"]) ? 1 : 0;
  $create_edit_insur = isset($_POST["create_edit_insur"]) ? 1 : 0;
  $add_new_patient = isset($_POST["add_new_patient"]) ? 1 : 0;
  $convert_patient_insur = isset($_POST["convert_patient_insur"]) ? 1 : 0;
  $see_med_rpt = isset($_POST["see_med_rpt"]) ? 1 : 0;
  $see_patient_log = isset($_POST["see_patient_log"]) ? 1 : 0;
  $see_patient_tranc_writeoff = isset($_POST["see_patient_tranc_writeoff"]) ? 1 : 0;
  $admitted_patient_alert = isset($_POST["admitted_patient_alert"]) ? 1 : 0;
  $ext_sales = isset($_POST["ext_sales"]) ? 1 : 0;
  $biller = isset($_POST["biller"]) ? 1 : 0;
  $discount = isset($_POST["discount"]) ? 1 : 0;
  $voucher = isset($_POST["voucher"]) ? 1 : 0;
  $voucher_unit = isset($_POST["voucher_unit"]) ? 1 : 0;
  $webmedic_admin_right = isset($_POST["webmedic_admin_right"]) ? 1 : 0;
  $enquiry = isset($_POST["enquiry"]) ? 1 : 0;
  $stock_mgr = isset($_POST["stock_mgr"]) ? 1 : 0;
  $procure = isset($_POST["procure"]) ? 1 : 0;
  $stock_mgr_pharm = isset($_POST["stock_mgr_pharm"]) ? 1 : 0;
  $purchase_approval = isset($_POST["purchase_approval"]) ? 1 : 0;
  $rq_approval = isset($_POST["rq_approval"]) ? 1 : 0;
  $b4_rq_approval = isset($_POST["b4_rq_approval"]) ? 1 : 0;
  $gen_claim_rpt = isset($_POST["gen_claim_rpt"]) ? 1 : 0;
  $pharmacy = isset($_POST["pharmacy"]) ? 1 : 0;
  $nursing = isset($_POST["nursing"]) ? 1 : 0;
  $doctor = isset($_POST["doctor"]) ? 1 : 0;
  $remove_stock_qty = isset($_POST["remove_stock_qty"]) ? 1 : 0;
  $procedure_module = isset($_POST["procedure_module"]) ? 1 : 0;
  $dialysis = isset($_POST["dialysis"]) ? 1 : 0;
  $transplant = isset($_POST["transplant"]) ? 1 : 0;
  $PO_payment = isset($_POST["PO_payment"]) ? 1 : 0;
  $Procurement_Officer_ack = isset($_POST["Procurement_Officer_ack"]) ? 1 : 0;
  $print_med_report = isset($_POST["print_med_report"]) ? 1 : 0;
  $lab_image_unit = isset($_POST["lab_image_unit"]) ? 1 : 0;
  $backup_db = isset($_POST["backup_db"]) ? 1 : 0;
  $template_setup = isset($_POST["template_setup"]) ? 1 : 0;
  $delete_doc = isset($_POST["delete_doc"]) ? 1 : 0;

  $stmt = $db->prepare("SELECT username FROM admin_users_rights WHERE username = :username");
  $stmt->bindParam(':username', $_POST["username"], PDO::PARAM_STR);
  $stmt->execute();

  if ($stmt->rowCount() > 0) {

    // Step 1: Get current values for this username
    $username = $_POST["username"];
    $currentDataStmt = $db->prepare("SELECT * FROM admin_users_rights WHERE username = :username");
    $currentDataStmt->bindParam(':username', $username, PDO::PARAM_STR);
    $currentDataStmt->execute();
    $currentData = $currentDataStmt->fetch(PDO::FETCH_ASSOC);

    if (!$currentData) {
      die("User not found");
    }

    // Step 2: Fields to compare
    $fields = [
      "speciality",
      "hr_payroll",
      "doc_income",
      "rdc",
      "report_mgr",
      "price_mgr",
      "bed_mgr",
      "accom_price",
      "writeoff_discharge",
      "see_statistic_rpt",
      "patient_master",
      "create_edit_insur",
      "add_new_patient",
      "convert_patient_insur",
      "see_med_rpt",
      "see_patient_log",
      "see_patient_tranc_writeoff",
      "admitted_patient_alert",
      "ext_sales",
      "biller",
      "discount",
      "voucher",
      "voucher_unit",
      "webmedic_admin_right",
      "enquiry",
      "stock_mgr",
      "stock_mgr_pharm",
      "purchase_approval",
      "procure",
      "rq_approval",
      "b4_rq_approval",
      "gen_claim_rpt",
      "pharmacy",
      "nursing",
      "doctor",
      "remove_stock_qty",
      "procedure_module",
      "dialysis",
      "transplant",
      "PO_payment",
      "print_med_report",
      "Procurement_Officer_ack",
      "lab_image_unit",
      "backup_db",
      "template_setup",
      "delete_doc"
    ];

    // Step 3: Compare and collect changed columns
    $changedBy = isset($_SESSION['username']) ? $_SESSION['username'] : 'unknown';
    $changedColumns = [];
    $oldValues = [];
    $newValues = [];

    foreach ($fields as $field) {
      $old = $currentData[$field];
      $new = isset($_POST[$field]) ? $_POST[$field] : 0;

      if ((string)$old !== (string)$new) {
        $changedColumns[] = $field;
        $oldValues[$field] = $old;
        $newValues[$field] = $new;
      }
    }

    // Step 4: If any changes, insert one log row
    $table_name = "ADMIN";
    if (!empty($changedColumns)) {
      $logStmt = $db->prepare("
        INSERT INTO admin_users_rights_log
        (username, changed_field, old_value, new_value, changed_by, table_name, changed_at)
        VALUES (:username, :changed_fields, :old_values, :new_values, :changed_by,:table_name, NOW())
      ");

      $logStmt->execute([
        ':username'       => $username,
        ':changed_fields' => implode(',', $changedColumns),
        ':old_values'     => json_encode($oldValues),
        ':new_values'     => json_encode($newValues),
        ':changed_by'     => $changedBy,
        ':table_name'     => $table_name
      ]);
    }
    // Step 5: Update the main record
    // Your update code here...
















    $stmt = $db->prepare("UPDATE admin_users_rights SET 
  speciality = :speciality,
  hr_payroll = :hr_payroll,
  doc_income = :doc_income,
  rdc = :rdc,
  report_mgr = :report_mgr,
  price_mgr = :price_mgr,
  bed_mgr = :bed_mgr,
  accom_price = :accom_price,
  writeoff_discharge = :writeoff_discharge,
  see_statistic_rpt = :see_statistic_rpt,
  patient_master = :patient_master,
  create_edit_insur = :create_edit_insur,
  add_new_patient = :add_new_patient,
  convert_patient_insur = :convert_patient_insur,
  see_med_rpt = :see_med_rpt,
  see_patient_log = :see_patient_log,
  see_patient_tranc_writeoff = :see_patient_tranc_writeoff,
  admitted_patient_alert = :admitted_patient_alert,
  ext_sales = :ext_sales,
  biller = :biller,
  discount = :discount,
  voucher = :voucher,
  voucher_unit = :voucher_unit,
  webmedic_admin_right = :webmedic_admin_right,
  enquiry = :enquiry,
  stock_mgr = :stock_mgr,
  stock_mgr_pharm = :stock_mgr_pharm,
  purchase_approval = :purchase_approval,
  procure = :procure,
  rq_approval = :rq_approval,
  b4_rq_approval = :b4_rq_approval,
  gen_claim_rpt = :gen_claim_rpt,
  pharmacy = :pharmacy,
  nursing = :nursing,
  doctor = :doctor,
  remove_stock_qty = :remove_stock_qty,
  procedure_module = :procedure_module,
  dialysis = :dialysis,
  transplant = :transplant,
  PO_payment = :PO_payment,
  print_med_report = :print_med_report,
  Procurement_Officer_ack = :Procurement_Officer_ack,
  lab_image_unit = :lab_image_unit,
  backup_db = :backup_db,
  template_setup = :template_setup,
  delete_doc = :delete_doc
  WHERE username = :username");

    $stmt->bindParam(':speciality', $_POST["speciality"], PDO::PARAM_STR);
    $stmt->bindParam(':hr_payroll', $hr_payroll, PDO::PARAM_INT);
    $stmt->bindParam(':doc_income', $doc_income, PDO::PARAM_INT);
    $stmt->bindParam(':rdc', $rdc, PDO::PARAM_INT);
    $stmt->bindParam(':report_mgr', $report_mgr, PDO::PARAM_INT);
    $stmt->bindParam(':price_mgr', $price_mgr, PDO::PARAM_INT);
    $stmt->bindParam(':bed_mgr', $bed_mgr, PDO::PARAM_INT);
    $stmt->bindParam(':accom_price', $accom_price, PDO::PARAM_INT);
    $stmt->bindParam(':writeoff_discharge', $writeoff_discharge, PDO::PARAM_INT);
    $stmt->bindParam(':see_statistic_rpt', $see_statistic_rpt, PDO::PARAM_INT);
    $stmt->bindParam(':patient_master', $patient_master, PDO::PARAM_INT);
    $stmt->bindParam(':create_edit_insur', $create_edit_insur, PDO::PARAM_INT);
    $stmt->bindParam(':add_new_patient', $add_new_patient, PDO::PARAM_INT);
    $stmt->bindParam(':convert_patient_insur', $convert_patient_insur, PDO::PARAM_INT);
    $stmt->bindParam(':see_med_rpt', $see_med_rpt, PDO::PARAM_INT);
    $stmt->bindParam(':see_patient_log', $see_patient_log, PDO::PARAM_INT);
    $stmt->bindParam(':see_patient_tranc_writeoff', $see_patient_tranc_writeoff, PDO::PARAM_INT);
    $stmt->bindParam(':admitted_patient_alert', $admitted_patient_alert, PDO::PARAM_INT);
    $stmt->bindParam(':ext_sales', $ext_sales, PDO::PARAM_INT);
    $stmt->bindParam(':biller', $biller, PDO::PARAM_INT);
    $stmt->bindParam(':discount', $discount, PDO::PARAM_INT);
    $stmt->bindParam(':voucher', $voucher, PDO::PARAM_INT);
    $stmt->bindParam(':voucher_unit', $voucher_unit, PDO::PARAM_INT);
    $stmt->bindParam(':webmedic_admin_right', $webmedic_admin_right, PDO::PARAM_INT);
    $stmt->bindParam(':enquiry', $enquiry, PDO::PARAM_INT);
    $stmt->bindParam(':stock_mgr', $stock_mgr, PDO::PARAM_INT);
    $stmt->bindParam(':stock_mgr_pharm', $stock_mgr_pharm, PDO::PARAM_INT);
    $stmt->bindParam(':purchase_approval', $purchase_approval, PDO::PARAM_INT);
    $stmt->bindParam(':procure', $procure, PDO::PARAM_INT);
    $stmt->bindParam(':rq_approval', $rq_approval, PDO::PARAM_INT);
    $stmt->bindParam(':b4_rq_approval', $b4_rq_approval, PDO::PARAM_INT);
    $stmt->bindParam(':gen_claim_rpt', $gen_claim_rpt, PDO::PARAM_INT);
    $stmt->bindParam(':pharmacy', $pharmacy, PDO::PARAM_INT);
    $stmt->bindParam(':nursing', $nursing, PDO::PARAM_INT);
    $stmt->bindParam(':doctor', $doctor, PDO::PARAM_INT);
    $stmt->bindParam(':remove_stock_qty', $remove_stock_qty, PDO::PARAM_INT);
    $stmt->bindParam(':procedure_module', $procedure_module, PDO::PARAM_INT);
    $stmt->bindParam(':dialysis', $dialysis, PDO::PARAM_INT);
    $stmt->bindParam(':transplant', $transplant, PDO::PARAM_INT);
    $stmt->bindParam(':PO_payment', $PO_payment, PDO::PARAM_INT);
    $stmt->bindParam(':print_med_report', $print_med_report, PDO::PARAM_INT);
    $stmt->bindParam(':Procurement_Officer_ack', $Procurement_Officer_ack, PDO::PARAM_INT);
    $stmt->bindParam(':lab_image_unit', $lab_image_unit, PDO::PARAM_INT);
    $stmt->bindParam(':backup_db', $backup_db, PDO::PARAM_INT);
    $stmt->bindParam(':template_setup', $template_setup, PDO::PARAM_INT);
    $stmt->bindParam(':delete_doc', $delete_doc, PDO::PARAM_INT);
    $stmt->bindParam(':username', $_POST["username"], PDO::PARAM_STR);
    $stmt->execute();
?>

    <div class="alert alert-success">Saved Successfully
    </div>
  <?php

  } else {
    ///echo ';;;;';
    $stmt = $db->prepare("INSERT INTO admin_users_rights (
  username,
  speciality,
  hr_payroll,
  doc_income,
  rdc,
  report_mgr,
  price_mgr,
  bed_mgr,
  accom_price,
  writeoff_discharge,
  see_statistic_rpt,
  patient_master,
  create_edit_insur,
  add_new_patient,
  convert_patient_insur,
  see_med_rpt,
  see_patient_log,
  see_patient_tranc_writeoff,
  admitted_patient_alert,
  ext_sales,
  biller,
  discount,
  voucher,
  voucher_unit,
  webmedic_admin_right,
  enquiry,
  stock_mgr,
  stock_mgr_pharm,
  purchase_approval,
  procure,
  rq_approval,
  b4_rq_approval,
  gen_claim_rpt,
  pharmacy,
  nursing,
  doctor,
  remove_stock_qty,
  procedure_module,
  dialysis,
  transplant,
  PO_payment,
  print_med_report,
  Procurement_Officer_ack,
  lab_image_unit,
  backup_db,
  template_setup,
  delete_doc
) VALUES (
  :username,
  :speciality,
  :hr_payroll,
  :doc_income,
  :rdc,
  :report_mgr,
  :price_mgr,
  :bed_mgr,
  :accom_price,
  :writeoff_discharge,
  :see_statistic_rpt,
  :patient_master,
  :create_edit_insur,
  :add_new_patient,
  :convert_patient_insur,
  :see_med_rpt,
  :see_patient_log,
  :see_patient_tranc_writeoff,
  :admitted_patient_alert,
  :ext_sales,
  :biller,
  :discount,
  :voucher,
  :voucher_unit,
  :webmedic_admin_right,
  :enquiry,
  :stock_mgr,
  :stock_mgr_pharm,
  :purchase_approval,
  :procure,
  :rq_approval,
  :b4_rq_approval,
  :gen_claim_rpt,
  :pharmacy,
  :nursing,
  :doctor,
  :remove_stock_qty,
  :procedure_module,
  :dialysis,
  :transplant,
  :PO_payment,
  :print_med_report,
  :Procurement_Officer_ack,
  :lab_image_unit,
  :backup_db,
  :template_setup,
  :delete_doc
)");

    $stmt->bindParam(':username', $_POST["username"], PDO::PARAM_STR);
    $stmt->bindParam(':speciality', $_POST["speciality"], PDO::PARAM_STR);
    $stmt->bindParam(':hr_payroll', $hr_payroll, PDO::PARAM_INT);
    $stmt->bindParam(':doc_income', $doc_income, PDO::PARAM_INT);
    $stmt->bindParam(':rdc', $rdc, PDO::PARAM_INT);
    $stmt->bindParam(':report_mgr', $report_mgr, PDO::PARAM_INT);
    $stmt->bindParam(':price_mgr', $price_mgr, PDO::PARAM_INT);
    $stmt->bindParam(':bed_mgr', $bed_mgr, PDO::PARAM_INT);
    $stmt->bindParam(':accom_price', $accom_price, PDO::PARAM_INT);
    $stmt->bindParam(':writeoff_discharge', $writeoff_discharge, PDO::PARAM_INT);
    $stmt->bindParam(':see_statistic_rpt', $see_statistic_rpt, PDO::PARAM_INT);
    $stmt->bindParam(':patient_master', $patient_master, PDO::PARAM_INT);
    $stmt->bindParam(':create_edit_insur', $create_edit_insur, PDO::PARAM_INT);
    $stmt->bindParam(':add_new_patient', $add_new_patient, PDO::PARAM_INT);
    $stmt->bindParam(':convert_patient_insur', $convert_patient_insur, PDO::PARAM_INT);
    $stmt->bindParam(':see_med_rpt', $see_med_rpt, PDO::PARAM_INT);
    $stmt->bindParam(':see_patient_log', $see_patient_log, PDO::PARAM_INT);
    $stmt->bindParam(':see_patient_tranc_writeoff', $see_patient_tranc_writeoff, PDO::PARAM_INT);
    $stmt->bindParam(':admitted_patient_alert', $admitted_patient_alert, PDO::PARAM_INT);
    $stmt->bindParam(':ext_sales', $ext_sales, PDO::PARAM_INT);
    $stmt->bindParam(':biller', $biller, PDO::PARAM_INT);
    $stmt->bindParam(':discount', $discount, PDO::PARAM_INT);
    $stmt->bindParam(':voucher', $voucher, PDO::PARAM_INT);
    $stmt->bindParam(':voucher_unit', $voucher_unit, PDO::PARAM_INT);
    $stmt->bindParam(':webmedic_admin_right', $webmedic_admin_right, PDO::PARAM_INT);
    $stmt->bindParam(':enquiry', $enquiry, PDO::PARAM_INT);
    $stmt->bindParam(':stock_mgr', $stock_mgr, PDO::PARAM_INT);
    $stmt->bindParam(':stock_mgr_pharm', $stock_mgr_pharm, PDO::PARAM_INT);
    $stmt->bindParam(':purchase_approval', $purchase_approval, PDO::PARAM_INT);
    $stmt->bindParam(':procure', $procure, PDO::PARAM_INT);
    $stmt->bindParam(':rq_approval', $rq_approval, PDO::PARAM_INT);
    $stmt->bindParam(':b4_rq_approval', $b4_rq_approval, PDO::PARAM_INT);
    $stmt->bindParam(':gen_claim_rpt', $gen_claim_rpt, PDO::PARAM_INT);
    $stmt->bindParam(':pharmacy', $pharmacy, PDO::PARAM_INT);
    $stmt->bindParam(':nursing', $nursing, PDO::PARAM_INT);
    $stmt->bindParam(':doctor', $doctor, PDO::PARAM_INT);
    $stmt->bindParam(':remove_stock_qty', $remove_stock_qty, PDO::PARAM_INT);
    $stmt->bindParam(':procedure_module', $procedure_module, PDO::PARAM_INT);
    $stmt->bindParam(':dialysis', $dialysis, PDO::PARAM_INT);
    $stmt->bindParam(':transplant', $transplant, PDO::PARAM_INT);
    $stmt->bindParam(':PO_payment', $PO_payment, PDO::PARAM_INT);
    $stmt->bindParam(':print_med_report', $print_med_report, PDO::PARAM_INT);
    $stmt->bindParam(':Procurement_Officer_ack', $Procurement_Officer_ack, PDO::PARAM_INT);
    $stmt->bindParam(':lab_image_unit', $lab_image_unit, PDO::PARAM_INT);
    $stmt->bindParam(':backup_db', $backup_db, PDO::PARAM_INT);
    $stmt->bindParam(':template_setup', $template_setup, PDO::PARAM_INT);
    $stmt->bindParam(':delete_doc', $delete_doc, PDO::PARAM_INT);
    $stmt->execute();
  ?>
    <div class="alert alert-success">Saved Successfully
    </div>

<?php

  }
}

?>


<div class="row">
  <div class="col-lg-12">
    <div class="ibox float-e-margins">
      <div class="ibox-title">
        <h5>General Rights</h5>
      </div>

      <div class="ibox-content">

        <form action="index.php?min" method="POST">
          <div class="row">
            <div class="col-md-8">


              <div class="form_sep">
                <label for="reg_input_no" class="req">Select a User & Setup Rights</label>
                <select name="staff_id" class="input-sm chosen-select" style="width:350px;">
                  <option selected="selected" value="">Search and Select Staff</option>
                  <?php $stmt = $db->query("
						SELECT 
						u.id, h.FirstName, h.MiddleName, h.LastName , h.Cadre 
						FROM admin_users as u 
						inner join hremp as h on h.EmployeeCode=u.EmployeeCode 
						WHERE u.status='1' order by FirstName");
                  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                    <option value="<?php echo $row["id"]; ?>"><?php echo $row["FirstName"] . ' ' . $row["LastName"] . ' ' . $row["MiddleName"] . ' (' . $row["Cadre"] . ')'; ?></option>
                  <?php } ?>
                </select>

              </div>
              <div class="form_sep">
                <button type="submit" class="btn btn-success btn btn-sm" autofocus name="apply_">Apply Search</button>
              </div>

            </div>

            <div class="col-md-4">
              <label for="reg_input_no" class="">.</label><br>
              <a rel="" href="index.php?rit" class="btn btn-danger btn btn-sm"><i class="fa fa-times"></i>&nbsp;Close</a>
            </div>
          </div>
        </form>



        <hr>
        <?php if (isset($_POST['apply_'])) {
          $stmt = $db->prepare("SELECT fullname, username FROM admin_users WHERE id = :id");
          $stmt->bindParam(':id', $_POST['staff_id'], PDO::PARAM_STR);
          $stmt->execute();
          $row = $stmt->fetch(PDO::FETCH_ASSOC);

        ?>

          <form action="index.php?min" method="POST">

            <div class="alert alert-success">
              <label for="" class="">Current User : <?php echo $row['fullname'] . ' / ' . $row['username']; ?></label>
              <input type="hidden" name="username" value="<?php echo $row['username']; ?>">
            </div>


            <table class="table table-striped table-bordered table-hover">
              <thead>
                <tr>
                  <th width="5%" data-toggle="true">&nbsp;</th>
                  <th width="46%" data-toggle="true">Rights</th>
                  <th width="5%" data-toggle="true">&nbsp;</th>
                  <th width="44%" data-toggle="true">Rights</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $stmt = $db->prepare("SELECT * FROM admin_users_rights WHERE username = :username");
                $stmt->bindParam(':username', $row['username'], PDO::PARAM_STR);
                $stmt->execute();
                $row2 = $stmt->fetch(PDO::FETCH_ASSOC);

                ?>
                <tr>
                  <td>&nbsp;</td>
                  <td><strong>FRONT DESK </strong></td>
                  <td>&nbsp;</td>
                  <td><strong>CLAIM MANAGER</strong></td>
                </tr>
                <tr>
                  <td><input type="checkbox" name="enquiry" value="1" <?php if ($row2['enquiry'] == 1) { ?> checked <?php } ?>></td>
                  <td>Patient Enquiry</td>
                  <td><input type="checkbox" name="convert_patient_insur" value="1" <?php if ($row2['convert_patient_insur'] == 1) { ?> checked <?php } ?>></td>
                  <td>Convert Patient Insurance Status (Private to Insurance/Insurance to Private)</td>
                </tr>
                <tr>
                  <td><input type="checkbox" name="add_new_patient" value="1" <?php if ($row2['add_new_patient'] == 1) { ?> checked <?php } ?>></td>
                  <td>Add New/Edit Patient Bio-Data</td>
                  <td><input type="checkbox" name="create_edit_insur" value="1" <?php if ($row2['create_edit_insur'] == 1) { ?> checked <?php } ?>></td>
                  <td>Add New/Edit NHIS/PHIS/Corporate/Family Folder/Private Entity</td>
                </tr>
                <tr>
                  <td><input type="checkbox" name="patient_master" value="1" <?php if ($row2['patient_master'] == 1) { ?> checked <?php } ?>></td>
                  <td>View Patients Master Manager</td>
                  <td><input type="checkbox" name="gen_claim_rpt" value="1" <?php if ($row2['gen_claim_rpt'] == 1) { ?> checked <?php } ?>></td>
                  <td>Prepare Claims Report for Corporate and Insurance</td>
                </tr>
                <tr>
                  <td><input type="checkbox" name="rdc" value="1" <?php if ($row2['rdc'] == 1) { ?> checked <?php } ?>></td>
                  <td>View Investigation Module<i class="fa fa-tick"></i></td>
                  <td><input type="checkbox" name="print_med_report" value="1" <?php if ($row2['print_med_report'] == 1) { ?> checked <?php } ?>></td>
                  <td>View and Print Medical Report<i class="fa fa-tick"></i></td>

                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td><strong>BILLING / BILLER BOOTH</strong></td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td><input type="checkbox" name="biller" value="1" <?php if ($row2['biller'] == 1) { ?> checked <?php } ?>></td>
                  <td>Access Biller from here</td>
                  <td><input type="checkbox" name="ext_sales" value="1" <?php if ($row2['ext_sales'] == 1) { ?> checked <?php } ?>></td>
                  <td>External Sales / Biller/Cashier</td>
                </tr>
                <tr>
                  <td><input type="checkbox" name="admitted_patient_alert" value="1" <?php if ($row2['admitted_patient_alert'] == 1) { ?> checked <?php } ?>></td>
                  <td>See Admitted Patient Credits/Accounts</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td><strong>STORE / PAHARMACY / ACCOUNTANT PART OF STORE</strong></td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td><input type="checkbox" name="stock_mgr" value="1" <?php if ($row2['stock_mgr'] == 1) { ?> checked <?php } ?>></td>
                  <td>General Store Manager<i class="fa fa-exclamation-triangle" style="color: red;"></i></td>
                  <td><input type="checkbox" name="purchase_approval" value="1" <?php if ($row2['purchase_approval'] == 1) { ?> checked <?php } ?>></td>
                  <td>Purchase Order(s) Final Approval Right Before the Accountant Can Post/Make Payment<i class="fa fa-exclamation-triangle" style="color: red;"></i></td>
                </tr>

                <tr>
                  <td><input type="checkbox" name="rq_approval" value="1" <?php if ($row2['rq_approval'] == 1) { ?> checked <?php } ?>></td>
                  <td>Store Department/Unit Requisition <b>Issuance</b> Rights </td>
                  <td><input type="checkbox" name="remove_stock_qty" value="1" <?php if ($row2['remove_stock_qty'] == 1) { ?> checked <?php } ?>></td>
                  <td>Removed Stock Qty from Inventory<i class="fa fa-exclamation-triangle" style="color: red;"></i></td>
                </tr>

                <tr>
                  <td><input type="checkbox" name="b4_rq_approval" value="1" <?php if ($row2['b4_rq_approval'] == 1) { ?> checked <?php } ?>></td>
                  <td>Store Department/Unit Requisition <b>Approval</b> Rights</td>
                  <td></td>
                  <td></td>
                </tr>

                <tr>
                  <td><input type="checkbox" name="procure" value="1" <?php if ($row2['procure'] == 1) { ?> checked <?php } ?>></td>
                  <td>Store Officer Data Entry After PO Approval<b style="color:red;">(Who to Enter in the Software When a Vendor Delivers an Item)</b></td>
                  <td><input type="checkbox" name="stock_mgr_pharm" value="1" <?php if ($row2['stock_mgr_pharm'] == 1) { ?> checked <?php } ?>></td>
                  <td><strong style="color: brown; ">Pharmacy Store Manager</strong><i class="fa fa-exclamation-triangle" style="color: red;"></i></td>
                </tr>
                <tr>
                  <td><input type="checkbox" name="PO_payment" value="1" <?php if ($row2['PO_payment'] == 1) { ?> checked <?php } ?>></td>
                  <td>Make Purchase Order Payment <b style="color:red;">(Accountant)</b></td>
                  <td><input type="checkbox" name="" value=""></td>
                  <td><strong style="color: brown; "></strong></td>
                </tr>
                <tr>
                  <td><input type="checkbox" name="Procurement_Officer_ack" value="1" <?php if ($row2['Procurement_Officer_ack'] == 1) { ?> checked <?php } ?>></td>
                  <td>Procurement Officer Acknowledgment<b style="color:red;">(Purchase Manager/Procurement Officer )</b></td>
                  <td><input type="checkbox" name="" value=""></td>
                  <td><strong style="color: brown; "></strong></td>
                </tr>

                <tr>
                  <td>&nbsp;</td>
                  <td><strong>MD/CEO/ADMIN RIGHTS<br>
                    </strong></td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td><input type="checkbox" name="hr_payroll" value="1" <?php if ($row2['hr_payroll'] == 1) { ?> checked <?php } ?>></td>
                  <td>HR / PAYROLL <i class="fa fa-exclamation-triangle" style="color: red;"></i></td>
                  <td><input type="checkbox" name="doc_income" value="1" <?php if ($row2['doc_income'] == 1) { ?> checked <?php } ?>></td>
                  <td>See Doctor's Income Generated<i class="fa fa-exclamation-triangle" style="color: red;"></i></td>
                </tr>

                <tr>
                  <td><input type="checkbox" name="price_mgr" value="1" <?php if ($row2['price_mgr'] == 1) { ?> checked <?php } ?>></td>
                  <td>Add/Edit Prices for Items and Services <i class="fa fa-exclamation-triangle" style="color: red;"></i></td>
                  <td><input type="checkbox" name="report_mgr" value="1" <?php if ($row2['report_mgr'] == 1) { ?> checked <?php } ?>></td>
                  <td>View All Financial Transactions <i class="fa fa-exclamation-triangle" style="color: red;"></i></td>
                </tr>

                <tr>
                  <td><input type="checkbox" name="accom_price" value="1" <?php if ($row2['accom_price'] == 1) { ?> checked <?php } ?>></td>
                  <td>Add/Edit Accommodation Price <i class="fa fa-exclamation-triangle" style="color: red;"></i></td>
                  <td><input type="checkbox" name="writeoff_discharge" value="1" <?php if ($row2['writeoff_discharge'] == 1) { ?> checked <?php } ?>></td>
                  <td>Write-off and Discharge Credits from Bed Manager<i class="fa fa-exclamation-triangle" style="color: red;"></i></td>
                </tr>

                <tr>
                  <td><input type="checkbox" name="bed_mgr" value="1" <?php if ($row2['bed_mgr'] == 1) { ?> checked <?php } ?>></td>
                  <td>Accommodation/Bed Manager <i class="fa fa-exclamation-triangle" style="color: red;"></i></td>
                  <td><input type="checkbox" name="see_patient_tranc_writeoff" value="1" <?php if ($row2['see_patient_tranc_writeoff'] == 1) { ?> checked <?php } ?>></td>
                  <td>See Patient transaction and writeoff/Delete double entries/Reverse<i class="fa fa-exclamation-triangle" style="color: red;"></i></td>
                </tr>

                <tr>
                  <td><input type="checkbox" name="see_statistic_rpt" value="1" <?php if ($row2['see_statistic_rpt'] == 1) { ?> checked <?php } ?>></td>
                  <td>View Statistics of patient data <i class="fa fa-exclamation-triangle" style="color: red;"></i></td>
                  <td><input type="checkbox" name="discount" value="1" <?php if ($row2['discount'] == 1) { ?> checked <?php } ?>></td>
                  <td>Generate Discount for patients <i class="fa fa-exclamation-triangle" style="color: red;"></i></td>
                </tr>

                <tr>
                  <td><input type="checkbox" name="see_patient_log" value="1" <?php if ($row2['see_patient_log'] == 1) { ?> checked <?php } ?>></td>
                  <td>See Patient Logs<i class="fa fa-exclamation-triangle" style="color: red;"></i></td>
                  <td><input type="checkbox" name="see_med_rpt" value="1" <?php if ($row2['see_med_rpt'] == 1) { ?> checked <?php } ?>></td>
                  <td>View Patient Consultation Notes<i class="fa fa-exclamation-triangle" style="color: red;"></i></td>
                </tr>

                <tr>

                  <td><input type="checkbox" name="voucher_unit" value="1" <?php if ($row2['voucher_unit'] == 1) { ?> checked <?php } ?>></td>
                  <td>Generate Voucher for Patient under my Unit only<i class="fa fa-exclamation-triangle" style="color: red;"></i></td>

                  <td><input type="checkbox" name="voucher" value="1" <?php if ($row2['voucher'] == 1) { ?> checked <?php } ?>></td>
                  <td>Generate Voucher for Patient (All Units)<i class="fa fa-exclamation-triangle" style="color: red;"></i></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>

                </tr>

                <tr>
                  <td><input type="checkbox" name="pharmacy" value="1" <?php if ($row2['pharmacy'] == 1) { ?> checked <?php } ?>></td>
                  <td>Access Pharmacy Department (<strong>MD Rights</strong>)<i class="fa fa-exclamation-triangle" style="color: red;"></i></td>

                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>

                <tr>
                  <td><input type="checkbox" name="doctor" value="1" <?php if ($row2['doctor'] == 1) { ?> checked <?php } ?>></td>
                  <td>Access Doctor Consultation (<strong>MD Rights</strong>)<i class="fa fa-exclamation-triangle" style="color: red;"></i></td>

                  <td><input type="checkbox" name="webmedic_admin_right" value="1" <?php if ($row2['webmedic_admin_right'] == 1) { ?> checked <?php } ?>></td>
                  <td>Assign rights to webmedic users <strong>(Super Admin Rights)</strong><i class="fa fa-exclamation-triangle" style="color: red;"></i></td>

                </tr>

                <tr>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td><input type="checkbox" name="nursing" value="1" <?php if ($row2['nursing'] == 1) { ?> checked <?php } ?>></td>
                  <td>Nursing Station (<strong>MD Rights</strong>)<i class="fa fa-exclamation-triangle" style="color: red;"></i></td>

                  <td>&nbsp;</td>
                  <td><strong>Select the options for Non-Medical Staff:-</strong></td>

                </tr>
                <tr>
                  <td><input type="checkbox" name="lab_image_unit" value="1" <?php if ($row2['lab_image_unit'] == 1) { ?> checked <?php } ?>></td>
                  <td>Lab and Radiology (Navigation) (<strong>MD Rights</strong>)<i class="fa fa-exclamation-triangle" style="color: red;"></i></td>

                  <td><input type="checkbox" name="transplant" value="1" <?php if ($row2['transplant'] == 1) { ?> checked <?php } ?>></td>
                  <td>Transplant<i class="fa fa-exclamation-triangle" style="color: red;"></i></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td><input type="checkbox" name="dialysis" value="1" <?php if ($row2['dialysis'] == 1) { ?> checked <?php } ?>></td>
                  <td>Dialysis<i class="fa fa-exclamation-triangle" style="color: red;"></i></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td><input type="checkbox" name="procedure_module" value="1" <?php if ($row2['procedure_module'] == 1) { ?> checked <?php } ?>></td>
                  <td>Procedure/Theatre<i class="fa fa-exclamation-triangle" style="color: red;"></i> </td>
                </tr>


                <tr>

                  <td><input type="checkbox" name="delete_doc" value="1" <?php if ($row2['delete_doc'] == 1) { ?> checked <?php } ?>></td>
                  <td>Delete document uploads after the period exceeds<i class="fa fa-exclamation-triangle" style="color: red;"></i> </td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>

                <tr>

                  <td><input type="checkbox" name="backup_db" value="1" <?php if ($row2['backup_db'] == 1) { ?> checked <?php } ?>></td>
                  <td>IT Staff (DB Backup )<i class="fa fa-exclamation-triangle" style="color: red;"></i> </td>

                  <td><input type="checkbox" name="template_setup" value="1" <?php if ($row2['template_setup'] == 1) { ?> checked <?php } ?>></td>
                  <td>Template Setup (IT Staff Control)<i class="fa fa-exclamation-triangle" style="color: red;"></i> </td>
                </tr>

              </tbody>
            </table>
            <div class="form_sep">
              <label for="reg_input_no" class="req">Select User Type</label>
              <select name="speciality" id="speciality" class="form-control" required>

                <?php if ($row2['speciality'] == '') { ?>
                  <option selected="selected" value="">Select ...</option>
                <?php } else { ?>
                  <option selected="selected" value="<?php echo $row2['speciality'] ?>"><?php echo $row2['speciality'] ?></option>
                <?php } ?>

                <option value="Administrator">User/Staff</option>
                <!-- <option value="User">User/Staff</option> -->
              </select>
            </div>

            <div class="form_sep">
              <button class="btn btn-primary" type="submit" name="apply_set">Apply</button>
            </div>

          </form>

        <?php } else { ?>

          <div class="alert alert-warning">
            <p>Select User from List above to Display Here</p>
          </div>


        <?php } ?>

      </div>

    </div>
  </div>



</div>