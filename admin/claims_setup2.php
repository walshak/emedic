<?php
if (isset($_POST['post_claims']) && !empty($_REQUEST['inv'])) {

    $adm_status      = $_POST['adm_status'];
    $special_package = $_POST['special_package'];
    $hos_no          = $_POST['hos_no'];
    $from            = $_POST["from"];
    $to              = $_POST["to"];
    $from_where      = $_POST["cpt"];
    $setdate         = date("Y-m-d");
    $transc_date     = date("Y-m-d H:i:s");

    $pro_inv = $_REQUEST['inv'];

    // Default values
    $paystatus      = 1;
    $invoice_status = 1;
    $claim_valid_by = $_SESSION['fullname'] . '/<br>' . date('d-m-y h:i:s a');

    try {
        $db->beginTransaction();

        foreach ($pro_inv as $inv_id) {

            list($sn) = explode("__", $inv_id);

            // Get service group
            $stmtChk = $db->prepare("SELECT serv_group FROM patient_ap_services WHERE hospital_no = :hn AND sn = :sn LIMIT 1");
            $stmtChk->execute(array(':hn' => $hos_no, ':sn' => $sn));
            $serv = $stmtChk->fetch(PDO::FETCH_ASSOC);
            $serv_group = isset($serv['serv_group']) ? $serv['serv_group'] : '';

            // SPECIAL PACKAGE
            if ($special_package == 'special_package') {

                $pay_mode = 'spkage';

                $sql = "UPDATE patient_ap_services
						SET paystatus      = :paystatus,
							invoice_status = :invoice_status,
							transact_date  = :transact_date,
							claim_valid_by = :claim_valid_by,
							pay_mode       = :pay_mode
						WHERE hospital_no = :hospital_no
						  AND sn          = :sn
						  AND paystatus   = 0";

                $stmt = $db->prepare($sql);
                $stmt->execute(array(
                    ':paystatus'      => $paystatus,
                    ':invoice_status' => $invoice_status,
                    ':transact_date'  => $transc_date,
                    ':claim_valid_by' => $claim_valid_by,
                    ':pay_mode'       => $pay_mode,
                    ':hospital_no'    => $hos_no,
                    ':sn'             => $sn
                ));
            } else {

                // NORMAL CLAIM RULES
                $process_claim = 1;

                // If Pharmacy → override logic
                if (strtolower($serv_group) == 'pharmacy') { // convert low		
                    $ph_paystatus      = 0;
                    $ph_invoice_status = 0;
                    $invoice_by_val    = null;
                    $invoice_date_val  = null;
                } else {
                    $ph_paystatus      = $paystatus;
                    $ph_invoice_status = $invoice_status;
                    $invoice_by_val    = $_SESSION['fullname'];
                    $invoice_date_val  = $transc_date;
                }

                $sql = "UPDATE patient_ap_services SET 
							paystatus      = :paystatus,
							invoice_status = :invoice_status,
							process_claim  = :process_claim,
							transact_date  = :transact_date,
							claim_valid_by = :claim_valid_by,
							invoice_by     = :invoice_by,
							invoice_date   = :invoice_date
						WHERE hospital_no = :hospital_no 
						  AND sn          = :sn
						  AND claim_amt > 0 
						  AND pay = 0";

                $stmt = $db->prepare($sql);
                $stmt->execute(array(
                    ':paystatus'      => $ph_paystatus,
                    ':invoice_status' => $ph_invoice_status,
                    ':process_claim'  => $process_claim,
                    ':transact_date'  => $transc_date,
                    ':claim_valid_by' => $claim_valid_by,
                    ':invoice_by'     => $invoice_by_val,
                    ':invoice_date'   => $invoice_date_val,
                    ':hospital_no'    => $hos_no,
                    ':sn'             => $sn
                ));
            }
        }

        $db->commit();
        echo "<script>alert('Claims processed successfully');</script>";
    } catch (Exception $e) {
        $db->rollBack();
        echo "ERROR PROCESSING CLAIM: " . $e->getMessage();
    }
}


if (isset($_GET['special_package']) and isset($_GET['status'])) {
    $special_package = $_GET['special_package'];

    if ($_GET['status'] == 0) {
        $my_status = 1;
    } else {
        $my_status = 0;
    }
    $updateSQL = "UPDATE special_package_booking SET status='$my_status' WHERE id='$special_package'";
    $db->exec($updateSQL);
}

///CONTINNUE TOMRW INSAH ALLAH -----------------------------



