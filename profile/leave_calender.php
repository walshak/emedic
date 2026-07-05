<?php
include("../inc/session.php");
include("../Connections/Conn.php");

// Get filter dates from form
$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : date('Y-m-01');
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : date('Y-m-t');

// Fetch leave applications
try {
    $sql = "SELECT * FROM hrlvapply WHERE 
            (starting_date BETWEEN :start_date1 AND :end_date1) OR 
            (ending BETWEEN :start_date2 AND :end_date2) OR
            (starting_date <= :start_date3 AND ending >= :end_date3)";

    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':start_date1' => $start_date,
        ':end_date1' => $end_date,
        ':start_date2' => $start_date,
        ':end_date2' => $end_date,
        ':start_date3' => $start_date,
        ':end_date3' => $end_date
    ]);

    $leaves = $stmt->fetchAll();

    $stmt = $db->query("SELECT * FROM hospital_details LIMIT 1");
    $hospital_details = $stmt->fetch();
} catch (PDOException $e) {
    die("Query failed: " . $e->getMessage());
}

function generateCalendar($start_date, $end_date, $leaves)
{
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $interval = DateInterval::createFromDateString('1 day');
    $period = new DatePeriod($start, $interval, $end->modify('+1 day'));

    $calendar = [];
    foreach ($period as $dt) {
        $calendar[$dt->format("Y-m")][$dt->format("Y-m-d")] = [];
    }

    // Add leaves to calendar
    foreach ($leaves as $leave) {
        $leave_start = new DateTime($leave['starting_date']);
        $leave_end = new DateTime($leave['ending']);
        $leave_period = new DatePeriod($leave_start, $interval, $leave_end->modify('+1 day'));

        foreach ($leave_period as $dt) {
            $date = $dt->format("Y-m-d");
            $month = $dt->format("Y-m");
            if (isset($calendar[$month][$date])) {
                $calendar[$month][$date][] = $leave;
            }
        }
    }

    return $calendar;
}

$calendar = generateCalendar($start_date, $end_date, $leaves);

