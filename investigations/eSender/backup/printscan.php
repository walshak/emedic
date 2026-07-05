<?php include("../Connections/Conn.php");
session_start();
?>

<?php

if (isset($_GET["i"])) {
    $item_to_search = $_GET["i"];
    $type_patient = "IN";
    $labrequest_no = $_GET['i'];
} elseif (isset($_GET["e"])) {
    $item_to_search = $_GET["e"];
    $type_patient = "EX";
    $labrequest_no = $_GET['e'];
}
$stmt = $db->prepare("SELECT * FROM lab_manage WHERE labrequest_no = :item_to_search");
$stmt->bindParam(':item_to_search', $item_to_search, PDO::PARAM_STR);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $roww = $stmt->fetch(PDO::FETCH_ASSOC);
    $hosp_no = $roww['patient'];
} else {
    // header("Location: mgt.php");
    // exit;

}




if (isset($_GET["i"])) {
    $sms = "i";
    $stmtx = $db->prepare("SELECT phone, insurance, gender, nationality, addr, dob FROM enrollee WHERE hospital_no = :hosp_no");
    $stmtx->bindParam(':hosp_no', $hosp_no, PDO::PARAM_STR);
    $stmtx->execute();

    if ($stmtx->rowCount() > 0) {
        $roww2 = $stmtx->fetch(PDO::FETCH_ASSOC);
        $nat = $roww2['nationality'];
        $gender = $roww2['gender'];
        $addr = $roww2['addr'];
        $insurance = $roww2['insurance'];
        $phone = $roww2['phone'];
        $birthDate = $roww2['dob'];
    } else {
        var_dump($hosp_no);
        die('hdhdh');
        header("Location: mgt.php");
        exit;
    }
}



if (isset($_GET["e"])) {
    $sms = "e";
    $stmtx = $db->prepare("SELECT phone, gender, address, dob FROM pharm_ext WHERE transc_code = :hosp_no");
    $stmtx->bindParam(':hosp_no', $hosp_no, PDO::PARAM_STR);
    $stmtx->execute();

    if ($stmtx->rowCount() > 0) {
        $roww2 = $stmtx->fetch(PDO::FETCH_ASSOC);
        $gender = $roww2['gender'];
        $addr = $roww2['address'];
        $insurance = 'Private(Self Pay)';
        $phone = $roww2['phone'];
        $birthDate = $roww2['dob'];
        $nat = '';
    } else {
        header("Location: mgt.php");
        exit;
    }
}