if (isset($_GET['claims']) and !isset($_GET['A'])) {
    $hos_no = $_GET['claims'];
    $today = date("Y-m-d");

    if (!empty($_GET['date_range'])) {
        list($from, $to) = explode('/', $_GET['date_range']);
        $from = trim($from);
        $to = trim($to);
    } else {
        // Try admission first
        $today = date("Y-m-d");
        $from  = $today; // default
        $to    = $today;

        // Check admission first
        $stmt = $db->prepare("
    SELECT date_admit AS start_date 
    FROM admission 
    WHERE hospital_no = :hos_no AND adm_status = '3' 
    ORDER BY sn DESC 
    LIMIT 1");
        $stmt->execute([':hos_no' => $hos_no]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && !empty($row['start_date'])) {
            $from = date('Y-m-d', strtotime($row['start_date']));
            $to   = $today;
        } else {
            // Fallback to appointment
            $stmt = $db->prepare("
        SELECT date_ap AS start_date 
        FROM apptm 
        WHERE hospital_no = :hos_no 
          AND status NOT IN ('discharge','cancelled')
        ORDER BY sn ASC 
        LIMIT 1");
            $stmt->execute([':hos_no' => $hos_no]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row && !empty($row['start_date'])) {
                $from = date('Y-m-d', strtotime($row['start_date']));
                $to   = $today;
            }
        }
    }
} elseif (isset($_GET['A'])) {

    $A = isset($_GET['A']) ? $_GET['A'] : '';
    $hos_no = isset($_GET['claims']) ? $_GET['claims'] : '';

    if (!$A || !$hos_no) {
        // handle error, missing params
        die('Missing required parameters.');
    }

    // Explode input param A safely
    $part = explode('/', $A);
    /* 	if (count($part) < 5) {
		die('Invalid parameter format.');
	} */

    $sn = $part[0];
    $from = $part[1];
    $to = $part[2];
    $type = $part[3];
    $from_where = $part[4];

    if ($sn != ''):
        $stmt = $db->prepare("SELECT * FROM patient_ap_services WHERE hospital_no = :hos_no AND sn = :sn LIMIT 1");
        $stmt->execute([':hos_no' => $hos_no, ':sn' => $sn]);

        $rwx = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$rwx) {
            // handle no record found
            die('Record not found.');
        }

        // Fallback for transact_date
        $transc_date = $rwx['transact_date'];
        if ($transc_date === '0000-00-00' || empty($transc_date)) {
            $transc_date = $rwx['date_entry'];
        }

        // Assign variables safely
        $app_no = $rwx['app_no'];
        $hosp_no = $rwx['hospital_no']; // corrected, assuming column is hospital_no
        $service_access = $rwx['access'];
        $serv_group = $rwx['serv_group'];
        $cat_type = $rwx['cat_type'];
        $dept_id = $rwx['dept_id'];      // fixed typo from $rxw to $rwx
        $item_sn = $rwx['drug_sn'];      // fixed typo
        $service_name = $rwx['item_services'];
        $hosp_price = $rwx['hosp_price'];
        $claim_amt = $rwx['claim_amt'];
        $invoice_status = $rwx['invoice_status'];
        $qty = $rwx['qty'];
        $amt_paying = $rwx['pay'];
        $pay_mode = $rwx['pay_mode'];
        $ccop_int_charge = $rwx['interest'];

        $invoice_by = $rwx['invoice_by'];
        $invoice_date = $rwx['invoice_date'];
        $invoice_no = $rwx['invoice_no'];


    ////==========================================================================		


    endif;
}

if (isset($_GET['clear'])) {
    include_once('../inc/clear_lab_request_error.php');
}




$interest = $insurance = $insurance_name = $insurance_no = $surname = $vip = $fname = '';

$sql = "SELECT 
            i.interest, 
            i.insurance_type, 
            i.insurance_name, 
            i.insurance_no, 
            e.surname, 
            e.fname, 
            e.vip 
        FROM enrollee AS e 
        INNER JOIN insurance_tbl AS i 
            ON e.hmo_no = i.insurance_no 
        WHERE e.hospital_no = :hos_no AND i.status = 'active'";

$stmt = $db->prepare($sql);
$stmt->bindParam(':hos_no', $hos_no);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $rwxx = $stmt->fetch(PDO::FETCH_ASSOC);

    $interest       = $rwxx['interest'];
    $insurance      = $rwxx['insurance_type'];
    $insurance_name = $rwxx['insurance_name'];
    $insurance_no   = $rwxx['insurance_no'];
    $surname        = $rwxx['surname'];
    $fname          = $rwxx['fname'];
    $vip            = $rwxx['vip'];
}


if (isset($_POST['display_list']) or isset($_GET['filter'])) {

    if (isset($_POST['action_type']) or isset($_POST['service_category'])) {

        $action_type = $_POST['action_type'];
        $service_category = $_POST['service_category'];
        $from = $_POST['date_from'];
        $to = $_POST['date_to'];
    } elseif (isset($_GET['filter'])) {

        ///echo 'dsdddddddddddddd';

        $filter = $_GET['filter'];
        $pp = explode('/', $filter);

        $action_type = $pp[0];
        $service_category = $pp[1];
    }

    if ($service_category == '') {
        $search_one = "";
    } elseif ($service_category == 'Appointment') {
        $search_one = "and (serv_group='Consultation' or serv_group='Registration')";
    } elseif ($service_category == 'Investigation') {
        $search_one = "and (serv_group ='Laboratory' or serv_group ='Radiology')";
    } else {
        $search_one = "and serv_group ='$service_category'";
    }

    if ($action_type == '') {
        $search_two = '';
    } elseif ($action_type == 'pending') {
        $search_two = "and process_claim =0";
    } elseif ($action_type == 'Validated') {
        $search_two = "and process_claim =1";
    } elseif ($action_type == 'Payable') {
        $search_two = "and pay>0 and paystatus=0";
    } elseif ($action_type == 'post') {
        $search_two = "and claim_amt>0 and paystatus=1";
    }
}


?>


