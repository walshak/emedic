<?php

include("../inc/session.php");
include("../inc/credit_current_balance.php");

if (isset($_POST['change_procedure_button'])) {
    $claim_amt = $_POST['claim_amt'];
    $hospital_no = $_POST['hospital_no'];

    $pay_mode = $_POST['pay_mode'];
    $hosp_price = $_POST['hosp_price'];
    $paystatus = $_POST['paystatus'];
    $change_procedure_id = $_POST['change_procedure'];
    $app_no = $_POST['app_no'];
    $old_request_id = $_POST['approve_request_id'];
    $payment_mode = $_POST['payment_mode'];
    $insurance_type = $_POST['insurance_type'];
    $insurance_no = $_POST['insurance_no'];
    $add_minus = $_POST['add_minus'];

    $prepared_by = $_SESSION['fullname'];
    $sale_no = $_POST['sale_no'];
    $setdate = date('Y-m-d H:i:s');
    $invoice_no = date('m') . sprintf('%006d', mt_rand(00000, 99999));
    $one = 1;

    $stmt = $db->query("SELECT * FROM prices_table WHERE sn='$change_procedure_id'");
    if ($stmt->rowCount() > 0) {
        $row_serv = $stmt->fetch(PDO::FETCH_ASSOC);
        $item_services = $row_serv['item_service'];
        $serv_group = $row_serv['price_table'];
        $cat_type = $row_serv['category'];
        $dept_id = $row_serv['dept'];
        $hosp_price = $row_serv['hosp_price'];
        $item_sn = $row_serv['sn'];
    }


    $target_sn = $item_sn;
    $NHIS_DRUG_CONSUMBL_STATE = 0;
    $_tariff_table = "hmo_medical_tariff";
    $amount_invoice = new_service_amount_cal(
        $db,
        $hospital_no,
        $app_no,
        $interest,
        $insurance_type,
        $insurance_no,
        $hosp_price,
        $ext_price,
        $nhis_price,
        $services_access,
        $add_minus,
        $payment_mode,
        $_tariff_table,
        $target_sn,
        $NHIS_DRUG_CONSUMBL_STATE,
        true
    );

    $claim_amt = $amount_invoice["claim_amt"];
    $amount_paying = $amount_invoice["amount_paying"];
    $pay_mode = $amount_invoice["pay_mode"];
    $ccop_int_charge = $amount_invoice["ccop_int_charge"];
    $hosp_price = $amount_invoice["item_amt"];
    $invoice_no = $amount_invoice["invoice_no"];

    if ($paystatus == 0) {

        if ($old_request_id != $change_procedure_id) {
            $enter_reason = '<i><b>Old Procedure Changed:</b></i>' . $_POST['old_procedures'] . ' :' . $_POST['enter_reason'];
            try {
                // Begin the transaction
                $db->beginTransaction();
                $stmt1 = $db->prepare("UPDATE patient_ap_services SET dept_id = ?, drug_sn = ?, item_services = ?, hosp_price = ?, claim_amt = ?, interest = ?, pay = ?, pay_mode = ?, prepared_by = ?, created_by = ? WHERE sn = ?");
                $stmt1->execute([
                    $dept_id,
                    $change_procedure_id,
                    $item_services,
                    $hosp_price,
                    $claim_amt,
                    $ccop_int_charge,
                    $amount_paying,
                    $pay_mode,
                    $prepared_by,
                    $_SESSION['id'], // Assuming this is correct
                    $sale_no // Assuming this is correctly sanitized
                ]);

                // Prepare and execute the second update
                $stmt2 = $db->prepare("UPDATE procedures SET indication = ?, procedures = ?, insurance_type = ?, interest = ?, service_id = ?, cost = ?, prepared_by = ?, created_by = ? WHERE sale_no = ?");
                $stmt2->execute([
                    $enter_reason,
                    $item_services,
                    $insurance_type,
                    $ccop_int_charge,
                    $change_procedure_id,
                    $amount_paying,
                    $prepared_by,
                    $_SESSION['id'], // Ensure this is set and valid
                    $sale_no // Assuming this is correctly sanitized
                ]);
                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
                echo "Failed to update records: " . $e->getMessage();
            }
        }
    } else {


        /// PAYMENT PAID =================================================

        if ($old_request_id != $change_procedure_id) {
            $enter_reason = '<i><b>Old Procedure Changed by: </b></i>' . $_SESSION['fullname'] . ' /' . $_POST['old_procedures'] . ' :' . $_POST['enter_reason'];
        } else {
            $enter_reason = '<i><b>Cancelled by:</b></i>' . $_SESSION['fullname'] . '/' . $_POST['old_procedures'] . ' :' . $_POST['enter_reason'];
        }
        $stmt2 = $db->prepare("
            SELECT sn, serv_group, wallet_payee, cr, pay_mode, drug_status, 
                hospital_no, wallet_debt_bill_to_acct, drug_sn, ledger_TX 
            FROM patient_ap_services 
            WHERE sn = ?");
        $stmt2->execute([$sn]);

        if ($rowx = $stmt2->fetch(PDO::FETCH_ASSOC)) {
            $serv_group    = $rowx['serv_group'];
            $drug_sn       = $rowx['drug_sn'];
            $wallet_payee  = $rowx['wallet_payee'];
            $pay_mode      = $rowx['pay_mode'];
            $drug_status   = $rowx['drug_status'];
            $lg_ref_no     = $rowx['ledger_TX'];
            $cr            = $rowx['cr'];
            $bill_to_acct  = $rowx['wallet_debt_bill_to_acct'];

            if ($drug_status == 1) {
                $error_msg = 'You Can Not Reverse Service That Has Been Delivered!';
            } else {

                try {
                    $db->beginTransaction();

                    if ($pay_mode == 'spkage') {
                        $updateSQL = "UPDATE patient_ap_services SET invoice_status = 1, paystatus = 0, transact_date =null, claim_valid_by =null, med_frequency = null WHERE sn = ?";
                        $db->prepare($updateSQL)->execute([$sn]);
                    } elseif ($pay_mode == 'cash' && ($cr == 2 || $bill_to_acct == 'CREDIT' || $bill_to_acct == 'BILL')) {

                        $updateSQL = "UPDATE patient_ap_services  SET invoice_status = 1, paystatus = 0, cr = 0 WHERE sn = ?";
                        $success1 = $db->prepare($updateSQL)->execute([$sn]);

                        if ($success1) {
                            $deleteLedger = "DELETE FROM chart_ledger WHERE sale_sn = ?";
                            $db->prepare($deleteLedger)->execute([$sn]);
                            $error_status = 2;
                            $error_msg = 'Reversed Successful';
                        } else {
                            throw new Exception('Failed to update patient service');
                        }
                    } else {

                        include_once("../admin/reverse.php");

                        ////if ($save_done) {
                        if ($status == "success") {
                            $setdatetime = date("Y-m-d H:i:s");
                            $updateSQL34 = "UPDATE patient_ap_services SET invoice_status='3', paystatus='3', transact_date='$setdatetime', pay_mode='reverse' WHERE sn='$sn'";
                            $updateResult = $db->exec($updateSQL34);

                            if ($updateResult !== false && $updateResult > 0) {
                                $error_status = 2;
                                if ($wallet_payee != '') {
                                    $error_msg = 'Reversed successful wallet transfer owner/s EMR number: ' . $hos_no;
                                } else {
                                    $error_msg = 'Reversed Successful';
                                }
                            } else {
                                // Update failed, roll back chart_ledger
                                $update = "DELETE FROM chart_ledger WHERE lg_ref_no='$lg_ref_no'";
                                $db->exec($update);
                                $error_status = 1;
                                $error_msg = 'Unable To Reverse Successfully';
                            }
                        } else {
                            $update = "DELETE FROM chart_ledger WHERE lg_ref_no='$lg_ref_no'";
                            $db->exec($update);
                            $error_status = 1;
                            $error_msg = 'Unable To Reverse Successfully';
                        }
                    }

                    $sql = $db->prepare("INSERT INTO patient_ap_services (app_no,hospital_no,serv_group,cat_type,dept_id,drug_sn,item_services,hosp_price,claim_amt,interest,qty,invoice_status,invoice_no,prepared_by,date_entry,pay,pay_mode) 
                            VALUES (:app_no,:hospital_no,:serv_group,:cat_type,:dept_id,:drug_sn,:item_services,:hosp_price,:claim_amt,:interest,:qty,:invoice_status,:invoice_no,:prepared_by,:date_entry,:pay,:pay_mode)");

                    $sql->bindParam(':app_no', $app_no, PDO::PARAM_STR);
                    $sql->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
                    $sql->bindParam(':serv_group', $serv_group, PDO::PARAM_STR);
                    $sql->bindParam(':cat_type', $cat_type, PDO::PARAM_STR);
                    $sql->bindParam(':dept_id', $dept_id, PDO::PARAM_STR);
                    $sql->bindParam(':drug_sn', $item_sn, PDO::PARAM_STR);
                    $sql->bindParam(':item_services', $item_services, PDO::PARAM_STR);
                    $sql->bindParam(':hosp_price', $hosp_price, PDO::PARAM_STR);
                    $sql->bindParam(':claim_amt', $claim_amt, PDO::PARAM_STR);
                    $sql->bindParam(':interest', $ccop_int_charge, PDO::PARAM_STR);
                    $sql->bindParam(':qty', $one, PDO::PARAM_STR);
                    $sql->bindParam(':invoice_status', $one, PDO::PARAM_STR);
                    $sql->bindParam(':invoice_no', $invoice_no, PDO::PARAM_STR);
                    $sql->bindParam(':prepared_by', $_SESSION['fullname'], PDO::PARAM_STR);
                    $sql->bindParam(':date_entry', $setdate, PDO::PARAM_STR);
                    $sql->bindParam(':pay', $amount_paying, PDO::PARAM_STR);
                    $sql->bindParam(':pay_mode', $pay_mode, PDO::PARAM_STR);
                    $sql->execute();
                    $lastId = $db->lastInsertId();

                    $stmt2 = $db->prepare("UPDATE procedures SET indication = ?, procedures = ?, insurance_type = ?, interest = ?, service_id = ?, cost = ?, prepared_by = ?, created_by = ?, sale_no = ? WHERE sale_no = ?");
                    $stmt2->execute([
                        $enter_reason,
                        $item_services,
                        $insurance_type,
                        $ccop_int_charge,
                        $change_procedure_id,
                        $amount_paying,
                        $prepared_by,
                        $_SESSION['id'], // Ensure this is set and valid
                        $lastId, // Assuming this is correctly sanitized
                        $sale_no // Assuming this is correctly sanitized
                    ]);

                    $db->commit();
                } catch (PDOException $e) {
                    $db->rollBack();
                    echo "Error: " . $e->getMessage();
                }
            }
        } else {
            $error_msg = 'Unable to Reserve Transaction! Service Not Found!';
            $error_status = 1;
        }
    }
}

if (isset($_POST['book_procedure'])) {

    $hospital_no = $_REQUEST['hospital_no'];
    $require_theater = $_REQUEST['require_theater'];
    $procedure_list = $_REQUEST['procedure_list'];
    $main_data = $_REQUEST['consultant_id'];

    $pp = explode('__', $main_data);
    $consultant_name = $pp[1];
    $consultant_id = $pp[0];

    $reason_procedure = $_REQUEST['reason_procedure'];
    $theater_select = $_REQUEST['theater_select'];
    $procedure_time = $_REQUEST['procedure_time'];

    $old_procedure_name = $_REQUEST['old_procedure_name'];
    $old_procedure_id = $_REQUEST['old_procedure_id'];

    if ($theater_select == 'Other') {
        $theater_select = $_REQUEST['new_theater'];
    }

    $theater_select = trim(ucwords(strtolower($theater_select)));

    $date_time = preg_split('/T/', $_POST['start_date']);
    $ap_date = $date_time[0];
    $ap_time = $date_time[1];
    $date_timee = $ap_date . ' ' . $ap_time;


    if (substr($hospital_no, 0, 2) === 'EX') {
        // starts with EX

        $stmt = $db->query("SELECT * FROM pharm_ext WHERE transc_code='$hospital_no'");
        if ($stmt->rowCount() > 0) {
            $hospital_no = null;
            $row_serv = $stmt->fetch(PDO::FETCH_ASSOC);
            $patient_name = $row_serv['cust_name'];
            $hospital_no = $row_serv['transc_code'];
            $sex = null;
            $patient_insurance = 'EX';
            $patient_access_type = null;
            $nhis_no_ext = null;
            $nhis_no = null;
            $insurance_name = 'Private(Self)';
            $interest = null;
            $insurance_type =  'Private(Self)';
            $insurance = '';
            $services_access = null;
            $insurance_no = 1000;
            $payment_mode = null;
            $add_minus = null;
        }
    } else {

        $patient_info = $Patient->getByHospitalNo($hospital_no);
        if (!empty($patient_info)) {
            $dob = $patient_info->dob;
            $patient_name =  $patient_info->surname . ' ' . $patient_info->fname;
            $sex = $patient_info->gender;
            $patient_insurance = $patient_info->insurance_type;
            $patient_access_type = $patient_info->services_access;
            $nhis_no_ext = $patient_info->nhis_no_ext;
            $nhis_no = $patient_info->nhis_no;
            $insurance_name = $patient_info->insurance_name;
            $interest = $patient_info->interest;
            $insurance_type = $patient_info->insurance_type;
            $insurance = $patient_info->insurance_type;
            $services_access = $patient_info->services_access;
            $insurance_no = $patient_info->insurance_no;
            $payment_mode = $patient_info->payment_mode;
            $add_minus = $patient_info->add_minus;
        } else {
            $hospital_no = '';
        }
    }

    if ($hospital_no == '') {
        echo '<div class="alert alert-danger">Please type and select a patient from <u>DROPDOWN LIST</u> to proceed</div>';
    } else {

        $patientOnQueuestmt = $db->prepare("SELECT * FROM apptm  
		WHERE hospital_no = '$hospital_no' AND status != 'cancelled' ORDER BY sn desc LIMIT 1 ");
        $patientOnQueuestmt->execute();
        if ($patientOnQueuestmt->rowCount() > 0) {
            $patientAppoint = $patientOnQueuestmt->fetch();
            $app_no = $patientAppoint['appt_no'];
        } else {
            $app_no = $hospital_no;
        }


        $procedure_item_services = '';
        $sn = 1;
        $procedure_sn = $procedure_list;

        $stmt = $db->query("SELECT * FROM prices_table WHERE sn=$procedure_sn");
        if ($stmt->rowCount() > 0) {
            $row_serv = $stmt->fetch(PDO::FETCH_ASSOC);
            $item_sn = $row_serv['sn'];
            $item_services = $row_serv['item_service'];
            $coverage = $row_serv['coverage'];
            $price_table = $row_serv['price_table'];
            $serv_group = $row_serv['price_table'];
            $cat_type = $row_serv['category'];
            $dept_id = $row_serv['dept'];
            $hosp_price = $row_serv['hosp_price'];
            $ext_price = $row_serv['ext_price'];
            $nhis_price = $row_serv['nhis_price'];
            $nhis_price = $nhis_price == 0 ? $hosp_price : $nhis_price;
            $cash_price = $hosp_price;

            $procedure_item_services = $item_services;
            $hos_no = $hospital_no;

            $target_sn = $item_sn;
            $NHIS_DRUG_CONSUMBL_STATE = 0;
            $_tariff_table = "hmo_medical_tariff";

            $amount_invoice = new_service_amount_cal(
                $db,
                $hospital_no,
                $app_no,
                $interest,
                $insurance_type,
                $insurance_no,
                $hosp_price,
                $ext_price,
                $nhis_price,
                $services_access,
                $add_minus,
                $payment_mode,
                $_tariff_table,
                $target_sn,
                $NHIS_DRUG_CONSUMBL_STATE,
                true
            );


            $claim_amt = $amount_invoice["claim_amt"];
            $amount_paying = $amount_invoice["amount_paying"];
            $pay_mode = $amount_invoice["pay_mode"];
            $ccop_int_charge = $amount_invoice["ccop_int_charge"];
            $item_amt = $amount_invoice["amount_paying"];

            if ($claim_amt > 0) {
                $procedure_amount = $claim_amt;
            } else {
                $procedure_amount = $item_amt;
            }


            $setdate = date('Y-m-d H:i:s');
            $invoice_no = date('m') . sprintf('%006d', mt_rand(00000, 99999));
            $one = 1;

            $date_entry = date('Y-m-d');
            $stmt = $db->prepare("SELECT * FROM patient_ap_services WHERE hospital_no = ? AND drug_sn = ? and paystatus = '0' and serv_group ='Medical Services' and date(date_entry) ='$date_entry'");
            $stmt->execute(array($hospital_no, $item_sn));


            if ($stmt->rowCount() <= 5) {

                try {
                    $db->beginTransaction();
                    $sql = $db->prepare("INSERT INTO patient_ap_services (app_no,hospital_no,serv_group,cat_type,dept_id,drug_sn,item_services,hosp_price,claim_amt,interest,qty,invoice_status,invoice_no,prepared_by,created_by,date_entry,pay,pay_mode) 
                    VALUES (:app_no,:hospital_no,:serv_group,:cat_type,:dept_id,:drug_sn,:item_services,:hosp_price,:claim_amt,:interest,:qty,:invoice_status,:invoice_no,:prepared_by,:created_by,:date_entry,:pay,:pay_mode)");

                    $sql->bindParam(':app_no', $app_no, PDO::PARAM_STR);
                    $sql->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
                    $sql->bindParam(':serv_group', $serv_group, PDO::PARAM_STR);
                    $sql->bindParam(':cat_type', $cat_type, PDO::PARAM_STR);
                    $sql->bindParam(':dept_id', $dept_id, PDO::PARAM_STR);
                    $sql->bindParam(':drug_sn', $item_sn, PDO::PARAM_STR);
                    $sql->bindParam(':item_services', $item_services, PDO::PARAM_STR);
                    $sql->bindParam(':hosp_price', $hosp_price, PDO::PARAM_STR);
                    $sql->bindParam(':claim_amt', $claim_amt, PDO::PARAM_STR);
                    $sql->bindParam(':interest', $ccop_int_charge, PDO::PARAM_STR);
                    $sql->bindParam(':qty', $one, PDO::PARAM_STR);
                    $sql->bindParam(':invoice_status', $one, PDO::PARAM_STR);
                    $sql->bindParam(':invoice_no', $invoice_no, PDO::PARAM_STR);
                    $sql->bindParam(':prepared_by', $_SESSION['fullname'], PDO::PARAM_STR);
                    $sql->bindParam(':created_by', $_SESSION['id'], PDO::PARAM_STR);
                    $sql->bindParam(':date_entry', $setdate, PDO::PARAM_STR);
                    $sql->bindParam(':pay', $amount_paying, PDO::PARAM_STR);
                    $sql->bindParam(':pay_mode', $pay_mode, PDO::PARAM_STR);
                    $sql->execute();
                    $lastId = $db->lastInsertId();

                    if ($lastId > 0) {
                        // Save procedure
                        // $check = $Procedure->get(["hospital_no" => $hospital_no, "procedures" => $procedure_item_services, "sDate" => $date_timee]);
                        ///if (empty($check)) {
                        $setdate = date('Y-m-d H:i:s');

                        if (isset($old_procedure_id) && !empty($old_procedure_id)) {
                            $stmt = $db->prepare("INSERT INTO procedures(app_no, hospital_no,name,insurance_no,insurance_type,interest,primary_diag,dept_id,referral,prepared_by,date_entry,sDate,procedures, service_id, cost, created_by,consultant_id, consultant_name,require_theater,theater,procedure_time,sale_no, old_procedure_id, old_procedure_name) VALUES (?,?,?,?,?,?,?,?,?,?,?, ?, ?, ?, ?, ?, ?,?,?,?,?,?,?,?)");
                            $save2 = $stmt->execute(array($app_no, $hospital_no, $patient_name, $insurance_no, $patient_insurance, $interest, $reason_procedure, $dept_id, 'IN', $_SESSION['fullname'], $setdate, $date_timee, $procedure_item_services, $item_sn, $procedure_amount, $_SESSION['id'], $consultant_id, $consultant_name, $require_theater, $theater_select, $procedure_time, $lastId, $old_procedure_id, $old_procedure_name));
                        } else {
                            $stmt = $db->prepare("INSERT INTO procedures(app_no, hospital_no,name,insurance_no,insurance_type,interest,primary_diag,dept_id,referral,prepared_by,date_entry,sDate,procedures, service_id, cost, created_by,consultant_id, consultant_name,require_theater,theater,procedure_time,sale_no) VALUES (?,?,?,?,?,?,?,?,?,?,?, ?, ?, ?, ?, ?, ?,?,?,?,?,?)");
                            $save2 = $stmt->execute(array($app_no, $hospital_no, $patient_name, $insurance_no, $patient_insurance, $interest, $reason_procedure, $dept_id, 'IN', $_SESSION['fullname'], $setdate, $date_timee, $procedure_item_services, $item_sn, $procedure_amount, $_SESSION['id'], $consultant_id, $consultant_name, $require_theater, $theater_select, $procedure_time, $lastId));
                        }
                        if ($save2) {
                            // Commit the transaction
                            $db->commit();
                            $error_status = 2;
                            $error_msg = 'Success: Request Saved Successfully!';
                        } else {
                            // If the second insert fails, throw an exception
                            throw new Exception('Failed to save procedure.');
                        }
                        // } else {
                        //    $error_status = 1;
                        //     $error_msg = 'Error : Procedure Already Exist for the Selected Date & Time!';
                        // }
                    }
                } catch (Exception $e) {
                    // Rollback the transaction if something failed
                    $db->rollBack();
                    $error_status = 1;
                    $error_msg = 'Error: ' . $e->getMessage();
                }
            } else {
                $error_status = 1;
                $error_msg = 'Error : Procedure Already Exist!';
            }
        }
    }
}

if (isset($_GET['pr'])) {
    include_once('procedure_detail.php');
} elseif (isset($_GET['report'])) {
    include_once('_reports.php');
} else {
?>
    <div class="ibox ">
        <div class="ibox-title">
            <h5>Procedures</h5>
            <div class="ibox-tools">
            </div>
        </div>

        <div class="ibox-content">
            <?php
            if (isset($_GET['patient']) && in_array($_SESSION['rights'], ['NS', 'AD', 'MD', 'DR'])) {
                if ($rights == 'NS') {
                    $href = "nursing";
                } else {
                    $href = "doctor";
                } ?>
                <a href="../<?= $href; ?>/patient.php?hosp_no=<?php echo $_GET['patient']; ?>" class="btn btn-primary">
                    <i class="fa fa-user"></i>&nbsp;Goto Patient Dashboard</a>
                &nbsp; | &nbsp;

            <?php
            }
            ?>
            <a href="../doctor/index.php?procedure" class="btn btn-success" style="color:#fff"> <i class="fa fa-home"></i>&nbsp;Goto All Procedures List </a>
            &nbsp; | &nbsp;
            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#bookProcedureModal"> + Book New Procedure </button> &nbsp; | &nbsp;
            <a href="../doctor/procedure.php?procedure&report" class="btn btn-success " style="color:#fff">&nbsp;Procedures Report & Statistic</a>


            <div class="" style="margin-top:20px">
                <?php

                if (!isset($_GET['patient'])) {
                    include_once('../inc/procedure_stats.php');
                }

                ?>
            </div>

            <hr>
            <?php
            include_once('_procedure_list.php');
            ?>
        </div>
    </div>
<?php
}
?>




<?php
$procedure_history = [];
include_once('_book_procedure_modal.php');
?>
<script>
    $("#bookProcedureModal").modal('show');
</script>
<script>
    function load_template() {
        var template_id1 = document.getElementById('template_id1').value;
        toastr.info('Please wait...', '', {
            timeOut: 5000
        })
        $.ajax({
            url: "../inc/text_editor2.php",
            method: "POST",
            data: {
                template_id: template_id1
            },
            success: function(data) {
                toastr.clear();
                $("#pre_opt_notes").html(data);
            }
        });
    }

    function load_template2() {
        var template_id2 = document.getElementById('template_id2').value;
        toastr.info('Please wait...', '', {
            timeOut: 5000
        })
        $.ajax({
            url: "../inc/text_editor2.php",
            method: "POST",
            data: {
                template_id: template_id2
            },
            success: function(data) {
                toastr.clear();
                $("#post_opt_notes").html(data);
            }
        });
    }


    function pre_operation_save() {
        let mgt_notes = $("#pre_opt_notes").html();
        var sn = document.getElementById('sn').value;
        var sn_code = document.getElementById('sn_code').value;
        var hospital_no = document.getElementById('hospital_no').value;

        toastr.info('Please wait...', 'Saving', {
            timeOut: 1000
        })
        $.ajax({
            url: "../inc/text_editor2.php",
            method: "POST",
            data: {
                save_pre_operation: true,
                pre_opt_notes: mgt_notes,
                sn: sn,
                hospital_no: hospital_no
            },
            success: function(response) {
                ///document.getElementById("pre_operation_btn").disabled = true; 
                toastr.success(response, 'Attention', {
                    timeOut: 1000
                })
                window.location.href = "index.php?procedure&pr=" + sn_code;
            }
        });
    }

    function anas_operation_save() {
        let mgt_notes = $("#anas_opt_notes").html();
        var sn = document.getElementById('sn').value;
        var sn_code = document.getElementById('sn_code').value;
        var hospital_no = document.getElementById('hospital_no').value;

        toastr.info('Please wait...', 'Saving', {
            timeOut: 1000
        })

        $.ajax({
            url: "../inc/text_editor2.php",
            method: "POST",
            data: {
                save_anas_operation: true,
                anas_opt_notes: mgt_notes,
                sn: sn,
                hospital_no: hospital_no
            },
            success: function(response) {
                ///document.getElementById("pre_operation_btn").disabled = true; 
                toastr.success(response, 'Attention', {
                    timeOut: 1000
                })
                window.location.href = "index.php?procedure&pr=" + sn_code;
            }
        });
    }

    $(document).on('click', '.change_item', function() {
        var change_item_id = $(this).attr("id");
        if (change_item_id != '') {
            $.ajax({
                url: "fetch_set_change_procedure.php",
                method: "POST",
                data: {
                    procedure_sn: change_item_id
                },
                success: function(data) {

                    $('.modal-title').text('Change Procedure for Patient');
                    $('#change_item_modal').modal('show');
                    $('#change_item_body').html(data);
                }
            });
        }

    });

    function change_procedure_list() {
        var interest = document.getElementById('interest').value;
        var insurance_type = document.getElementById('insurance_type').value;
        var add_minus = document.getElementById('add_minus').value;
        var services_access = document.getElementById('services_access').value;
        var change_procedure = document.getElementById('change_procedure').value;
        var hospital_no = document.getElementById('hospital_no').value;
        var app_no = document.getElementById('app_no').value;

        $.ajax({
            url: "fetch_set_change_procedure.php",
            method: "POST",
            data: {
                interest: interest,
                insurance_type: insurance_type,
                add_minus: add_minus,
                change_procedure: change_procedure,
                app_no: app_no,
                hospital_no: hospital_no,
                services_access: services_access
            },
            success: function(data) {
                var jsonn = JSON.parse(data);
                document.getElementById('amount_paying').value = jsonn["amount_paying"];
                document.getElementById('claim_amt').value = jsonn["claim_amt"];
                document.getElementById('pay_mode').value = jsonn["pay_mode"];
                document.getElementById('ccop_int_charge').value = jsonn["ccop_int_charge"];
                document.getElementById('hosp_price').value = jsonn["hosp_price"];

            }
        });

    }

    function payNow(sale_sn, target, hospital_no) {

        if (target == 'procedure') {
            var rr = confirm("Are you sure you want to Pay from Patient's Deposit?");
        } else {
            rr = true;
        }

        if (rr === true) {

            document.getElementById('pay_now' + sale_sn).innerHTML = "Wait ...";
            document.getElementById('pay_now' + sale_sn).disabled = true;

            $.ajax({
                url: "../payfrom_wallet.php",
                method: "POST",
                data: {
                    sale_sn: sale_sn
                },
                success: function(data) {

                    var jsonn = JSON.parse(data);
                    if (jsonn["status"] == 1) {

                        document.getElementById("pay_now" + sale_sn).innerHTML = 'Pay from Wallet';
                        document.getElementById('pay_now' + sale_sn).disabled = false;
                        toastr.error(jsonn["message"], 'Attention', {
                            timeOut: 5000
                        })
                    } else {

                        document.getElementById("pay_now" + sale_sn).innerHTML = 'Paid';
                        toastr.success('Successful!', 'Success', {
                            timeOut: 5000
                        })
                    }

                }
            });
        }
    }

    function confirmChange() {
        return confirm("Are you sure you want to change?");
    }

    function post_operation_save() {

        let mgt_notes = $("#post_opt_notes").html();
        var sn = document.getElementById('sn').value;
        var sn_code = document.getElementById('sn_code').value;
        var hospital_no = document.getElementById('hospital_no').value;
        var post_op_results = document.getElementById('post_op_results').value;
        var performed_date = document.getElementById('performed_date').value;
        var service_id = document.getElementById('service_id').value;
        var cr = document.getElementById('cr').value;
        var sale_no = document.getElementById('sale_no').value;
        var post_opt_notes_id = document.getElementById('post_opt_notes_id').value;
        var post_opt_notes_old = document.getElementById('post_opt_notes_old').value;

        toastr.info('Please wait...', 'Saving', {
            timeOut: 1000
        })
        $.ajax({
            url: "../inc/text_editor2.php",
            method: "POST",
            data: {
                save_post_operation: true,
                post_opt_notes: mgt_notes,
                sn: sn,
                hospital_no: hospital_no,
                post_opt_notes_id: post_opt_notes_id,
                post_op_results: post_op_results,
                performed_date: performed_date,
                service_id: service_id,
                sale_no: sale_no,
                post_opt_notes_old: post_opt_notes_old,
                cr: cr
            },
            success: function(response) {

                var jsonn = JSON.parse(response);
                if (jsonn["status"] == 1) {
                    toastr.success(jsonn["message"], 'Attention', {
                        timeOut: 1000
                    })
                } else {
                    toastr.error(jsonn["message"], 'Attention', {
                        timeOut: 1000
                    })
                }
                /// window.location.href = "index.php?procedure&pr=" + sn_code;
            }
        });
    }

    $(document).ready(function() {
        var search_result = [];

        $('#search_patient_input').typeahead({
            minLength: 5, // start searching after 2 characters
            items: 100, // 🔴 IMPORTANT: default is 8, increase it
            autoSelect: false, // optional, prevents auto selection
            source: function(query, query_response) {
                $.ajax({
                    url: "_search_patient_json.php",
                    method: "POST",
                    data: {
                        search_patient_json: true,
                        input_text: $('#search_patient_input').val()
                    },
                    dataType: "json",
                    success: function(data) {
                        search_result = data;

                        // Map both name and hospital_no
                        query_response($.map(data, function(item) {
                            return item.name + ' | ' + item.hospital_no;
                        }));
                    }
                });
            },

            updater: function(item) {
                // Find the selected item in search_result
                search_result.forEach(element => {
                    if ((element.name + ' | ' + element.hospital_no) === item) {
                        $('#search_patient_hospital_no').val(element.hospital_no);
                        return item;
                    }
                });
                return item;
            },

            minLength: 1,
            autoSelect: true
        });
    });
</script>
<script>
    $(document).ready(function() {
        $('.trumbowygEditor').trumbowyg({
            btns: [
                ['viewHTML'],
                ['undo', 'redo'], // Only supported in Blink browsers
                ['formatting'],
                ['strong', 'em', 'del'],
                ['superscript', 'subscript'],
                ['link'],
                ['insertImage'],
                ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
                ['unorderedList', 'orderedList'],
                ['horizontalRule'],
                ['removeformat'],
                ['fullscreen']
            ]
        });
    })
</script>