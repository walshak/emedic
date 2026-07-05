<?php

if (isset($_POST['save_credit_limit'])) {
    include("../process_credit_limit.php");
}

if (isset($_GET["can_y_credit"])) {
    include("../process_credit_limit.php");
}




if (isset($_GET["rv"])) {

    $hosp_no = $_GET["rv"];

    try {
        // Update enrollee insurance info using prepared statements for PHP 5.6+
        $updateSQL = $db->prepare("UPDATE enrollee SET hmo_no = :hmo_no, insurance = :insurance WHERE hospital_no = :hospital_no");
        $updateSQL->bindParam(':hmo_no', $hmo_no_val, PDO::PARAM_STR);
        $updateSQL->bindParam(':insurance', $insurance_val, PDO::PARAM_STR);
        $updateSQL->bindParam(':hospital_no', $hosp_no, PDO::PARAM_STR);

        $hmo_no_val = '1000';
        $insurance_val = 'Private(Self)';

        $updateSQL->execute();

        // Fetch updated values for confirmation/logging
        $stmt = $db->prepare("SELECT hmo_no, insurance FROM enrollee WHERE hospital_no = :hospital_no");
        $stmt->bindParam(':hospital_no', $hosp_no, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() === 1) {
            $rwx = $stmt->fetch(PDO::FETCH_ASSOC);
            $hmo_no = $rwx['hmo_no'];
            $insurance = $rwx['insurance'];

            $desc = 'Patient Record Updated / ' . $hmo_no . ' [ ' . $insurance . ' ] to 1000 [ Private(Self) ]';

            // Fallback if session is empty (PHP 5.6 compatible check)
            $staff = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'Unknown Staff';
            $pid = $hosp_no;
            $pname = '';
            $action = 'Patient Data/Updated';

            include_once("../logs.php");
        } else {
            echo '<b style="color:red">Patient record not found!</b>';
        }
    } catch (PDOException $e) {
        echo "Database Error: " . htmlspecialchars($e->getMessage());
    }
}


