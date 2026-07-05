<?php include("../Connections/Conn.php");
session_start();
?>

<?php


if (isset($_POST['generate_bill'])) {
    $hospital_no = $_POST['hospital_no'];

    $ledger_TX = 1;
    $sql = $db->prepare("INSERT INTO patient_ap_bill_group (ledger_id) VALUES (:ledger_id)");
    $sql->bindParam(':ledger_id', $ledger_TX, PDO::PARAM_STR);

    // Execute the statement and check if it was successful
    if ($sql->execute()) {
        // Get the last inserted ID
        $last_id = $db->lastInsertId();

        // Check if last_id is greater than zero
        if ($last_id > 0) {
            $last_id = str_pad($last_id, 3, "0", STR_PAD_LEFT);


            if ($last_id != '') {
                if (isset($_POST['inv_bill_2']) && is_array($_POST['inv_bill_2'])) {
                    $selectedRequests = $_POST['inv_bill_2'];

                    // Loop through each selected request
                    foreach ($selectedRequests as $request) {

                        $updateSQL = "UPDATE lab_manage SET bill = :bill WHERE labrequest_no = :labrequest_no AND bill is null";
                        $stmt_update = $db->prepare($updateSQL);

                        // Bind parameters
                        $stmt_update->bindParam(':bill', $last_id, PDO::PARAM_STR);
                        $stmt_update->bindParam(':labrequest_no', $request, PDO::PARAM_STR);
                        $stmt_update->execute();
                    }

                    header("Location: mgt.php?hosp_no=$hospital_no");
?>

        <?php  } else {
                    echo '<h2>No Item checkboxes selected.</h2>';
                    echo '<a href="mgt.php">Return</a>';
                }
            } else {
                echo 'Empty Lab Number';
            }
        }
    } else {
        // Handle the error if the execution failed
        echo "Insertion failed: " . implode(", ", $sql->errorInfo());
    }
    exit;
}


