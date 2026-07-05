<?php include("../Connections/Conn.php");
session_start();
?>
<!DOCTYPE html>
<html>
<?php include("../inc/header.php"); ?>
</head>

<body class="fixed-navigation">

    <div id="wrapper">
        <?php include("../inc/nav_admin_side_bar.php"); ?>

        <div id="page-wrapper" class="gray-bg sidebar-content">
            <?php include("../inc/nav_header.php"); ?>

            <?php include("../inc/billing_side_bar.php"); ?>

            <div class="wrapper wrapper-content">
                <div class="row">


                    <div class="col-lg-12">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">
                                <?php if (isset($_GET['owing'])) { ?>
                                    <h5>PATIENT ACCOUNT RECIEVABLE (DEBT)</h5>
                                <?php } else { ?>
                                    <h5>PATIENT DEPOSITS</h5>
                                <?php } ?>
                            </div>

                            <div class="ibox-content" id="content">
                                <a href="index.php?report" class="btn btn-danger">Close</a>&nbsp;:&nbsp;
                                <?php
                                if (isset($_GET['owing'])) { ?>
                                    <a href="patients_balance2.php?all&owing" class="btn btn-primary">Show All Patients</a> &nbsp;:&nbsp;
                                    <a href="patients_balance2.php?ext&owing" class="btn btn-warning">Show External Patients</a> &nbsp;:&nbsp;
                                    <a href="patients_balance2.php?nonfamily&owing" class="btn btn-success">Show Private Patients Only</a> &nbsp;:&nbsp;
                                    <a href="patients_balance2.php?family&owing" class="btn btn-info">Show Family</a> &nbsp;:&nbsp;
                                <?php } else { ?>
                                    <a href="patients_balance2.php?all&deposit" class="btn btn-primary">Show All Patients</a> &nbsp;:&nbsp;
                                    <a href="patients_balance2.php?ext&deposit" class="btn btn-warning">Show External Patients</a> &nbsp;:&nbsp;
                                    <a href="patients_balance2.php?nonfamily&deposit" class="btn btn-success">Show Private Patients Only</a> &nbsp;:&nbsp;
                                    <a href="patients_balance2.php?family&deposit" class="btn btn-info">Show Family</a> &nbsp;:&nbsp;
                                <?php } ?>
                                <hr>
                                <div class="alert alert-success">
                                    <h4><?php

                                        if (isset($_GET['ext'])) {
                                            echo 'EXTERNAL PATIENTS';
                                        } elseif (isset($_GET['nonfamily'])) {
                                            echo 'PRIVATE PATIENTS';
                                        } elseif (isset($_GET['all'])) {
                                            echo 'ALL PATIENTS';
                                        } elseif (isset($_GET['family'])) {
                                            echo 'FAMILY FOLDERS ';
                                        } else {
                                            echo 'CLICK BUTTON ABOVE TO DISPLAY RECORDS';
                                        }
                                        ?></h4>
                                </div>

                                <?php
                                $tbl_body = '';
                                $sn = 1;
                                $Total_recievabl_bal = 0;

                                if (isset($_GET['ext']) or isset($_GET['all'])) {

                                    $insurance_name = '<div style="color:brown;">EXTERNAL PATIENT</div>';
                                    $stmt_main = $db->query("SELECT distinct hospital_no FROM chart_ledger WHERE insurance_no ='EX0001'");
                                    if ($stmt_main->rowCount() > 0) {
                                        while ($row = $stmt_main->fetch(PDO::FETCH_ASSOC)) {
                                            $hospital_no = $row['hospital_no'];
                                            $stmt = $db->query("SELECT sum(dr_amt) as TOTAL_DEBITS, sum(cr_amt) as TOTAL_CREDITS FROM chart_ledger WHERE hospital_no='$hospital_no' and account_no=2121");
                                            $row45 = $stmt->fetch(PDO::FETCH_ASSOC);
                                            $TOTAL_CREDITS = $row45['TOTAL_CREDITS'];
                                            $TOTAL_DEBITS = $row45['TOTAL_DEBITS'];
                                            $current_balance = $TOTAL_CREDITS - $TOTAL_DEBITS;
                                            $current_balance = floatval($current_balance);
                                            $threshold = 0.00001;

                                            $stmcct = $db->query("Select cust_name from pharm_ext where transc_code='$hospital_no'");
                                            $rowx = $stmcct->fetch(PDO::FETCH_ASSOC);
                                            $patient_name = $rowx['cust_name'];

                                            // Check if the current balance is less than -threshold
                                            if ($current_balance < -$threshold && isset($_GET['owing'])) {

                                                $credit = abs($current_balance);
                                                $total_credit = $total_credit + $credit;

                                                ////==================
                                                // Prepare the SQL statement to prevent SQL injection
                                                $stmt_ = $db->prepare("SELECT SUM(dr_amt) AS TOTAL_DEBITS, SUM(cr_amt) AS TOTAL_CREDITS FROM chart_ledger WHERE hospital_no = :hospital_no AND account_no = :account_no");
                                                $account_no = 1502; // Assuming account_no is constant as per your original code
                                                $stmt_->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
                                                $stmt_->bindParam(':account_no', $account_no, PDO::PARAM_INT);
                                                $stmt_->execute();
                                                if ($stmt_->rowCount() > 0) {
                                                    $row455 = $stmt_->fetch(PDO::FETCH_ASSOC);
                                                    $TOTAL_CREDITS_2 = $row455['TOTAL_CREDITS']; // Use null coalescing operator to handle null values
                                                    $TOTAL_DEBITS_2 = $row455['TOTAL_DEBITS']; // Use null coalescing operator to handle null values
                                                    $acct_recievabl_bal = $TOTAL_DEBITS_2 - $TOTAL_CREDITS_2;
                                                    $Total_recievabl_bal += $acct_recievabl_bal; // Use shorthand for addition
                                                    $RV = number_format($acct_recievabl_bal, 2);
                                                } else {
                                                    $RV = 'N/A';
                                                }

                                                // Optionally, you can return or echo $RV here
                                                $tbl_body .= '<tr>
                                                <td>' . $sn++ . '</td>
                                                <td>' . $hospital_no . '</td>
                                                <td>' . $insurance_name . '</td>
                                                <td>' . $patient_name . '</td>
                                                <td>' . number_format($TOTAL_DEBITS, 2) . '</td>
                                                <td>' . number_format($TOTAL_CREDITS, 2) . '</td>
                                                <td>' . number_format($current_balance, 2) . '</td>
                                                <td>' . $RV . '</td>
                                            </tr>';
                                            } elseif ($current_balance > 0 && isset($_GET['deposit'])) {

                                                $total_credit = $total_credit + $current_balance;

                                                $tbl_body .= '<tr>
                                                <td>' . $sn++ . '</td>
                                                <td>' . $hospital_no . '</td>
                                                <td>' . $insurance_name . '</td>
                                                <td>' . $patient_name . '</td>
                                                <td>' . number_format($TOTAL_DEBITS, 2) . '</td>
                                                <td>' . number_format($TOTAL_CREDITS, 2) . '</td>
                                                <td>' . number_format($current_balance, 2) . '</td>
                                                <td>' . $RV . '</td>
                                            </tr>';
                                            }
                                        }
                                    }
                                }






                                //=================================================================================================
                                if (isset($_GET['nonfamily']) or isset($_GET['all'])) {
                                    //$insurance_name = 'PRIVATE PATIENT';
                                    $insurance_name = '<div style="color:green;">PRIVATE PATIENT</div>';
                                    $stmt_main = $db->query("SELECT distinct hospital_no FROM chart_ledger WHERE insurance_no =1000 or insurance_no ='private'");
                                    if ($stmt_main->rowCount() > 0) {
                                        while ($row = $stmt_main->fetch(PDO::FETCH_ASSOC)) {
                                            $hospital_no = $row['hospital_no'];
                                            $stmt = $db->query("SELECT sum(dr_amt) as TOTAL_DEBITS, sum(cr_amt) as TOTAL_CREDITS FROM chart_ledger WHERE hospital_no='$hospital_no' and account_no=2121");
                                            $row45 = $stmt->fetch(PDO::FETCH_ASSOC);
                                            $TOTAL_CREDITS = $row45['TOTAL_CREDITS'];
                                            $TOTAL_DEBITS = $row45['TOTAL_DEBITS'];
                                            $current_balance = $TOTAL_CREDITS - $TOTAL_DEBITS;
                                            $current_balance = floatval($current_balance);
                                            $threshold = 0.00001;

                                            // Check if the current balance is less than -threshold
                                            if ($current_balance < -$threshold) {
                                                $stmcct = $db->query("Select surname,fname,oname from enrollee where hospital_no='$hospital_no'");
                                                $rowx = $stmcct->fetch(PDO::FETCH_ASSOC);
                                                $patient_name = $rowx['surname'] . ' ' . $rowx['fname'] . ' ' . $rowx['oname'];

                                                $credit = abs($current_balance);
                                                $total_credit = $total_credit + $credit;

                                                ////==================
                                                // Prepare the SQL statement to prevent SQL injection
                                                $stmt_ = $db->prepare("SELECT SUM(dr_amt) AS TOTAL_DEBITS, SUM(cr_amt) AS TOTAL_CREDITS FROM chart_ledger WHERE hospital_no = :hospital_no AND account_no = :account_no");
                                                $account_no = 1502; // Assuming account_no is constant as per your original code
                                                $stmt_->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
                                                $stmt_->bindParam(':account_no', $account_no, PDO::PARAM_INT);
                                                $stmt_->execute();
                                                if ($stmt_->rowCount() > 0) {
                                                    $row455 = $stmt_->fetch(PDO::FETCH_ASSOC);
                                                    $TOTAL_CREDITS_2 = $row455['TOTAL_CREDITS']; // Use null coalescing operator to handle null values
                                                    $TOTAL_DEBITS_2 = $row455['TOTAL_DEBITS']; // Use null coalescing operator to handle null values
                                                    $acct_recievabl_bal = $TOTAL_DEBITS_2 - $TOTAL_CREDITS_2;
                                                    $Total_recievabl_bal += $acct_recievabl_bal; // Use shorthand for addition
                                                    $RV = number_format($acct_recievabl_bal, 2);
                                                } else {
                                                    $RV = 'N/A';
                                                }

                                                // Optionally, you can return or echo $RV here
                                                $tbl_body .= '<tr>
                                                <td>' . $sn++ . '</td>
                                                <td>' . $hospital_no . '</td>
                                                <td>' . $insurance_name . '</td>
                                                <td>' . $patient_name . '</td>
                                                <td>' . number_format($TOTAL_DEBITS, 2) . '</td>
                                                <td>' . number_format($TOTAL_CREDITS, 2) . '</td>
                                                <td>' . number_format($current_balance, 2) . '</td>
                                                <td>' . $RV . '</td>
                                            </tr>';
                                            } elseif ($current_balance > 0 && isset($_GET['deposit'])) {

                                                $total_credit = $total_credit + $current_balance;

                                                $tbl_body .= '<tr>
                                            <td>' . $sn++ . '</td>
                                            <td>' . $hospital_no . '</td>
                                            <td>' . $insurance_name . '</td>
                                            <td>' . $patient_name . '</td>
                                            <td>' . number_format($TOTAL_DEBITS, 2) . '</td>
                                            <td>' . number_format($TOTAL_CREDITS, 2) . '</td>
                                            <td>' . number_format($current_balance, 2) . '</td>
                                            <td>' . $RV . '</td>
                                        </tr>';
                                            }
                                        }
                                    }
                                }


                                /////////////=================================== FAMILTY FOLDER

                                if (isset($_GET['family']) or isset($_GET['all'])) {

                                    $stmt_main = $db->query("SELECT distinct insurance_no FROM chart_ledger WHERE insurance_no !=1000 AND insurance_no !='EX0001' AND insurance_no !='private' AND insurance_no!=''");
                                    if ($stmt_main->rowCount() > 0) {
                                        while ($row = $stmt_main->fetch(PDO::FETCH_ASSOC)) {

                                            $insurance_no = $row['insurance_no'];
                                            $stmt = $db->query("SELECT sum(dr_amt) as TOTAL_DEBITS, sum(cr_amt) as TOTAL_CREDITS FROM chart_ledger WHERE insurance_no='$insurance_no' and account_no=2121");
                                            $row45 = $stmt->fetch(PDO::FETCH_ASSOC);
                                            $TOTAL_CREDITS = $row45['TOTAL_CREDITS'];
                                            $TOTAL_DEBITS = $row45['TOTAL_DEBITS'];
                                            $current_balance = $TOTAL_CREDITS - $TOTAL_DEBITS;
                                            $current_balance = floatval($current_balance);
                                            $threshold = 0.00001;

                                            $stmcct = $db->query("Select insurance_name from insurance_tbl where insurance_no='$insurance_no'");
                                            $rowx = $stmcct->fetch(PDO::FETCH_ASSOC);
                                            $insurance_name = $rowx['insurance_name'];

                                            // Check if the current balance is less than -threshold

                                            if ($current_balance > 0 && isset($_GET['deposit'])) {
                                                $total_credit = $total_credit + $current_balance;
                                                $tbl_body .= '<tr>
                                                        <td>' . $sn++ . '</td>
                                                        <td>' . $hospital_no . '</td>
                                                        <td>' . $insurance_name . '</td>
                                                        <td>' . '' . '</td>
                                                        <td>' . number_format($TOTAL_DEBITS, 2) . '</td>
                                                        <td>' . number_format($TOTAL_CREDITS, 2) . '</td>
                                                        <td>' . number_format($current_balance, 2) . '</td>
                                                        <td></td>
                                                    </tr>';
                                                $current_balance = 0;
                                            } elseif ($current_balance < -$threshold && isset($_GET['owing'])) {
                                                $credit = abs($current_balance);
                                                $total_credit = $total_credit + $credit;

                                                ////==================
                                                // Prepare the SQL statement to prevent SQL injection
                                                $stmt_ = $db->prepare("SELECT SUM(dr_amt) AS TOTAL_DEBITS, SUM(cr_amt) AS TOTAL_CREDITS FROM chart_ledger WHERE insurance_no = :insurance_no AND account_no = :account_no");
                                                $account_no = 1502; // Assuming account_no is constant as per your original code
                                                $stmt_->bindParam(':insurance_no', $insurance_no, PDO::PARAM_STR);
                                                $stmt_->bindParam(':account_no', $account_no, PDO::PARAM_INT);
                                                $stmt_->execute();
                                                if ($stmt_->rowCount() > 0) {
                                                    $row455 = $stmt_->fetch(PDO::FETCH_ASSOC);
                                                    $TOTAL_CREDITS_2 = $row455['TOTAL_CREDITS']; // Use null coalescing operator to handle null values
                                                    $TOTAL_DEBITS_2 = $row455['TOTAL_DEBITS']; // Use null coalescing operator to handle null values
                                                    $acct_recievabl_bal = $TOTAL_DEBITS_2 - $TOTAL_CREDITS_2;
                                                    $Total_recievabl_bal += $acct_recievabl_bal; // Use shorthand for addition
                                                    $RV = number_format($acct_recievabl_bal, 2);
                                                } else {
                                                    $RV = 'N/A';
                                                }

                                                // Optionally, you can return or echo $RV here
                                                $tbl_body .= '<tr>
                                                <td>' . $sn++ . '</td>
                                                <td>' . $hospital_no . '</td>
                                                <td><div style="color:blue;">' . $insurance_name . '</div></td>
                                                <td><div style="color:blue;">' . 'family folder' . '</div></td>
                                                <td>' . number_format($TOTAL_DEBITS, 2) . '</td>
                                                <td>' . number_format($TOTAL_CREDITS, 2) . '</td>
                                                <td>' . number_format($current_balance, 2) . '</td>
                                                <td>' . $RV . '</td>
                                            </tr>';
                                            }
                                        }
                                    }
                                }

                                ?>


                                <table class="table table-striped table-bordered table-hover dataTables-example" style="font-size: 14px;">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Patient No</th>
                                            <th>Insurance Name</th>
                                            <th>Name</th>
                                            <th>DR</th>
                                            <th>CR</th>
                                            <th>Balance</th>
                                            <th>Account RECIEVABLE</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?= $tbl_body; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th></th>
                                            <th>Patient No</th>
                                            <th>Insurance Name</th>
                                            <th>Patient Name</th>
                                            <th>DR</th>
                                            <th>CR</th>
                                            <th><?= number_format($total_credit, 2); ?></th>
                                            <th><?= number_format($Total_recievabl_bal, 2); ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>


                </div>




                <?php include("../inc/footer.php"); ?>

            </div>
        </div>
        <div class="modal inmodal fade" id="edit_price_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title" id="">Edit Price</h4>
                    </div>
                    <div class="modal-body" id="edit_price_body">
                    </div>
                </div>
            </div>
        </div>




        <?php include("../inc/footer_scripts.php"); ?>

        <!-- Data Tables -->
        <script src="../js/jquery-3.1.1.min.js"></script>
        <script src="../js/bootstrap.min.js"></script>
        <script src="../js/plugins/metisMenu/jquery.metisMenu.js"></script>
        <script src="../js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

        <script src="../js/plugins/dataTables/datatables.min.js"></script>

        <!-- Custom and plugin javascript -->
        <script src="../js/inspinia.js"></script>
        <script src="../js/plugins/pace/pace.min.js"></script>

        <!-- Page-Level Scripts -->
        <script>
            $(document).on('click', '.edit_price_entry', function() {
                var editprice_id = $(this).attr("id");

                if (editprice_id != '') {
                    $.ajax({
                        url: "fetch_set_edit_patient_bal.php",
                        method: "POST",
                        // data:{edit_price_id:res[0]+'__'+res[1]+'__'+res[2]}, 
                        data: {
                            editprice_id: editprice_id
                        },

                        success: function(data) {

                            $('.modal-title').text('Edit Deposit');

                            $('#edit_price_body').html(data);
                            $('#edit_price_modal').modal('show');
                        }
                    });
                }
            });




            $(document).ready(function() {
                $('.dataTables-example').DataTable({
                    pageLength: 25,
                    responsive: true,
                    dom: '<"html5buttons"B>lTfgitp',
                    buttons: [{
                            extend: 'copy'
                        },
                        {
                            extend: 'csv'
                        },
                        {
                            extend: 'excel',
                            title: 'ExampleFile'
                        },
                        {
                            extend: 'pdf',
                            title: 'ExampleFile'
                        },

                        {
                            extend: 'print',
                            customize: function(win) {
                                $(win.document.body).addClass('white-bg');
                                $(win.document.body).css('font-size', '10px');

                                $(win.document.body).find('table')
                                    .addClass('compact')
                                    .css('font-size', 'inherit');
                            }
                        }
                    ]

                });

            });
        </script>


</body>

</html>