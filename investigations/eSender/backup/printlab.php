<?php include("../Connections/Conn.php");
session_start();
?>

<?php



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
                                                    <td width="30%">
                                                        <strong>D.O.B:</strong> &nbsp; <?php echo $dob; ?>
                                                        &nbsp; | &nbsp; <strong>Age:</strong> &nbsp; <?php echo $age; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Patient No:</strong></td>
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




                                    $lab_tests = '';
                                    $pro_inv = $_REQUEST['inv'];
                                    for ($i = 0; $i < count($pro_inv); $i++) {
                                        $main_data = $pro_inv[$i];
                                        $row_tes = explode("__", $main_data);
                                        $test_name_ = $row_tes[1];
                                        $lab_tests = $lab_tests . $test_name_ . ',';
                                    }
                                    $lab_tests = substr_replace($lab_tests, "", -1);
                                    echo '<strong>Tests Requested:</strong> ' . $lab_tests;  ?>

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



                                        //$_detail=$test_id.'__'.$test_name.'__'.$labrequest_no.'__'.$preferred_specimen.'__'.
                                        //	$entered_by.'__'.$lab_combo.'__'.$request_note.'__'.$data_capture_status.'__'.$collected_specimen;

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


                                        $stmt3 = $db->query("SELECT * FROM lab_result WHERE lab_no='$labrequest_no' and test_no='$test_id'");
                                        $rowxx = $stmt3->fetch(PDO::FETCH_ASSOC);
                                        $comment = $rowxx['comment'];
                                        $notes = $rowxx['notes'];
                                        $result_date = $rowxx['result_date'];
                                        $lab_sci_name = $rowxx['lab_sci_name'];
                                        $lab_sci_speciality = $rowxx['lab_sci_speciality'];
                                        $spm = $rowxx['speciment_taken'];
                                        $test_result = $rowxx['field_value'];
                                        $field_name = $rowxx['field_name'];
                                        $field_ref = $rowxx['field_ref'];
                                        if ($spm == '') {
                                            $stmt3 = $db->query("SELECT collected_specimen FROM lab_manage WHERE labrequest_no='$labrequest_no'");
                                            $rowxx = $stmt3->fetch(PDO::FETCH_ASSOC);
                                            $spm = $rowxx['collected_specimen'];
                                        }

                                    ?>

                                        <?php



                                        include("printlab_bdy.php");
                                        if (isset($_POST['prev_result']) and $_POST['prev_result'] != '') {
                                            include("printlab_old_rlst.php");
                                        }
                                        //}
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
} else {
    header("location:mgt.php");
}
    ?>


    <div class="modal inmodal" id="send_mdl" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content animated bounceInRight">
                <div class="modal-body" id="modal_body">
                    <div id="">
                        <label><strong>Enter eMail Address: </strong></label>
                        <input type="text" maxlength="150" name="result_email_address" id="result_email_address" class="form-control" value="<?= $email; ?>" required>
                        <br>
                        <span id="send_res_status_str_good" style="color:green;"></span>
                        <span id="send_res_status_str_bad" style="color:red;"></span>
                        <br>

                        <button type="button" id="send_res_btn" onClick="sent_rslt()" id="" class="btn btn-primary btn-sm"><i class="fa fa-mail-forward"></i>&nbsp; Send Result</button>
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
            var hosp_no = "<?= $hosp_no; ?>";
            var labrequest_no = "<?= $labrequest_no; ?>";
            var type_patient = "<?= $type_patient; ?>";

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
                    email: email
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