<div class="row">
    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>Claims Reports</h5>
            </div>

            <div class="ibox-content">
                <div align="center">
                    <h3>FE REPORT </h3>
                    <h3 style="color: #000"><?php echo $hos_no . ' // ' . $surname . ', ' . $fname; ?> </h3>
                    <h3 style="color: #F00"><?php echo $insurance_no . ' | ' . $insurance_name . ' | ' . $insurance . ' | Int. ' . $interest . '%'; ?></h3>
                    <strong>Reports Dates: <?php echo date("d M Y", strtotime($from)) . ' - ' . date("d M Y", strtotime($to)) ?></strong>
                    <br>
                    <form action="<?php echo $editFormAction; ?>" method="POST" id="subject" name="subject" enctype="multipart/form-data">
                        <table>

                            <tr>

                                <td style="padding-left: 12px;">
                                    <a href="index.php?ptm=all/<?= $hos_no; ?>" class="btn btn-primary btn-sm ">Patient Profile</a>
                                    &nbsp;
                                    <input type="button" name="book_app" value="Set Date Range" data-target="#modal" id="<?php echo $hosp_no; ?>"
                                        class="btn btn-danger btn-sm claims" />
                                </td>
                                <td style="padding-left: 12px;">
                                    <a href="index.php?claims=<?php echo $hos_no; ?>&A=<?php echo '/' . $from . '/' . $to . $pk; ?>" class="btn btn-success btn btn-sm"> <i class="fa fa-refresh"></i> &nbsp;Refresh List</a>

                                </td>
                                <td style="padding-left: 12px;">

                                    <select name="service_category" class="form-control">
                                        <option value="">Select Service Category</option>
                                        <option value="Appointment" <?php if ($service_category == 'Appointment') { ?>selected<?php } ?>>Appointment</option>
                                        <option value="Pharmacy" <?php if ($service_category == 'Pharmacy') { ?>selected<?php } ?>>Pharmacy</option>
                                        <option value="Nursing Services" <?php if ($service_category == 'Nursing Services') { ?>selected<?php } ?>>Nursing Consumable/Services</option>
                                        <option value="Investigation" <?php if ($service_category == 'Investigation') { ?>selected<?php } ?>>Investigation</option>
                                        <option value="Medical Services" <?php if ($service_category == 'Medical Services') { ?>selected<?php } ?>>Medical Services</option>
                                        <option value="External Services" <?php if ($service_category == 'External Services') { ?>selected<?php } ?>>Other Services</option>
                                    </select>
                                </td>
                                <td style="padding-left: 12px;">
                                    <select name="action_type" id="" class="form-control">
                                        <option selected="selected" value="">Select Action Type</option>
                                        <option value="pending" <?php if ($action_type == 'pending') { ?>selected<?php } ?>>Pending</option>
                                        <option value="Validated" <?php if ($action_type == 'Validated') { ?>selected<?php } ?>>Validated</option>
                                        <option value="Payable" <?php if ($action_type == 'Payable') { ?>selected<?php } ?>>Payable</option>
                                        <option value="post" <?php if ($action_type == 'post') { ?>selected<?php } ?>>Posted</option>
                                    </select>
                                </td>
                                <td style="padding-left: 12px;">
                                    <button class="btn btn-info btn-sm" type="submit" name="display_list">Display List</button>
                                </td>
                            </tr>

                        </table>
                        <input type="hidden" name="date_from" value="<?= $from; ?>">
                        <input type="hidden" name="date_to" value="<?= $to; ?>">
                    </form>
                    &nbsp; : &nbsp;

                    <?php
                    $adm_status = 3;
                    $stmt_chk_adm = $db->query("
						SELECT 
							date_admit, 
							date_discharge, 
							reason_adm, 
							discharge_note 
						FROM admission 
						WHERE hospital_no = '$hos_no' AND adm_status = '$adm_status' 
						ORDER BY sn DESC 
						LIMIT 1	");

                    $adm_count_status = $stmt_chk_adm->rowCount();

                    if ($adm_count_status > 0) {
                        $rwx = $stmt_chk_adm->fetch(PDO::FETCH_ASSOC);

                        date_default_timezone_set('Africa/Lagos');
                        $current_date = new DateTime();
                        $admit_date = new DateTime($rwx['date_admit']);

                        $diff = $admit_date->diff($current_date);
                        $day = $diff->format('%a');

                        $compute_status = ($day > 0) ? 'yes' : 'no';
                        $adm_status = 1;
                    ?>

                        <div class="alert alert-danger">
                            <strong><i>PATIENT ON ADMISSION</i></strong>
                            <table width="100%">
                                <tr>
                                    <td><strong>Admitted Date:</strong></td>
                                    <td><?= date('d-m-Y H:i:s a', strtotime($rwx['date_admit'])); ?></td>
                                    <td><strong>Discharge Date:</strong></td>
                                    <td>
                                        <?= !empty($rwx['date_discharge']) ? date('d-m-Y H:i:s a', strtotime($rwx['date_discharge'])) : '---'; ?>
                                    </td>
                                </tr>

                                <?php if ($vip == 0) { ?>
                                    <tr>
                                        <td><strong>Admitted Reason:</strong></td>
                                        <td><?= htmlspecialchars($rwx['reason_adm']); ?></td>
                                        <td><strong>Discharge Note:</strong></td>
                                        <td><?= htmlspecialchars($rwx['discharge_note']); ?></td>
                                    </tr>
                                <?php } ?>
                            </table>
                        </div>

                        <?php
                    } else {
                        $adm_status = '';
                        $compute_status = '';
                    }


                    if (isset($_GET['special_package'])) {

                        $currentDate = date('Y-m-d');
                        $stmt_chk_adm = $db->query("
							SELECT 
								id, hospital_no, service_title, service_amount, date_open, 
								date_expire, status, created_by, service_id, ap_service_id 
							FROM special_package_booking 
							WHERE hospital_no = '$hos_no' AND date_expire >= '$currentDate'
						");
                        $special_pkg_count_status = $stmt_chk_adm->rowCount();

                        if ($special_pkg_count_status > 0) { ?>
                            <div style="background-color:aquamarine;">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Description</th>
                                            <th>Amount</th>
                                            <th>Amount Paid</th>
                                            <th>Balance</th>
                                            <th>Created By</th>
                                            <th>Created Date</th>
                                            <th>Expiry Date</th>
                                            <th>Day(s) Left</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        while ($rwx = $stmt_chk_adm->fetch(PDO::FETCH_ASSOC)) {
                                            $service_table_id = $rwx['service_id'];
                                            $ap_service_id = $rwx['ap_service_id'];
                                            $status_validation = $rwx['status'];
                                            $amount_paid = 0;
                                            $balance = 0;
                                            $color = 'black';

                                            // Sum payments from patient_ap_services
                                            $stAps = $db->query("
												SELECT paystatus, pay 
												FROM patient_ap_services 
												WHERE sn = '$ap_service_id' OR remarks = '$ap_service_id'
											");

                                            while ($rw_amt = $stAps->fetch(PDO::FETCH_ASSOC)) {
                                                if ($rw_amt['paystatus'] == 1) {
                                                    $amount_paid += $rw_amt['pay'];
                                                } else {
                                                    $balance += $rw_amt['pay'];
                                                    if ($balance > 0) {
                                                        $color = 'red';
                                                    }
                                                }
                                            }

                                            $expiryDate = new DateTime($rwx['date_expire']);
                                            $today = new DateTime();
                                            $daysLeft = $expiryDate->diff($today)->format('%a');

                                            $actionTitle = ($rwx['status'] == 0) ? 'Deactivate' : 'Activate';
                                        ?>
                                            <tr>
                                                <td>#</td>
                                                <td>
                                                    <?= htmlspecialchars($rwx['service_title']); ?>
                                                    <input type="button" name="edit_gd" value="View list"
                                                        data-target="#modal" id="<?= $service_table_id; ?>"
                                                        class="btn btn-warning btn-xs special_pake_list" />
                                                </td>
                                                <td><?= number_format($rwx['service_amount']); ?></td>
                                                <td><?= number_format($amount_paid); ?></td>
                                                <td><strong style="color:<?= $color; ?>;"><?= number_format($balance); ?></strong></td>
                                                <td><?= htmlspecialchars($rwx['created_by']); ?></td>
                                                <td><?= date('d-m-Y', strtotime($rwx['date_open'])); ?></td>
                                                <td><?= date('d-m-Y', strtotime($rwx['date_expire'])); ?></td>
                                                <td><?= $daysLeft; ?></td>
                                                <td>
                                                    <a href="index.php?claims=<?= $hos_no; ?>&special_package=<?= $rwx['id']; ?>&status=<?= $rwx['status']; ?>">
                                                        <?= $actionTitle; ?>
                                                    </a>
                                                </td>
                                            </tr>

                                        <?php
                                            // Load service breakdown only if inactive
                                            if ($status_validation == 0) {
                                                $stmt2 = $db->query("
													SELECT service_id, service_type 
													FROM special_package 
													WHERE service_table_id = '$service_table_id'
												");
                                                $data = $stmt2->fetchAll(PDO::FETCH_ASSOC);

                                                $medical_services = [];
                                                $investigations = [];
                                                $pharmacy = [];

                                                foreach ($data as $row2) {
                                                    switch ($row2['service_type']) {
                                                        case 'medical_services':
                                                            $medical_services[] = $row2['service_id'];
                                                            break;
                                                        case 'investigations':
                                                            $investigations[] = $row2['service_id'];
                                                            break;
                                                        case 'pharmacy':
                                                            $pharmacy[] = $row2['service_id'];
                                                            break;
                                                    }
                                                }
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                    <?php
                        } else {
                            echo '<h2 style="color:white;">No Active Special Package</h2>';
                        }
                    }

                    ?>
                </div>

                <form action="index.php?claims=<?php echo $hos_no . '&A=' . "/$from/$to .$pk"; ?>" method="post">

                    <?php

                    $filter = $action_type . '/' . $service_category;

                    $grand_amt_hmo = 0;
                    $grand_pay_amt = 0;
                    $total_cr_pay = 0;
                    $inv_processed_pay = 0;
                    $valid_amt = 0;
                    $total_cr_claim = 0;
                    $phamarcy_desc = '';
                    $investigation_desc = '';

                    // Define required date range and hospital number
                    $from = date('Y-m-d', strtotime($from));
                    $to = date('Y-m-d', strtotime($to));

                    $sql = "
						SELECT 
							sn, item_services, pay, claim_amt, serv_group, qty, paystatus, transact_date,
							hosp_price, process_claim, invoice_no, invoice_status, date_entry, drug_status,
							cr, cat_type, prepared_by, remarks, claim_valid_by, invoice_by, pay_mode,
							dsp_by, drug_sn, created_by, invoice_date
						FROM 
							patient_ap_services
						WHERE 
							hospital_no = :hos_no
							AND invoice_status != '3'
							$search_one
							$search_two
							AND DATE(date_entry) BETWEEN :from AND :to
						ORDER BY 
							date_entry DESC";

                    $stmt = $db->prepare($sql);
                    $stmt->execute([
                        ':hos_no' => $hos_no,
                        ':from' => $from,
                        ':to' => $to
                    ]);

                    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);


                    ?>
                    <h4>Consultation</h4>
                    <table class="table table-striped table-bordered" id="consultation_tab">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Description</th>
                                <th width="5%">Unit</th>
                                <th width="3%">Qty</th>
                                <th width="5%">Amount</th>
                                <th width="7%">Billed</th>
                                <th width="15%">Valid/Invoiced</th>
                                <th width="10%">By / Date</th>
                                <th width="10%">.</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $amt_due = 0;
                            $amt_hmo = 0;
                            $n = 1;
                            $claim_set = 0;
                            $pay_amt = 0;
                            $convert = 0;
                            $printButton = 0;
                            //while($row=$stmt->fetch(PDO::FETCH_ASSOC)){ 
                            foreach ($data as $key => $row) {
                                if ($row['serv_group'] == 'Consultation' or $row['serv_group'] == 'Registration') {
                                    $app_no = $row['app_no'];
                                    $tab = "consultation_tab";
                                    $stmt_appt = $db->query("SELECT hospital_no FROM apptm WHERE appt_no='$app_no' and referal_doc='Any Doctor'");
                                    if ($stmt_appt->rowCount() == 0) { ?>
                                        <?php ///include("claim_body.php"); 
                                        ?>

                            <?php }
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                    <div align="right" style="font-size:15px; "><strong><i><small>Total Claims:</small></i></strong>&nbsp;&nbsp;<strong>
                            <?php $grand_amt_hmo = $grand_amt_hmo + $amt_hmo;
                            echo number_format($amt_hmo, 2, '.', ','); ?> </strong>/<strong><i><small>Total Payments:</small></i></strong>&nbsp;&nbsp;<strong>
                            <?php $grand_pay_amt = $grand_pay_amt + $pay_amt;;
                            echo number_format($pay_amt, 2, '.', ','); ?> </strong></div>


                    <h4>Pharmacy</h4>
                    <table class="table table-striped table-bordered" id="pharmacy_tab">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Description</th>
                                <th width="5%">Unit</th>
                                <th width="3%">Qty</th>
                                <th width="5%">Amount</th>
                                <th width="7%">Billed</th>
                                <th width="15%">Valid/Invoiced</th>
                                <th width="10%">By / Date</th>
                                <th width="10%">.</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $amt_due = 0;
                            $amt_hmo = 0;
                            $n = 1;
                            $claim_set = 0;
                            $pay_amt = 0;
                            $convert = 0;
                            $printButton = 0;
                            //while($row=$stmt->fetch(PDO::FETCH_ASSOC)){

                            foreach ($data as $key => $row) {
                                if ($row['serv_group'] == 'Pharmacy') {
                                    $tab = "pharmacy_tab";
                                    $paystatus = 0;
                                    $invoice_status = 0; ?>
                                    <?php ///include("claim_body.php"); 
                                    ?>

                            <?php    }
                            }    ?>
                        </tbody>
                    </table>

                    <div align="right" style="font-size:15px; "><strong><i><small>Total Claims:</small></i></strong>&nbsp;&nbsp;<strong>
                            <?php $grand_amt_hmo = $grand_amt_hmo + $amt_hmo;
                            echo number_format($amt_hmo, 2, '.', ','); ?> </strong>/<strong><i><small>Total Payments:</small></i></strong>&nbsp;&nbsp;<strong>
                            <?php $grand_pay_amt = $grand_pay_amt + $pay_amt;;
                            echo number_format($pay_amt, 2, '.', ','); ?> </strong></div>

                    <h4>Investigations</h4>
                    <table class="table table-striped table-bordered" id="investigation_tab">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Description</th>
                                <th width="5%">Unit</th>
                                <th width="3%">Qty</th>
                                <th width="5%">Amount</th>
                                <th width="7%">Billed</th>
                                <th width="15%">Valid/Invoiced</th>
                                <th width="10%">By / Date</th>
                                <th width="10%">.</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $amt_due = 0;
                            $amt_hmo = 0;
                            $n = 1;
                            $claim_set = 0;
                            $pay_amt = 0;
                            $convert = 0;
                            $printButton = 0;
                            //while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
                            foreach ($data as $key => $row) {
                                ///cat_type='Radiology' or cat_type='Laboratory'
                                if ($row['serv_group'] == 'Radiology' or $row['serv_group'] == 'Laboratory') {
                                    $paystatus = 1;
                                    $invoice_status = 1;
                                    $tab = "investigation_tab";
                            ?>

                                    <?php ///include("claim_body.php"); 
                                    ?>

                            <?php }
                            }
                            ?>
                        </tbody>
                    </table>
                    <div align="right" style="font-size:15px; "><strong><i><small>Total Claims:</small></i></strong>&nbsp;&nbsp;<strong>
                            <?php $grand_amt_hmo = $grand_amt_hmo + $amt_hmo;
                            echo number_format($amt_hmo, 2, '.', ','); ?> </strong>/<strong><i><small>Total Payments:</small></i></strong>&nbsp;&nbsp;<strong>
                            <?php $grand_pay_amt = $grand_pay_amt + $pay_amt;;
                            echo number_format($pay_amt, 2, '.', ','); ?> </strong></div>

                    <h4>Nursing Services/Accomodation and Consumables</h4>
                    <table class="table table-striped table-bordered" id="nursing_tab">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Description</th>
                                <th width="5%">Unit</th>
                                <th width="3%">Qty</th>
                                <th width="5%">Amount</th>
                                <th width="7%">Billed</th>
                                <th width="15%">Valid/Invoiced</th>
                                <th width="10%">By / Date</th>
                                <th width="10%">.</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $amt_due = 0;
                            $amt_hmo = 0;
                            $n = 1;
                            $claim_set = 0;
                            $pay_amt = 0;
                            $convert = 0;
                            $printButton = 0;
                            ///while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
                            foreach ($data as $key => $row) {
                                if ($row['serv_group'] == 'Nursing Services') {
                                    $tab = "nursing_tab";
                                    $paystatus = 1;
                                    $invoice_status = 1; ?>
                                    <?php ///include("claim_body.php"); 
                                    ?>

                            <?php }
                            }
                            ?>
                        </tbody>
                    </table>

                    <div align="right" style="font-size:15px; "><strong><i><small>Total Claims:</small></i></strong>&nbsp;&nbsp;<strong>
                            <?php $grand_amt_hmo = $grand_amt_hmo + $amt_hmo;
                            echo number_format($amt_hmo, 2, '.', ','); ?> </strong>/<strong><i><small>Total Payments:</small></i></strong>&nbsp;&nbsp;<strong>
                            <?php $grand_pay_amt = $grand_pay_amt + $pay_amt;;
                            echo number_format($pay_amt, 2, '.', ','); ?> </strong></div>


                    <h4>Other Services</h4>
                    <table class="table table-striped table-bordered" id="others_tab">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Description</th>
                                <th width="5%">Unit</th>
                                <th width="3%">Qty</th>
                                <th width="5%">Amount</th>
                                <th width="7%">Billed</th>
                                <th width="15%">Valid/Invoiced</th>
                                <th width="10%">By / Date</th>
                                <th width="10%">.</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $amt_due = 0;
                            $amt_hmo = 0;
                            $n = 1;
                            $claim_set = 0;
                            $pay_amt = 0;
                            $convert = 0;
                            $printButton = 0;
                            ///while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
                            ///cat_type='Other Services' or cat_type='Medical Services
                            foreach ($data as $key => $row) {
                                if (
                                    $row['cat_type'] == 'Transplant'
                                    or $row['cat_type'] == 'Dialysis' or
                                    $row['cat_type'] == 'Other Services' or
                                    $row['serv_group'] == 'Other Services' or
                                    $row['cat_type'] == 'External Services' or
                                    $row['serv_group'] == 'EX' or
                                    $row['serv_group'] == 'Medical Services' or
                                    $row['cat_type'] == 'Medical Services'
                                ) {
                                    $tab = "others_tab";
                                    $paystatus = 1;
                                    $invoice_status = 1; ?>
                                    <?php ///include("claim_body.php"); 
                                    ?>

                            <?php }
                            } ?>
                        </tbody>
                    </table>

                    <div align="right" style="font-size:15px; "><strong><i><small>Total Claims:</small></i></strong>&nbsp;&nbsp;<strong>
                            <?php $grand_amt_hmo = $grand_amt_hmo + $amt_hmo;
                            echo number_format($amt_hmo, 2, '.', ','); ?> </strong>/<strong><i><small>Total Payments:</small></i></strong>&nbsp;&nbsp;<strong>
                            <?php $grand_pay_amt = $grand_pay_amt + $pay_amt;;
                            echo number_format($pay_amt, 2, '.', ','); ?> </strong></div>


                    <?php if ($_SESSION['see_med_rpt'] == 1 && $vip == 0) { ?>
                        <input type="button" name="add_insur" value="Consultation Notes/Medical Report" data-target="#modal" id="<?php echo 'new_' . $ptm; ?>" class="btn btn-success btn-sm view_medical_rpt" />
                    <?php } ?>

                    <hr>


                    <br>
                    <?php if ($compute_status == 'no') { ?>
                        <strong style="color:#F00">Patient on admission---------------------</strong>
                    <?php } ?>


                    <?php if ($insurance_no != '1000' or (isset($_GET['special_package']) and $special_pkg_count_status > 0)) { ?>


                        <?php if ($valid_pending == 1) {
                        ?>
                            <button class="btn btn-danger  btn-sm" type="submit" name="post_claims" id="" <?php if ($adm_status == '1' and $compute_status == 'no') { ?>disabled <?php } ?>
                                onclick="return confirm('Are you sure you want to VALIDATE?')">Validate</button>
                            <input type="hidden" name="hos_no" value="<?php echo $hos_no; ?>" />
                            <input type="hidden" name="from" value="<?php echo $from; ?>" />
                            <input type="hidden" name="to" value="<?php echo $to; ?>" />
                            <input type="hidden" name="getvalue" value="<?php echo $getvalue; ?>" />
                            <input type="hidden" name="cpt" value="<?php echo $from_where; ?>" />
                            <input type="hidden" name="adm_status" value="<?php echo $adm_status; ?>" />
                            <input type="hidden" name="special_package" value="<?php
                                                                                if (isset($_GET['special_package'])) {
                                                                                    echo 'special_package';
                                                                                } ?>" />

                        <?php }
                        ?>
                    <?php } ?>
                    &nbsp; : &nbsp;
                    <button class="btn btn-success  btn-sm" type="submit" name="prt_claim_selected" id="prt_claim"><i class="icon-print icon-1x"></i>&nbsp;Print Selected Item(s)</button>
                    &nbsp; : &nbsp;
                    <a data-toggle="modal" class="btn btn-warning btn-sm" href="#modal-form">Copy Prescriptions/Others</a>

                </form>

                <?php
                // 1. Fetch one apptm record with ap_type = '2' and date range
                $sql = "SELECT ap_type, auth_code, appt_no, checkin_by, ap_date_time 
        FROM apptm 
        WHERE hospital_no = :hos_no 
          AND ap_type = '2' 
          AND DATE(date_ap) BETWEEN :from AND :to 
        ORDER BY sn 
        LIMIT 1";
                $stmt2 = $db->prepare($sql);
                $stmt2->execute([':hos_no' => $hos_no, ':from' => $from, ':to' => $to]);
                $appt = $stmt2->fetch(PDO::FETCH_ASSOC);

                $print_btn_lock = 0;
                $auth_code = '';
                if ($appt) {
                    $auth_code = $appt['auth_code'];
                    if ($appt['ap_type'] === '2' && strlen($auth_code) <= 6) {
                        $print_btn_lock = 1;
                    }
                }

                // 2. Calculate last month and year efficiently
                $currentYear = (int)date('Y');
                $currentMonth = (int)date('m');
                if ($currentMonth === 1) {
                    $last_month = 12;
                    $last_year = $currentYear - 1;
                } else {
                    $last_month = $currentMonth - 1;
                    $last_year = $currentYear;
                }

                // 3. Check for valid claims last month
                $stmt1 = $db->prepare("SELECT 1 FROM patient_ap_services 
                       WHERE paystatus = 1 
                         AND process_claim = 0 
                         AND claim_amt > 0 
                         AND MONTH(transact_date) = :last_month 
                         AND YEAR(transact_date) = :last_year 
                       LIMIT 1");
                $stmt1->execute([':last_month' => $last_month, ':last_year' => $last_year]);
                $valid_error = ($stmt1->fetchColumn() !== false) ? 1 : 0;

                // 4. Check if insurance_claim_rpt entry exists for last month
                $stmt2 = $db->prepare("SELECT 1 FROM insurance_claim_rpt WHERE month = :last_month AND year = :last_year LIMIT 1");
                $stmt2->execute([':last_month' => $last_month, ':last_year' => $last_year]);
                $settlement_error = ($stmt2->fetchColumn() === false) ? 1 : 0;

                // 5. Prepare all data first to avoid logic inside HTML output
                ?>

                <table width="100%" align="right">
                    <tr>
                        <td align="right">
                            <div align="right">
                                <form action="index.php?printout" method="post">
                                    <?php if (!isset($_GET['special_package'])): ?>
                                        <input type="checkbox" name="plan_show" value="plan_show"> Show plan
                                        <button class="btn btn-info btn-sm" type="submit" name="prt_claim" id="prt_claim">
                                            <i class="icon-print icon-1x"></i>&nbsp;Print Report
                                        </button>
                                    <?php endif; ?>

                                    <input type="hidden" name="hos_no" value="<?= htmlspecialchars($hos_no); ?>" />
                                    <input type="hidden" name="from" value="<?= htmlspecialchars($from); ?>" />
                                    <input type="hidden" name="to" value="<?= htmlspecialchars($to); ?>" />
                                    <input type="hidden" name="getvalue" value="<?= htmlspecialchars($getvalue); ?>" />
                                    <input type="hidden" name="cpt" value="<?= htmlspecialchars($from_where); ?>" />
                                    <input type="hidden" name="auth_code" value="<?= htmlspecialchars($auth_code); ?>" />
                                    <input type="hidden" name="special_package" value="<?= isset($_GET['special_package']) ? 'special_package' : ''; ?>" />
                                </form>
                            </div>
                        </td>

                        <td align="right">
                            <div align="right">
                                <?php
                                if (!empty($from_where)):
                                    $parts = explode('_', $from_where);
                                    $from_where_to = $parts[0];
                                    $hmo = isset($parts[1]) ? $parts[1] : '';

                                    if ($hmo === 'daily'): ?>
                                        <a href="index.php?cptclaim" style="font-size:20px;">
                                            <button class="btn btn-danger btn-sm"><i class="icon-print"></i> Close</button>
                                        </a>
                                    <?php elseif ($hmo === 'dates'): ?>
                                        <form action="index.php?cptclaim" method="POST" id="subject" name="subject">
                                            <input type="hidden" name="start" value="<?= htmlspecialchars($from); ?>" />
                                            <input type="hidden" name="end" value="<?= htmlspecialchars($to); ?>" />
                                            <button class="btn btn-danger btn-sm" type="submit" name="apply_enrollees">Close</button>
                                        </form>
                                    <?php else:
                                        $dateParts = explode('-', $from);
                                        $year = isset($dateParts[0]) ? $dateParts[0] : '';
                                        $month = isset($dateParts[1]) ? $dateParts[1] : '';
                                    ?>
                                        <form action="index.php?cptclaim" method="POST" id="subject" name="subject">
                                            <input type="hidden" name="insurance_type" value="<?= htmlspecialchars($hmo); ?>" />
                                            <input type="hidden" name="year" value="<?= htmlspecialchars($year); ?>" />
                                            <input type="hidden" name="month" value="<?= htmlspecialchars($month); ?>" />
                                            <input type="hidden" name="typerpt" value="Enrollee Claims" />
                                            <button class="btn btn-danger btn-sm" type="submit" name="apply_rpt">Close</button>
                                        </form>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <a href="index.php?ptm=<?= $getvalue === '' ? "all/" . urlencode($hos_no) : urlencode($getvalue); ?>" style="font-size:20px;">
                                        <button class="btn btn-danger btn-sm">Close</button>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                </table>

                <br>
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal fade" id="medical_report_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title">View Claims Report</h4>
            </div>

            <div class="modal-body" id="claims_body">
                <div align="center" style="font:bold 18px 'Arial';">Medical Reports</div>

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Complaints</th>
                            <th>Diagnosis</th>
                            <th>Notes</th>
                            <th>Plan/Medication</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $mynotes = 'no';
                        $notesData = [];
                        $apServicesByDate = [];

                        //----------------------------------------------------------
                        // STEP 1 — Get Notes
                        //----------------------------------------------------------
                        $sql = "SELECT notes_type, notes, prepared_by, DATE(date_entry) AS note_date
                                FROM notes
                                WHERE hospital_no = :hos_no
                                  AND DATE(date_entry) BETWEEN :from AND :to
                                ORDER BY date_entry";

                        $stmt = $db->prepare($sql);
                        $stmt->execute([':hos_no' => $hos_no, ':from' => $from, ':to' => $to]);
                        $notesFetched = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        if (count($notesFetched) > 0) {
                            $mynotes = 'yes';

                            foreach ($notesFetched as $note) {
                                $date = $note['note_date'];
                                $entry = $note['notes'] . '<br><small>' . htmlspecialchars($note['prepared_by']) . '</small><br>';

                                if (!isset($notesData[$date])) {
                                    // Important: initialize all fields
                                    $notesData[$date] = [
                                        'c' => '',
                                        'd' => '',
                                        'other' => '',
                                        'plan' => ''
                                    ];
                                }

                                switch (strtolower($note['notes_type'])) {
                                    case 'c':
                                        $notesData[$date]['c'] .= $entry;
                                        break;
                                    case 'd':
                                        $notesData[$date]['d'] .= $entry;
                                        break;
                                    case 'plan':
                                        $notesData[$date]['plan'] .= $entry;
                                        break;
                                    default:
                                        $notesData[$date]['other'] .= $entry;
                                        break;
                                }
                            }

                            //----------------------------------------------------------
                            // STEP 2 — Get Lab / Pharmacy / Radiology Services
                            //----------------------------------------------------------
                            $sql2 = "SELECT DATE(date_entry) AS serv_date, item_services, remarks, serv_group
                                     FROM patient_ap_services
                                     WHERE serv_group IN ('Laboratory','Radiology','Pharmacy')
                                       AND hospital_no = :hos_no
                                       AND DATE(date_entry) BETWEEN :from AND :to
                                     ORDER BY date_entry";

                            $stmt2 = $db->prepare($sql2);
                            $stmt2->execute([':hos_no' => $hos_no, ':from' => $from, ':to' => $to]);
                            $apResults = $stmt2->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($apResults as $rowx) {
                                $date = $rowx['serv_date'];

                                if (!isset($apServicesByDate[$date])) {
                                    $apServicesByDate[$date] = [
                                        'pharmacy' => [],
                                        'lab' => []
                                    ];
                                }

                                if ($rowx['serv_group'] === 'Pharmacy') {
                                    $apServicesByDate[$date]['pharmacy'][] =
                                        htmlspecialchars($rowx['item_services']) .
                                        ' <strong>Prescription:</strong> ' .
                                        htmlspecialchars($rowx['remarks']);
                                } else {
                                    $apServicesByDate[$date]['lab'][] =
                                        htmlspecialchars($rowx['item_services']);
                                }
                            }

                            //----------------------------------------------------------
                            // STEP 3 — PRINT TABLE ROWS (Always 5 Columns!)
                            //----------------------------------------------------------
                            foreach ($notesData as $date => $noteSet) {

                                // Guarantee all keys exist
                                $noteSet = array_merge(
                                    ['c' => '', 'd' => '', 'other' => '', 'plan' => ''],
                                    $noteSet
                                );

                                $pharmacyOutput = isset($apServicesByDate[$date]['pharmacy'])
                                    ? implode(', ', $apServicesByDate[$date]['pharmacy'])
                                    : '';

                                $labOutput = isset($apServicesByDate[$date]['lab'])
                                    ? implode(', ', $apServicesByDate[$date]['lab'])
                                    : '';

                                echo "<tr>";

                                // COLUMN 1 - DATE
                                echo "<td>" . date('d /<br>M,y', strtotime($date)) . "</td>";

                                // COLUMN 2 - COMPLAINTS
                                echo "<td>{$noteSet['c']}</td>";

                                // COLUMN 3 - DIAGNOSIS
                                echo "<td>{$noteSet['d']}</td>";

                                // COLUMN 4 - NOTES
                                echo "<td>{$noteSet['other']}</td>";

                                // COLUMN 5 - PLAN / MEDICATION (always printed)
                                echo "<td>";

                                $content = '';

                                if ($noteSet['plan'] !== '') {
                                    $content .= "<strong>Plan:</strong><br>{$noteSet['plan']}<br>";
                                }
                                if ($pharmacyOutput !== '') {
                                    $content .= "<strong>Medications:</strong><br>{$pharmacyOutput}<br>";
                                }
                                if ($labOutput !== '') {
                                    $content .= "<strong>Investigations:</strong><br>{$labOutput}";
                                }

                                echo $content;   // can be empty safely

                                echo "</td>";

                                echo "</tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>

                <?php if ($mynotes === 'no') { ?>
                    <strong>No Medical Report Available!</strong>
                <?php } ?>
            </div>
        </div>
    </div>
</div>





<?php
$scrollTo = isset($_GET['tab']) ? $_GET['tab'] : '';
?>
<?php if ($scrollTo != ''): ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var target = document.getElementById("<?php echo $scrollTo; ?>");
            if (target) {
                target.scrollIntoView({
                    behavior: "smooth",
                    block: "start"
                });
            }
        });
    </script>
<?php endif; ?>