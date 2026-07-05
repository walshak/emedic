<?php
if (isset($_REQUEST['cancelDialysis'])) {
    session_start();
    include_once('../../Connections/Conn.php');
    include("../inc/credit_current_balance.php");
    include('../objects.php');
    $message = 'Something went wrong';
    $status = false;
    header('Content-Type: application/json');

    $token =  $_REQUEST['token'];
    $stmt = $db->prepare("SELECT id, app_no, hospital_no, price_table_id from dialysis WHERE token = ?  AND status = '1' LIMIT 1 ");
    $stmt->execute(array($token));
    if ($stmt->rowCount() > 0) {
        $dialysisInfo = json_decode(json_encode($stmt->fetch(PDO::FETCH_ASSOC)));

        $PatientApService_info = $PatientApService->get([
            'app_no' => $dialysisInfo->app_no,
            'hospital_no' => $dialysisInfo->hospital_no,
            'drug_sn' => $dialysisInfo->price_table_id
        ]);




        if (!empty($PatientApService_info)) {
            $paystatus = $PatientApService_info->paystatus;
            if ($paystatus == '0') {
                $stmt = $db->prepare("DELETE FROM dialysis WHERE id = ?  AND status = '1' LIMIT 1 ");
                $stmt->execute([$dialysisInfo->id]);

                $removed = $PatientApService->delete(['sn' => $PatientApService_info->sn]);
                $status = true;
                $message = 'Success: Dialysis cancelled';
            } else {
                $message = 'Sorry! You cannot cancel this dialysis [ Payment already made ]';
            }
        }
    }

    echo json_encode(["status" => 200, "message" => $message, 'success' => $status]);
    exit;
}

?>
<style>
    table {
        font-size: 12px;
    }

    td {
        padding: 10px;

    }

    th {
        text-align: center;
        font-weight: bold;
    }
