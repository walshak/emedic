<?php include("../Connections/Conn.php"); ?>

<?php
session_start();

$msg = null;


if (isset($_GET["delete"])) {
    $test_id = $_GET["delete"];

    $stmt = $db->query("SELECT sn FROM lab_manage where test_id='$test_id'");
    if ($stmt->rowCount() == 0) {

        $stmt = $db->prepare("DELETE FROM lab_scan WHERE  sn = ? ");
        $delete = $stmt->execute(array($test_id));
        if ($delete) {
            $error_msg = "Deleted";
        } else {
            $error_msg = "Unable to Delete";
        }
    } else {
        $error_msg = "Unable to Delete! Investigation already Attached to Patient Records";
    }
}

if (isset($_POST["Add_Test_Body"])) {


    try {
        // Get input values
        $string = $_POST["labname"];
        $sub_category = $_POST["sub_category"];
        $doctor_can_upload = $_POST["doctor_can_upload"];

        // Clean the input string
        $charactersToRemove = '*/=%",;:\'.'; // Add '.' to the list
        $pattern = '/[' . preg_quote($charactersToRemove, '/') . ']/';
        $cleanedString = preg_replace($pattern, '', $string);

        // Prepare and execute the SELECT statement
        $stmt = $db->prepare("SELECT * FROM lab_scan WHERE test = :test");
        $stmt->bindParam(':test', $cleanedString, PDO::PARAM_STR);
        $stmt->execute();

        // Check if any rows were returned
        if ($stmt->rowCount() == 0) {
            $part = explode("__", $_POST['Department']);
            $dept_id = $part[0];
            $dept_type = $part[1];
            $LC = isset($_POST["labcombos"]) ? 1 : 0;

            // Prepare and execute the INSERT statement
            $insertSQL = $db->prepare("INSERT INTO lab_scan (test, category, sub_category, dept, combo_test,doctor_result_status) 
                                       VALUES (:test, :category, :sub_category, :dept, :combo_test, :doctor_result_status)");

            $insertSQL->bindParam(':test', $cleanedString, PDO::PARAM_STR);
            $insertSQL->bindParam(':category', $dept_type, PDO::PARAM_STR);
            $insertSQL->bindParam(':sub_category', $sub_category, PDO::PARAM_STR);
            $insertSQL->bindParam(':dept', $dept_id, PDO::PARAM_STR); // Adjust the data type if dept_id is not a string
            $insertSQL->bindParam(':combo_test', $LC, PDO::PARAM_INT); // Use PDO::PARAM_INT for integer values
            $insertSQL->bindParam(':doctor_result_status', $doctor_can_upload, PDO::PARAM_INT); // Use PDO::PARAM_INT for integer values
            $insertSQL->execute();

            $msg = "Record inserted successfully.";
        } else {
            $msg = "Record already exists.";
        }
    } catch (PDOException $e) {
        // Handle any errors that occur during the database operations
        $msg = "Database error: " . $e->getMessage();
    } catch (Exception $e) {
        // Handle any other errors
        $msg = "An error occurred: " . $e->getMessage();
    }

    // Display the message (you can also log it or handle it differently)
    ///echo $msg;
}

if (isset($_POST["save_template"])) {
    /// lab_scan_fields


    $type_no = $_POST['type_no'];
    $stmt = $db->prepare("SELECT sn FROM invsti_template WHERE sn = :type_no");
    $stmt->bindParam(':type_no', $type_no, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $updateStmt = $db->prepare("UPDATE invsti_template SET template_2 = :template_note WHERE sn = :type_no");
        $updateStmt->bindParam(':template_note', $_POST['template_note'], PDO::PARAM_STR);
        $updateStmt->bindParam(':type_no', $_POST['type_no'], PDO::PARAM_STR);
        $updateStmt->execute();
    } else {
        $insertStmt = $db->prepare("INSERT INTO invsti_template (sn, type, template_2, title) VALUES (:type_no, :type, :template_note, :template_title)");
        $insertStmt->bindParam(':type_no', $_POST['type_no'], PDO::PARAM_STR);
        $insertStmt->bindParam(':type', $_POST['type'], PDO::PARAM_STR);
        $insertStmt->bindParam(':template_note', $_POST['template_note'], PDO::PARAM_STR);
        $insertStmt->bindParam(':template_title', $_POST['template_title'], PDO::PARAM_STR);
        $insertStmt->execute();
    }


    ///------------------

    $stmt = $db->prepare("SELECT test_no FROM lab_scan_fields WHERE test_no = :type_no");
    $stmt->bindParam(':type_no', $type_no, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        // If no record exists, insert a new record
        $insertStmt = $db->prepare("INSERT INTO lab_scan_fields (test_no, field, field_type) VALUES (:type_no, :field, :field_type)");
        $insertStmt->bindParam(':type_no', $_POST['type_no'], PDO::PARAM_STR);
        $insertStmt->bindParam(':field', 'report', PDO::PARAM_STR); // Assuming 'report' is the field value to be inserted
        $insertStmt->bindParam(':field_type', 'report', PDO::PARAM_STR); // Assuming 'report' is also the field_type value
        $insertStmt->execute();
    }

    $save = 1; // Assuming $save is set to 1 for some further logic		

}

?>


<!DOCTYPE html>
<html>

<?php include("../inc/header.php"); ?>


<body>

    <div id="wrapper">

        <?php include("../inc/nav_side.php"); ?>


        <div id="page-wrapper" class="gray-bg">
            <?php include("../inc/nav_header.php"); ?>


            <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-lg-10">
                    <h2>Investigation Setup & Costing</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index.html">Home</a>
                        </li>
                        <li class="active">
                            <strong>Investigation</strong>
                        </li>
                    </ol>
                </div>
                <div class="col-lg-2">

                </div>
            </div>

            <div class="wrapper wrapper-content  animated fadeInRight">




                <div class="row">
                    <div class="col-lg-12">
                        <div class="ibox ">
                            <div class="ibox-title">
                                <h5>Investigation Setup List</h5>
                                <div class="ibox-tools">

                                    <?php if (isset($_GET['combos'])) { ?>
                                        <!--  <input type="button" name="edit"  value="New Lab Combination" data-target="#myModal5" 
        class="btn btn-primary btn-xs add_lab_combos" />-->

                                    <?php } else { ?>
                                        <a href="invst_tmplate.php" class="btn btn-success btn-xs">Investigation Template</a>

                                        <input type="button" name="edit" value="Add New Investigation" <?php if ($_SESSION['create1'] == 0) { ?> disabled <?php } ?> data-target="#myModal5" id=""
                                            class="btn btn-primary btn-xs add_lab_testname" />
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="ibox-content">

                                <?php
                                if (isset($_GET['combos'])) {

                                    include("refresh.php");


                                    if ($_SESSION['col4'] == "1") {
                                        $dept_id = $_SESSION['dept_id'];
                                        $sort_by_dept_3 = " AND dept='$dept_id'";
                                    } else {
                                        $sort_by_dept_3 = "";
                                    }

                                    $sub = combos($sort_by_dept_3);
                                } elseif (isset($_GET['template'])) {
                                    $invst_no = $_GET['template'];

                                    if ($_SESSION['col4'] == "1") {
                                        $dept_id = $_SESSION['dept_id'];
                                        $sort_by_dept = " AND lab.dept='$dept_id'";
                                    } elseif ($_SESSION['section'] == 'Laboratory' or $_SESSION['section'] == 'Radiology') {
                                        $category = $_SESSION['section'];
                                        $sort_by_dept_ = " AND lab.category='$category'";
                                        $sort_by_dept_2 = " WHERE lab.category='$category'";
                                    } else {
                                        $sort_by_dept = "";
                                    }

                                    $stmt = $db->prepare("SELECT lab.*, d.department FROM lab_scan AS lab 
             INNER JOIN department AS d ON d.sn = lab.dept WHERE lab.sn = :invst_no $sort_by_dept ORDER BY lab.sn");
                                    $stmt->bindParam(':invst_no', $invst_no, PDO::PARAM_STR);
                                    $stmt->execute();

                                    if ($stmt->rowCount() > 0) {
                                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                ?>

                                        <form method="POST" id="template_form">
                                            <div class="mail-text h-200">

                                                <?php
                                                $stmt2 = $db->prepare("SELECT template_2 FROM invsti_template WHERE sn = :invst_no");
                                                $stmt2->bindParam(':invst_no', $invst_no, PDO::PARAM_STR);
                                                $stmt2->execute();

                                                if ($stmt2->rowCount() > 0) {
                                                    $row_NOTE = $stmt2->fetch(PDO::FETCH_ASSOC);
                                                }
                                                ?>
                                                <textarea name="template_note" id="template_note" cols="45" rows="5" class="summernote" placeholder="Type Your Message Here"><?php echo $row_NOTE['template_2']; ?></textarea>

                                                <div class="clearfix"></div>
                                            </div>

                                            <button class="btn btn-sm btn-primary" type="submit" name="save_template" id="save_template"><i class="fa fa-save"></i> Save</button>
                                            <div class="pull-right">
                                                <a href="setup.php" class="btn btn-danger btn-sm" data-toggle="tooltip" title="Close"><i class="fa fa-times"></i> Close</a>
                                            </div>


                                            <input type="hidden" name="lab_no" id="lab_no" value="<?php echo $invst_no; ?>" />
                                            <input type="hidden" name="template_title" id="template_title" value="<?php echo $row['test'] . ' (' . $row['department'] . ')' ?>" />
                                            <input type="hidden" name="type" id="type" value="<?php echo $row['test']; ?>" />
                                            <input type="hidden" name="type_no" id="type_no" value="<?php echo $invst_no; ?>" />
                                            <input type="hidden" name="MM_update" value="add_template" />
                                        </form>

                                    <?php } else {
                                        header("location:setup.php");
                                    } ?>

                                <?php } else {
                                    $stmt2 = $db->query("SELECT * FROM department WHERE department_type='Laboratory' or department_type='Radiology'");

                                ?>
                                    <div class="alert alert-info">
                                        <h2><?php if ($msg != '') {
                                                echo $msg;
                                            } ?></h2>
                                    </div>
                                    <div class="form_sep">
                                        <label for="reg_input_no" class="">Department/Unit Name</label>
                                        <select name="Department" onchange="window.open(this.options[this.selectedIndex].value,'_top')" id="Department" class="form-control" data-required="true">

                                            <option selected="selected" value="">Select Investigation by Department...</option>
                                            <option value="<?php echo 'setup.php'; ?>">Show All Tests</option>

                                            <?php while ($row_NOTE = $stmt2->fetch(PDO::FETCH_ASSOC)) { ?>
                                                <option value="<?php echo 'setup.php?did=' . $row_NOTE['sn']; ?>"><?php echo $row_NOTE["department"]; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <hr>

                                <?php
                                    include("refresh.php");
                                    $sub = labs();
                                } ?>



                            </div>
                        </div>


                    </div>

                    <?php include("../inc/setup_mdl.php"); ?>

                </div>

            </div>

        </div>
    </div>


    <?php include("search_modal.php") ?>

    <?php include("../inc/footer_scripts.php"); ?>
    <?php if ($save == '1') { ?>
        <script>
            toastr.success('Save Successfully!', 'Saved', {
                timeOut: 2000
            })
        </script>
    <?php } ?>


    <?php if ($error_msg != '') { ?>
        <script>
            toastr.success('<?= $error_msg; ?>', 'Deleted', {
                timeOut: 2000
            })
        </script>
    <?php } ?>



    <!-- Data Tables -->
    <script src="../js/plugins/dataTables/jquery.dataTables.js"></script>
    <script src="../js/plugins/dataTables/dataTables.bootstrap.js"></script>
    <script src="../js/plugins/dataTables/dataTables.responsive.js"></script>
    <script src="../js/plugins/dataTables/dataTables.tableTools.min.js"></script>


    <?php include("javascripts_setup.php"); ?>

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