if (isset($_POST['display_result_print'])) {
    $hosp_no = $_POST['hosp_no'];
    $app_no = $_POST['app_no'];
    $type_patient = $_POST['type_patient'];

    if (!empty($_REQUEST['inv'])) {





        if ($type_patient == 'IN') {

            $stmtE = $db->query("SELECT phone, insurance,gender,nationality,addr,dob,surname,fname,dob
	FROM enrollee WHERE hospital_no='$hosp_no'");
            $roww = $stmtE->fetch(PDO::FETCH_ASSOC);
            $nat = $roww['nationality'];
            $gender = $roww['gender'];
            $addr = $roww['addr'];
            $insurance = $roww['insurance'];
            $phone = $roww['phone'];
            $email = $roww['email'];
            $birthDate = $roww['dob'];
            $patient_name = $roww['fname'] . ' ' . $roww['surname'];
        } else {

            $stmtE = $db->query("SELECT phone,gender,address,dob,cust_name FROM  pharm_ext WHERE transc_code='$hosp_no'");
            $roww = $stmtE->fetch(PDO::FETCH_ASSOC);
            $gender = $roww['gender'];
            $addr = $roww['address'];
            $insurance = 'Private(Self Pay)';
            $phone = $roww['phone'];
            $birthDate = $roww['dob'];
            $email = $roww['email'];
            $patient_name = $roww['cust_name'];
            $nat = '';
            //$approved_by=$roww['approved_by'];
            //	$result_date=$roww['result_date'];
            //$requesting_physician=$roww['requesting_physician'];
        }
        date_default_timezone_set('Africa/Lagos');
        $Current_date = date('Y-m-d');
        $date1 = new DateTime($Current_date);
        $date2 = new DateTime($birthDate);
        $diff = $date2->diff($date1);
        $age = $diff->format('%y');

        if ($age == 0) {
            $age = 'N/A';
        }

        $dd = date("d-m-Y", strtotime($birthDate));
        if ($dd == '01-01-1970') {
            $dob = '';
        } else {
            $dob = $dd;
        }

        ?>


        <!DOCTYPE html>
        <html>
        <style>
            .td_s {
                padding-right: 60px;
                padding-bottom: 10px;
            }
        </style>
        <?php include("../inc/header.php"); ?>


        <body>


            <div id="wrapper">

                <?php include("../inc/nav_side.php"); ?>


                <div id="page-wrapper" class="gray-bg">
                    <?php include("../inc/nav_header.php"); ?>

                    <div class="row wrapper border-bottom white-bg page-heading">
                        <div class="col-lg-8">
                            <h2>Investigation Report</h2>
                            <ol class="breadcrumb">
                                <li>
                                    <a href="index.php">Home</a>
                                </li>
                                <li>
                                    <a href="mgt.php">Lab</a>
                                </li>
                                <li class="active">
                                    <strong>Lab Results</strong>
                                </li>
                            </ol>
                        </div>
                        <div class="col-lg-4">
                            <div class="title-action">
                                <input type="button" onClick="fun()" id="a1b" target="_blank" class="btn btn-success btn-xs" value="eMail Result" /> <input type="button" onClick="Clickheretoprint()" target="_blank" class="btn btn-primary btn-xs" value="Print Report" />


                                <a href="mgt.php?hosp_no=<?php echo $hosp_no; ?>" class="btn btn-info btn-xs"><i class="fa fa-arrow-circle-o-right"></i>Return</a>
                                <a href="mgt.php" class="btn btn-danger btn-xs"><i class="fa fa-times"></i> Close</a>
                            </div>
                        </div>
                    </div>
                    <div class="row" id="content">
                        <div class="col-lg-12">

                            <div class="wrapper wrapper-content animated fadeInRight main-res-content">
                                <?php
                                function adjustBrightness($hex, $factor)
                                {
                                    $hex = str_replace('#', '', $hex);

                                    // Convert to RGB
                                    $r = hexdec(substr($hex, 0, 2));
                                    $g = hexdec(substr($hex, 2, 2));
                                    $b = hexdec(substr($hex, 4, 2));

                                    // Adjust brightness
                                    $r = max(0, min(255, $r * $factor));
                                    $g = max(0, min(255, $g * $factor));
                                    $b = max(0, min(255, $b * $factor));

                                    // Create lighter/darker color
                                    $r = str_pad(dechex((int)$r), 2, '0', STR_PAD_LEFT);
                                    $g = str_pad(dechex((int)$g), 2, '0', STR_PAD_LEFT);
                                    $b = str_pad(dechex((int)$b), 2, '0', STR_PAD_LEFT);

                                    return '#' . $r . $g . $b;
                                }
                                ?>
                                <div class="ibox-content p-xl">
                                    <!-- Header with logo and hospital info -->
                                    <div class="row">
                                        <table width="100%">
                                            <tr>
                                                <td width="50%">
                                                    <img alt="hospital logo" src="../img/logo.png" style="max-height: 80px;">
                                                </td>
                                                <td width="50%">
                                                    <div class="hospital-info pull-right" style="border-left: 3px solid <?php echo $_SESSION['h_color_code_hex'] ?>; padding-left: 15px;">
                                                        <h3 style="margin-bottom: 5px; color: <?php echo $_SESSION['h_color_code_hex'] ?>;"><?php echo $_SESSION['h_name'] ?></h3>
                                                        <div style="color: #777;">
                                                            <?php echo $_SESSION['h_address']; ?><br>
                                                            <?php echo $_SESSION['h_phone']; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>

                                    <!-- Report Title -->
                                    <div align="center" style="margin: 20px 0;">
                                        <h2 style="background-color: <?php echo $_SESSION['h_color_code_hex'] ?>; color: white; padding: 8px 15px; border-radius: 4px;"><?php echo $_SESSION['section']; ?> Report</h2>
                                    </div>

                                    <!-- Patient Information -->
                                    <div class="patient-info" style="margin-bottom: 20px;">
                                        <table cellpadding="5" cellspacing="0" class="table table-bordered" style="font-size: 13px; font-family: Arial, Helvetica, sans-serif; width: 100%;">
                                            <tr style="background-color: <?php echo adjustBrightness($_SESSION['h_color_code_hex'], 0.9);  ?>; color:white;">
                                                <td width="15%"><strong>Patient's Name:</strong></td>
                                                <td width="40%"><?php echo $patient_name; ?></td>
                                                <td width="15%"><strong>Sex:</strong> &nbsp; <?php echo $roww['gender']; ?></td>
                                                <td width="30%">
                                                    <strong>D.O.B:</strong> &nbsp; <?php echo $dob; ?>
                                                    &nbsp; | &nbsp; <strong>Age:</strong> &nbsp; <?php echo $age; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Patient No:</strong></td>
                                                <td><?php echo $hosp_no; ?></td>
                                            </tr>
                                            <tr>
                                                <td colspan="4"><strong>Address/Phone:</strong> <?php echo $addr . ' / ' . $phone; ?></td>
                                            </tr>
                                        </table>

                                        <?php if (isset($_GET["i"]) or isset($_GET["e"])) { ?>
                                            <table cellpadding="5" cellspacing="0" class="table table-bordered" style="font-size: 13px; font-family: Arial, Helvetica, sans-serif; width: 100%; margin-top: 10px;">
                                                <tr style="text-color: <?php echo adjustBrightness($_SESSION['h_color_code_hex'], 0.9); ?>;">
                                                    <td><strong>Lab Request No.:</strong> &nbsp; <?php echo $roww['labrequest_no']; ?></td>
                                                    <td><strong>Tests Requested:</strong> &nbsp; <?php echo $roww['test_name']; ?></td>
                                                    <td><strong>Requested Date:</strong> &nbsp;<?php
                                                                                                $result_date2 = $roww['result_date'];
                                                                                                echo date('d-m-Y', strtotime($roww['request_date'])); ?>
                                                    </td>
                                                </tr>
                                            </table>
                                        <?php } ?>
                                    </div>

                                    <hr style="border-top: 1px solid <?php echo $_SESSION['h_color_code_hex'] ?>;">

                                    <!-- Tests Requested Section -->
                                    <div class="tests-requested" style="margin-bottom: 20px;">
                                        <?php
                                        $lab_tests = '';
                                        $pro_inv = $_REQUEST['inv'];
                                        $test_req_ids = array();

                                        for ($i = 0; $i < count($pro_inv); $i++) {
                                            $main_data = $pro_inv[$i];
                                            $row_tes = explode("__", $main_data);
                                            $test_name_ = $row_tes[1];
                                            $test_req_ids[$i] = $row_tes[2];
                                            $lab_tests = $lab_tests . $test_name_ . ', ';
                                        }
                                        $lab_tests = substr_replace($lab_tests, "", -2);
                                        ?>
                                        <div style="color: <?php echo adjustBrightness($_SESSION['h_color_code_hex'], 0.9); ?>; border-left: 4px solid <?php echo $_SESSION['h_color_code_hex'] ?>; padding: 10px;">
                                            <strong>Tests Requested:</strong> <?php echo $lab_tests; ?>
                                        </div>
                                    </div>

                                    <!-- Test Results Section -->
                                    <?php
                                    $lab_cat = $_POST['department'];
                                    if ($lab_cat != '') {
                                        $depart_part = " and lab_cat='$lab_cat'";
                                    } else {
                                        $depart_part = "";
                                    }

                                    $pro_inv2 = $_REQUEST['inv'];

                                    for ($ii = 0; $ii < count($pro_inv2); $ii++) {
                                        $main_data = $pro_inv2[$ii];
                                        $row_test = explode("__", $main_data);
                                        $labrequest_no = $row_test[2];
                                        $lab_combo = $row_test[5];
                                        $test_name = $row_test[1];
                                        $test_id = $row_test[0];
                                        $approved_by = $row_test[4];
                                        $lab_cat = $row_test[6];
                                        $preferred_specimen = $row_test[3];
                                        $collected_specimen = $row_test[8];

                                        $stmtget = $db->prepare("SELECT field_type, sn FROM lab_scan_fields WHERE test_no = :test_id");
                                        $stmtget->bindParam(':test_id', $test_id, PDO::PARAM_STR);
                                        $stmtget->execute();

                                        if ($stmtget->rowCount() > 0) {
                                            $row_get = $stmtget->fetch(PDO::FETCH_ASSOC);
                                            $field_type = $row_get['field_type'];
                                            $field_no = $row_test['sn'];
                                        } else {
                                            $field_type = 'report';
                                        }

                                        $stmt3 = $db->prepare("
                                        SELECT 
                                            r.*, 
                                            l.request_by, 
                                            l.approved_by, 
                                            l.lab_sci_speciality AS approver_speciality,
                                            l.collected_specimen
                                        FROM lab_result r
                                        LEFT JOIN lab_manage l ON l.labrequest_no = r.lab_no
                                        WHERE r.lab_no = :lab_no AND r.test_no = :test_no
                                    ");
                                        $stmt3->execute([
                                            ':lab_no' => $labrequest_no,
                                            ':test_no' => $test_id
                                        ]);

                                        $rowxx = $stmt3->fetch(PDO::FETCH_ASSOC);

                                        if ($rowxx) {
                                            $comment = $rowxx['comment'];
                                            $notes = $rowxx['notes'];
                                            $result_date = $rowxx['result_date'];
                                            $lab_sci_name = $rowxx['lab_sci_name'];
                                            $lab_sci_speciality = $rowxx['lab_sci_speciality'];
                                            $test_result = $rowxx['field_value'];
                                            $field_name = $rowxx['field_name'];
                                            $field_ref = $rowxx['field_ref'];
                                            $requesting_physician = $rowxx['request_by'];
                                            $approve_by = $rowxx['approve_by'];
                                            $approver_speciality = $rowxx['approver_speciality'];
                                            $lab_no_bill = $rowxx['bill'];

                                            $spm = $rowxx['speciment_taken'];
                                            if (empty($spm)) {
                                                $spm = $rowxx['collected_specimen'];
                                            }
                                        }

                                    ?>

                                        <div class="test-result" style="margin-bottom: 10px; border: 1px solid <?php echo $_SESSION['h_color_code_hex'] ?>; border-radius: 4px;">
                                            <!-- <div style="background-color: <?php echo $_SESSION['h_color_code_hex'] ?>; color: white; padding: 8px 15px; border-top-left-radius: 4px; border-top-right-radius: 4px;">
                                                <h4 style="margin: 0; font-size: 16px;"><?php echo $test_name; ?>, </h4>
                                            </div> -->
                                            <div style="padding: 15px;">

                                                <?php if (isset($_POST['prev_result']) and $_POST['prev_result'] != '') {
                                                    include("printlab_old_rlst.php");
                                                } else {
                                                    include("printlab_bdy.php");
                                                } ?>
                                            </div>
                                        </div>

                                        <!-- <hr style="border-top: 1px solid <?php echo $_SESSION['h_color_code_hex'] ?>;"> -->
                                    <?php } ?>

                                    <!-- Signature Section -->
                                    <div style="margin-top: 30px; clear: both;">
                                        <?php if ($_SESSION['h_code'] == 'RMS') {
                                            include("signatures.php");
                                        } else { ?>
                                            <div style="border-top: 2px solid <?php echo $_SESSION['h_color_code_hex'] ?>; width: 300px; float: right; padding: 10px; margin-bottom: 20px;">
                                                <div style="text-align: center;">
                                                    <strong style="font-size: 14px; color: <?php echo $_SESSION['h_color_code_hex'] ?>;"><?php echo $lab_sci_name; ?></strong>
                                                    <div style="font-size: 12px;"><?php echo $lab_sci_speciality; ?></div>
                                                </div>
                                            </div>
                                            <div style="clear: both;"></div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
            </div>

    <?php } else {
        header("location:mgt.php?invalid_selection&hosp_no=$hosp_no");
    }
} else {
    header("location:mgt.php");
}
    ?>


    <div class="modal inmodal" id="send_mdl" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content animated bounceInRight">
                <div class="modal-body" id="modal_body">
                    <div id="">
                        <input type="hidden" id="test_to_send" name="test_to_send" value="<?php echo implode(',', $test_req_ids) ?>">
                        <label><strong>Enter eMail Address: </strong></label>
                        <input type="text" maxlength="150" name="result_email_address" id="result_email_address" class="form-control" value="<?= $email; ?>" required>
                        <br><br>
                        <div class="form-sep form-group">
                            <label for="service_to_use_instant_res">Use InstantResult NG</label>
                            <input type="radio" value="instant_res" name="service_to_use" id="service_to_use_instant_res" checked>
                            <br>
                            <label for="service_to_use_facility">Use Facility Mail Server</label>
                            <input type="radio" value="facility" name="service_to_use" id="service_to_use_facility">
                        </div>
                        <!-- New encryption option -->
                        <div class="form-sep form-group">
                            <label for="encrypt_pdf">
                                <input type="checkbox" id="encrypt_pdf" name="encrypt_pdf">
                                Password Protect PDF
                            </label>
                            <!-- Password input field that shows only when checkbox is checked -->
                            <div id="password_section" style="display: none; margin-top: 10px;">
                                <label for="pdf_password"><small>Default is patient hospital no</small></label>
                                <input type="password" id="pdf_password" name="pdf_password" value="<?php echo $hosp_no ?>" class="form-control" placeholder="Enter password for PDF">
                            </div>
                        </div>
                        <span id="send_res_status_str_good" style="color:green;"></span>
                        <br>
                        <span id="send_res_status_str_bad" style="color:red;"></span>
                        <br>
                        <button type="button" id="send_res_btn" onClick="sent_rslt()" class="btn btn-primary btn-sm"><i class="fa fa-mail-forward"></i>&nbsp; Send Result</button>
                    </div>

                    <!-- Add this JavaScript to toggle password field -->
                    <script>
                        document.getElementById('encrypt_pdf').addEventListener('change', function() {
                            var passwordSection = document.getElementById('password_section');
                            passwordSection.style.display = this.checked ? 'block' : 'none';

                            // Clear password when unchecking
                            // if (!this.checked) {
                            //     document.getElementById('pdf_password').value = '';
                            // }
                        });
                    </script>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>



    <?php include("search_modal.php") ?>
    <?php include("../inc/footer_scripts.php"); ?>


    <script>
        function fun() {
            $("#send_mdl").modal('show');
        }

        // function sent_rslt() {
        //     var text1 = document.getElementById("content");
        //     var text2 = document.getElementById("content");

        //     function emailIsValid(email) {
        //         return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
        //     }

        //     var email = document.getElementById("result_email_address").value;
        //     var err = emailIsValid(email); // false
        //     if (err) {} else {
        //         alert('Invalid eMail Address!');
        //         exit;
        //     }



        //     //alert(text1.innerHTML);

        //     $.ajax({
        //         url: "send-email.php",
        //         data: {
        //             text2: text1.innerHTML,
        //             email: email
        //         },
        //         type: 'POST',
        //         success: function(response) {
        //             alert(response);
        //         }
        //     });
        // }
    </script>
    <script>
        function sent_rslt() {
            var email = document.getElementById("result_email_address").value;
            var test_to_send = document.getElementById("test_to_send").value;
            var encrypt_pdf = document.getElementById("encrypt_pdf").checked ? 'yes' : 'no';
            var pdf_password = document.getElementById("pdf_password").value;
            var hosp_no = "<?= $hosp_no; ?>";
            var labrequest_no = "<?= $labrequest_no; ?>";
            var type_patient = "<?= $type_patient; ?>";
            var service_to_use = document.querySelector('input[name="service_to_use"]:checked').value;



            function emailIsValid(email) {
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
            }

            if (!emailIsValid(email)) {
                //alert('Invalid eMail Address!');
                $('#send_res_status_str_bad').text('Invalid eMail Address! ');
                return;
            }
            $('#send_res_btn').attr('disabled', true);
            $('#send_res_status_str_good').text('Sending result...please wait!');
            $.ajax({
                url: "send-email-instant-result.php",
                data: {
                    hosp_no: hosp_no,
                    labrequest_no: labrequest_no,
                    type_patient: type_patient,
                    email: email,
                    test_to_send: test_to_send,
                    service_to_use: service_to_use,
                    encrypt_pdf: encrypt_pdf,
                    pdf_password: pdf_password,
                },
                type: 'POST',
                success: function(response) {
                    //alert(response);
                    console.log(response);
                    $('#send_res_btn').attr('disabled', false);
                    $('#send_res_status_str_good').text(response);
                    $('#send_res_status_str_bad').text('');
                },
                error: function(xhr, status, error) {
                    console.error(xhr);
                    //alert('An error occurred: ' + error);
                    $('#send_res_btn').attr('disabled', false);
                    $('#send_res_status_str_bad').text('An error occurred: ' + error);
                    $('#send_res_status_str_good').text('');
                }
            });
        }
    </script>


    <script>
        function Clickheretoprint() {
            var disp_setting = "toolbar=yes,location=no,directories=yes,menubar=yes,";
            disp_setting += "scrollbars=yes,width=800, height=400, left=100, top=25";
            var content_vlue = document.getElementById("content").innerHTML;

            var docprint = window.open("", "", disp_setting);
            docprint.document.open();

            // Add print-specific CSS to force background colors and images
            var printCSS = `
                <style>
                    @media print {
                        * {
                            -webkit-print-color-adjust: exact !important; /* Chrome/Safari/Edge */
                            color-adjust: exact !important;               /* Firefox */
                            print-color-adjust: exact !important;         /* Future standard */
                        }
                        body {
                            width: 800px; 
                            font-size: 13px; 
                            font-family: arial;
                        }
                    }
                </style>
            `;

            docprint.document.write('<html><head>' + printCSS + '</head>');
            docprint.document.write('<body onLoad="self.print()">');
            docprint.document.write(content_vlue);
            docprint.document.write('</body></html>');
            docprint.document.close();
            docprint.focus();
        }
    </script>

    <!-- Data Tables -->
    <script src="../js/plugins/dataTables/jquery.dataTables.js"></script>
    <script src="../js/plugins/dataTables/dataTables.bootstrap.js"></script>
    <script src="../js/plugins/dataTables/dataTables.responsive.js"></script>
    <script src="../js/plugins/dataTables/dataTables.tableTools.min.js"></script>

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

        </body>

        </html>