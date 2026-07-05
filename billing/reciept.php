<?php
include("../Connections/Conn.php");
include("../inc/credit_current_balance.php");


if ($_GET['emr']) {
    $emr = $_GET['emr'];
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
                    <h2>Patient's Reciept</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index.php">Home</a>
                        </li>
                        <li class="active">
                            <strong>Receipt</strong>
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
                                <table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
                                    <tr>
                                        <td width="50%" align="left"><img src="../img/logo.png" width="196" height="111"></td>
                                        <td width="50%" align="right">
                                            <div style="font-size:18px; font:Verdana, Geneva, sans-serif""><strong><?php echo $_SESSION['h_name']; ?></strong></div> <br><div style=" font-size:14px"><?php echo $_SESSION['h_address']; ?><br><br> <?php echo $_SESSION['h_phone']; ?></div>
                                        </td>
                                    </tr>
                                </table>
                                <br>
                                <table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 14px;text-align:left;width : 100%;">
                                    <tr>
                                        <td width="50%" align="left">
                                            <div>
                                                <table width="100%">
                                                    <tr>
                                                        <td width="30%"><strong>Hospital #</strong></td>
                                                        <td><?php echo $emr; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Name</strong></td>
                                                        <td><?php echo $patient_name; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Gender</strong></td>
                                                        <td><?php echo $gender; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Address</strong></td>
                                                        <td><?php echo $address; ?></td>
                                                    </tr>

                                                </table>
                                            </div>
                                        </td>
                                        <td width="50%" align="right">
                                            <div>
                                                <table width="100%">
                                                    <?php if ($row_rstSelect_d['nhis_no'] != '') { ?>
                                                        <tr>
                                                            <td width="25%"><strong>Insurance #</strong></td>
                                                            <td><?php echo $row_rstSelect_d['nhis_no']; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <tr>
                                                        <td width="25%"><strong>Receipt #</strong></td>
                                                        <td><?php echo $finalcode = createRandomPassword(); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Date:</strong></td>
                                                        <td><?php echo date('d,M Y', strtotime(date("Y-m-d"))); ?></td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                </table>

                                <?php
                                $stmt = $db->query("SELECT sale_no FROM invsti_invoice WHERE emr='$emr' ORDER by sn");
                                if ($stmt->rowCount() > 0) {
                                ?>

                                    <div align="center" style="font:bold 14px 'Arial';"><?php echo 'SERVICE DETAILS'; ?></div><br>

                                    <table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
                                        <thead>
                                            <tr bgcolor="#CCCCCC">
                                                <th>#</th>
                                                <th>Date</th>
                                                <th> Description</th>
                                                <th> Hospital<br>Price </th>
                                                <th> Total<br>Quantity </th>
                                                <th>
                                                    <div align="right">Amount(=N=)</div>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                            <?php $t_amt = 0;
                                            $n = 1;
                                            while ($rowx = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                $sale_no = $rowx['sale_no'];

                                                $stmtx = $db->query("SELECT item_services,qty, pay,transact_date FROM patient_ap_services WHERE sn='$sale_no' and hospital_no='$emr'");
                                                //             $stmt->execute();
                                                $row = $stmtx->fetch(PDO::FETCH_ASSOC); ?>

                                                <tr class="record">
                                                    <td width="5%" style="border-bottom: 1px solid #ddd;"><?php echo $n; ?></td>
                                                    <td width="10%" style="border-bottom: 1px solid #ddd;"><?php echo date('d,M Y', strtotime($ddate = $row['transact_date'])); ?></td>
                                                    <td width="50%" style="border-bottom: 1px solid #ddd;"><?php echo $row['item_services']; ?></td>
                                                    <td width="10%" style="border-bottom: 1px solid #ddd;"><?php echo $row['pay']; ?></td>
                                                    <td width="10%" style="border-bottom: 1px solid #ddd;"><?php echo $row['qty']; ?></td>
                                                    <td width="10%" style="border-bottom: 1px solid #ddd;">
                                                        <div align="right"><strong><?php echo number_format($row['pay'], 2, '.', ','); ?></strong></div>
                                                    </td>
                                                    <?php
                                                    number_format($row['pay'], 2, '.', ',');
                                                    $t_amt = $t_amt + $row['pay'];
                                                    $n++;
                                                    ?>
                                                </tr>
                                            <?php }    ?>
                                        </tbody>
                                    </table>




                                    <table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
                                        <tr>
                                            <td width="50%" align="left">

                                                <div align="right"><strong>&nbsp;</strong></div>
                                                <div align="right" style="font-size:24px">&nbsp;</strong></div>
                                                <hr>
                                                <div align="right"><strong>Current Balance(Deposit):<br>&nbsp;</strong></div>
                                                <div align="right" style="font-size:24px"><strong>&#8358;<?php echo  number_format($current_balance, 2, '.', ','); ?></strong></div>
                                            </td>
                                            <td width="50%" align="right">

                                                <div align="right"><strong>Total Amount Paid: </strong></div>
                                                <div align="right" style="font-size:24px"><strong>&#8358;<?php echo  number_format($t_amt, 2, '.', ','); ?></strong></div>
                                                <?php if ($total_hmo_amt > 0) { ?>
                                                    <hr>
                                                    <div align="right"><strong>Total Insurance Billed to:<br> <?php echo $insurance_name; ?> </strong></div>
                                                    <div align="right" style="font-size:24px"><strong>&#8358;<?php echo  number_format($total_hmo_amt, 2, '.', ','); ?></strong></div>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    </table>

                                <?php
                                }
                                ?>




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