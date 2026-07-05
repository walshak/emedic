<?php include("../Connections/Conn.php"); ?>

<?php
session_start();


if (isset($_POST["cancel"])) {
    header("location:manage.php");
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
                    <h2>Investigations Result Sheet Template</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index.html">Home</a>
                        </li>
                        <li class="active">
                            <strong>Settings</strong>
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
                                <h5>Search settings</h5>
                            </div>
                            <div class="ibox-content">


                                <div class="row">
                                    <form action="invst_tmplate.php" method="post">
                                        <div class="col-lg-3">
                                            <div class="form_sep">
                                                <label for="reg_input_no" class="">Investigation Template</label>
                                                <select name="invest_tem" id="invest_tem" class="form-control" data-required="true">
                                                    <option selected="selected" value="">Select ...</option>
                                                    <option value="ivr">Display Investigations With Result Template</option>
                                                    <option value="iwr">Display Investigations Without Results Template</option>

                                                </select>
                                            </div>

                                        </div>

                                        <div class="col-lg-3">


                                            <div class="form_sep">
                                                <label for="reg_input_no" class="">Template Types</label>
                                                <select name="template_ty" id="template_ty" class="form-control" data-required="true">
                                                    <option selected="selected" value="">Select ...</option>
                                                    <?php
                                                    $stmt = $db->query("SELECT distinct field_type FROM lab_scan_fields");
                                                    while ($rxws = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                                        <option value="<?php echo $rxws['field_type']; ?>"><?php echo $rxws["field_type"]; ?></option>
                                                    <?php } ?>


                                                </select>
                                            </div>

                                        </div>

                                        <div class="col-lg-3">
                                            <div class="form_sep">
                                                <label for="reg_input_no" class="">Show by Departments</label>
                                                <select name="Department" id="Department" class="form-control" data-required="true">

                                                    <option selected="selected" value="">Select Department ...</option>
                                                    <?php




                                                    $stmt = $db->query("SELECT * FROM department WHERE $dept_ (department_type='Radiology' or department_type='Laboratory')");
                                                    while ($row_rstdepartment = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                                        <option value="<?php echo $row_rstdepartment['sn']; ?>"><?php echo $row_rstdepartment["department"]; ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-lg-3">

                                            <label for="reg_input_no" class="">.</label><br>
                                            <input type="submit" name="show_list" value="Show" class="btn btn-primary btn-sm" />

                                        </div>

                                    </form>
                                </div>





                            </div>
                        </div>
                    </div>

                    <div class="row">
                    </div>
                    <div class="col-lg-12">
                        <div class="ibox ">
                            <div class="ibox-title">
                                <h5>Data Display</h5>
                            </div>
                            <div class="ibox-content">

                                <?php
                                if (isset($_POST['invest_tem']) and $_POST['invest_tem'] != '') {
                                    $invest_tem = $_POST['invest_tem'];

                                    if (isset($_POST['template_ty']) and $_POST['template_ty'] != '') {
                                        $template_ty = $_POST['template_ty'];
                                        $template_ty = " and field_type='$template_ty'";
                                    } else {
                                        $template_ty = "";
                                    }


                                    if (isset($_POST['Department']) and $_POST['Department'] != '') {
                                        $Department = $_POST['Department'];
                                        $Department = " where d.sn='$Department'";
                                    } else {
                                        $Department = "";
                                    }


                                    if ($_SESSION['col4'] == "1") {
                                        $dept_id = $_SESSION['dept_id'];
                                        $sort_by_dept = " AND ls.dept='$dept_id'";
                                    } elseif ($_SESSION['section'] == 'Laboratory' or $_SESSION['section'] == 'Radiology') {
                                        $category = $_SESSION['section'];
                                        $sort_by_dept = " AND ls.category='$category'";
                                        $sort_by_dept_2 = " WHERE ls.category='$category'";

                                        ///$dept_ = " sn='$dept_id' AND ";
                                    }


                                    if ($invest_tem == 'ivr') {

                                        ////-----------------------  TEMPLATE SET DONE					
                                        $stmt = $db->query("select ls.sn, ls.test, ls.category, d.department 
                                        from lab_scan as ls 
                                        inner join department as d on d.sn=ls.dept $Department $sort_by_dept order by dept, test");
                                        if ($stmt->rowCount() > 0) { ?>

                                            <table class="table table-striped table-bordered table-hover dataTables-example">
                                                <thead>
                                                    <tr>
                                                        <th data-toggle="true">#</th>
                                                        <th data-toggle="true">Lab Test Name</th>
                                                        <th data-toggle="true">Department</th>
                                                        <th data-toggle="true">Category</th>
                                                        <th data-toggle="true">Template Type</th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                    <?php

                                                    $n = 0;
                                                    $no_Con = 0;
                                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                        $lab_test_no = $row['sn']; ?>

                                                        <?php $stmtget = $db->query("select field_type FROM lab_scan_fields WHERE test_no='$lab_test_no' $template_ty");
                                                        if ($stmtget->rowCount() > 0) {
                                                            $rowx = $stmtget->fetch(PDO::FETCH_ASSOC);
                                                            $n = $n + 1;
                                                        ?>
                                                            <tr>
                                                                <td><?php echo $n; ?></td>
                                                                <td><?php echo $row['test']; ?></td>
                                                                <td><?php echo $row['department']; ?></td>
                                                                <td><?php echo $row['category']; ?></td>
                                                                <td><?php echo $rowx['field_type']; ?></td>
                                                            </tr>
                                                    <?php }
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>

                                            <hr>
                                            <h3>Total Number: &nbsp; <?php echo $n; ?></h3>

                                        <?php
                                        }
                                    } elseif ($invest_tem == 'iwr') {

                                        /////////////////////// UNDONE

                                        ////-----------------------  TEMPLATE SET DONE					
                                        $stmt = $db->query("select ls.sn, ls.test, ls.category, d.department from lab_scan as ls 
                                        inner join department as d on d.sn=ls.dept  $Department $sort_by_dept order by dept, test");
                                        if ($stmt->rowCount() > 0) { ?>

                                            <table class="table table-striped table-bordered table-hover dataTables-example">
                                                <thead>
                                                    <tr>
                                                        <th data-toggle="true">#</th>
                                                        <th data-toggle="true">Lab Test Name</th>
                                                        <th data-toggle="true">Department</th>
                                                        <th data-toggle="true">Category</th>
                                                        <th data-toggle="true">Template Type</th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                    <?php
                                                    $n = 0;
                                                    $no_Con = 0;
                                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                        $lab_test_no = $row['sn']; ?>

                                                        <?php $stmtget = $db->query("select field_type FROM lab_scan_fields WHERE test_no='$lab_test_no' $template_ty");
                                                        if ($stmtget->rowCount() == 0) {
                                                            $rowx = $stmtget->fetch(PDO::FETCH_ASSOC);
                                                            $n = $n + 1;
                                                        ?>
                                                            <tr>
                                                                <td><?php echo $n; ?></td>
                                                                <td><?php echo $row['test']; ?></td>
                                                                <td><?php echo $row['department']; ?></td>
                                                                <td><?php echo $row['category']; ?></td>
                                                                <td><?php echo 'Not Available' ?>

                                                                    &nbsp; | &nbsp;

                                                                    <?php

                                                                    if ($row['category'] == "Laboratory") { ?>
                                                                        <input type="button" name="view_lab_tbl" value="View" data-toggle="modal" data-target="#myModal5" id="<?php echo $row["sn"] . '__' . $row['test']; ?>" class="btn btn-info btn-xs view_lab" />
                                                                </td> <?php } elseif ($row['category'] == "Radiology") { ?>

                                                                <a href="setup.php?template=<?php echo $row["sn"] ?>" class="btn btn-primary btn-xs">Template</a>
                                                            <?php } ?>


                                                            </td>
                                                            </tr>
                                                    <?php }
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                            <hr>
                                            <h3>Total Number: &nbsp; <?php echo $n; ?></h3>
                                <?php
                                        }
                                    }
                                }
                                ?>



                            </div>
                        </div>


                    </div>
                </div>

            </div>

        </div>
    </div>

    <?php include("../inc/setup_mdl.php"); ?>
    <?php include("search_modal.php") ?>

    <?php include("../inc/footer_scripts.php"); ?>

    <script src="../js/setup.js"></script>

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