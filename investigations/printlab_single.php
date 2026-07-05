<?php include("../Connections/Conn.php");
session_start();
?>

<?php

if (!empty($_GET['labrequest_no'])) {

    $labrequest_no = $_GET['labrequest_no'];
    $stmtE = $db->query("SELECT * FROM lab_manage WHERE labrequest_no='$labrequest_no'");
    $roww = $stmtE->fetch(PDO::FETCH_ASSOC);
    $type_patient = $roww['business_service_center'];
    $hosp_no = $roww['patient'];
    $app_no = $roww['app_no'];
    $lab_tests = $roww['test_name'];
    $lab_combos = $roww['lab_combos'];
    $test_id = $roww['test_id'];
    $approved_by = $roww['approved_by'];
    $lab_cat = $roww['lab_cat'];
    $Specimen = $roww['collected_specimen'];


    if ($type_patient == 'IN') {

        $stmtE = $db->query("SELECT phone, insurance,gender,nationality,addr,dob,surname,fname FROM enrollee WHERE hospital_no='$hosp_no'");
        $roww = $stmtE->fetch(PDO::FETCH_ASSOC);
        $nat = $roww['nationality'];
        $gender = $roww['gender'];
        $addr = $roww['addr'];
        $insurance = $roww['insurance'];
        $phone = $roww['phone'];
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
                            <input type="button" onClick="fun()" id="a1b" target="_blank" class="btn btn-success btn-xs" value="eMail Result" />
                            <input type="button" onClick="Clickheretoprint()" target="_blank" class="btn btn-primary btn-xs" value="Print Report" />


                            <a href="mgt.php?hosp_no=<?php echo $hosp_no; ?>" class="btn btn-info btn-xs"><i class="fa fa-arrow-circle-o-right"></i>Return</a>
                            <a href="mgt.php" class="btn btn-danger btn-xs"><i class="fa fa-times"></i> Close</a>
                        </div>
                    </div>
                </div>
                <div class="row" id="content">
                    <div class="col-lg-12">

                        <div class="wrapper wrapper-content animated fadeInRight">
                            <div class="ibox-content p-xl">
                                <div class="row">
                                    <table width="100%">
                                        <tr>
                                            <td>
                                                <img alt="image" src="../img/logo.png" height="111" width="196">
                                            </td>
                                            <td>
                                                <div align="right" class="pull-right">
                                                    <?php echo $_SESSION['h_address']; ?><br><?php echo $_SESSION['h_phone']; ?></div>
                                            </td>
                                        </tr>
                                    </table>

                                    <div align="center">
                                        <h2><?php echo $_SESSION['section']; ?> Report</h2>
                                    </div>


                                    <div class="col-sm-12">
                                        <table cellpadding="4" cellspacing="4" class="table table-bordered" style="font-size:12px; font-family:Arial, Helvetica, sans-serif;" width="100%">
                                            <tr>
                                                <td width="15%"><strong>Patient's Name:</strong></td>
                                                <td width="40%"><?php echo $patient_name; ?></td>
                                                <td width="15%"><strong>Sex:</strong> &nbsp; <?php echo $roww['gender']; ?></td>
                                                <td width="30%"><strong>D.O.B:</strong> &nbsp; <?php if ($birthDate != '0000-00-00') {
                                                                                                    echo date("d-m-Y", strtotime($birthDate));
                                                                                                } else {
                                                                                                    $age = 'N/A';
                                                                                                } ?> &nbsp; | &nbsp; <strong>Age:</strong> &nbsp; <?php echo $age; ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Patient ID No:</strong></td>
                                                <td><?php echo $hosp_no; ?></td>
                                                <td colspan="2"><strong>Requesting Physician:&nbsp;</strong><?php //echo $requesting_physician;
                                                                                                            ?></td>
                                            </tr>
                                            <tr>
                                                <td colspan="4"><?php echo '<strong>Address/Phone:</strong> ' . $addr . ' / ' . $phone; ?></td>
                                            </tr>
                                        </table>

                                        <?php if (isset($_GET["i"]) or isset($_GET["e"])) { ?>
                                            <table cellpadding="5" cellspacing="5" class="table table-bordered" style="font-size:12px; font-family:Arial, Helvetica, sans-serif;" width="100%">
                                                <tr>
                                                    <td>Lab Request No.: &nbsp; <?php echo $roww['labrequest_no']; ?></td>
                                                    <td>Tests Requested: &nbsp; <?php echo $roww['test_name']; ?></td>
                                                    <td><strong>Requested Date:</strong> &nbsp;<?php
                                                                                                $result_date2 = $roww['result_date'];
                                                                                                echo date('d-m-Y', strtotime($roww['request_date'])); ?></td>
                                                </tr>
                                            </table>
                                        <?php } ?>

                                    </div>
                                    <hr>
                                </div>
                                <?php

                                echo '<strong>Tests Requested:</strong> ' . $lab_tests;  ?>

                                <?php



                                if ($lab_combo == 1) {

                                    $cb_no = $test_id;
                                    $stmt2 = $db->query("SELECT c.test_id, l.test FROM lab_combos_items as c inner join lab_scan as l on c.test_id=l.sn WHERE combos_id='$cb_no'");
                                    while ($row_test = $stmt2->fetch(PDO::FETCH_ASSOC)) {
                                        $test_name = $row_test['test'] . '<br>';
                                        $test_id = $row_test['test_id'];
                                        $data = $test_id . '__' . $test_name . '__' . $labrequest_no . '__' . '' . '__' . $cb_no;
                                        $all_data .= $data . ',';
                                    }
                                } else {

                                    $data = $test_id . '__' . $test_name . '__' . $labrequest_no . '__' . $Specimen . '__0';
                                    $all_data .= $data . ',';
                                }


                                $all_data = rtrim($all_data, ", ");
                                $myArray = explode(',', $all_data);

                                //echo '<br>';
                                ///print_r($myArray);
                                foreach ($myArray as $value) {
                                    ///echo "$value <br>";
                                    $row_test = explode("__", $value);

                                    $test_id = $row_test[0];
                                    $test_name = $row_test[1];
                                    $labrequest_no = $row_test[2];

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

                                    /// sort by 

                                    ///echo $depart_part;

                                    $stmt3 = $db->query("SELECT sn FROM lab_manage WHERE labrequest_no='$labrequest_no' $depart_part");
                                    if ($stmt3->rowCount() > 0) {

                                        $stmt3 = $db->query("SELECT * FROM lab_result WHERE lab_no='$labrequest_no' and test_no='$test_id'");
                                        $rowxx = $stmt3->fetch(PDO::FETCH_ASSOC);
                                        $comment = $rowxx['comment'];
                                        $notes = $rowxx['notes'];
                                        $result_date = $rowxx['result_date'];
                                        $lab_sci_name = $rowxx['lab_sci_name'];
                                        $lab_sci_speciality = $rowxx['lab_sci_speciality'];
                                        $spm = $rowxx['specimen_collected'];
                                        $test_result = $rowxx['field_value'];
                                        $field_name = $rowxx['field_name'];
                                        $field_ref = $rowxx['field_ref'];

                                ?>

                                    <?php



                                        include("printlab_bdy.php");
                                        if (isset($_POST['prev_result']) and $_POST['prev_result'] != '') {
                                            include("printlab_old_rlst.php");
                                        }
                                    }
                                    ?>
                                    <hr>

                            </div>

                        <?php } ?>

                        <?php
                        if ($_SESSION['h_name'] == 'Rayfield Medical Services') {
                            include("signatures.php");
                        } else { ?>

                            <table align="right" style="font-size:12px; font-family:Arial, Helvetica, sans-serif;">
                                <tr>
                                    <td width="40%"><strong><?php echo '<strong>' . $lab_sci_name . '</strong>'; ?></strong> <BR><?php echo $lab_sci_speciality; ?>
                                    </td>
                                </tr>
                            </table>
                        <?php } ?>



                        </div>
                    </div>
                </div>
            </div>


        </div>
        </div>

    <?php } else {
    header("location:mgt.php?invalid_selection&hosp_no=$hosp_no");
}

    ?>


    <div class="modal inmodal" id="send_mdl" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content animated bounceInRight">
                <div class="modal-body" id="modal_body">

                    <div id="">
                        <label><strong>Enter eMail Address: </strong></label>

                        <input type="text" maxlength="150" name="result_email_address" id="result_email_address" class="form-control" value="" required>
                        <br>


                        <button type="button" onClick="sent_rslt()" id="" class="btn btn-primary btn-sm"><i class="fa fa-mail-forward"></i>&nbsp; Send Result</button>
                    </div>

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

        function sent_rslt() {
            var text1 = document.getElementById("content");
            var text2 = document.getElementById("content");

            function emailIsValid(email) {
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
            }

            var email = document.getElementById("result_email_address").value;
            var err = emailIsValid(email); // false	
            if (err) {} else {
                alert('Invalid eMail Address!');
                exit;
            }



            ///alert(text1.innerHTML);

            $.ajax({
                url: "send-email.php",
                data: {
                    text2: text1.innerHTML,
                    email: email
                },
                type: 'POST',
                success: function(response) {
                    alert(response);
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
            docprint.document.write('</head><body onLoad="self.print()" style="width: 800px; font-size: 13px; font-family: arial;">');
            docprint.document.write(content_vlue);
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