// Helper function to safely output HTML
function h($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

?>


<!DOCTYPE html>
<html>

<head>

    <?php include("../inc/header.php"); ?>
    <style>
        /* Regular view styles */
        .calendar-day {
            min-height: 100px;
            border: 1px solid #ddd;
            padding: 5px;
            margin-bottom: 15px;
        }

        .leave-entry {
            margin: 2px 0;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 12px;
            cursor: pointer;
        }

        .status-Approve {
            background-color: rgb(183, 234, 162);
            border-left: 3px rgb(49, 134, 50);
        }

        .status-pending {
            background-color: rgb(158, 115, 236);
            border-left: 3px rgb(62, 21, 149);
        }

        .status-reject {
            background-color: rgb(242, 129, 129);
            border-left: 3px rgb(167, 47, 45);
        }

        .calendar-header {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: center;
            padding: 10px;
            margin-bottom: 15px;
        }

        .date-label {
            font-weight: bold;
            margin-bottom: 5px;
            border-bottom: 1px solid #eee;
            padding-bottom: 3px;
        }

        .legend {
            margin: 20px 0;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 4px;
        }

        .legend-item {
            display: inline-block;
            margin-right: 20px;
        }

        .legend-color {
            display: inline-block;
            width: 20px;
            height: 20px;
            margin-right: 5px;
            vertical-align: middle;
            border-radius: 3px;
        }

        /* Print styles */
        @media print {


            /* Only show print-container and its contents */
            #print-container,
            #print-container * {
                display: block !important;
            }

            /* Hide non-printable elements */
            #wrapper>*:not(#page-wrapper),
            .ibox-title,
            .form-inline,
            .legend,
            nav,
            #nav_side,
            #nav_header,
            .footer {
                display: none !important;
            }

            .container {
                width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            /* Reset calendar day styles for print */
            .calendar-day {
                min-height: 0 !important;
                height: auto !important;
                page-break-inside: avoid !important;
                border: 1px solid #000 !important;
                padding: 2px !important;
                margin-bottom: 0 !important;
                font-size: 10px !important;
            }

            .leave-entry {
                margin: 1px 0 !important;
                padding: 1px 2px !important;
                font-size: 9px !important;
                line-height: 1.2 !important;
            }

            .date-label {
                margin-bottom: 2px !important;
                padding-bottom: 1px !important;
                font-size: 10px !important;
            }

            .row {
                display: flex !important;
                flex-wrap: wrap !important;
            }

            .col-md-2 {
                float: left !important;
                width: 16.666% !important;
                flex: 0 0 16.666% !important;
            }

            .calendar-header {
                padding: 5px !important;
                margin-bottom: 5px !important;
                font-size: 12px !important;
            }

            /* Force background colors in print */
            .status-Approve {
                background-color: rgb(120, 201, 88) !important;
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            .status-pending {
                background-color: rgb(118, 74, 205) !important;
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            .status-reject {
                background-color: rgb(211, 95, 95) !important;
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
        }

        /* Hide print container in normal view */
        #print-container {
            display: none;
        }
    </style>

<body>

    <div id="wrapper">

        <?php include("nav_side.php"); ?>


        <div id="page-wrapper" class="gray-bg">
            <?php include("nav_header.php"); ?>

            <div class="wrapper wrapper-content">

                <div class="row">

                    <div class="col-lg-12">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">
                                <h5>Leave Claender </h5>
                            </div>

                            <div class="ibox-content">
                                <!-- Filter Form -->
                                <form method="POST" class="form-inline mb-4">
                                    <div class="form-group">
                                        <label for="start_date">Start Date:</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date"
                                            value="<?php echo h($start_date); ?>">
                                    </div>
                                    <div class="form-group" style="margin: 0 15px;">
                                        <label for="end_date">End Date:</label>
                                        <input type="date" class="form-control" id="end_date" name="end_date"
                                            value="<?php echo h($end_date); ?>">
                                    </div>
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                    <button type="button" class="btn btn-success" onclick="printReport();">
                                        <span class="glyphicon glyphicon-print"></span> Print Calendar
                                    </button>
                                </form>

                                <!-- Legend -->
                                <div class="legend">
                                    <div class="legend-item">
                                        <div class="legend-color status-Approve"></div>
                                        Approved
                                    </div>
                                    <div class="legend-item">
                                        <div class="legend-color status-pending"></div>
                                        Pending
                                    </div>
                                    <div class="legend-item">
                                        <div class="legend-color status-reject"></div>
                                        Rejected
                                    </div>
                                </div>

                                <!-- Calendar View -->
                                <?php foreach ($calendar as $yearMonth => $days): ?>
                                    <div class="panel panel-default">
                                        <div class="calendar-header">
                                            <?php echo date('F Y', strtotime($yearMonth)); ?>
                                        </div>
                                        <div class="panel-body">
                                            <div class="row">
                                                <?php foreach ($days as $date => $leaves): ?>
                                                    <div class="col-md-2 calendar-day">
                                                        <div class="date-label">
                                                            <?php echo date('d', strtotime($date)); ?>
                                                            <small><?php echo date('D', strtotime($date)); ?></small>
                                                        </div>
                                                        <?php foreach ($leaves as $leave): ?>
                                                            <div class="leave-entry status-<?php echo h($leave['status']); ?>"
                                                                data-toggle="tooltip"
                                                                data-placement="top"
                                                                title="<?php echo h($leave['reason']); ?>">
                                                                <strong><?php echo h($leave['Name']); ?></strong><br>
                                                                <small><?php echo h($leave['type_leave']); ?></small>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                                <!-- Print Container -->
                                <div id="print-container">
                                    <!-- Hospital Header -->
                                    <table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px; text-align:left; width:100%;">
                                        <tr>
                                            <td width="50%" align="left">
                                                <img src="../img/logo.png" width="196" height="111">
                                            </td>
                                            <td width="50%" align="right">
                                                <div style="font-size:18px; font:Verdana, Geneva, sans-serif">
                                                    <strong><?php echo h($hospital_details['name']); ?></strong>
                                                </div>
                                                <br>
                                                <div style="font-size:14px">
                                                    <?php echo h($hospital_details['address']); ?><br><br>
                                                    <?php echo h($hospital_details['phones']); ?>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                    <br>
                                    <h2 style="text-align: center;">Leave Calendar Report</h2>
                                    <p style="text-align: center;">
                                        Period: <?php echo date('d M Y', strtotime($start_date)); ?> -
                                        <?php echo date('d M Y', strtotime($end_date)); ?>
                                    </p>

                                    <!-- Calendar View -->
                                    <?php foreach ($calendar as $yearMonth => $days): ?>
                                        <div class="panel panel-default">
                                            <div class="calendar-header">
                                                <?php echo date('F Y', strtotime($yearMonth)); ?>
                                            </div>
                                            <div class="panel-body">
                                                <div class="row">
                                                    <?php foreach ($days as $date => $leaves): ?>
                                                        <div class="col-md-2 calendar-day">
                                                            <div class="date-label">
                                                                <?php echo date('d', strtotime($date)); ?>
                                                                <small><?php echo date('D', strtotime($date)); ?></small>
                                                            </div>
                                                            <?php foreach ($leaves as $leave): ?>
                                                                <div class="leave-entry status-<?php echo h($leave['status']); ?>"
                                                                    data-toggle="tooltip"
                                                                    data-placement="top"
                                                                    title="<?php echo h($leave['reason']); ?>">
                                                                    <strong><?php echo h($leave['Name']); ?></strong><br>
                                                                    <small><?php echo h($leave['type_leave']); ?></small>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                    <!-- Print footer -->
                                    <p style="text-align: right; margin-top: 30px;">
                                        Generated on: <?php echo date('d M Y H:i:s'); ?>
                                    </p>
                                </div>
                            </div>




                        </div>
                    </div>

                </div>



            </div>
            <?php include("../inc/footer.php"); ?>

        </div>
    </div>

    <?php include('../modal_lock.php'); ?>
    <?php include("../inc/footer_scripts.php"); ?>

    <!-- Mainly scripts -->
    <script src="../js/jquery-2.1.1.js"></script>

    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="../js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

    <!-- Custom and plugin javascript -->
    <script src="../js/inspinia.js"></script>
    <script src="../js/plugins/pace/pace.min.js"></script>

    <!-- iCheck -->
    <script src="../js/plugins/iCheck/icheck.min.js"></script>
    <script src="../js/idle.js"></script>
    <script>
        function printReport() {
            // Create the content for the new window
            let printContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Leave Calendar Report</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    .calendar-header { 
                        background-color: #f5f5f5; 
                        font-weight: bold; 
                        text-align: center; 
                        padding: 10px; 
                        margin-bottom: 15px; 
                    }
                    .calendar-day {
                        width: 16.666%;
                        float: left;
                        min-height: 100px;
                        border: 1px solid #ddd;
                        padding: 5px;
                        margin-bottom: 15px;
                        box-sizing: border-box;
                    }
                    .date-label {
                        font-weight: bold;
                        margin-bottom: 5px;
                        border-bottom: 1px solid #eee;
                        padding-bottom: 3px;
                    }
                    .leave-entry {
                        margin: 2px 0;
                        padding: 2px 5px;
                        border-radius: 3px;
                        font-size: 12px;
                    }
                    .status-Approve {
                        background-color: #dff0d8;
                        border-left: 3px solid #3c763d;
                    }
                    .status-pending {
                        background-color: #fcf8e3;
                        border-left: 3px solid #8a6d3b;
                    }
                    .status-reject {
                        background-color: #f2dede;
                        border-left: 3px solid #a94442;
                    }
                    .row::after {
                        content: "";
                        clear: both;
                        display: table;
                    }
                    .panel { margin-bottom: 20px; }
                    @media print {
                        .no-print { display: none; }
                    }
                </style>
            </head>
            <body>`;

            // Add hospital header
            const hospitalLogo = document.querySelector('img[src="../img/logo.png"]');
            const hospitalDetails = document.querySelector('.ibox-content table[cellpadding="5"]');

            // Create hospital header HTML
            printContent += `
            <table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px; text-align:left; width:100%;">
                <tr>
                    <td width="50%" align="left">
                        <img src="${hospitalLogo.src}" width="196" height="111">
                    </td>
                    <td width="50%" align="right">
                        ${document.querySelector('.ibox-content table[cellpadding="5"] td[align="right"]').innerHTML}
                    </td>
                </tr>
            </table>`;

            // Add report title and date range
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            printContent += `
            <h2 style="text-align: center;">Leave Calendar Report</h2>
            <p style="text-align: center;">
                Period: ${new Date(startDate).toLocaleDateString()} - ${new Date(endDate).toLocaleDateString()}
            </p>`;

            // Add calendar content - only from the main view, not the print container
            const calendarPanels = document.querySelectorAll('.ibox-content > .panel.panel-default');
            calendarPanels.forEach(panel => {
                printContent += panel.outerHTML;
            });

            // Add footer
            printContent += `
            <p style="text-align: right; margin-top: 30px;">
                Generated on: ${new Date().toLocaleString()}
            </p>
            <div class="no-print" style="text-align: center; margin-top: 20px;">
                <button onclick="window.print()">Print Report</button>
                <button onclick="window.close()">Close Window</button>
            </div>
            </body>
            </html>`;

            // Open new window and write content
            const printWindow = window.open('', 'Print Calendar',
                'width=1000,height=600,toolbar=0,scrollbars=1,status=0');

            printWindow.document.write(printContent);
            printWindow.document.close();

            // Wait for images to load before triggering print
            printWindow.onload = function() {
                // Add event listener for after print
                printWindow.onafterprint = function() {
                    // Optional: close the window after printing
                    // printWindow.close();
                };

                // Trigger print
                printWindow.focus(); // Required for IE
                printWindow.print();
            };
        }

        // Add event listener for printing errors
        window.addEventListener('error', function(e) {
            if (e.target.tagName === 'IMG') {
                // Handle image loading errors
                e.target.src = '../img/placeholder-logo.png'; // Provide a fallback image
            }
        }, true);
    </script>

</body>

</html>