</style>
<?php
/*  ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);  */
$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
if (isset($_GET["d"])) {
    if (isset($_GET["saving"])) {
        if ($_GET["saving"] == "1010") {
            if (isset($_POST["dialysis_id"]) and $_POST["dialysis_id"] > 0) {
                $data['fluid_loss'] = '';
                if (!empty($data['uf']) && !empty($data['duration'])) {
                    $uf =   floatval($data['uf']);
                    $duration =  floatval($data['duration']);
                    if ($duration != 0) {
                        $data['fluid_loss'] = round($uf / $duration, 2);
                    }
                }

                $DialysisData->save($_POST);
                $notes = trim($_POST['dialysis_note']);
                $app_no = $_POST['app_no'];
                $hospital_no = $_POST['hospital_no'];
                $setdate =  $now_setdate = date('Y-m-d H:i:s');

                if ($notes != '') {
                    $stmt = $db->prepare('INSERT INTO  notes (app_no, hospital_no, notes, tag, notes_type, prepared_by, date_entry, specialty, created_by, 
                    service_id)  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                    $updated = $stmt->execute(
                        array(
                            $app_no,
                            $hospital_no,
                            $notes,
                            'NS',
                            'dialysis',
                            $_SESSION['fullname'],
                            $now_setdate,
                            $setdate,
                            null,
                            null
                        )
                    );
                }

                $date_timee = date("Y-m-d h:i:s");
                $app_expiration_date = date("Y-m-d h:i:s", strtotime($date_timee . "+1 days", time()));
                $stmt = $db->prepare("UPDATE apptm SET queue_lock = '1', app_expiration_date = :app_expiration_date WHERE appt_no = :appt_no AND hospital_no = :hospital_no AND queue_lock = '0'");
                $stmt->bindParam(':app_expiration_date', $app_expiration_date);
                $stmt->bindParam(':appt_no', $app_no);
                $stmt->bindParam(':hospital_no', $hospital_no);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    $stmt = $db->prepare("UPDATE enrollee SET visit_status = 'old' WHERE hospital_no='$hospital_no'");
                    $stmt->execute();
                }

                $actual_link = str_replace("&saving=1010", "", $actual_link);
                header("Location: " . $actual_link);
            }
        }
    }

    $token = $_GET["d"];
    $details_page_url = 'dialysis.php?p=' . $_GET["p"] . '&d=' . $_GET["d"];

    /////////////// Mark as completed ////////////////////
    if (isset($_POST['mark_dialysis_as_completed'])) {
        $id = $_POST['dialysis_id'];


        $error_status = 1;
        $error_msg = 'Oops! Something went wrong....';


        $date_marked_completed = date('Y-m-d H:i:s');
        $mark_completed_by = $_SESSION['fullname'];




        $update_completed_stmt = $db->prepare("UPDATE  dialysis SET completed = 'yes', date_marked_completed=?, mark_completed_by=? WHERE id = ?");
        $update_dialysis = $update_completed_stmt->execute(array($date_marked_completed, $mark_completed_by, $id));
        if ($update_dialysis == true) {
            $error_status = 2;
            $error_msg = 'Success: Dialysis marked as completed';
        }
    }


    if (isset($_POST['saveDialysisNote'])) {


        $data['uf'] = $_POST['uf'];
        $data['blood_flow'] = $_POST['blood_flow'];
        $data['vp_pre_weight'] = $_POST['vp_pre_weight'];
        $data['ap_post_weight'] = $_POST['ap_post_weight'];
        $data['ufr'] = $_POST['ufr'];
        $data['performed_date'] = $_POST['performed_date'];
        $data['performed_time'] = $_POST['performed_time'];
        $data['performed_by'] = $_POST['performed_by'];
        $data['hep'] = $_POST['hep'];
        $data['bp'] = $_POST['bp'];
        $data['pulse'] = $_POST['pulse'];
        $data['fluid_loss'] = $_POST['fluid_loss'];
        $data['dialysis_note'] = $_POST['dialysis_note'];
        $data['diagnosis'] = $_POST['diagnosis'];
        $data['access'] = $_POST['access'];
        $data['dialyzer'] = $_POST['dialyzer'];
        $data['pcv'] = $_POST['pcv'];
        $data['duration'] = $_POST['duration'];
        $data['spo'] = $_POST['spo'];
        $data['name_nurse'] = $_POST['name_nurse'];
        $data['duty_shift'] = $_POST['duty_shift'];
        $id = $_POST['token'];
        $data['token'] = $_POST['dialysis_token'];
        $data['intradialysis_complication'] = $_POST['intradialysis_complication'];

        $data['fluid_loss'] = '';
        if (!empty($data['uf']) && !empty($data['duration'])) {
            $uf =   floatval($data['uf']);
            $duration =  floatval($data['duration']);
            if ($duration != 0) {
                $data['fluid_loss'] = round($uf / $duration, 2);
            }
        }



        $setdate = date('Y-m-d H:i:s');
        $error_status = 1;
        $error_msg = 'Oops! Something went wrong....';



        $data['completed'] = 'no';
        $data['date_marked_completed'] = NULL;
        if (isset($_POST['completed'])) {
            $data['completed'] = $_POST['completed'];
            $data['date_marked_completed'] = date('Y-m-d H:i:s');
            $data['mark_completed_by'] = $_SESSION['fullname'];
        }



        $update_dialysis = $DialysisData->update($data, $id);

        if ($update_dialysis) {

            $app_no = $_POST['app_no'];
            $hospital_no = $_POST['hospital_no'];
            $stmt = $db->prepare("SELECT * from notes WHERE app_no = ?  AND hospital_no = ?  ");
            $stmt->execute(array($app_no, $hospital_no));

            if ($stmt->rowCount() == 0) {

                /// noted 
                $now_setdate = date('Y-m-d H:i:s');

                $notes = $_POST['dialysis_note'];
                $stmt = $db->prepare('INSERT INTO  notes (app_no, hospital_no, notes, tag, notes_type, prepared_by, date_entry, specialty, created_by, 
               service_id)  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $updated = $stmt->execute(
                    array(
                        $app_no,
                        $hospital_no,
                        $notes,
                        'NS',
                        'dialysis',
                        $_SESSION['fullname'],
                        $now_setdate,
                        $setdate,
                        null,
                        null
                    )
                );
            }


            $fullname = $_SESSION['fullname'];
            $fullname = str_replace("'", "", $fullname);
            $stmt = $db->prepare("UPDATE  patient_ap_services SET drug_status = '1', dsp_by='$fullname' WHERE hospital_no=?  and app_no=? and cat_type='Dialysis' ");
            $stmt->execute(array($hospital_no, $app_no));

            $error_status = 2;
            $error_msg = 'Success! Dialysis data is saved....';
            $actual_link = str_replace("&saving=1010", "", $actual_link);
            $actual_link = str_replace("&data=" . base64_encode(base64_encode($id)), "", $actual_link);
            header("Location: " . $actual_link);
        }
    }

    $where = " ";
    if (isset($_GET['completed'])) {
        $where = " AND completed = 'yes' ";
    }


    $stmt = $db->prepare("SELECT * from dialysis WHERE token = ?  AND status = '1' LIMIT 1 ");
    $stmt->execute(array($token));

    // Get last id of dialysis
    $last_id = null;
    $dialysis_id = null;
    if ($stmt->rowCount() > 0) {
        $dialysis = json_decode(json_encode($stmt->fetch(PDO::FETCH_ASSOC)));
        $hospital_no = $dialysis->hospital_no;
        $patient_name = $dialysis->patient_name;
        $patient_info = $Patient->get(['hospital_no' => $hospital_no]);
        $dialysis_id;
        $dialysis_info = $Dialysis->find($dialysis->id);

        $hospital_no = $dialysis_info->hospital_no;
        // Patient info:
        $patient_name =  $patient_info->surname . ' ' . $patient_info->fname;
        $sex = $patient_info->gender;
        $insurance_type = $patient_info->insurance_type;
        $interest = $patient_info->interest;
        $add_minus = $patient_info->add_minus;
        $payment_mode = $patient_info->payment_mode;
        $age = 0;
        $age_full = null;
        if (!empty($dob)) {
            $components = preg_split("/-/", $dob);
            $year = $components[0];
            $age = date('Y') - $year;
            $age_full = $age . ' yrs';

            if ($age == 0) {
                $month = abs(date('m') - $components[1]);
                $age_full = $month . ' Months';
            }
        }
        $token = $dialysis_info->token;
        $completed = $dialysis_info->completed;
        $uf = $dialysis_info->uf;
        $blood_flow = $dialysis_info->blood_flow;

        $stmt = $db->prepare(" SELECT * from dialysis WHERE token = ?  AND status = '1'  ");
        $stmt->execute(array($token));
        if ($stmt->rowCount() > 0) {

            $dialysis_list = json_decode(json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)));
            foreach ($dialysis_list as $key => $dialysis) {
                // $hospital_no = $dialysis->hospital_no;
                // $patient_name = $dialysis->patient_name;
                $last_id = $dialysis->id;
                $dialysis_id = $dialysis->dialysis_id;
            }
        }
    } else {
        echo "<script>window.location.href = 'dialysis.php';</script>";
        exit;
    }



    $dialysis_data = $DialysisData->getByHosp($hospital_no);
}

