<?php
session_start();
include("../Connections/Conn.php");

//fetch clinical forms. 

?>

<!DOCTYPE html>
<html>
<?php

include("../inc/header.php");
?>


<title>WebMedic | <?php if ($_SESSION['Designation'] != "") {
                        echo $_SESSION['Designation'];
                    } else {
                        echo $_SESSION['speciality'];
                    } ?></title>


<body>
    <div id="wrapper">
        <?php include("nav_side.php"); ?>
        <div id="page-wrapper" class="gray-bg">
            <?php include("nav_header.php"); ?>

            <div class="wrapper wrapper-content">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>Clinical forms Report</h5>&nbsp; <a href="index.php?rpt">Go back</a>
                    </div>

                    <div class="ibox-content">
                        <p>
                            <b>For the period [<?php echo $_GET['from_date'] ?> to <?php echo $_GET['to_date'] ?>]</b>
                        <p>Patient: <?php echo ($_GET['hosp_no']) ? $_GET['hosp_no'] : 'N/A' ?></p>
                        </p>
                        <?php if (isset($_GET['from_date']) && isset($_GET['to_date'])): ?>
                            <?php
                            // Validate and sanitize input
                            $hos_no = isset($_GET['hosp_no']) && !empty($_GET['hosp_no']) ? htmlspecialchars($_GET['hosp_no']) : null;
                            $from_date = htmlspecialchars($_GET['from_date']);
                            $to_date = htmlspecialchars($_GET['to_date']);

                            try {
                                if ($hos_no) {
                                    $stmt = $db->prepare("
                                SELECT 
                                    ns.id, 
                                    ns.hospital_no, 
                                    CONCAT(p.surname, ', ', p.fname, ' ', p.oname) as patient_name, 
                                    ns.app_no, 
                                    ns.service, 
                                    ns.created_at, 
                                    ns.created_by
                                FROM 
                                    notes_services ns
                                LEFT JOIN 
                                    enrollee p ON ns.hospital_no = p.hospital_no
                                WHERE 
                                    ns.hospital_no = :hospital_no
                                    AND ns.service IN ('Patient_discharge_councelling_form', 'MEDICATION_INTERVENTION_FORM', 'MEDICATION_RECONCILE_FORM')
                                    AND DATE(ns.created_at) BETWEEN :from_date AND :to_date
                                
                            ");
                                    $stmt->bindParam(':hospital_no', $hos_no, PDO::PARAM_STR);
                                } else {
                                    $stmt = $db->prepare("
                                SELECT 
                                    ns.id, 
                                    ns.hospital_no, 
                                    CONCAT(p.surname, ', ', p.fname, ' ', p.oname) as patient_name, 
                                    ns.app_no, 
                                    ns.service, 
                                    ns.created_at, 
                                    ns.created_by
                                FROM 
                                    notes_services ns
                                LEFT JOIN 
                                    enrollee p ON ns.hospital_no = p.hospital_no
                                WHERE 
                                    ns.service IN ('Patient_discharge_councelling_form', 'MEDICATION_INTERVENTION_FORM', 'MEDICATION_RECONCILE_FORM')
                                    AND DATE(ns.created_at) BETWEEN :from_date AND :to_date
                            ");
                                }
                                $stmt->bindParam(':from_date', $from_date, PDO::PARAM_STR);
                                $stmt->bindParam(':to_date', $to_date, PDO::PARAM_STR);
                                $stmt->execute();

                                // Fetch results
                                $clinical_forms = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            } catch (PDOException $e) {
                                $error_message = 'Error: ' . $e->getMessage();
                                error_log($error_message);
                                echo "<p class='alert alert-danger'>Error while fetching clinical forms history. Please try again later.</p>";
                            }

                            ?>

                            <?php if (!empty($clinical_forms)): ?>
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Hospital No</th>
                                            <th>Patient Name</th>
                                            <th>Appointment No</th>
                                            <th>Service</th>
                                            <th>Created At</th>
                                            <!-- <th>Created By</th> -->
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($clinical_forms as $form): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($form['hospital_no']); ?></td>
                                                <td><?= htmlspecialchars($form['patient_name']); ?></td>
                                                <td><?= htmlspecialchars($form['app_no']); ?></td>
                                                <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $form['service']))); ?></td>
                                                <td><?= htmlspecialchars($form['created_at']); ?></td>
                                                <!-- <td><?= htmlspecialchars($form['created_by']); ?></td> -->
                                                <td>
                                                    <button class="btn btn-primary btn-xs print-btn" data-id="<?= htmlspecialchars($form['id']); ?>" onclick="print_clinical_form(this)">Print</button>
                                                    <!-- <button class="delete-btn btn btn-danger btn-xs" data-id="<?= htmlspecialchars($form['id']); ?>" onclick="delete_clinical_form(this)">Delete</button> -->
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p class="alert alert-warning">No forms found during the selected date range.</p>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="alert alert-info">Please provide a valid date range to view clinical forms history.</p>
                        <?php endif; ?>

                        <?php include('../modal_lock.php'); ?>
                    </div>
                </div>


                <?php include("../inc/footer.php"); ?>
            </div>
        </div>
        <script src="../js/jquery-ui.min.js"></script>
        <?php include("../inc/footer_scripts.php"); ?>

        <?php if (isset($_GET['sv'])) { ?>
            <script>
                toastr.success('Saved Successfully!', 'Success', {
                    timeOut: 5000
                })
            </script>
        <?php } ?>

        <!-- Data Tables -->
        <script src="../js/plugins/dataTables/jquery.dataTables.js"></script>
        <script src="../js/plugins/dataTables/dataTables.bootstrap.js"></script>
        <script src="../js/plugins/dataTables/dataTables.responsive.js"></script>
        <script src="../js/plugins/dataTables/dataTables.tableTools.min.js"></script>

        <script src="../js/vendors/editor/dist/trumbowyg.js"></script>
        <script src="../js/vendors/editor/plugins/fontsize/trumbowyg.fontsize.js"></script>
        <script src="../js/vendors/editor/plugins/colors/trumbowyg.colors.js"></script>


        <script>
            $('.dataTables-example').dataTable({
                responsive: true,
                "dom": 'T<"clear">lfrtip',
                "tableTools": {
                    "sSwfPath": "../js/plugins/dataTables/swf/copy_csv_xls_pdf.swf"
                }
            });
        </script>

        <script>
            // function delete_clinical_form(obj) {

            //     // Get the ID from the data-id attribute
            //     var id = $(obj).data('id');
            //     var row = $(obj).closest('tr');

            //     if (confirm('Are you sure you want to delete this record?')) {
            //         // Make the AJAX request
            //         $.ajax({
            //             url: 'fetch_set.php',
            //             type: 'POST',
            //             data: {
            //                 clinical_form_del_id: id
            //             },
            //             success: function(response) {
            //                 row.remove();
            //                 toastr.success("Form deleted successfully", 'Attention', {
            //                     timeOut: 5000
            //                 })
            //             },
            //             error: function(jqXHR, textStatus, errorThrown) {
            //                 console.error('Error:', textStatus, errorThrown);
            //                 toastr.success("Error While deleting Form", 'Attention', {
            //                     timeOut: 5000
            //                 })
            //             }
            //         });
            //     }

            // }

            function print_clinical_form(button) {
                // Get the ID from the data-id attribute
                var id = $(button).data('id');

                // Make the AJAX request to fetch the clinical form notes
                $.ajax({
                    url: 'fetch_set.php',
                    type: 'POST',
                    data: {
                        clinical_form_print_id: id
                    },
                    success: function(response) {
                        //response contains the HTML of the clinical form
                        if (response) {
                            // Create a new window for printing
                            var printWindow = window.open('', '_blank');

                            // Write the content to the new window
                            printWindow.document.write(`
                                <html>
                                <head>
                                    <title>Print Clinical Form</title>
                                    <style>
                                        table{
                                            border-collapse: collapse;
                                        }
                                    </style>
                                </head>
                                <body>
                                    ${response}
                                </body>
                                </html>
                            `);

                            // Close the document to ensure all content is loaded
                            printWindow.document.close();

                            // Print the content after a slight delay to ensure everything is rendered
                            setTimeout(function() {
                                printWindow.print();
                                printWindow.close();
                            }, 500);
                        } else {
                            alert('No data found for this clinical form.');
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error('Error:', textStatus, errorThrown);
                        toastr.error("Error while printing", 'Attention', {
                            timeOut: 5000
                        })
                    }
                });
            }
        </script>

        <script src="../js/idle.js"></script>
</body>

</html>