if (isset($_GET["dlp"])) {
    $parts = $_GET["dlp"];
    $pp = explode("/", $parts);

    if (count($pp) === 2) {
        $hos_no = $pp[0];
        $appt_no = $pp[1];

        try {
            $db->beginTransaction();

            $stmt = $db->prepare("SELECT app_no FROM patient_ap_services WHERE app_no = :appt_no AND cat_type = 'Dialysis'");
            $stmt->bindParam(':appt_no', $appt_no, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {

                $stmt = $db->prepare("SELECT app_no FROM notes WHERE app_no = :appt_no");
                $stmt->bindParam(':appt_no', $appt_no, PDO::PARAM_STR);
                $stmt->execute();

                if ($stmt->rowCount() === 0) {

                    // Delete patient_ap_services
                    $deletePatientApServices = $db->prepare("DELETE FROM patient_ap_services WHERE app_no = :appt_no AND paystatus = '0'");
                    $deletePatientApServices->bindParam(':appt_no', $appt_no, PDO::PARAM_STR);
                    $deletePatientApServices->execute();

                    // Delete apptm
                    $deleteApptm = $db->prepare("DELETE FROM apptm WHERE appt_no = :appt_no AND queue_lock=0");
                    $deleteApptm->bindParam(':appt_no', $appt_no, PDO::PARAM_STR);
                    $deleteApptm->execute();

                    $db->commit();

                    echo '
<div class="alert alert-success" role="alert" style="font-weight:bold; margin-top:10px;">
    <i class="fa fa-exclamation-triangle"></i> 
    Save Successful
</div>';
                } else {
                    echo '
<div class="alert alert-danger" role="alert" style="font-weight:bold; margin-top:10px;">
    <i class="fa fa-exclamation-triangle"></i> 
    You cannot delete this appointment because the doctor has documented!
</div>';
                }
            } else {
                $db->rollBack();

                echo '
<div class="alert alert-danger" role="alert" style="font-weight:bold; margin-top:10px;">
    <i class="fa fa-exclamation-triangle"></i> 
    You cannot delete the dialysis appointment!
</div>';
            }
        } catch (PDOException $e) {
            $db->rollBack();

            echo '
<div class="alert alert-danger" role="alert" style="font-weight:bold; margin-top:10px;">
    <i class="fa fa-exclamation-triangle"></i> 
    Error: ' . htmlspecialchars($e->getMessage()) . '
</div>';
        }
    } else {
        echo '
<div class="alert alert-danger" role="alert" style="font-weight:bold; margin-top:10px;">
    <i class="fa fa-exclamation-triangle"></i> 
    Invalid request format!
</div>';
    }
}


if (isset($_GET["dgr"])) {
    $parts = $_GET["dgr"];
    $pp = explode("/", $parts);
    $hos_no = $pp[0];
    $appt_no = $pp[1];
    $sn = $pp[2];
    $paymode = $pp[3];
    $status = 'discharge';
    $ap_direction = '1';

    $updateSQL = "UPDATE apptm SET ap_direction=:ap_direction, status=:status WHERE appt_no=:appt_no";
    $stmt = $db->prepare($updateSQL);

    $stmt->bindParam(':ap_direction', $ap_direction, PDO::PARAM_STR);
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $stmt->bindParam(':appt_no', $appt_no, PDO::PARAM_STR);

    if ($stmt->execute()) {
        header("Location: index.php?sv");
        exit();
    }
}

if (isset($_GET["drv"])) {

    $parts = $_GET["drv"];
    $pp = explode("/", $parts);
    $hos_no = $pp[0];
    $appt_no = $pp[1];
    $sn = $pp[2];
    $sale_sn = $sn;
    $paymode = $pp[3];



    try {
        // Begin transaction
        $db->beginTransaction();

        $stmt2 = $db->prepare("
            SELECT  wallet_payee, cr, pay_mode, drug_status, 
                   hospital_no, wallet_debt_bill_to_acct, drug_sn, ledger_TX 
            FROM patient_ap_services 
            WHERE sn = ?");
        $stmt2->execute([$sn]);

        if ($rowx = $stmt2->fetch(PDO::FETCH_ASSOC)) {
            $drug_sn       = $rowx['drug_sn'];
            $wallet_payee  = $rowx['wallet_payee'];
            $pay_mode      = $rowx['pay_mode'];
            $drug_status   = $rowx['drug_status'];
            $lg_ref_no     = $rowx['ledger_TX'];
            $cr            = $rowx['cr'];
            $bill_to_acct  = $rowx['wallet_debt_bill_to_acct'];

            if ($pay_mode == 'spkage') {
                $stmt1 = $db->prepare("
                    UPDATE patient_ap_services 
                    SET invoice_status = 3, paystatus = 3, transact_date = NULL, 
                        claim_valid_by = NULL, med_frequency = NULL 
                    WHERE sn = ?");
                $stmt1->execute([$sn]);

                $stmt2 = $db->prepare("UPDATE apptm SET status = 'cancelled' WHERE appt_no = ?");
                $stmt2->execute([$appt_no]);
            } elseif ($pay_mode == 'cash' && ($cr == 2 || $bill_to_acct == 'CREDIT' || $bill_to_acct == 'BILL')) {

                $stmt1 = $db->prepare("
                    UPDATE patient_ap_services 
                    SET paystatus = 3, invoice_status = 3, drug_status = 3 
                    WHERE sn = ?");
                $stmt1->execute([$sn]);

                if ($stmt1->rowCount() > 0) {
                    $stmt2 = $db->prepare("UPDATE apptm SET status = 'cancelled' WHERE appt_no = ?");
                    $stmt2->execute([$appt_no]);

                    $stmt3 = $db->prepare("DELETE FROM chart_ledger WHERE sale_sn = ? AND hospital_no = ?");
                    $stmt3->execute([$sn, $hos_no]);
                }
            } elseif ($pay_mode == 'cash') {

                include("reverse.php");
                if ($status == "success") {
                    $stmt2 = $db->prepare("UPDATE apptm SET status = 'cancelled' WHERE appt_no = ?");
                    $stmt2->execute([$appt_no]);

                    $stmt1 = $db->prepare("UPDATE patient_ap_services SET paystatus = 3, invoice_status = 3, drug_status = 3 WHERE app_no = ?");
                    $stmt1->execute([$appt_no]);
                    $error_msg = 'Appointment Deleted Successful!';
                    $error_status = 2;
                }
            } else {
                // claims
                $stmt2 = $db->prepare("UPDATE apptm SET status = 'cancelled' WHERE appt_no = ?");
                $stmt2->execute([$appt_no]);

                $stmt1 = $db->prepare("UPDATE patient_ap_services SET paystatus = 3, invoice_status = 3, drug_status = 3 WHERE sn = ?");
                $stmt1->execute([$sn]);

                $error_msg = 'Appointment Deleted Successful!';
                $error_status = 2;
            }

            // Commit transaction if all went well
            $db->commit();
        } else {
            $db->rollBack();
            $error_msg = 'Unable to Reserve Transaction! Patient Deposit/Wallet Transferred Payment Not Reversable!';
            $error_status = 1;
        }
    } catch (Exception $e) {
        // Rollback on any error
        $db->rollBack();
        $error_msg = 'Transaction failed: ' . $e->getMessage();
        $error_status = 1;
    }
}


if (isset($_GET["drn"])) {

    $appt_no = $_GET["drn"];
    $updateSQL = "UPDATE apptm SET status='checkin' WHERE appt_no='$appt_no'";
    $db->exec($updateSQL);

    $desc = "Change from future Appointment to checkin now";
    $staff = $_SESSION['fullname'];
    $pid = $hos_no;
    $pname = '';
    $action = 'Re-schedule Appointment';
    include_once("../logs.php");
    header("location:index.php?sv");
}

if (isset($_POST["del_guardian_submit"])) {

    $delete_gd_id = $_POST["delete_gd_id"];
    $ptm_gurdian = $_POST["ptm_gurdian"];


    $update = "DELETE FROM guardian_tbl WHERE guardian_id='$delete_gd_id'";
    $db->exec($update);

    ////=============================================== logs
    $stmt = $db->query("SELECT * FROM guardian_tbl WHERE guardian_id='$delete_gd_id'");
    if ($stmt->rowCount() == 1) {
        $rwx = $stmt->fetch(PDO::FETCH_ASSOC);
        $guardian_Name = $rwx['guardian_Name'];
        $patient_id = $rwx['patient_id'];
    }

    $desc = 'Next of Kin Record Deleted / ' . $guardian_Name;
    $staff = $_SESSION['fullname'];
    $pid = $patient_id;
    $pname = '';
    $action = 'Patient Data/Deleted';
    include_once("../logs.php");

    header("location:index.php?ptm=$ptm_gurdian");
}

if (isset($_POST["save_guard"])) {
    try {
        $setdate = date("Y-m-d H:i:s");
        $guard_id = isset($_POST['guard_id']) ? trim($_POST['guard_id']) : '';
        $hos_no = isset($_POST['hos_no']) ? trim($_POST['hos_no']) : '';

        // Validate required fields
        if (empty($hos_no)) {
            throw new Exception("Hospital number is required.");
        }

        // Check if guardian exists
        $stmt = $db->prepare("SELECT guardian_id FROM guardian_tbl WHERE guardian_id = :guard_id");
        $stmt->bindParam(':guard_id', $guard_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // UPDATE existing guardian
            $updateSQL = "UPDATE guardian_tbl SET 
                        guardian_Name = :g_name,
                        guardian_gender = :gender,
                        guardian_address = :g_addr,
                        guardian_phone = :g_phone,
                        guardian_occupation = :occupation,
                        guardian_relationship = :g_relation
                      WHERE guardian_id = :guard_id";

            $stmt = $db->prepare($updateSQL);
            $stmt->bindParam(':g_name', $_POST['g_name']);
            $stmt->bindParam(':gender', $_POST['gender']);
            $stmt->bindParam(':g_addr', $_POST['g_addr']);
            $stmt->bindParam(':g_phone', $_POST['g_phone']);
            $stmt->bindParam(':occupation', $_POST['occupation']);
            $stmt->bindParam(':g_relation', $_POST['g_relation']);
            $stmt->bindParam(':guard_id', $guard_id);
            $stmt->execute();

            // echo "Guardian updated successfully.";
        } else {
            // INSERT new guardian
            $insertSQL = "INSERT INTO guardian_tbl
                        (patient_id, guardian_Name, guardian_gender, guardian_address,
                         guardian_phone, guardian_occupation, guardian_relationship, date_captured)
                      VALUES
                        (:hos_no, :g_name, :gender, :g_addr, :g_phone, :occupation, :g_relation, :setdate)";

            $stmt = $db->prepare($insertSQL);
            $stmt->bindParam(':hos_no', $hos_no);
            $stmt->bindParam(':g_name', $_POST['g_name']);
            $stmt->bindParam(':gender', $_POST['gender']);
            $stmt->bindParam(':g_addr', $_POST['g_addr']);
            $stmt->bindParam(':g_phone', $_POST['g_phone']);
            $stmt->bindParam(':occupation', $_POST['occupation']);
            $stmt->bindParam(':g_relation', $_POST['g_relation']);
            $stmt->bindParam(':setdate', $setdate);
            $stmt->execute();

            ///  echo "Guardian added successfully.";
        }
    } catch (PDOException $e) {
        echo "Database Error: " . $e->getMessage();
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }

    ////=============================================== ../logs

}

$stmt = $db->prepare("
    SELECT 
        e.*, 
        i.insurance_name, 
        i.interest, 
        i.insurance_type, 
        i.payment_mode, 
        i.add_minus 
    FROM enrollee AS e
    INNER JOIN insurance_tbl AS i 
        ON e.hmo_no = i.insurance_no
    WHERE e.hospital_no = :hosp_no 
      AND i.status = 'active'
");
$stmt->execute([':hosp_no' => $hosp_no]);

if ($stmt->rowCount() > 0) {
    $row_rstSelect = $stmt->fetch(PDO::FETCH_ASSOC);

    $nhis_no = $row_rstSelect['nhis_no'];
    $nhis_no_ext = $row_rstSelect['nhis_no_ext'];
    $insurance = $row_rstSelect['insurance'];
    $interest = $row_rstSelect['interest'];
    $add_minus = $row_rstSelect['add_minus'];
    $payment_mode = $row_rstSelect['payment_mode'];
    $hmo_no = $row_rstSelect['hmo_no'];
    $validation_status = $row_rstSelect['validation_status'];
    $insurance_name = $row_rstSelect['insurance_name'];
    $visit_status = $row_rstSelect['visit_status'];

    $em = 0;
    $disable_link = 0;
    $emptyFields = [];

    if (empty($row_rstSelect['gender'])) {
        $emptyFields[] = 'Gender';
        $em = 1;
    }

    if (empty($row_rstSelect['dob'])) {
        $emptyFields[] = 'Date of Birth';
        $em = 1;
    }

    if (empty($row_rstSelect['phone'])) {
        $emptyFields[] = 'Phone Number';
        $em = 1;
    }

    if ($insurance === 'NHIS' && empty($row_rstSelect['nhis_no'])) {
        $emptyFields[] = 'NHIS Number';
        $em = 1;
        $disable_link = 1;
    }

    $hospital_no = $row_rstSelect['hospital_no'];

    if (!empty($emptyFields)) {
        $msg = "* Click Edit Patient Data to Update missing field(s):<br> - " . implode('<br> - ', $emptyFields) . "<br><br>";
        $alert = 'danger'; ?>

        <div class="alert alert-danger">
            <h3><?= $msg; ?></h3>
        </div>

    <?php }
} else {
    $updateSQL = "UPDATE enrollee SET hmo_no='1000',insurance='Private(Self)' WHERE hospital_no='$hosp_no'";
    $db->exec($updateSQL);
    header("location:index.php?ptm=all/$hosp_no");
}

$stmt_chk = $db->prepare("
    SELECT care_giver, care_giver_phone
    FROM admission 
    WHERE hospital_no = :hosp_no AND adm_status = '3'
    LIMIT 1
");
$stmt_chk->bindParam(':hosp_no', $hosp_no, PDO::PARAM_STR);
$stmt_chk->execute();

if ($row = $stmt_chk->fetch(PDO::FETCH_ASSOC)):
    ?>
    <div class="alert alert-danger">
        <h3>PATIENT IS CURRENTLY ON-ADMISSION</h3>
        <?php if (!empty($row['care_giver'])): ?>
            <p><strong>Care Giver:</strong> <?= htmlspecialchars($row['care_giver']) ?></p>
        <?php endif; ?>

        <?php if (!empty($row['care_giver_phone'])): ?>
            <p><strong>Phone:</strong> <?= htmlspecialchars($row['care_giver_phone']) ?></p>
        <?php endif; ?>
    </div>
<?php endif; ?>


<?php if ($disble_link == '1') { ?> <h2>You have been <u>BLOCKED</u> from Booking Appointment Until you Update Missing Field(s)</h2> <?php } ?>

<div class="form_sep" align="">

    <input type="button" name="eidt_gd" value="Edit Patient Data" data-target="#modal" id="<?php echo $hosp_no; ?>" class="btn btn-warning  edit_patient" style="font-size: 14px; " />

    &nbsp;|&nbsp;

    <input type="button" <?php if ($disble_link == '1') { ?> disabled <?php } ?> name="book_app" value="Book Appointment" data-target="#modal" id="<?php echo $hosp_no; ?>" class="btn btn-success appointment_link" style="font-size: 14px; " />
    <?php if ($_SESSION['biller'] == 1) { ?>
        &nbsp;|&nbsp;


        <a href="../billing/pacct.php?emr=<?php echo $hosp_no; ?>" class="btn btn-white" style="font-size: 14px; color: red; "><i class="fa fa-paypal"></i>&nbsp; Payment / Account</a> <?php } ?>
    &nbsp;|&nbsp;

    <a href="document.php?emr=<?php echo $hosp_no; ?>" class="btn btn-white" style="font-size: 14px; color: black; "><i class="fa fa-folder"></i>&nbsp; Document</a>
    &nbsp;|&nbsp;


    <a href="index.php?ptm" class="btn btn-danger" style="font-size: 14px; color:white;"><i class="fa fa-times"></i></a>

</div>
<div class="form_sep"></div>

<table class="table table-striped table-bordered table-hover dataTables-example">
    <tr style="background: #666; color: #FFF;">
        <td style="font:bold 14px 'Arial';" width="30%">Patient No: </td>
        <td style="font:bold 14px 'Arial';" width="30%">Patient Name: </td>
        <td style="font:bold 14px 'Arial';" width="30%">Gender: </td>

    </tr>
    <tr>
        <td><?php echo $row_rstSelect['hospital_no']; ?></td>
        <td><?php echo $row_rstSelect['surname'] . ', ' . $row_rstSelect['fname'] . ' ' . $row_rstSelect['oname'];
            $name = trim($row_rstSelect['surname'] . ' ' . $row_rstSelect['fname'] . ' ' . $row_rstSelect['oname']);
            ?></td>
        <td><?php echo $row_rstSelect['gender']; ?></td>
    </tr>
    <tr style="background: #666; color: #FFF;">
        <td style="font:bold 14px 'Arial';">Date of Birth: </td>
        <td style="font:bold 14px 'Arial';">Age: </td>
        <td style="font:bold 14px 'Arial';">Date/Captured By: </td>
    </tr>
    <tr>
        <td><?php if ($row_rstSelect['dob'] == ''  or $row_rstSelect['dob'] == '0000-00-00') {
                echo '';
            } else {
                echo date('d M,Y', strtotime($row_rstSelect['dob']));
            } ?></td>
        <td><?php echo $row_rstSelect['age']; ?></td>
        <td>
            <?php
            $date = $row_rstSelect['date_capture'];
            $capturedBy = $row_rstSelect['captured_by'];

            if (!empty($date) && $date != '0000-00-00') {
                echo date('d M, Y h:i a', strtotime($date));
                if (!empty($capturedBy)) {
                    echo ' <br><b>Captured by</b> ' . htmlspecialchars($capturedBy);
                }
            }
            ?>
        </td>

    </tr>
    <tr style="background: #666; color: #FFF;">
        <td style="font:bold 14px 'Arial';">Marital Status: </td>
        <td style="font:bold 14px 'Arial';">Blood Group: </td>
        <td style="font:bold 14px 'Arial';">Genotype: </td>
    </tr>
    <tr>
        <td><?php echo $row_rstSelect['marital_status']; ?></td>
        <td><?php echo $row_rstSelect['blood_g']; ?></td>
        <td><?php echo $row_rstSelect['geno_type']; ?></td>
    </tr>
    </tr>
    <tr style="background: #666; color: #FFF;">
        <td style="font:bold 14px 'Arial';">Tribe</td>
        <td style="font:bold 14px 'Arial';">State/LGA: </td>
        <td style="font:bold 14px 'Arial';">Nationality: </td>
    </tr>
    <tr>
        <td><?php echo $row_rstSelect['tribe']; ?></td>
        <td><?php echo $row_rstSelect['state_lga']; ?></td>
        <td><?php echo $row_rstSelect['nationality']; ?></td>
    </tr>

    </tr>
    <tr style="background: #666; color: #FFF;">
        <td style="font:bold 14px 'Arial';">Occupation</td>
        <td style="font:bold 14px 'Arial';">Patient Reviewer/Brief</td>
        <td style="font:bold 14px 'Arial';"></td>
    </tr>
    <tr>
        <td><?php echo $row_rstSelect['occupation']; ?></td>
        <td><?php echo $row_rstSelect['patient_review']; ?></td>
        <td></td>
    </tr>
</table>
<br>

<?php

if ($row_rstSelect['insurance'] == '' or $row_rstSelect['hmo_no'] == '') {
    $updateSQL = "UPDATE enrollee SET hmo_no='1000',insurance='Private(Self)' WHERE hospital_no='$hosp_no'";
    $db->exec($updateSQL);
}

if ($row_rstSelect['insurance'] != 'Private(Self)') { ?>

    <h3 class="heading_a" style="color:brown; ">Insurance / <?php echo $insurance_name ?></h3>
    <table class="table table-striped table-bordered table-hover dataTables-example">
        <tr style="background: #666; color: #FFF;">
            <td width="33%" style="font:bold 14px 'Arial';">Insurance Name: </td>
            <td width="34%" style="font:bold 14px 'Arial';">Membership/NHIS No.:</td>
            <td width="33%" style="font:bold 14px 'Arial';">Membership: </td>
        </tr>
        <tr>
            <td><?php echo $row_rstSelect['hmo_no'] . ' / ' . $row_rstSelect['insurance']; ?></td>
            <td><?php echo $row_rstSelect['nhis_no'] . '-' . $row_rstSelect['nhis_no_ext']; ?></td>
            <td><?php if ($row_rstSelect['nhis_no_ext'] == '0') {
                    echo 'Principal';
                } elseif ($row_rstSelect['nhis_no_ext'] == '1') {
                    echo 'Spouse';
                } elseif ($row_rstSelect['nhis_no_ext'] == '2') {
                    echo 'Dependant 2';
                } elseif ($row_rstSelect['nhis_no_ext'] == '3') {
                    echo 'Dependant 3';
                } elseif ($row_rstSelect['nhis_no_ext'] == '4') {
                    echo 'Dependant 4';
                } else {
                    echo 'Extra Dependant';
                }
                ?></td>
        </tr>
    </table>

<?php } else { ?>
    <h3 class="heading_a" style="color: brown">Insurance/PRIVATE (SELF)</h3>
<?php } ?>

<input type="button" name="convrt_insur" value="Convert Patient Insurance" <?php if ($_SESSION['convert_patient_insur'] == 0) { ?> disabled <?php } ?> data-target="#modal" id="<?php echo $hosp_no; ?>" class="btn btn-danger btn-sm convert_insurance" style="font-size: 14px; " />


<hr>
<h3 class="heading_a">Contact information</h3>
<table class="table table-striped table-bordered table-hover dataTables-example">
    <tr style="background: #666; color: #FFF;">
        <td style="font:bold 14px 'Arial';" width="30%">Phone No: </td>
        <td style="font:bold 14px 'Arial';" width="30%">Email: </td>
        <td style="font:bold 14px 'Arial';" width="30%">Address: </td>
    </tr>
    <tr>
        <td><?php echo $row_rstSelect['phone']; ?></td>
        <td><?php echo $row_rstSelect['email']; ?></td>
        <td><?php echo $row_rstSelect['addr']; ?></td>
    </tr>
</table>

<h3 class="heading_a">Next of Kin Information</h3>

<?php
$stmt = $db->prepare("
    SELECT guardian_id, guardian_Name, guardian_phone, guardian_relationship, guardian_address
    FROM guardian_tbl 
    WHERE patient_id = :hosp_no 
    ORDER BY guardian_id
");
$stmt->bindParam(':hosp_no', $hosp_no, PDO::PARAM_STR);
$stmt->execute();

if ($stmt->rowCount() > 0) { ?>
    <table class="table table-striped table-bordered table-hover dataTables-example">
        <thead>
            <tr>
                <th width="1%">#</th>
                <th width="20%">Next of Kin Name</th>
                <th width="30%">Phone No / Relationship</th>
                <th width="20%">Address</th>
                <th width="20%">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $n = 1;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                    <td><?= $n++; ?></td>
                    <td><?= htmlspecialchars($row['guardian_Name']); ?></td>
                    <td><?= htmlspecialchars($row['guardian_phone']) . ' / ' . htmlspecialchars($row['guardian_relationship']); ?></td>
                    <td><?= htmlspecialchars($row['guardian_address']); ?></td>
                    <td>
                        <button type="button" class="btn btn-danger btn-xs confirm_gd_delete"
                            data-target="#modal"
                            id="<?= htmlspecialchars($ptm . '/' . $ptm_2 . '/' . $hosp_no . '__' . $row['guardian_id']); ?>">
                            Delete
                        </button>

                        &nbsp;|&nbsp;

                        <button type="button" class="btn btn-warning btn-xs add_guardian_edit"
                            data-target="#modal"
                            id="<?= htmlspecialchars($row['guardian_id']); ?>">
                            Edit
                        </button>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
<?php } else { ?>
    <div class="alert alert-danger">
        <h2>No Next of Kin Information</h2>
    </div>
    <div class="form_sep"></div>
    <div class="form_sep"></div>
<?php } ?>


<input type="button" name="gd" value=" + Add Next of Kin Data" data-target="#modal" id="" class="btn btn-success btn-sm add_guardian" />

<br></br>

<?php if (isset($_GET["rv"])) { ?>
    <strong style="color:#F00">Member Has Been Moved to Private(Self) Status</strong>
<?php } ?>
<?php

if (in_array($insurance, array('NHIS', 'PHIS', 'Corporate'))) {
    $query_ext = $db->prepare("
        SELECT hospital_no, nhis_no, nhis_no_ext, surname, fname, oname, member
        FROM enrollee 
        WHERE nhis_no = :nhis_no 
        AND insurance = :insurance 
        AND nhis_no != '' 
        AND hospital_no != :hosp_no 
        ORDER BY hospital_no
    ");
    $query_ext->execute(array(
        ':nhis_no' => $nhis_no,
        ':insurance' => $insurance,
        ':hosp_no' => $hosp_no
    ));

    if ($query_ext->rowCount() > 0) { ?>
        <h3 class="heading_a">Membership</h3>
        <table class="table table-striped table-bordered table-hover dataTables-example">
            <thead>
                <tr>
                    <th width="14%">Picture</th>
                    <th width="86%">Member Details</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $roleMap = array(
                    '0' => 'Principal',
                    '1' => 'Spouse',
                    '2' => 'Dependant 2',
                    '3' => 'Dependant 3',
                    '4' => 'Dependant 4'
                );

                while ($row = $query_ext->fetch(PDO::FETCH_ASSOC)) {
                    $imgPath = enrollee_p . $row['hospital_no'] . '.jpg';
                    $photo = file_exists($imgPath) ? $imgPath : '../img/no_photo.jpg';
                    $role = isset($roleMap[$row['nhis_no_ext']]) ? $roleMap[$row['nhis_no_ext']] : 'Extra Dependant';
                ?>
                    <tr class="record">
                        <td>
                            <img src="<?php echo $photo; ?>" alt="photo" height="70" width="70" class="img-thumbnail user_avatar">
                        </td>
                        <td>
                            <ul style="list-style: none; padding-left: 0;">
                                <li>NHIS/Membership No.: <?php echo $row['nhis_no'] . '-' . $row['nhis_no_ext']; ?></li>
                                <li>Hospital No.: <?php echo $row['hospital_no']; ?></li>
                                <li>Name: <?php echo $row['surname'] . ' ' . $row['fname'] . ' ' . $row['oname']; ?></li>
                                <li><?php echo $role . ' / ' . $row['member']; ?></li>
                            </ul>
                            <a href="index.php?rv=<?php echo $row['hospital_no']; ?>&ptm=<?php echo $ptm . '/' . $row['nhis_no'] . '/' . $hosp_no; ?>">[ Remove Member ]</a> |
                            <a href="index.php?ptm=all/<?php echo $row['hospital_no']; ?>">[ View ]</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

    <?php } elseif (!empty($nhis_no)) { ?>
        <strong>No current member in this <?php echo htmlspecialchars($insurance); ?> group. Click the button below to add additional members.</strong>
        <div class="form_sep"></div>
        <div class="form_sep"></div>
    <?php }

    if (!empty($nhis_no)) { ?>
        <input type="button" name="book_app" value="+ Add New Member"
            data-target="#modal"
            <?php echo ($_SESSION['add_new_patient'] == 0) ? 'disabled' : ''; ?>
            class="btn btn-danger btn-sm add_patient" />
<?php }
} ?>


<div class="modal inmodal fade" id="confirm_gd_delete_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body" id="confirm_gd_delete_body">

            </div>
        </div>
    </div>
</div>


<div class="modal inmodal fade" id="add_guardian_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">

            </div>
            <div class="modal-body" id="add_guardian_body">

                <form action="index.php?ptm=<?php echo 'all/' . $hosp_no; ?>" method="POST">

                    <div class="form_sep">
                        <label for="reg_input_name" class="req">Next of Kin Full Name</label>
                        <input type="text" id="g_name" name="g_name" class="form-control" required>
                    </div>

                    <div class="form_sep">
                        <label for="reg_select" class="req">Gender</label>
                        <select name="gender" id="gender" class="form-control" required>
                            <option selected="selected" value="">Select...</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>

                    <div class="form_sep">
                        <label for="reg_input_name" class="req">Phone Number(s)</label>
                        <input type="text" id="g_phone" name="g_phone" class="form-control" required>
                    </div>

                    <div class="form_sep">
                        <label for="reg_input_name" class="">Occupation</label>
                        <input type="text" id="occupation" name="occupation" class="form-control">
                    </div>

                    <div class="form_sep">
                        <label for="reg_select" class="req">Relationship</label>
                        <select name="g_relation" id="g_relation" class="form-control" required>

                            <option selected="selected" value="">Select Relationship...</option>
                            <option value="father">father</option>
                            <option value="son">son</option>
                            <option value="husband">husband</option>
                            <option value="brother">brother</option>
                            <option value="grandfather">grandfather</option>
                            <option value="grandson">grandson</option>
                            <option value="uncle">uncle</option>
                            <option value="nephew">nephew</option>
                            <option value="cousin">cousin</option>
                            <option value="mother">mother</option>
                            <option value="daughter">daughter</option>
                            <option value="wife">wife</option>
                            <option value="sister">sister</option>
                            <option value="grandmother">grandmother</option>
                            <option value="granddaughter">granddaughter</option>
                            <option value="aunt">aunt</option>
                            <option value="niece">niece</option>
                            <option value="parent">parent</option>
                            <option value="child">child</option>
                            <option value="spouse">spouse</option>
                            <option value="sibling">sibling</option>
                            <option value="grandparents">grandparents</option>
                            <option value="grandchild">grandchild</option>
                            <option value="friend">friend</option>
                            <option value="Fiance">Fiance</option>
                            <option value="Fiancee">Fiancee</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>

                    <div class="form_sep">
                        <label for="reg_textarea_message" class="req">Address</label>
                        <textarea name="g_addr" id="g_addr" cols="30" rows="4" class="form-control" required></textarea>
                    </div>

                    <div class="form_sep">
                        <div class="pull-left">
                            <button type="submit" class="btn btn-success btn btn-sm" name="save_guard" id="save_guard">Save</button>
                            <input type="hidden" name="hos_no" id="hospital_number_edit" value="<?php echo $hosp_no; ?>" />
                            <input type="hidden" name="guard_id" id="guard_id" />

                        </div>

                        <div class="pull-right">
                            <a href="index.php?ptm=<?php
                                                    if ($ptm_2 == '') {
                                                        echo $ptm . '/' . $hosp_no;
                                                    } else {
                                                        echo $ptm . '/' . $ptm_2 . '/' . $hosp_no;
                                                    } ?>" class="btn btn-danger btn btn-sm">Close</a>

                        </div>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>


<div class="modal inmodal fade" id="insurance_confirm_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

            </div>
            <div class="modal-body" id="insurance_confirm_body">


            </div>
        </div>
    </div>
</div>

<div class="modal inmodal fade" id="passport_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Passport</h4>
            </div>

            <div class="modal-body">
                <form action="passport.php?ptm=<?php echo $ptm . '/' . $ptm_2 . '/' . $hosp_no; ?>" enctype="multipart/form-data" method="POST">


                    <div class="form_sep">
                        <strong>Upload Passport</strong>
                    </div>

                    <div class="form_sep">
                        <input type="file" name="file_foto" id="file_foto" class="form-control" />
                    </div>

                    <div class="form_sep">

                        <button class="btn btn-primary btn-xs" type="submit" name="upload_pass">Upload Passport</button>&nbsp;&nbsp;
                        <a href="" class="btn btn-warning btn-xs Cancel_lab_request">Cancel</a>

                    </div>
                    <input type="hidden" name="hosp_no" value="<?php echo $hosp_no; ?>" />
                    <input type="hidden" name="url" value="<?php echo 'ptm=' . $ptm . '/' . $ptm_2 . '/' . $hosp_no; ?>" />
                </form>
            </div>
        </div>
    </div>
</div>


<div class="modal inmodal fade" id="view_insurance_convert_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body" id="view_insurance_convert_body">

            </div>
        </div>
    </div>
</div>


<div class="modal inmodal fade" id="appointment_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id=""></h4>

            </div>
            <div class="modal-body" id="appointment_body">
                <?php
                /// echo $hmo_no . '<br>';
                $book_path = $interest . '/' . $insurance . '/' . $hosp_no . '/' . $name . '/' . $visit_status . '/' . $hmo_no . '/' . $add_minus . '/' . $payment_mode . '/'; ?>
                <div class="row">
                    <div class="col-md-4">
                        <div class="ibox-content text-center">
                            <!--<h1>Nicki Smith</h1>-->
                            <div class="m-b-sm">
                                <img alt="image" class="img-circle appt_link2" id="<?php echo $book_path . 'gopd'; ?>" src="../img/go.png" width="50" height="50">
                            </div>
                            <div class="text-center">
                                <input type="button" name="what_todo" value="GOPD" data-target="#myModal5" id="<?php echo $book_path . 'gopd'; ?>" class="btn btn-info btn-sm btn-block appt_link2" />
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="ibox-content text-center">
                            <!--<h1>Nicki Smith</h1>-->
                            <div class="m-b-sm">
                                <img alt="image" class="img-circle appt_link2" id="<?php echo $book_path . 'Paedia'; ?>" src="../img/pd.png" width="50" height="50">
                            </div>
                            <div class="text-center">
                                <input type="button" name="what_todo" value="Paediatric" data-target="#myModal5" id="<?php echo $book_path . 'Paedia'; ?>" class="btn btn-success btn-sm btn-block appt_link2" />
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="ibox-content text-center">
                            <!--<h1>Nicki Smith</h1>-->
                            <div class="m-b-sm">
                                <img alt="image" class="img-circle appt_link2" id="<?php echo $book_path . 'Orthopae'; ?>" src="../img/orth.png" width="50" height="50">
                            </div>
                            <div class="text-center">
                                <input type="button" name="what_todo" value="Orthopaedic" data-target="#myModal5" id="<?php echo $book_path . 'Orthopae'; ?>" class="btn btn-primary btn-sm btn-block appt_link2" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="ibox-content text-center">
                            <!--<h1>Nicki Smith</h1>-->

                            <div class="text-center">
                                <?php if ($_SESSION['dialysis_visible'] == 1) { ?>
                                    <div class="m-b-sm">
                                        <img alt="image" class="img-circle appt_link2" id="<?php echo $book_path . 'Nephro'; ?>" src="../img/eye.png" width="50" height="50">
                                    </div>
                                    <input type="button" name="what_todo" value="Nephrology" data-target="#myModal5" id="<?php echo $book_path . 'Nephro'; ?>" class="btn btn-info btn-sm btn-block appt_link2" />
                                <?php } elseif ($_SESSION['ivf'] == 1) { ?>
                                    <div class="m-b-sm">
                                        <img alt="image" class="img-circle appt_link2" id="<?php echo $book_path . 'ivf'; ?>" src="../img/ivf.png" width="50" height="50">
                                    </div>
                                    <input type="button" name="what_todo" value="IVF" data-target="#myModal5" id="<?php echo $book_path . 'ivf'; ?>" class="btn btn-info btn-sm btn-block appt_link2" />

                                <?php } else { ?>


                                    <div class="m-b-sm">
                                        <img alt="image" class="img-circle appt_link2" id="<?php echo $book_path . 'physio'; ?>" src="../img/physio.png" width="50" height="50">
                                    </div>
                                    <input type="button" name="what_todo" value="Physiotherapy" data-target="#myModal5" id="<?php echo $book_path . 'physio'; ?>" class="btn btn-info btn-sm btn-block appt_link2" />


                                <?php } ?>

                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="ibox-content text-center">
                            <!--<h1>Nicki Smith</h1>-->
                            <div class="m-b-sm">
                                <img alt="image" class="img-circle appt_link2" id="<?php echo $book_path . 'Antenatal'; ?>" src="../img/an.png" width="50" height="50">
                            </div>
                            <div class="text-center">
                                <input type="button" name="what_todo" value="Antenatal" data-target="#myModal5" id="<?php echo $book_path . 'Antenatal'; ?>" class="btn btn-primary btn-sm btn-block appt_link2" />
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="ibox-content text-center">
                            <!--<h1>Nicki Smith</h1>-->
                            <div class="m-b-sm">
                                <img alt="image" class="img-circle appt_link2" id="<?php echo $book_path . 'ent'; ?>" src="../img/ent.png" width="50" height="50">
                            </div>
                            <div class="text-center">
                                <input type="button" name="what_todo" value="E.N.T." data-target="#myModal5" id="<?php echo $book_path . 'ent'; ?>" class="btn btn-white btn-sm btn-block appt_link2" />
                            </div>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-md-4">
                        <div class="ibox-content text-center">
                            <!--<h1>Nicki Smith</h1>-->
                            <div class="m-b-sm">
                                <img alt="image" class="img-circle appt_link2" id="<?php echo $book_path . 'emergency'; ?>" src="../img/em.png" width="50" height="50">
                            </div>
                            <div class="text-center">
                                <input type="button" name="what_todo" value="Emergency" data-target="#myModal5" id="<?php echo $book_path . 'emergency'; ?>" class="btn btn-danger btn-sm btn-block appt_link2" />
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="ibox-content text-center">
                            <!--<h1>Nicki Smith</h1>-->
                            <div class="m-b-sm">
                                <img alt="image" class="img-circle appt_link2" id="<?php echo $book_path . 'Vaccination'; ?>" src="../img/vacine.png" width="50" height="50">
                            </div>
                            <div class="text-center">
                                <input type="button" name="what_todo" value="Vaccination" data-target="#myModal5" id="<?php echo $book_path . 'Vaccination'; ?>" class="btn btn-info btn-sm btn-block appt_link2" />
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="ibox-content text-center">
                            <!--<h1>Nicki Smith</h1>-->
                            <div class="m-b-sm">
                                <img alt="image" class="img-circle appt_link2" id="<?php echo $book_path . 'More_services'; ?>" src="../img/mail.jpg" width="50" height="50">
                            </div>
                            <div class="text-center">
                                <input type="button" name="what_todo" value="More + " data-target="#myModal5" id="<?php echo $book_path . 'More_services'; ?>" class="btn btn-default btn-sm btn-block appt_link2" />
                            </div>
                        </div>
                    </div>

                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="ibox-content text-center">
                            <!--<h1>Nicki Smith</h1>-->

                            <div class="text-center">

                                <div class="m-b-sm">
                                    <img alt="image" id="<?php echo $book_path . 'physio'; ?>" class="img-circle appt_link2" src="../img/physio.png" width="50" height="50">
                                </div>
                                <input type="button" name="what_todo" value="Physiotherapy" data-target="#myModal5" id="<?php echo $book_path . 'physio'; ?>" class="btn btn-info btn-sm btn-block appt_link2" />
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="ibox-content text-center">
                            <!--<h1>Nicki Smith</h1>-->

                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="ibox-content text-center">
                            <!--<h1>Nicki Smith</h1>-->

                        </div>
                    </div>
                </div>




            </div>
        </div>
    </div>
</div>


<div class="modal inmodal fade" id="appointment_page2_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id=""></h4>
            </div>
            <div class="modal-body" id="appointment_page2_body">

            </div>
        </div>
    </div>
</div>


<div class="modal inmodal fade" id="booking_confirm_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id=""></h4>
            </div>
            <div class="modal-body" id="booking_confirm_body">

            </div>
        </div>
    </div>
</div>

<div class="modal inmodal fade" id="missing_data_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id=""><?php echo $title_header; ?></h4>
            </div>
            <div class="modal-body" id="claims_body">

                <div class="alert alert-<?php echo $alert;  ?>"><?php echo $msg; ?></div>
                <hr>

            </div>

        </div>
    </div>
</div>

<?php include_once("convert_insurance.php"); ?>