date_default_timezone_set('Africa/Lagos');
$Current_date = date('Y-m-d');
$date1 = new DateTime($Current_date);
$date2 = new DateTime($birthDate);
$diff = $date2->diff($date1);
$age = $diff->format('%y');

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
                            <a href="mgt.php">Managment</a>
                        </li>
                        <li class="active">
                            <strong>Scan/Imaging Results</strong>
                        </li>
                    </ol>
                </div>
                <div class="col-lg-4">
                    <div class="title-action">
                        <?php /*?>  <a href="sms.php?<?php echo $sms.'=' . $item_to_search .'&scan'; ?>" class="btn btn-white btn-xs"><i class="fa fa-reply-all "></i> SMS Result </a><?php */ ?>

                        <input type="button" onClick="fun()" id="a1b" target="_blank" class="btn btn-success btn-xs" value="eMail Result" /> <input type="button" onClick="Clickheretoprint()" target="_blank" class="btn btn-primary btn-xs" value="Print Report" /> <a href="mgt.php?hosp_no=<?php echo $hosp_no; ?>" class="btn btn-info btn-xs" title="Close & Return to Previous Page"><i class="fa fa-arrow-circle-o-right"></i>Return</a>

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
                                            <div align="" class="pull-right">
                                                <?php echo $_SESSION['h_address']; ?><br><br> <?php echo $_SESSION['h_phone']; ?></div>

                                        </td>
                                    </tr>
                                </table>

                                <div align="center">
                                    <h2>Radiology Report</h2>
                                </div>


                                <div class="col-sm-12">
                                    <table cellpadding="5" cellspacing="2" class="table table-bordered" style="font-size:12px; font-family:Arial, Helvetica, sans-serif;" width="100%">
                                        <tr>
                                            <td width="15%">Patient's Name:</td>
                                            <td width="40%"><?php echo $roww['patient_name']; ?></td>
                                            <td width="15%">Sex: &nbsp; <?php echo $gender; ?></td>
                                            <td width="30%">Age: &nbsp; <?php echo $age; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Patient No:</td>
                                            <td><?php echo $roww['patient']; ?></td>
                                            <td>Patient Phone No.:</td>
                                            <td><?php echo $phone; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Address:</td>
                                            <td colspan="3"><?php echo $addr; ?></td>
                                        </tr>
                                    </table>

                                    <table cellpadding="5" cellspacing="5" class="table table-bordered" style="font-size:12px; font-family:Arial, Helvetica, sans-serif;" width="100%">
                                        <tr>
                                            <td><strong>Investigation Requested</strong>:</td>
                                            <td><?php echo $roww['test_name']; ?></td>
                                            <td><strong>Requested Date</strong>:</td>
                                            <td>&nbsp;<?php echo date('d-m-Y', strtotime($roww['request_date'])); ?></td>

                                            <td><strong>Result Date</strong>:</td>
                                            <td>&nbsp;<?php echo date('d-m-Y', strtotime($roww['result_date'])); ?></td>

                                        </tr>
                                    </table>
                                    <?php

                                    $lab_no = $roww['labrequest_no'];
                                    $stmtx = $db->query("SELECT * FROM lab_result WHERE lab_no='$lab_no'");
                                    if ($stmtx->rowCount() > 0) {
                                        $roww2 = $stmtx->fetch(PDO::FETCH_ASSOC);
                                        $result_note = $roww2['field_value'];
                                    } ?>


                                    <section style="border:1px solid #888; padding: 10px; "><?php echo $result_note; ///['result_note'] 
                                                                                            ?></section>


                                    </tbody>
                                    </table>


                                    <?php
                                    $approved_by = $roww['approved_by'];
                                    $stmt = $db->query("Select username from admin_users where fullname like '%$approved_by%'");
                                    if ($stmt->rowCount() > 0) {
                                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                        $uname = $row['username'];
                                    ?>
                                        <?php if (file_exists(staff_p . 'sign_' . $uname . '.' . 'jpg')) { ?>

                                            <table align="right" style="font-size:12px;" width="100%">
                                                <tr>
                                                    <td width="40%"><?php echo $roww['approved_by']; ?><BR><strong><?php echo $roww['lab_sci_speciality']; ?></strong></td>
                                                    <td width="15%">&nbsp;</td>
                                                </tr>
                                            </table>

                                            <img src="<?php if (file_exists(staff_p . 'sign_' . $uname . '.' . 'jpg')) {
                                                            echo staff_p . 'sign_' . $uname . '.' . 'jpg';
                                                        } ?>" height="100" width="170">
                                        <?php } ?>

                                    <?php }
                                    ?>





                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>









        </div>
    </div>



    <!-- <div class="modal inmodal" id="send_mdl" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
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
    </div> -->

    <div class="modal inmodal" id="send_mdl" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content animated bounceInRight">
                <div class="modal-body" id="modal_body">
                    <div id="">
                        <label><strong>Enter eMail Address: </strong></label>
                        <!-- <input type="text" maxlength="150" name="result_email_address" id="result_email_address" class="form-control" value="<?= $email; ?>" required> -->
                        <input type="text" maxlength="150" name="result_email_address" id="result_email_address" class="form-control" value="" required>
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



        //     ///alert(text1.innerHTML);

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