?>

<div class="row">
    <div class="col-lg-12">
        <div class="ibox ">
            <div class="ibox-title">
                <h5>Dialysis Panel</h5>
            </div>
            <div class="ibox-content">
                <div class="row">
                    <div class="col-md-9 b-r">
                        <div class="card feature wow fadeInRight animated animated" style="visibility: visible; animation-name: fadeInRight; position: relative;">

                            <?php
                            /// include('_patient_basic_profile.php');
                            ?>

                            <div class="well well-sm">
                                <div class="light-card ">
                                    <?php

                                    if (isset($_REQUEST['dataOnCredit'])) {
                                        $app_service_tbl_id = cleanInput($_POST['app_service_tbl_id']);

                                        $updateStmt = $db->prepare("UPDATE patient_ap_services SET cr = 1 WHERE sn = ? ");
                                        $updateStmt->execute([$app_service_tbl_id]);
                                        $error_status = 2;
                                        $error_msg = 'Success! Dialysis data can be entered on credit';
                                    }

                                    $dialysisInfo = $dialysis_list[0];
                                    $PatientApService_info = $PatientApService->get([
                                        'app_no' => $dialysisInfo->app_no,
                                        'hospital_no' => $dialysisInfo->hospital_no,
                                        'drug_sn' => $dialysisInfo->price_table_id
                                    ]);

                                    $check_count = $dialysisInfo->completed == 'no' ? 1 : 0;

                                    $foundPayment = false;
                                    $paystatus = '0';
                                    $creditStatus = 0;
                                    if (!empty($PatientApService_info)) {

                                        $foundPayment = true;
                                        $paystatus = $PatientApService_info->paystatus;
                                        $pay_mode = $PatientApService_info->pay_mode;
                                        $claim_amt = $PatientApService_info->claim_amt;
                                        $pay = $PatientApService_info->pay;
                                        $creditStatus = $PatientApService_info->cr;
                                    } else {
                                        echo 'Payment not found!';
                                    }

                                    ///////////////// CAP EDIT TO A DAY AFTER MARKED COMPLETED

                                    $can_edit_dialysis_session = true;
                                    if ($dialysisInfo->date_marked_completed != null) {
                                        $last_marked_completed_array = dateDifference_array($dialysisInfo->date_marked_completed);
                                        $day_since_marked_completed = $last_marked_completed_array['day'];

                                        if ($day_since_marked_completed > 0) {
                                            $can_edit_dialysis_session = false;
                                        }
                                    }
                                    ?>
                                    <div>


                                        <table width="100%">
                                            <tr>
                                                <td>
                                                    <h2><strong><?= $dialysisInfo->hospital_no; ?></strong>, <?= $patient_name; ?></h2>

                                                    <p> <strong><?= $dialysisInfo->request_type; ?></strong>

                                                        <?php
                                                        ///	echo '==' . $PatientApService_info->sn;///echo $dialysisInfo->app_no;

                                                        $stmt = $db->prepare("SELECT token FROM dialysis_data WHERE token=:token");
                                                        $stmt->bindValue(':token', $token, PDO::PARAM_STR);
                                                        $stmt->execute();
                                                        if ($stmt->rowCount() == 0) {

                                                            // if ($paystatus == '1') {
                                                        ?>
                                                            <br> <button class="btn btn-sm btn-success btn-xs" title="Change Machine" data-toggle="modal"
                                                                data-target="#changeMachineModal<?= $dialysisInfo->id; ?>"><i class="fa fa-plus"></i> Change Machine </button>
                                                        <?php //}
                                                        }



                                                        ?>

                                                        <br />
                                                    <h2><?php

                                                        if ($pay_mode == 'cash') {
                                                            $pay_Status = 'Paid';
                                                            $amount = $pay;
                                                        } else {
                                                            $pay_mode = 'Claim Insured';
                                                            $amount = $claim_amt;
                                                        }

                                                        if ($paystatus == '1') {
                                                            echo '<b class="text-green">' . $pay_Status . '</b>';
                                                        } else if ($creditStatus == 1) {
                                                            echo '<b class="text-danger">Credit</b>';
                                                        } else {
                                                            echo '<b class="text-danger">Not Paid</b>';
                                                        }


                                                        ?>
                                                        / &#8358; <?= number_format($amount); ?></h2>



                                                </td>
                                                <td>
                                                    <?php

                                                    $complains = [];
                                                    $stmt = $db->prepare("SELECT * FROM tbl_patient_alerts WHERE hospital_no=? and alert!='' ORDER BY id DESC ");
                                                    $stmt->execute(array($hospital_no));
                                                    $clinical_count = $stmt->rowCount();
                                                    if ($clinical_count > 0) {
                                                        $complains = $stmt->fetchAll();

                                                    ?>
                                                        <table width="100%" style="font-size: 14px;">
                                                            <thead>
                                                                <tr>
                                                                    <td><b style="color:red; ">Patient Alert!!!</b></td>
                                                                </tr>
                                                            </thead>
                                                            <tbody style="background-color:moccasin;">

                                                                <?php
                                                                $sn = 1;
                                                                foreach ($complains as $key => $complain) {
                                                                ?>
                                                                    <tr id="pending_tasK_list_<?= $complain['id']; ?>">

                                                                        <td><?= $complain['alert'] = wordwrap($complain['alert'], 30, "<br>\n", true); ?> <b><i><?= $complain['created_by']; ?></i></b>:
                                                                            <?php if ($complain['created_by'] == $_SESSION['fullname']) { ?>
                                                                                <a href="<?= $details_page_url . "&editAlert=" . base64_encode(base64_encode($complain['id'])); ?>" class="btn btn-primary btn-xs">Edit</a>
                                                                            <?php } ?>
                                                                        </td>
                                                                    </tr>
                                                                <?php
                                                                }

                                                                ?>

                                                            </tbody>
                                                        </table>
                                                    <?php
                                                    }

                                                    ?>
                                                </td>

                                            </tr>
                                        </table>

                                        <?php
                                        if ($paystatus == '0' and $pay_mode == 'cash') {
                                            $hosp_no = $dialysisInfo->hospital_no;
                                            $stmt = $db->query("SELECT 
					sum(dr_amt) as TOTAL_DEBITS, 
					sum(cr_amt) as TOTAL_CREDITS
				FROM chart_ledger WHERE hospital_no='$hosp_no' and account_no='2121'");
                                            if ($stmt->rowCount() > 0) {
                                                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                                $TOTAL_CREDITS = $row['TOTAL_CREDITS'];
                                                $TOTAL_DEBITS = $row['TOTAL_DEBITS'];
                                                $current_balance = $TOTAL_CREDITS - $TOTAL_DEBITS;
                                            }
                                            if ($current_balance > 0 and $_SESSION['payfrom_status'] == 1) { ?>
                                                <button type="button" class="btn btn-warning btn-xs dropdown-toggle" id="pay_now<?php echo $PatientApService_info->sn; ?>" onClick="payNow('<?php echo $PatientApService_info->sn; ?>','dailysis','<?= $hosp_no; ?>')">Pay from Wallet</button>
                                        <?php }
                                        }
                                        ?>


                                        <br />
                                        <b class="text-danger"><?= ($dialysisInfo->completed == 'yes' ? 'Completed' : ''); ?></b>
                                        <br />
                                        <b>Request By: </b> <?= $dialysisInfo->request_by; ?> | <?= date('d M, Y, h:i A', strtotime('' . $dialysisInfo->request_date)); ?>
                                        <br />
                                        <h4>Doctor's Request Notes & Dialysis Data : </h4>
                                        <p>
                                            <?php

                                            $stmt_check = $db->prepare("SELECT request_note from dialysis 
                                                WHERE token = '$token' and hospital_no='$hospital_no'");
                                            $stmt_check->execute();
                                            $rw = $stmt_check->fetch(PDO::FETCH_ASSOC);
                                            ?>
                                        <div style="font-size: 14px; " align="center"><?php echo $request_note = $rw['request_note']; ?></div>
                                        <hr>
                                        </p>
                                        </p>
                                        <hr />

                                        <input type="button" name="on_cr" value="See Biodata" data-target="#modal"
                                            id="<?php echo $hospital_no; ?>" class="btn btn-primary btn-sm bio_data_link" style="font-size: 12px; color:white; " />



                                        <?php
                                        $iddd = $dialysisInfo->id;
                                        $dialysis_list_stmt2 = $db->prepare("SELECT id from dialysis_data WHERE dialysis_id='$iddd'");
                                        $dialysis_list_stmt2->execute();

                                        if ($foundPayment == true) {
                                            if ($paystatus == '1' || $creditStatus == 1) {
                                                if ($dialysisInfo->completed == 'no') {
                                        ?>

                                                    <button class="btn btn-sm btn-success" title="Add Dialysis Data" data-toggle="modal"
                                                        data-target="#dialysisAddNoteModal<?= $dialysisInfo->id; ?>"><i class="fa fa-plus"></i> Enter data </button>

                                                    <?php if ($dialysis_list_stmt2->rowCount() > 0) { ?>
                                                        <form action="<?php echo $editFormAction; ?>" method="post" onsubmit="return confirm('Please confirm to mark as completed')" style="display:inline">
                                                            <input type="hidden" name="dialysis_id" value="<?php echo $dialysisInfo->id; ?>">
                                                            <button type="submit" name="mark_dialysis_as_completed" class="btn btn-sm btn-primary" title="Mark as Completed"><i class="fa fa-check"></i> Mark completed</button>
                                                        </form>
                                                    <?php } ?>
                                                    <?php // } 
                                                    ?>

                                                    <!-- <button class="btn btn-sm btn-warning" title="View Dialysis Data" data-toggle="modal" 
                                                                data-target="#dialysisData_<?= $dialysisInfo->id ?>"><i class="fa fa-eye"></i> View data </button> -->

                                                <?php
                                                }
                                                ?>

                                                <?php
                                                $p = $_GET['p'];
                                                $d = $_GET['d'];
                                                if ($_SESSION['rights'] == 'NS' and $dialysisInfo->completed == 'no') {

                                                ?>
                                                    <a href="../nursing/patient.php?hosp_no=<?php echo $dialysisInfo->hospital_no; ?>&lab&dly&p=<?= $p; ?>&d=<?= $d; ?>" class="btn btn-sm btn-success" title="Lab Investigation"><i class="fa fa-flask"></i></a>

                                                    <a href="../nursing/patient.php?hosp_no=<?php echo $dialysisInfo->hospital_no; ?>&drug&dly&p=<?= $p; ?>&d=<?= $d; ?>" class="btn btn-sm btn-success" title="Add Drug">Drug</a>
                                                    <?php } else {

                                                    if ($dialysisInfo->completed == 'yes') {
                                                        $date1 = new DateTime("" . $dialysisInfo->date_marked_completed);
                                                        $date2 = new DateTime("now");
                                                        $interval = $date1->diff($date2);
                                                        $hoursDifference =  $interval->d;
                                                    ?>
                                                        <h3>Completed by: <?= $dialysisInfo->mark_completed_by; ?>, <?= date('D d M, Y h:i:A', strtotime("$dialysisInfo->date_marked_completed")); ?>
                                                            <br><?php
                                                                if ($hoursDifference < 1) {



                                                                ?>
                                                                <a href="dialysis.php?p=<?= $p; ?>&d=<?= $d; ?>&undoCompletion=<?php echo base64_encode($dialysisInfo->id); ?>" class="btn btn-xs btn-danger" title="">Undo Completed</a>
                                                                <span class="text-danger">[<?= 'Editing of this dialysis data will be disabled after the next ' . (24 - $hoursDifference) . ' hour(s)'; ?>]</span>
                                                        </h3><?php
                                                                }
                                                            }
                                                        }
                                                                ?>

                                            <a href="dialysis.php?p=<?php echo $_GET['p']; ?>&d=<?php echo $_GET['d']; ?>" class="btn btn-sm btn-default" title="Refresh"><i class="fa fa-repeat"></i></a>

                                            <a href="dialysis.php" class="btn btn-sm btn-danger" title="Close"><i class="fa fa-close"></i> Close </a>

                                        <?php
                                            } else {
                                        ?>
                                            <button class="btn btn-sm btn-success" title="View Request Details" data-toggle="modal" data-target="#viewDialysiDetailsModal<?= $dialysisInfo->id; ?>"><i class="fa fa-eye"> View </i></button>
                                            <button class="btn btn-sm btn-danger" title="Cancel Dialysis" id="cancelDialysis"
                                                arial-token="<?= $dialysisInfo->token; ?>"><i class="fa fa-trash"> Cancel Request</i></button>
                                            <a href="dialysis.php?p=<?php echo $_GET['p']; ?>&d=<?php echo $_GET['d']; ?>" class="btn btn-sm btn-default" title="Refresh"><i class="fa fa-repeat"></i></a>
                                            <hr>
                                            <?php


                                                $stmt = $db->prepare("SELECT * FROM admission as adm  WHERE (adm_status=3 OR adm_status=0) AND hospital_no = ? order by sn DESC LIMIT 1");
                                                $stmt->execute(array($dialysisInfo->hospital_no));
                                                if ($stmt->rowCount() > 0) {
                                                    $mm = " [ <b> This patient is on admission </b> ] <br>";
                                                } else {
                                                    $mm = null;
                                                }

                                                $emr = $dialysisInfo->hospital_no;
                                                $general_credit_limit =    $_SESSION['credit_limit_status'];
                                                $items = call_current_balance($db, $emr, $general_credit_limit);
                                                $current_balance =  $items["current_balance"];
                                                $credit_limit  = $items['bal_credit_limit'];
                                                $credit_limit_actual  = $items['credit_limit'];

                                                if ($stmt->rowCount() > 0 or $amount <= $credit_limit) {
                                            ?>

                                                <p class="text-right">
                                                    <?php if ($credit_limit_actual > 0) { ?>
                                                        <span style="color:red; ">Patient Credit Limit: <?= number_format($credit_limit, 2); ?></span>
                                                    <?php } ?>
                                                    <?= $mm; ?>
                                                <form action="#" method="post" onsubmit="return confirm('Click OK to proceed with data entry on credit.')" style="display:inline-block;float:right">
                                                    <button class="text-danger" name="dataOnCredit"> Click here to enter dialyis data on credit.</button>
                                                    <input type="hidden" name="app_service_tbl_id" value="<?= $PatientApService_info->sn; ?>">
                                                </form>
                                                <br>
                                                </p>
                                            <?php

                                                }

                                            ?>
                                    <?php
                                            }
                                        } else {
                                            echo 'Payment Details Not found!';
                                        }
                                    ?>
                                    </div>

                                </div>
                            </div>


                        </div>


                    </div>
                    <div class="col-md-3">
                        <div class=" wow fadeInLeft animated animated">
                            <?php if (isset($_GET['p'])) { ?>
                                <a href="dialysis.php" class="btn btn-card btn-full btn-text-left pd-20-left <?= ($page == 'home' ? 'btn-card-active' : ''); ?>" style="font-size: 17px; ">&nbsp;&nbsp;<i class="fa fa-home"></i>&nbsp;&nbsp;Home</a>
                            <?php } ?>


                            <?php if (isset($_GET['patient'])) {
                                $patient = $_GET['patient'];
                            ?>
                                <a href="<?= $FormAction_ . '?p=' . base64_encode('New_request'); ?>&patient=<?php echo $patient; ?>" class="btn btn-card btn-full btn-text-left pd-20-left <?= ($page == 'New_request' ? 'btn-card-active' : ''); ?>" style="font-size: 16px; ">&nbsp;&nbsp;<i class="fa fa-file"></i>&nbsp;&nbsp;New Request</a>
                            <?php } else { ?>
                                <a href="<?= $FormAction_ . '?p=' . base64_encode('New_request'); ?>" class="btn btn-card btn-full btn-text-left pd-20-left <?= ($page == 'New_request' ? 'btn-card-active' : ''); ?>" style="font-size: 16px; ">&nbsp;&nbsp;<i class="fa fa-file"></i>&nbsp;&nbsp;New Request</a>
                            <?php } ?>
                        </div>
                        <hr>
                        <?php
                        include_once('dialysis/_dialysis_reminder.php');

                        ?>

                    </div>
                </div>


                <div class="row">
                    <div class="col-md-12">
                        <div class="light-card">
                            <div class="ibox float-e-margins">
                                <div class="ibox-content">



                                    <?php
                                    $dialysis_list_stmt = $db->prepare("SELECT * from dialysis_data WHERE token = ? AND dialysis_id <= ? AND status = '1'");
                                    $dialysis_list_stmt->execute(array($token, $last_id));
                                    $dialysis_list_arrr = json_decode(json_encode($dialysis_list_stmt->fetchAll(PDO::FETCH_ASSOC)));
                                    ?>


                                    <div>
                                        <?php if (count($dialysis_list_arrr) > 0) : ?>
                                            <!-- <p class="text-right"> <button class="btn btn-sm btn-info" title="Print Dialysis Data" data-toggle="modal" data-target="#printialysiDetailsModal<?= $dialysisInfo->id; ?>"><i class="fa fa-print"></i> Print </button> </p>							 -->
                                        <?php endif; ?>

                                        <table class="table table-bordered dataTables-example" width="100%" border='2'>
                                            <thead>
                                                <tr>
                                                    <!-- <th>performed by</th> -->
                                                    <th>Date</th>
                                                    <th>UF</th>
                                                    <th>Blood Flow</th>
                                                    <th>Pre W.</th>
                                                    <th>Post W.</th>
                                                    <th>UFR</th>
                                                    <th>HEP</th>
                                                    <th>BP</th>
                                                    <th>PCV</th>
                                                    <th>Status</th>
                                                    <th>Notes</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php

                                                foreach ($dialysis_list_arrr as $data) {
                                                ?>
                                                    <tr>
                                                        <!-- <td><?= $AdminUser->find($data->performed_by)->fullname; ?></td> -->
                                                        <td><?= date('d M Y', strtotime('' . $data->performed_date)); ?> <?= $data->performed_time; ?></td>
                                                        <td><?= $data->uf; ?></td>
                                                        <td><?= $data->blood_flow; ?></td>
                                                        <td><?= $data->vp_pre_weight; ?></td>
                                                        <td><?= $data->ap_post_weight; ?></td>
                                                        <td><?= $data->ufr; ?></td>
                                                        <td><?= $data->hep; ?></td>
                                                        <td><?= $data->bp; ?></td>
                                                        <td><?= $data->PCV; ?></td>
                                                        <td><?= ($dialysisInfo->completed == 'yes' ? 'Completed' : 'Not Completed') ?></td>
                                                        <td><?= $data->dialysis_note; ?></td>
                                                        <td>
                                                            <?php

                                                            if ($data->created_by == $_SESSION["id"] && $dialysisInfo->completed == 'no') {
                                                            ?>
                                                                <button onclick='(function(){if(confirm("Are sure you want to delete this record?")){let res  = $.ajax({type: "POST",url: "delete_dialysis.php",data: {id:<?= $data->id; ?>},async: false}).responseText; if(res==200){location.reload()}else{alert("Error:Please check your internet connetion")}} })()' class="btn btn-danger fa fa-times"></button>
                                                                <a href="<?= $details_page_url . "&data=" . base64_encode(base64_encode($data->id)); ?>" class="btn btn-success btn-sm">View More & Edit</a>

                                                                <?php
                                                            } else {
                                                                if ($hoursDifference < 1) {
                                                                ?><a href="<?= $details_page_url . "&data=" . base64_encode(base64_encode($data->id)); ?>" class="btn btn-danger btn-sm">View More & Edit </a><?php
                                                                                                                                                                                                            }
                                                                                                                                                                                                        }

                                                                                                                                                                                                                ?>
                                                            <a href="<?= $details_page_url . "&print=" . base64_encode(base64_encode($data->id)); ?>" class="btn btn-primary btn-sm">Print</a>
                                                        </td>
                                                    </tr>
                                                <?php
                                                    ///}
                                                }
                                                ?>

                                            </tbody>

                                        </table>
                                        <h3>Dialysis History</h3>
                                        <table class="table table-bordered dataTables-example" width="100%" border='2'>
                                            <thead>
                                                <tr>
                                                    <!-- <th>performed by</th> -->
                                                    <th>DATE</th>
                                                    <th>REQUEST TYPE/MACHINE</th>
                                                    <th>REQUEST BY</th>
                                                    <th>DURATION</th>
                                                    <th>NURSE</th>
                                                    <th>COMPLETED BY</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $dialysis_list_stmt = $db->prepare("SELECT * FROM dialysis  WHERE  status = '1' AND hospital_no = ? AND token != ? ORDER BY id DESC ");
                                                $dialysis_list_stmt->execute(array($hospital_no, $token));
                                                $dialysis_list_arrr__ = json_decode(json_encode($dialysis_list_stmt->fetchAll(PDO::FETCH_ASSOC)));

                                                foreach ($dialysis_list_arrr__ as $data) {
                                                ?>
                                                    <tr>

                                                        <td><?= date('d M Y', strtotime('' . $data->request_date)); ?> </td>
                                                        <td><?= $data->request_type; ?></td>
                                                        <td><?= $data->request_by; ?></td>
                                                        <td><?= $data->Duration; ?></td>
                                                        <td><?= $data->Name_Nurse; ?></td>
                                                        <td><?= $data->mark_completed_by; ?></td>

                                                        <td>
                                                            <!-- <a href="<?= $details_page_url . "&print=" . base64_encode(base64_encode($data->id)); ?>" class="btn btn-primary btn-sm">View</a>
                                                                                                     -->
                                                        </td>
                                                    </tr>
                                                <?php
                                                    ///}

                                                }
                                                ?>
                                            </tbody>
                                        </table>


                                    </div>
                                </div>
                            </div>
                            <canvas id="myChart" width="400" height="100">
                                <p>Loading chart, please wait...</p>
                            </canvas>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="modal inmodal fade" id="bio_data_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Patient Biodata</h4>
            </div>
            <div class="modal-body" id="bio_data_body">
            </div>
        </div>
    </div>
</div>

<?php
if ($completed == 'no') {
    $stmt_check = $db->prepare("SELECT * from dialysis WHERE token != '$token' and hospital_no='$hospital_no' AND completed = 'no'");
    $stmt_check->execute();
    $check_count = $stmt_check->rowCount();
} else {
    $check_count = 0;
}
?>

<!--- MODALS ------------->
<?php
include_once('_new.php');
include_once('change_machine.php');
if (isset($_GET['data']) && !empty($_GET['data'])):
    $data_token = base64_decode(base64_decode($_GET['data']));
    $dialysis_data_info = [];
    foreach ($dialysis_list_arrr as $key => $dialysis_data): ?>
        <?php if ($dialysis_data->id == $data_token):
            $dialysis_data_info = $dialysis_data;
            break;
        endif;
    endforeach;
    if (!empty($dialysis_data_info)):

        include_once('_edit.php');
        ?>
        <script>
            $(document).ready(function() {
                $('#editDialysisModal').modal('show');
            });
        </script>
    <?php
    endif;
endif;


if (isset($_GET['print']) && !empty($_GET['print'])):
    $data_token = base64_decode(base64_decode($_GET['print']));
    $dialysis_data_info = [];
    foreach ($dialysis_list_arrr as $key => $dialysis_data): ?>
        <?php if ($dialysis_data->id == $data_token):
            $dialysis_data_info = $dialysis_data;
            break;
        endif;
    endforeach;
    if (!empty($dialysis_data_info)):

        include_once('_print.php');
        ?>
        <script>
            $(document).ready(function() {
                $('#printDialysisModal').modal('show');
            });
        </script>
<?php
    endif;
endif;
include('../inc/_other_modals.php');
?>
<script>
    $(document).on('click', '#new-allergy-btn', function() {
        $('#newPatientAllergiesModal').modal('show');
    });

    function save_allergies() {
        var hospital_no = $('#hospital_no').val();
        var alert_notes = $('#allergies_notes').val();
        $.ajax({
            url: "dialysis/_dialysis_reminder.php",
            method: "POST",
            data: {
                saveAlert: true,
                hospital_no: hospital_no,
                alert_notes: alert_notes
            },
            success: function(data) {
                alert(data);
                window.location.reload(true);
            }
        });
        window.location.reload(true);
    }
</script>
<script>
    function closeModal(modal, param) {

        // Get the current URL
        var url = new URL(window.location.href);

        // Get the search parameters
        var searchParams = new URLSearchParams(url.search);

        // Remove the "data" parameter
        searchParams.delete(param);

        // Update the URL without reloading the page
        var newURL = window.location.protocol + '//' + window.location.host + window.location.pathname + '?' + searchParams.toString();
        history.replaceState({
            path: newURL
        }, '', newURL);
        $('#' + modal).modal('hide');

    }
    $(document).on('click', '#cancelDialysis', function() {

        const token = $(this).attr('arial-token');
        if (confirm('Are you sure you want to cancel this dialysis request? ')) {
            $(this).prop('disabled', true);
            $(this).text('Cancelling, please wait... ');
            toastr.info('Please Wait .... ', 'Processing', {
                timeOut: 9000
            })
            $.ajax({
                url: './dialysis/_details.php',
                method: "POST",
                data: {
                    cancelDialysis: true,
                    token: token
                },
                success: function(response) {
                    toastr.clear();
                    console.log(response)
                    if (response.success) {
                        toastr.success(' ', response.message, {
                            timeOut: 9000
                        })
                        location.reload();
                    } else {
                        toastr.error('Error: ', response.message, {
                            timeOut: 9000
                        })
                    }

                    $(this).prop('disabled', false);
                    $(this).text('Cancel Request ');

                },
                error: function(err) {
                    toastr.clear();
                    $(this).prop('disabled', false);
                    console.log(err);
                }
            });
        }


    })
</script>
<script>
    function print_page() {
        document.getElementById('button-area').style.display = 'none'
        window.print();
        document.getElementById('button-area').style.display = 'block'

    }

    function ClickheretoprintDiv(div_id) {
        var disp_setting = "toolbar=yes,location=no,directories=yes,menubar=yes,";
        disp_setting += "scrollbars=yes,width=800, height=400, left=100, top=25";
        var content_vlue = document.getElementById(div_id).innerHTML;

        var docprint = window.open("", "", disp_setting);
        docprint.document.write('<html><head><title>.::Webmedic </title> <link rel="stylesheet" href="../css/bootstrap.min.css">');
        docprint.document.write('</head><body onLoad="self.print()" style="width: 100%; height="auto" font-size:16px; font-family:arial;">');
        docprint.document.write(content_vlue);
        docprint.document.write('</body></html>');
        docprint.document.close();
        docprint.focus();
    }


    $(document).on('click', '.bio_data_link', function() {
        var bio_data_id = $(this).attr("id");
        ////alert(bio_data_id);

        if (bio_data_id != '') {
            $.ajax({
                url: "../doctor/fetch_set.php",
                method: "POST",
                data: {
                    bio_data_id: bio_data_id
                },
                success: function(data) {

                    ////    alert(data);

                    $('.modal-title').text('Patient Bio - Data');

                    $('#bio_data_body').html(data);
                    $('#bio_data_modal').modal('show');
                }
            });
        }
    });
</script>