<?php include("../Connections/Conn.php"); ?>

<?php
session_start();
include('../inc/header.php');

//get income and expense classes
$classes = $db->query("SELECT * FROM chart_class WHERE cid = 4 OR cid = 5");

$classes = $classes->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['start']) && isset($_GET['end'])) {
    $the_stetment = 1;
}
include('inc/functions.php');
redirect_to_active_year();
?>

<body class="fixed-navigation">
    <style>
        @media print {
            a[href]:after {
                content: none !important;
            }
        }
    </style>
    <div id="wrapper">
        <?php include("nav_side.php"); ?>

        <div id="page-wrapper" class="gray-bg sidebar-content">

            <?php include '../../inc/nav_header.php'; ?>
            <div class="wrapper wrapper-content">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">

                                <h5>Accounts Dashboard</h5>


                            </div>
                            <div class="ibox-content">

                                <form action="" method="get" class="form-inline">
                                    <div class="form_sep" align="">
                                        <label for="">Start Date</label>
                                        <input type="date" name="start" class="form-control" value="<?php echo isset($_GET['start']) ? $_GET['start'] : ''; ?>" required>

                                        &nbsp;

                                        <label for="">End Date</label>
                                        <input type="date" name="end" class="form-control" value="<?php echo isset($_GET['end']) ? $_GET['end'] : ''; ?>" required>
                                        &nbsp;

                                        <button type="submit" class="btn btn-primary">Display </button>
                                    </div>
                                    <br>

                                </form>


                                <?php if (isset($the_stetment)) : ?>
                                    <div class="table-responsive" id="chart_groups">

                                        <h1>Income Statment</h1>
                                        <h3>For the period starting <?php echo date('d M Y', strtotime($_GET['start'])); ?> and ending
                                            <?php echo date('d M Y', strtotime($_GET['end'])); //echo $_GET['end']; 
                                            ?></h3>
                                        <br>


                                        <table class="table" style="font-size: 17px; ">

                                            <tbody>
                                                <?php $netTotal = 0; ?>
                                                <tr>

                                                    <?php
                                                    $groups = $db->prepare("SELECT * FROM chart_groups WHERE class_id = ?");
                                                    $groups->execute([4]);
                                                    $groups = $groups->fetchAll(PDO::FETCH_ASSOC);
                                                    ?>
                                                    <?php
                                                    foreach ($groups as $group) :
                                                        $gTotal = getGroupTotal($group['id'], $_GET['start'], $_GET['end'])['bal'];
                                                    ?>

                                                        <?php if ($gTotal > 0 or $gTotal < 0) { ?>
                                                <tr>
                                                    <td>&nbsp;</td>

                                                    <td>
                                                        <strong>
                                                            <i>
                                                                <a href="ledger_entries.php?level=group&id=<?php echo $group['id']; ?>&start=<?php echo $_GET['start']; ?>&end=<?php echo $_GET['end']; ?>" target="_blank"><?= $group['name'] ?>
                                                                </a>
                                                            </i>
                                                        </strong>
                                                    </td>
                                                    <td>&nbsp;</td>
                                                    <td>
                                                        <?php echo number_format(abs($gTotal), 2); ?>

                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        <?php endforeach ?>

                                        </tr>
                                        <tr>
                                            <th></th>
                                            <th colspan="2">
                                                <h3>&nbsp;&nbsp;TOTAL INCOME</h3>
                                                <?php $cTotal = getClassTotal(4, $_GET['start'], $_GET['end'])['bal']; ?>
                                            </th>
                                            <th><i><strong><?= number_format(makePositive($cTotal, ''), 2); ?></strong></i></th>
                                        </tr>

                                        <!------------ EXPENSES --------------->




                                        <tr>
                                            <td colspan="2">

                                                <br>
                                                <h2><i><strong>EXPENSES</strong></i></h2>
                                            </td>
                                            <td colspan="2"></td>
                                        </tr>
                                        <tr>

                                            <?php
                                            $groups = $db->prepare("SELECT * FROM chart_groups WHERE class_id = ?");
                                            $groups->execute([5]);
                                            $groups = $groups->fetchAll(PDO::FETCH_ASSOC);
                                            ?>
                                            <?php
                                            foreach ($groups as $group) :
                                                $gTotal = getGroupTotal($group['id'], $_GET['start'], $_GET['end'])['bal'];
                                            ?>

                                                <?php if ($gTotal > 0 or $gTotal < 0) { ?>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <td>
                                                <strong>
                                                    <i>
                                                        <a href="ledger_entries.php?level=group&id=<?php echo $group['id']; ?>&start=<?php echo $_GET['start']; ?>&end=<?php echo $_GET['end']; ?>" target="_blank"><?= $group['name'] ?>
                                                        </a>
                                                    </i>
                                                </strong>
                                            </td>
                                            <td><?php echo number_format(abs($gTotal), 2); ?></td>
                                            <td>&nbsp;</td>
                                        </tr>
                                    <?php } ?>
                                <?php endforeach ?>

                                </tr>
                                <tr>
                                    <th colspan="2">TOTAL EXPENSES:
                                        <?php $cTotal = getClassTotal(5, $_GET['start'], $_GET['end'])['bal']; ?>
                                    </th>
                                    <th><strong><?= number_format(makePositive($cTotal, ''), 2); ?></strong></th>
                                    <th>&nbsp;</th>
                                </tr>

                                <tr>
                                    <td colspan="2">

                                        <br>
                                        <h2><i><strong>NET PROFIT :


                                                    <span style="text-decoration: underline double; border-top: 1px solid #000; display: inline-block;  padding: 5px;">

                                                        <?= format_accounting(makePositive(getClassTotal('4', $_GET['start'], $_GET['end'])['bal']) -
                                                            makePositive(getClassTotal('5', $_GET['start'], $_GET['end'])['bal']), '') ?></span>

                                                </strong></i></h2>
                                    </td>
                                    <td colspan="2">




                                    </td>
                                </tr>


                                            </tbody>
                                        </table>
                                    </div>


                                    <button class="btn btn-success" onclick="printDiv('chart_groups')"><i class="fa fa-print">&nbsp; Print Report</i></button>
                                <?php endif ?>
                            </div>
                        </div>
                    </div>
                </div>


                <?php include '../../inc/footer.php'; ?>

            </div>
        </div>


        <?php include('../modal_lock.php'); ?>
        <?php include '/inc/footer_scripts.php'; ?>


        <script>
            <?php
            if ($error_status == 1) { ?>toastr.error('<?php echo $error_msg; ?>', 'Error', {
                timeOut: 5000
            })
            <?php } elseif ($error_status == 2) { ?>toastr.success(' <?php echo $error_msg; ?> ', 'Success', {
                timeOut: 5000
            })
            <?php } ?>
        </script>

        <script src="../../js/plugins/dataTables/jquery.dataTables.js"></script>
        <script src="../../js/plugins/dataTables/dataTables.bootstrap.js"></script>
        <script src="../../js/plugins/dataTables/dataTables.responsive.js"></script>
        <script src="../../js/plugins/dataTables/dataTables.tableTools.min.js"></script>
        <script>
            function printDiv(divId) {
                var content = document.getElementById(divId).innerHTML;
                var popupWindow = window.open('', '_blank', 'width=600,height=600');
                popupWindow.document.open();
                popupWindow.document.write('<html><head><title>' + document.title + '</title>');
                // Reference the external stylesheet from the main page
                popupWindow.document.write('<link rel="stylesheet" type="text/css" href="../../css/bootstrap.min.css">');
                //prevent links from showing thier href in print view
                popupWindow.document.write(`
                    <style>
                        @media print {
                            a[href]:after {
                                content: none !important;
                            }
                        }
                    </style>
                `);
                popupWindow.document.write('</head><body>');
                popupWindow.document.write(content);
                popupWindow.document.write('</body></html>');
                popupWindow.document.close();
                popupWindow.print();
            }
        </script>

        <script src="../js/idle.js"></script>
</body>

</html>