<?php include("../Connections/Conn.php"); ?>

<?php
session_start();
if (isset($_GET['del'])) {

    $labrequest_no = $_GET['del'];
    $emr = $_GET['emr'];

    try {
        // Begin transaction
        $db->beginTransaction();
        $delete1 = $db->prepare("DELETE FROM patient_ap_services WHERE drug_sn = ? AND paystatus = '0'");
        $deleted1 = $delete1->execute(array($labrequest_no));

        if ($deleted1) {
            // Delete from lab_manage
            $delete2 = $db->prepare("DELETE FROM lab_manage WHERE labrequest_no = ?");
            $deleted2 = $delete2->execute(array($labrequest_no));

            if ($deleted2) {
                // Commit transaction if both deletions were successful
                $db->commit();
                header("location:xsale.php?emr=$emr&investigations&deleted");
                exit();
            } else {

                $db->rollBack();
                echo "Error deleting from lab_manage.";
            }
        } else {

            $db->rollBack();
            echo "Error deleting from patient_ap_services.";
        }
    } catch (Exception $e) {
        // Rollback transaction in case of an exception
        $db->rollBack();
        echo "Failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<?php include("../inc/header.php"); ?>

<body>

    <div id="wrapper">
        <?php include("../inc/nav_side.php"); ?>

        <div id="page-wrapper" class="gray-bg">
            <?php include("../inc/nav_header.php"); ?>


            <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-lg-10">
                    <h2>General And External Patients</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index.html">Home</a>
                        </li>
                        <li class="active">
                            <strong>Patient Search</strong>
                        </li>
                    </ol>
                </div>
                <div class="col-lg-2">

                    <br>
                    <div id="labAlertBox" style="display:none; cursor:pointer; background:#f0fff0; padding:10px; border-radius:5px;">


                        <table width="100%">
                            <tr>
                                <td>🚶 Outpatients</td>
                                <td><strong id="lab_outpatient">0</strong></td>
                            </tr>
                            <tr>
                                <td>🏥 Inpatient:</td>
                                <td><strong id="lab_inpatient">0</strong></td>
                            </tr>
                        </table>
                    </div>

                </div>
            </div>

            <div class="wrapper wrapper-content  animated fadeInRight">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="ibox ">
                            <div class="ibox-title">
                                <h5> Search Patients Data</h5>
                                <div class="ibox-tools">
                                </div>
                            </div>
                            <div class="ibox-content">
                                <div class="row">
                                    <h3>&nbsp;&nbsp;To add an investigation request, search for the patient (General or External), then click Apply. Use the Control button to add the request</h3>
                                    <form action="xsale.php" method="POST" id="subject" name="subject" enctype="multipart/form-data">
                                        <div class="col-md-3">
                                            <div>
                                                <label for="ex_patient" class="req">External Patient's Number or Name</label>
                                                <select id="ex_patient" name="ex_patient" style="width:350px;"></select>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div id="">

                                                <label for="in_patient" class="req">Enter Patient's Hospital No or Name:</label>
                                                <select id="in_patient" name="in_patient" style="width:350px;"></select>


                                            </div>

                                        </div>

                                        <div class="col-md-2">
                                            <div id="">
                                                <label for="reg_input_no" class="req"><strong style="color:#F00">Click</strong></label><br>
                                                <button class="btn btn-primary btn btn-sm" type="submit" name="apply_approve">Apply</button>
                                            </div>
                                        </div>

                                    </form>
                                </div>

                                <hr>
                                <div class="row">
                                    <div class="col-md-12">

                                        <?php
                                        $move_type = null;
                                        if (isset($_GET['emr'])) {
                                            $strting = $_GET['emr'];

                                            if (strpos($strting, "EX") === 0) {
                                                $move_type = "EX";
                                            } else {
                                                $move_type = "IN";
                                            }
                                        }

                                        if ($_POST['in_patient'] != '' and $_POST['ex_patient'] != '') { ?>
                                            <strong style="color:#F00">Select Either Hospital or External Patient. Choose one patient type at a time </strong>
                                            <?php } else {
                                            if ((isset($_POST['in_patient']) and $_POST['in_patient'] != '') or $move_type == "IN") {

                                                if ($move_type == "IN") {
                                                    $hosp_no = $_GET['emr'];
                                                    $in_patient = $_GET['emr'];
                                                } else {
                                                    $in_patient = trim($_POST['in_patient']);
                                                    $hosp_no = trim($_POST['in_patient']);
                                                }

                                                $status = 'active';
                                                $stmt_en = $db->prepare("SELECT i.insurance_name,i.interest,i.add_minus,i.insurance_type,i.payment_mode,e.hmo_no,e.surname,e.fname,	e.gender,e.addr,e.discount_set FROM enrollee as e INNER JOIN insurance_tbl as i ON e.hmo_no=i.insurance_no WHERE hospital_no=:hospital_no and status=:status");
                                                $stmt_en->bindValue(':hospital_no', $in_patient, PDO::PARAM_STR);
                                                $stmt_en->bindValue(':status', $status, PDO::PARAM_STR);
                                                $stmt_en->execute();
                                                if ($stmt_en->rowCount() > 0) {

                                                    $row = $stmt_en->fetch(PDO::FETCH_ASSOC);
                                                    $insurance_name = $row['insurance_name'];
                                                    $insurance = $row['insurance_type'];
                                                    $interest = $row['interest'];
                                                    $add_minus = $row['add_minus'];
                                                    $insurance_no = $row['hmo_no'];
                                                    $patient_name = $row['surname'] . ', ' . $row['fname'];
                                                    $names = $row['surname'] . ', ' . $row['fname'];                                            ?>
                                                    <h4>Hospital #: <?php echo $in_patient; ?> / Names: <?php echo $patient_name; ?></h4>
                                                    <h4>Insurance #: <?php echo $insurance_no; ?> / <?php echo $insurance_type; ?></h4>
                                                <?php } else { ?>
                                                    <strong style="color: red;">Patient Details Not Available!</strong>
                                                <?php } ?>


                                                <?php if ($_SESSION['request'] == 1 and $in_patient != '' and $stmt_en->rowCount() > 0) { ?>
                                                    <input type="button" name="edit" value="Add New Request" data-target="#myModal5" id="<?php echo $in_patient . '__' . $patient_name . '__' . $insurance . '__' . $interest . '__' . $insurance_no . '__' . $add_minus . '__IN__self'; ?>" class="btn btn-primary btn-xs lab_request" />
                                                    &nbsp; | &nbsp;
                                                    <a href="mgt.php?emr=<?php echo $hosp_no; ?>" class="btn btn-success btn-xs">View Request</a>
                                                    <?php if ($_SESSION['bill'] == 1) { ?>
                                                        &nbsp; | &nbsp;
                                                        <a href="../billing/pacct.php?emr=<?php echo $hosp_no; ?>&inv" class="btn btn-danger btn-xs">Billing</a>
                                                <?php }
                                                } ?>


                                                &nbsp; | &nbsp;
                                                <a href="xsale.php?emr=<?php echo $hosp_no; ?>&investigations" class="btn btn-info btn-xs">View Investigation(s)</a>

                                            <?php }

                                            if ((isset($_POST['ex_patient']) and $_POST['ex_patient'] != '') or $move_type == "EX") {

                                                if ($move_type == "EX") {
                                                    $hosp_no = $_GET['emr'];
                                                    $patient_name = $_GET['emr'];
                                                } else {
                                                    $ex_patient = $_POST['ex_patient'];
                                                    $part = explode("__", $ex_patient);
                                                    $patient_name = $part['1'];
                                                    $hosp_no = $part['0'];
                                                    $referral = $part['2'];
                                                } ?>
                                                <h4>Hospital #: <?php echo $hosp_no; ?> / Names: <?php echo $patient_name; ?></h4>
                                                <?php

                                                ///echo '===' . $_SESSION['request'];

                                                if ($_SESSION['request'] == 1 and $hosp_no != '') { ?>
                                                    <input type="button" name="edit" value="Add New Request" data-target="#myModal5" id="<?php echo $hosp_no . '__' . $patient_name . '__' . 'insurance' . '__' . 'int' . '__' . 'insur_no' . '__' . 'add' . '__EX__' . $referral; ?>" class="btn btn-primary btn-xs lab_request" />

                                                    &nbsp; | &nbsp;
                                                    <a href="xsale.php?emr=<?php echo $hosp_no; ?>&investigations" class="btn btn-success btn-xs">View Request</a>

                                                    <?php if ($_SESSION['bill'] == 1) { ?>
                                                        &nbsp; | &nbsp;
                                                        <a href="../billing/pacct.php?emr=<?php echo $hosp_no; ?>&inv" class="btn btn-danger btn-xs">Billing</a>
                                                    <?php } ?>

                                                    &nbsp; | &nbsp;
                                                    <input type="button" name="edit" value="Edit Patient" data-target="#myModal5" id="<?php echo $hosp_no . '__' . $part['1']; ?>"
                                                        class="btn btn-warning btn-xs edit_ex" />

                                                    &nbsp; | &nbsp;
                                                    <a href="xsale.php?emr=<?php echo $hosp_no; ?>&investigations" class="btn btn-info btn-xs">View Investigation(s)</a>


                                        <?php }
                                            }
                                        } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-lg-12">
                        <div class="ibox ">
                            <div class="ibox-title">
                                <h5>Patients List</h5>
                                <div class="ibox-tools">
                                </div>
                            </div>
                            <div class="ibox-content">
                                <?php
                                if (isset($_GET['investigations']) or  (isset($_POST['ex_patient']) and $_POST['ex_patient'] != '')) {

                                    if (isset($_GET['investigations'])) {
                                        $emr = $_GET['emr'];
                                    } elseif (isset($_POST['in_patient']) and $_POST['in_patient'] != '') {
                                        $in_patient = trim($_POST['in_patient']);
                                        $emr = trim($_POST['in_patient']);
                                    } elseif (isset($_POST['ex_patient']) and $_POST['ex_patient'] != '') {
                                        $ex_patient = $_POST['ex_patient'];
                                        $part = explode("__", $ex_patient);
                                        $emr = $part['0'];
                                    }
                                    $stmt2 = $db->prepare("SELECT l.*, p.* FROM lab_manage l INNER JOIN patient_ap_services p on p.drug_sn = l.labrequest_no			WHERE l.patient=:hospital_no order by l.sn desc");
                                    $stmt2->bindValue(':hospital_no', $emr, PDO::PARAM_STR);
                                    $stmt2->execute();
                                    if ($stmt2->rowCount() > 0) { ?>
                                        <table class="table table-striped table-bordered table-hover dataTables-example">
                                            <thead>
                                                <tr>
                                                    <th data-toggle="true">#</th>
                                                    <th data-toggle="true">Investigation</th>
                                                    <th data-toggle="true">Section</th>
                                                    <th data-toggle="true">Request Date</th>
                                                    <th data-toggle="true">Request_by</th>
                                                    <th data-toggle="true">Amount</th>
                                                    <th data-toggle="true">.</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                                <?php
                                                $n = 1;
                                                while ($roww = $stmt2->fetch(PDO::FETCH_ASSOC)) {
                                                    $labrequest_no = $roww['labrequest_no'];
                                                ?>
                                                    <td><?php echo $n; ?></td>
                                                    <td><?php echo $roww['test_name']; ?></td>
                                                    <td><?php echo $roww['section']; ?></td>
                                                    <td><?php echo date('d-m-Y H:i', strtotime($roww['request_date'])); ?></td>
                                                    <td><?php echo $roww['request_by']; ?></td>
                                                    <td><?php echo $roww['pay']; ?></td>
                                                    <td>
                                                        <?php if ($roww['data_capture_status'] == 'approve') { ?>
                                                        <?php } ?>


                                                        <?php if ($roww['paystatus'] == '0' and $roww['data_capture_status'] != 'approve') { ?>
                                                            <a href="xsale.php?emr=<?= $emr ?>&investigation&del=<?= $labrequest_no ?>"
                                                                onclick="return confirm('Are you sure you want to Delete?');"
                                                                class="btn btn-warning btn-xs">Delete</a>
                                                        <?php } ?>
                                                    </td>


                                                    </tr>
                                                <?php
                                                    $n++;
                                                } ?>
                                            </tbody>
                                        </table>

                                    <?php } else { ?>
                                        <strong>No Records to Display</strong>
                                    <?php } ?>

                                <?php } else { ?>

                                    <form action="xsale.php" method="POST" id="subject" name="subject" enctype="multipart/form-data">
                                        <table width="70%" cellpadding="2">
                                            <tr>
                                                <td>
                                                    <strong>Filter By Date</strong>
                                                    <div class="form-group" id="">
                                                        <div class="input-daterange input-group" id="datepicker">
                                                            <input type="date" class="form-control" name="start" value="<?php echo date("Y-m-d"); ?>" />
                                                            <span class="input-group-addon">to</span>
                                                            <input type="date" class="form-control" name="end" value="<?php echo date("Y-m-d"); ?>" />
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    Sort by Referral <strong>[Optional]</strong>
                                                </td>
                                                <td>
                                                    <select name="referral" class="input-sm chosen-select" style="width:350px;">
                                                        <option selected="selected" value="">Select referrals</option>

                                                        <?php $stmt = $db->query("SELECT name FROM referrals order by name");
                                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                                            <option value="<?php echo $row["name"]; ?>"><?php echo $row["name"]; ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </td>
                                                <td style="padding-left:10px;"><button class="btn btn-success btn-sm" type="submit" name="apply_date">Apply</button></td>
                                            </tr>
                                        </table>
                                    </form>

                                    <?php

                                    if (isset($_POST['apply_date'])) {
                                        $start = $_POST['start'];
                                        $end = $_POST['end'];
                                        $referral = $_POST['referral'];

                                        if (!empty($referral)) {
                                            $stmt2 = $db->prepare("SELECT p.*, r.name as referral_name FROM pharm_ext p LEFT JOIN referrals r ON p.referral = r.sn WHERE referral = :referral ORDER BY sn DESC");
                                            $stmt2->bindParam(':referral', $referral, PDO::PARAM_STR);
                                        } else {
                                            $stmt2 = $db->prepare("SELECT p.*, r.name as referral_name FROM pharm_ext p LEFT JOIN referrals r ON p.referral = r.sn WHERE date_ap BETWEEN :start AND :end ORDER BY sn DESC");
                                            $stmt2->bindParam(':start', $start, PDO::PARAM_STR);
                                            $stmt2->bindParam(':end', $end, PDO::PARAM_STR);
                                        }

                                        $stmt2->execute();
                                    } else {
                                        $currentDate = date("Y-m-d");
                                        $stmt2 = $db->prepare("SELECT p.*, r.name as referral_name FROM pharm_ext p LEFT JOIN referrals r ON p.referral = r.sn WHERE description = 'investigation' AND date_ap = :today ORDER BY sn DESC");
                                        $stmt2->bindParam(':today', $currentDate, PDO::PARAM_STR);
                                        $stmt2->execute();
                                    }

                                    if ($stmt2->rowCount() > 0) {
                                    ?>
                                        <table class="table table-striped table-bordered table-hover dataTables-example">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Patient Code</th>
                                                    <th>Name</th>
                                                    <th>Contact</th>
                                                    <th>Phone</th>
                                                    <th>Address</th>
                                                    <th>Referral</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $n = 1;
                                                while ($roww = $stmt2->fetch(PDO::FETCH_ASSOC)) {
                                                ?>
                                                    <tr>
                                                        <td><?= $n++; ?></td>
                                                        <td><?= htmlspecialchars($roww['transc_code']); ?></td>
                                                        <td><?= htmlspecialchars($roww['cust_name']); ?></td>
                                                        <td><?= htmlspecialchars($roww['description']); ?></td>
                                                        <td><?= htmlspecialchars($roww['phone']); ?></td>
                                                        <td><?= htmlspecialchars($roww['address']); ?></td>
                                                        <td><?= htmlspecialchars($roww['referral_name']); ?></td>
                                                        <td>
                                                            <input type="button"
                                                                <?= ($_SESSION['request'] == 0) ? '' : ''; /// cehck leter 
                                                                ?>
                                                                name="add_req"
                                                                value="Add New Request"
                                                                data-target="#myModal5"
                                                                id="<?= $roww['transc_code'] . '__' . $roww['cust_name'] . '__EX__' . $roww['referral']; ?>"
                                                                class="btn btn-primary btn-xs lab_request" />

                                                            <?php if ($_SESSION['bill'] == 1): ?>
                                                                <br>&nbsp; | &nbsp;
                                                                <a href="../billing/pacct.php?emr=<?= $roww['transc_code']; ?>&inv" class="btn btn-danger btn-xs">Billing</a>
                                                            <?php endif; ?>

                                                            &nbsp; | &nbsp;
                                                            <input type="button"
                                                                name="edit"
                                                                value="Edit Patient"
                                                                data-target="#myModal5"
                                                                id="<?= $roww['transc_code'] . '__' . $roww['cust_name']; ?>"
                                                                class="btn btn-warning btn-xs edit_ex" />

                                                            &nbsp; | &nbsp;
                                                            <a href="xsale.php?emr=<?= $roww['transc_code']; ?>&investigation" class="btn btn-info btn-xs">View Investigation(s)</a>
                                                        </td>
                                                    </tr>
                                                <?php
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    <?php
                                    } else {
                                        echo '<br>No Data Available!';
                                    }
                                    ?>


                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include("../inc/lab_mdl.php") ?>
    <?php include("../inc/footer_scripts.php"); ?>

    <?php if (isset($_GET['sv'])) { ?>
        <script>
            toastr.success('Data Save Successfully!', 'Saved', {
                timeOut: 5000
            })
        </script>
    <?php } ?>

    <?php include('alert.php'); ?>


    <!-- Data Tables -->
    <script src="../js/plugins/dataTables/jquery.dataTables.js"></script>
    <script src="../js/plugins/dataTables/dataTables.bootstrap.js"></script>
    <script src="../js/plugins/dataTables/dataTables.responsive.js"></script>
    <script src="../js/plugins/dataTables/dataTables.tableTools.min.js"></script>
    <link rel="stylesheet" href="../js/select2/css/select2.min.css">
    <script src="../js/select2/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.dataTables-example').dataTable({
                responsive: true,
                "dom": 'T<"clear">lfrtip',
                "tableTools": {
                    "sSwfPath": "js/plugins/dataTables/swf/copy_csv_xls_pdf.swf"
                }
            });



            /* Init DataTables */
            var oTable = $('#editable').dataTable();

            /* Apply the jEditable handlers to the table */
            oTable.$('td').editable('../example_ajax.php', {
                "callback": function(sValue, y) {
                    var aPos = oTable.fnGetPosition(this);
                    oTable.fnUpdate(sValue, aPos[0], aPos[1]);
                },
                "submitdata": function(value, settings) {
                    return {
                        "row_id": this.parentNode.getAttribute('id'),
                        "column": oTable.fnGetPosition(this)[2]
                    };
                },

                "width": "90%",
                "height": "100%"
            });


        });

        function fnClickAddRow() {
            $('#editable').dataTable().fnAddData([
                "Custom row",
                "New row",
                "New row",
                "New row",
                "New row"
            ]);

        }
    </script>


    <script>
        $(document).ready(function() {
            // External Patient Search
            $('#ex_patient').select2({
                placeholder: 'Search and Select Patient',
                minimumInputLength: 2,
                ajax: {
                    url: 'get_ext_patients.php',
                    type: 'POST',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            search: params.term,
                            mode: 'external' // Key difference
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                }
            });

            // Internal Patient Search
            $('#in_patient').select2({
                placeholder: 'Search and Select Patient',
                minimumInputLength: 2,
                ajax: {
                    url: 'get_ext_patients.php',
                    type: 'POST',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            search: params.term,
                            mode: 'internal' // Key difference
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                }
            });
        });
    </script>
    <script>
        var prevOut = localStorage.getItem('lab_outpatient') ?
            parseInt(localStorage.getItem('lab_outpatient')) : 0;

        var prevIn = localStorage.getItem('lab_inpatient') ?
            parseInt(localStorage.getItem('lab_inpatient')) : 0;


        function checkLab() {

            $.ajax({
                url: 'fetch_lab_count.php',
                method: 'GET',
                dataType: 'json',
                success: function(data) {

                    let outpatient = parseInt(data.outpatient) || 0;
                    let inpatient = parseInt(data.inpatient) || 0;

                    let total = outpatient + inpatient;




                    if (total > 0) {

                        $('#labAlertBox').fadeIn();

                        $('#lab_outpatient').text(outpatient);
                        $('#lab_inpatient').text(inpatient);

                        // 🔊 SOUND TRIGGER
                        if (outpatient > prevOut || inpatient > prevIn) {

                            let sound = new Audio('../sounds/notification.wav');
                            sound.play().catch(() => {});

                            $('#labAlertBox')
                                .css('background', '#d4edda')
                                .fadeOut(200).fadeIn(200)
                                .fadeOut(200).fadeIn(200);

                            setTimeout(() => {
                                $('#labAlertBox').css('background', '#f0fff0');
                            }, 3000);
                        }

                    } else {
                        $('#labAlertBox').fadeOut();
                    }

                    prevOut = outpatient;
                    prevIn = inpatient;

                    localStorage.setItem('lab_outpatient', outpatient);
                    localStorage.setItem('lab_inpatient', inpatient);
                }
            });
        }


        // CLICK
        $('#labAlertBox').on('click', function() {

            $('#labModal').modal('show');

            $.ajax({
                url: 'fetch_lab_list.php',
                success: function(data) {
                    $('#lab_body').html(data);
                }
            });

        });


        setInterval(checkLab, 10000);
        checkLab();
    </script>
</body>

</html>