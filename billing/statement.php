<?php
session_start();
include("../Connections/Conn.php");
include("../inc/credit_current_balance.php");


if ($_GET['id']) {

    $id = $_GET['id'];
    $parts = explode("/", $id);
    $from = $parts['0'];
    $to = $parts['1'];
    $emr = $parts['2'];

    $general_credit_limit =    $_SESSION['credit_limit_status'];
    $items = call_current_balance($db, $emr, $general_credit_limit);
    $current_balance =  $items["current_balance"];
    $patient_name      = $items['patient_name'];
    $names             = $items['names'];
    $gender            = $items['gender'];
    $address           = $items['address'];
    $referral_name     = $items['referral_name'];
    $discount_set      = $items['discount_set'];
    $insurance_type    = $items['insurance_type'];
    $insurance_no      = $items['insurance_no'];
    $save_insurance_no = $items['save_insurance_no'];
    $wallet_amount     = $items['wallet_amount'];
}

function createRandomPassword()
{
    $chars = "003232303232023232023456789";
    srand((float)microtime() * 1000000);
    $i = 0;
    $pass = '';
    while ($i <= 7) {

        $num = rand() % 33;

        $tmp = substr($chars, $num, 1);

        $pass = $pass . $tmp;

        $i++;
    }
    return $pass;
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

        <?php include("../inc/nav_side_bill.php"); ?>


        <div id="page-wrapper" class="gray-bg">
            <?php include("../inc/nav_header.php"); ?>

            <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-lg-8">
                    <h2>Patient's Statement of Account</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index.php">Home</a>
                        </li>
                        <li class="active">
                            <strong>Statement</strong>
                        </li>
                    </ol>

                </div>
                <div class="col-lg-4">
                    <div class="title-action">


                        <a href="javascript:Clickheretoprint()" target="_blank" class="btn btn-primary btn-xs"><i class="fa fa-print"></i> Print Report </a>

                        <a href="pacct.php?emr=<?php echo $emr; ?>" class="btn btn-danger btn-xs"><i class="fa fa-times "></i> Close </a>
                    </div>
                </div>
            </div>
            <div class="row" id="content">
                <div class="col-lg-12">
                    <div class="wrapper wrapper-content animated fadeInRight">
                        <div class="ibox-content p-xl">
                            <div class="row">

                                <div class="col-sm-12" id="content">
                                    <!--<div class="panel-heading">
                <h4 class="panel-title">Simple Validation</h4>
        </div>-->
                                    <div align="left" style="font:bold 14px 'Arial';">
                                    </div>
                                    <table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
                                        <tr>
                                            <td width="50%" align="left"><img src="../img2/logo.png" width="196" height="111"></td>
                                            <td width="50%" align="right">
                                                <div style="font-size:18px; font:Verdana, Geneva, sans-serif"><strong><?php echo $_SESSION['h_name']; ?></strong></div> <br>
                                                <div style="font-size:14px"><?php echo $_SESSION['h_address']; ?><br><br> <?php echo $_SESSION['h_phone']; ?></div>
                                            </td>
                                        </tr>
                                    </table>
                                    <hr>

                                    <div align="center" style="font-size:20px; font:Verdana, Geneva, sans-serif;">

                                        Bill Statement For <?php echo $patient_name . ' ( Hospital #: ' . $emr . ' )'  . '<br>'; ?>
                                        <?php echo 'PERIOD: [' . date('d M, Y', strtotime($from)) . ' - ' . date('d M, Y', strtotime($to)) . ']'; ?>

                                    </div>

                                    <hr>


                                    <?php

                                    if ($transc_type == '') {

                                        $sql = "SELECT * FROM chart_ledger WHERE hospital_no = :hospital_no AND date_entry2 BETWEEN :from AND :to ORDER BY sn";
                                        $stmt = $db->prepare($sql);
                                        $stmt->bindValue(':hospital_no', $emr, PDO::PARAM_STR); // Assuming $emr is a string
                                        $stmt->bindValue(':from', $from, PDO::PARAM_STR); // Assuming $from is a string (date format)
                                        $stmt->bindValue(':to', $to, PDO::PARAM_STR); // Assuming $to is a string (date format)
                                    } else {
                                        $sql = "SELECT * FROM chart_ledger WHERE hospital_no = :hospital_no AND ref_value = :transc_type AND date_entry2 BETWEEN :from AND :to ORDER BY sn";
                                        $stmt = $db->prepare($sql);
                                        $stmt->bindValue(':hospital_no', $emr, PDO::PARAM_STR); // Assuming $emr is a string
                                        $stmt->bindValue(':transc_type', $transc_type, PDO::PARAM_STR); // Assuming $transc_type is a string
                                        $stmt->bindValue(':from', $from, PDO::PARAM_STR); // Assuming $from is a string (date format)
                                        $stmt->bindValue(':to', $to, PDO::PARAM_STR); // Assuming $to is a string (date format)
                                    }

                                    // Execute the query
                                    $stmt->execute();

                                    // Check if there are rows returned
                                    if ($stmt->rowCount() > 0) { ?>

                                        <table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
                                            <thead>
                                                <tr bgcolor="#CCCCCC">
                                                    <th width="20%">Date </th>
                                                    <th width="35%"> Description </th>
                                                    <th width="7%"> Type </th>
                                                    <th width="7%"> DR(N) </th>
                                                    <th width="7%"> CR(N) </th>
                                                    <th width="10%"> Balance </th>
                                                    <th width="5%"> Auth.Code </th>

                                                </tr>
                                            </thead>
                                            <tbody>


                                                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>

                                                    <tr class="record">
                                                        <td style="border-bottom: 1px solid #ddd;"><?php echo date('d M, Y', strtotime($row['date_entry'])); ?> </td>
                                                        <td style="border-bottom: 1px solid #ddd;"><?php echo $row['item_services']; ?></td>
                                                        <td style="border-bottom: 1px solid #ddd;"><?php echo $row['transc_type']; ?></td>
                                                        <td style="border-bottom: 1px solid #ddd;"><?php if ($row['dr_amt'] > 0) {
                                                                                                        echo number_format($row['dr_amt'], 2, '.', ',');
                                                                                                    } else {
                                                                                                        echo '';
                                                                                                    } ?></td>
                                                        <td style="border-bottom: 1px solid #ddd;"><?php if ($row['cr_amt'] > 0) {
                                                                                                        echo number_format($row['cr_amt'], 2, '.', ',');
                                                                                                    } else {
                                                                                                        echo '';
                                                                                                    } ?></td>
                                                        <td style="border-bottom: 1px solid #ddd;"><?php echo number_format($row['bal'], 2, '.', ','); ?></td>
                                                        <td style="border-bottom: 1px solid #ddd;"><?php echo $row['auth_code']; ?></td>

                                                    </tr>
                                                <?php
                                                }
                                                ?>

                                            </tbody>

                                            <?php


                                            if ($insurance_type == 'Family' or $insurance_type == 'Corporate') {
                                                $stmt = $db->prepare('SELECT bal FROM chart_ledger WHERE insurance_no = :insurance_no ORDER BY sn DESC LIMIT 1');
                                                $stmt->bindParam(':insurance_no', $insurance_no);
                                            } else {
                                                $stmt = $db->prepare('SELECT bal FROM chart_ledger WHERE hospital_no = :hospital_no ORDER BY sn DESC LIMIT 1');
                                                $stmt->bindParam(':hospital_no', $emr);
                                            }

                                            $stmt->execute();
                                            if ($stmt->rowCount() > 0) {
                                                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                                $current_bal = $row['bal'];
                                            }

                                            $stmt = $db->query("SELECT SUM(dr_amt) AS TotalPaid FROM chart_ledger WHERE hospital_no='$emr' and transc_type='Debit' and date_entry2 between '$from' AND '$to'");
                                            $row = $stmt->fetch(PDO::FETCH_ASSOC);

                                            $stmt = $db->query("SELECT SUM(cr_amt) AS TotalPaid2 FROM chart_ledger WHERE hospital_no='$emr' and transc_type='Credit' and date_entry2 between '$from' AND '$to'");
                                            $row2 = $stmt->fetch(PDO::FETCH_ASSOC);
                                            ?>

                                            <tr class="">
                                                <td style="border-bottom: 1px solid #ddd;"></td>
                                                <td style="border-bottom: 1px solid #ddd; padding:20px;">Totals:</td>
                                                <td style="border-bottom: 1px solid #ddd;"></td>
                                                <td style="border-bottom: 1px solid #ddd;"><strong><?php echo number_format($row['TotalPaid'], 2, '.', ','); ?></strong></td>
                                                <td style="border-bottom: 1px solid #ddd;"><strong><?php echo number_format($row2['TotalPaid2'], 2, '.', ','); ?></strong></td>
                                                <td style="border-bottom: 1px solid #ddd;"><strong><?php echo number_format($current_bal, 2, '.', ','); ?></strong></td>
                                                <td style="border-bottom: 1px solid #ddd;"></td>

                                            </tr>

                                            <tr class="">
                                                <td style="border-bottom: 1px solid #ddd;"></td>
                                                <td style="border-bottom: 1px solid #ddd; padding:10px;"><strong>Current Balance:</strong></td>
                                                <td style="border-bottom: 1px solid #ddd;"></td>
                                                <td style="border-bottom: 1px solid #ddd;"></td>
                                                <td style="border-bottom: 1px solid #ddd;"></td>
                                                <td style="border-bottom: 1px solid #ddd;"><strong>&#8358;<?php echo ' ' . number_format($current_bal, 2, '.', ','); ?></strong></td>
                                                <td style="border-bottom: 1px solid #ddd;"></td>

                                            </tr>
                                        </table>

                                        <br>
                                    <?php } ?>



                                </div>






                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>

        <?php include("../inc/footer_scripts.php"); ?>


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