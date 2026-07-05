<?php include("../Connections/Conn.php"); ?>

<?php
session_start();

if (isset($_POST["save_ml"])) {
    $ml_sheet = $_POST["ml_sheet"];
    $stock_id = $_POST["stock_id"];

    $stmt = $db->prepare("UPDATE stock_table SET ml_1sheet = :ml_sheet WHERE sn = :stock_id");
    $stmt->bindParam(':ml_sheet', $ml_sheet, PDO::PARAM_STR);
    $stmt->bindParam(':stock_id', $stock_id, PDO::PARAM_STR);
    $stmt->execute();

    // Redirect after updating
    header("location: cnsumbl.php?show_list&sv");
    exit; // Ensure script stops execution after redirection
}


if (isset($_POST["cancel"])) {
    header("location:manage.php");
}


if (isset($_GET["rdel"])) {
    $stmt = $db->prepare("DELETE FROM lab_reagent WHERE sn = :rdel");
    $stmt->bindParam(':rdel', $_GET["rdel"], PDO::PARAM_STR);
    $stmt->execute();
    header("location: cnsumbl.php");
    exit; // Ensure script stops execution after redirection
}

if (isset($_GET["ldel"])) {
    $stmt = $db->prepare("DELETE FROM lab_reagent_labtest WHERE sno = :ldel");
    $stmt->bindParam(':ldel', $_GET["ldel"], PDO::PARAM_STR);
    $stmt->execute();
    header("location: cnsumbl.php");
    exit; // Ensure script stops execution after redirection
}

if (isset($_GET["dl"])) {
    // Start a transaction for multiple queries
    $db->beginTransaction();

    try {
        // Delete from lab_test_consumble
        $stmt = $db->prepare("DELETE FROM lab_test_consumble WHERE lab_test_no = :dl");
        $stmt->bindParam(':dl', $_GET["dl"], PDO::PARAM_STR);
        $stmt->execute();

        // Update lab_scan
        $stmt_update = $db->prepare("UPDATE lab_scan SET consumable_setup = :consumable_setup WHERE sn = :dl");
        $consumable_setup = '0'; // Assuming this value
        $stmt_update->bindParam(':consumable_setup', $consumable_setup, PDO::PARAM_STR);
        $stmt_update->bindParam(':dl', $_GET["dl"], PDO::PARAM_STR);
        $stmt_update->execute();

        // Commit the transaction
        $db->commit();

        header("location: cnsumbl.php");
        exit; // Ensure script stops execution after redirection
    } catch (PDOException $e) {
        // Rollback the transaction on error
        $db->rollBack();
        echo "Error: " . $e->getMessage();
    }
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
                    <h2>Investigation Consumables Setup</h2>
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
                                <h5> Set Consumable and Quantity to Investigation</h5>
                                <div class="ibox-tools">
                                    <input type="button" name="edit" value="Add New" data-target="#myModal5" id="<?php echo $roww["sn"]; ?>"
                                        class="btn btn-primary btn-xs add_labtest" />

                                </div>
                            </div>
                            <div class="ibox-content">



                                <div class="row">
                                    <form action="cnsumbl.php" method="post">
                                        <div class="col-lg-4">
                                            <div class="form_sep">
                                                <label for="reg_input_no" class="">Select Consumable Settings</label>
                                                <select name="consumble" id="consumble" class="form-control" data-required="true">
                                                    <option selected="selected" value="">Select ...</option>
                                                    <option value="ICD">See Investigations with consumables</option>
                                                    <option value="ICP">See Investigation without consumable</option>
                                                    <option value="SAI">Stocks Assign to investigations</option>
                                                    <option value="SNI">Stocks Not assign to investigations</option>
                                                </select>
                                            </div>

                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form_sep">
                                                <label for="reg_input_no" class="">Departments</label>
                                                <select name="Department" id="Department" class="form-control" data-required="true">
                                                    <option selected="selected" value="">Select Department ...</option>
                                                    <?php
                                                    $stmt = $db->query("SELECT * FROM department WHERE department_type = 'Radiology' OR department_type = 'Laboratory'");
                                                    while ($row_rstdepartment = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                    ?>
                                                        <option value="<?php echo htmlspecialchars($row_rstdepartment['sn'] . '__' . $row_rstdepartment["department"]); ?>"><?php echo htmlspecialchars($row_rstdepartment["department"]); ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-lg-4">

                                            <label for="reg_input_no" class="">.</label><br>
                                            <input type="submit" name="show_list" value="Show" class="btn btn-primary btn-sm" />

                                        </div>

                                    </form>
                                </div>


                            </div>
                        </div>

                        <div class="row">

                            <div class="col-lg-12">
                                <div class="ibox ">

                                    <div class="ibox-content">

                                        <?php if (isset($_POST['show_list']) or isset($_GET['show_list'])) {

                                            if (isset($_POST['Department']) and $_POST['Department'] != '') {
                                                $dept = $_POST['Department'];
                                                $part = explode("__", $dept);
                                                $dept_idx = $part[0];
                                                $dept_id2 = $part[0];
                                                $dept_name = $part[1];
                                                $dept_idx = " where dept='$dept_idx'";
                                                $dept_id2 = " and  dept='$dept_id2'";
                                            } else {
                                                $dept_idx = "";
                                                $dept_id2 = "";
                                            }

                                            if (isset($_GET['show_list'])) {
                                                $consumble = 'SNI';
                                            } else {
                                                $consumble = $_POST['consumble'];
                                            }

                                            if ($consumble == "ICD") {
                                                ////////////////////////////////////////////// ICD ----------------------------------------
                                                $stmt = $db->query("select c.lab_test_no, l.test from lab_test_consumble as c inner join lab_scan as l on c.lab_test_no=l.sn $dept_idx order by test");
                                                if ($stmt->rowCount() > 0) { ?>

                                                    <?php if ($dept_name != '') { ?> <h3>Department: <?php echo $dept_name; ?> </h3> <?php } ?>
                                                    <hr>

                                                    <table class="table table-striped table-bordered table-hover dataTables-example">
                                                        <thead>
                                                            <tr>
                                                                <th data-toggle="true">#</th>
                                                                <th data-toggle="true">Lab Test Name</th>
                                                                <th data-toggle="true">Consumbles Attached:</th>
                                                                <th data-toggle="true">Total Consumbles Attached:</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>

                                                            <?php
                                                            $n = 1;
                                                            $no_Con = 0;
                                                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                                $lab_test_no = $row['lab_test_no']; ?>

                                                                <?php $stmtget = $db->query("select s.product_name,c.qty_test,c.posting_method from lab_test_consumble as c inner join stock_table as s ON c.consumable_no=s.sn WHERE lab_test_no='$lab_test_no'");
                                                                if ($stmtget->rowCount() > 0) {
                                                                    $consumbe = '';
                                                                    $sn = 1;
                                                                    while ($rwx = $stmtget->fetch(PDO::FETCH_ASSOC)) {
                                                                        //if($rwx['posting_method']=='1'){$pm='Auto';}else{$pm='Manual';}
                                                                        $consumbe .= $sn . ' - ' . $rwx['product_name'] . ': ' . 'Qty: ' .  $rwx['qty_test']  . '<br>';
                                                                        $sn++;
                                                                        $no_Con = $no_Con + 1;
                                                                    }
                                                                ?>
                                                                    <tr>
                                                                        <td><?php echo $n; ?></td>
                                                                        <td><?php echo $row['test']; ?></td>
                                                                        <td><?php echo $consumbe; ?></td>
                                                                        <td><?php echo $stmtget->rowCount(); ?></td>
                                                                    </tr>
                                                            <?php
                                                                }
                                                                $n++;
                                                            }
                                                            ?>

                                                        </tbody>
                                                    </table>

                                                    <hr>
                                                    <h4>Total Investigation(s) Setup for Consumable: &nbsp; <?php echo $no_Con; ?> </h4>

                                                <?php
                                                }
                                            } elseif ($consumble == "ICP") {

                                                //// ==========================================  ICP

                                                $stmt = $db->query("select sn, test from lab_scan $dept_idx order by test");
                                                if ($stmt->rowCount() > 0) { ?>

                                                    <?php if ($dept_name != '') { ?> <h3>Department: <?php echo $dept_name; ?> </h3> <?php } ?>
                                                    <hr>

                                                    <table class="table table-striped table-bordered table-hover dataTables-example">
                                                        <thead>
                                                            <tr>
                                                                <th data-toggle="true">#</th>
                                                                <th data-toggle="true">Lab Test Name</th>
                                                                <th data-toggle="true">Consumbles Attached:</th>
                                                                <th data-toggle="true">Total Consumbles Attached:</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>

                                                            <?php
                                                            $n = 1;
                                                            $no_Con = 0;
                                                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                                $lab_test_no = $row['sn']; ?>

                                                                <?php $stmtget = $db->query("select c.qty_test,c.posting_method from lab_test_consumble as c inner join stock_table as s ON c.consumable_no=s.sn WHERE lab_test_no='$lab_test_no'");
                                                                if ($stmtget->rowCount() == 0) {
                                                                    $consumbe = 'No Consumable';
                                                                    $no_Con = $no_Con + 1;
                                                                ?>
                                                                    <tr>
                                                                        <td><?php echo $n; ?></td>
                                                                        <td><?php echo $row['test']; ?></td>
                                                                        <td><?php echo $consumbe; ?></td>
                                                                        <td><?php echo $stmtget->rowCount(); ?></td>
                                                                    </tr>
                                                            <?php }
                                                                $n++;
                                                            }
                                                            ?>

                                                        </tbody>
                                                    </table>
                                                    <hr>
                                                    <h4>Total Investigation(s) <u>Without</u> Consumable Attached: &nbsp; <?php echo $no_Con; ?> </h4>
                                                <?php
                                                }
                                            } elseif ($consumble == "SNI") {

                                                /////////////////////////////  STOCK WITHout investiagtions
                                                $stmt = $db->query("SELECT distinct s.sn,s.product_name,s.ml_1sheet FROM stock_table s INNER JOIN stock_table_inven i on i.stock_sn=s.sn WHERE cust_patient_id='$dept_id' ORDER BY product_name");
                                                if ($stmt->rowCount() > 0) { ?>

                                                    <table class="table table-striped table-bordered table-hover dataTables-example">
                                                        <thead>
                                                            <tr>
                                                                <th data-toggle="true">#</th>
                                                                <th data-toggle="true">Stock Name</th>
                                                                <th data-toggle="true">Volume/Sheet</th>
                                                                <th data-toggle="true">Investigations Lists:</th>
                                                                <th data-toggle="true">Total Investigations:</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>

                                                            <?php
                                                            $n = 1;
                                                            $no_Con = 0;
                                                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                                $stock_sn = $row['sn']; ?>

                                                                <?php $stmtget = $db->query("select i.test,c.qty_test,c.posting_method from lab_test_consumble as c inner join lab_scan as i ON c.lab_test_no=i.sn WHERE consumable_no='$stock_sn'");
                                                                if ($stmtget->rowCount() == 0) {
                                                                    $investigations = 'No Investigations';
                                                                    $no_Con = $no_Con + 1;
                                                                ?>
                                                                    <tr>
                                                                        <td><?php echo $n; ?></td>
                                                                        <td><?php echo $row['product_name']; ?></td>
                                                                        <td>
                                                                            <input type="button" value="Set" data-toggle="modal" data-target="#myModal5" id="<?php echo $row["sn"]; ?>" class="btn btn-info btn-xs set_volume_sheet" />&nbsp;<?php echo $row['ml_1sheet'] . 'ml/sheet'; ?>
                                                                        </td>
                                                                        <td><?php echo $investigations; ?></td>
                                                                        <td><?php echo $stmtget->rowCount(); ?></td>
                                                                    </tr>
                                                            <?php }
                                                                $n++;
                                                            }
                                                            ?>

                                                        </tbody>
                                                    </table>
                                                    <hr>
                                                    <h4>Total Stocks(s) <u>Without</u> Investigations Attached: &nbsp; <?php echo $no_Con; ?> </h4>
                                                <?php
                                                }
                                            } elseif ($consumble == "SAI") {



                                                /////////////////////////////  STOCK assign  investigations
                                                $stmt = $db->query("SELECT distinct s.sn,s.product_name,s.ml_1sheet FROM stock_table s INNER JOIN stock_table_inven i on i.stock_sn=s.sn WHERE cust_patient_id='$dept_id' ORDER BY product_name");
                                                if ($stmt->rowCount() > 0) { ?>

                                                    <table class="table table-striped table-bordered table-hover dataTables-example">
                                                        <thead>
                                                            <tr>
                                                                <th data-toggle="true">#</th>
                                                                <th data-toggle="true">Stock Name</th>
                                                                <th data-toggle="true">Volume/Sheet</th>
                                                                <th data-toggle="true">Investigations Lists:</th>
                                                                <th data-toggle="true">Total Investigations:</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>

                                                            <?php
                                                            //  echo 

                                                            $n = 0;
                                                            $no_Con = 0;
                                                            $sn = 1;
                                                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                                $stock_sn = $row['sn']; ?>

                                                                <?php $stmtget = $db->query("select distinct i.test,c.qty_test,c.posting_method from lab_test_consumble as c inner join lab_scan as i ON c.lab_test_no=i.sn WHERE consumable_no='$stock_sn' $dept_id2");
                                                                if ($stmtget->rowCount() > 0) {
                                                                    $investigations = '';
                                                                    $no_Con = $no_Con + 1;

                                                                    while ($rwx = $stmtget->fetch(PDO::FETCH_ASSOC)) {
                                                                        //if($rwx['posting_method']=='1'){$pm='Auto';}else{$pm='Manual';}
                                                                        $investigations .= $sn . ' - ' . $rwx['test'] . ': ' . 'Qty: ' .  $rwx['qty_test']  . '<br>';
                                                                        $sn++;
                                                                    }

                                                                ?>
                                                                    <tr>
                                                                        <td><?php echo $n; ?></td>
                                                                        <td><?php echo $row['product_name']; ?></td>
                                                                        <td>
                                                                            <input type="button" value="Set" data-toggle="modal" data-target="#myModal5" id="<?php echo $row["sn"]; ?>" class="btn btn-info btn-xs set_volume_sheet" />&nbsp;<?php echo $row['ml_1sheet'] . 'ml/sheet'; ?>
                                                                        </td>
                                                                        <td><?php echo $investigations; ?></td>
                                                                        <td><?php echo $stmtget->rowCount(); ?></td>
                                                                    </tr>
                                                            <?php }
                                                                $n++;
                                                            }
                                                            ?>

                                                        </tbody>
                                                    </table>
                                                    <hr>
                                                    <h4>Total Stock(s) Assigned to Investigations: &nbsp; <?php echo $no_Con; ?> </h4>
                                            <?php
                                                }
                                            }
                                        } else { ?>

                                            <?php $stmt = $db->prepare('SELECT * FROM lab_scan WHERE consumable_setup = :consumable_setup');
                                            $stmt->execute([':consumable_setup' => '1']);
                                            if ($stmt->rowCount() > 0) { ?>
                                                <table class="table table-striped table-bordered table-hover dataTables-example">
                                                    <thead>
                                                        <tr>
                                                            <th data-toggle="true">#</th>
                                                            <th data-toggle="true">Lab Test Name</th>
                                                            <th data-toggle="true">.</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>

                                                        <?php
                                                        $n = 1;
                                                        while ($roww = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                        ?>
                                                            <tr>
                                                                <td><?php echo $n; ?></td>
                                                                <td><?php echo $roww['test']; ?></td>

                                                                <td>

                                                                    <a href="cnsumbl.php?dl=<?php echo $roww["sn"]; ?>" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure you want to delete?')">Delete</a>
                                                                    &nbsp; | &nbsp; <input type="button" value="Manage Consumables" data-toggle="modal" data-target="#myModal5" id="<?php echo $roww["sn"] . '__' . $roww['test']; ?>" class="btn btn-info btn-xs manage_consmbles" />
                                                                </td>
                                                            </tr>
                                                        <?php
                                                            $n++;
                                                        } ?>

                                                    </tbody>
                                                </table>
                                            <?php } else { ?>
                                                <strong>No Consumable(s) Setup to Display</strong>
                                            <?php } ?>

                                        <?php } ?>

                                    </div>
                                </div>


                            </div>
                        </div>

                    </div>

                </div>
            </div>




            <div class="modal inmodal fade" id="add_new_lab_test_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            <h4 class="modal-title" id="">Add a New Lab Test & Consumables</h4>
                        </div>

                        <div class="modal-body">
                            <form method="POST" id="add_new_lab_test_form">

                                <?php
                                $stmt = $db->prepare('SELECT * FROM lab_scan WHERE consumable_setup = :consumable_setup');
                                $stmt->execute([':consumable_setup' => '0']);
                                ?>

                                <div class="form_sep">
                                    <label for="reg_input_no" class="req">Lab Test </label>
                                    <select name="lab_test[]" data-placeholder="Search ..." class="chosen-select" multiple style="width:350px;" tabindex="0">
                                        <option value="">Select ...</option>
                                        <?php
                                        if ($stmt->rowCount() > 0) {
                                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                                <option value="<?php echo $row["sn"] . '__' . $row["test"]; ?>"><?php echo $row["test"]; ?></option>
                                        <?php }
                                        }
                                        ?>
                                    </select>

                                </div>
                                <br><br><br><br><br><br><br><br><br>

                                <div class="form_sep">
                                    <button class="btn btn-primary btn-xs" type="submit" name="add">Add </button>
                                </div>
                                <input type="hidden" name="MM_update" value="add_labtest_consumable" />
                            </form>

                        </div>
                    </div>
                </div>
            </div>

            <div class="modal inmodal fade" id="add_consumable_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            <h4 class="modal-title" id="">Add a New Lab Test & Consumables</h4>
                        </div>

                        <div class="modal-body">
                            <form method="POST" id="add_consumable_form">


                                <div class="form_sep">

                                    <?php

                                    $stmt = $db->query("SELECT distinct s.sn,s.product_name FROM stock_table s INNER JOIN stock_table_inven i on i.stock_sn=s.sn WHERE cust_patient_id='$dept_id' ORDER BY product_name");
                                    ?>
                                    <h3 style="color: coral;">What to do here: </h3>
                                    <h3>Select the Stock you wish to set as consumable<br>
                                        Set number of Test(s) to be conducted based on the volume of the stock or other unit of measurment</h3>
                                    <hr>

                                    <label for="reg_input_no" class="">Select Consumable</label>
                                    <select name="consumable" id="consumable_c" class="form-control" data-required="true">
                                        <option selected="selected" value="">Select ...</option>
                                        <?php if ($stmt->rowCount() > 0) {
                                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                                <option value="<?php echo $row["sn"]; ?>"><?php echo $row["product_name"]; ?></option>
                                        <?php }
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="form_sep">
                                    <label for="reg_input_no" class="">Specify Number Test(s) required to exhaust One quantity </label><label id="vll"></label>
                                    <input type="number" name="whole_qty" class="form-control" min="1">
                                </div>


                                <div class="form_sep">
                                    <button class="btn btn-primary btn-xs" type="submit" name="add">Add </button>
                                </div>

                                <input type="hidden" name="test_no" id="test_no" />
                                <input type="hidden" name="test_name" id="test_name" />

                                <input type="hidden" name="MM_update" value="add_labtest_consumable_items" />
                            </form>
                        </div>
                    </div>
                </div>
            </div>




            <div class="modal inmodal fade" id="view_consumables_mgt_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            <h4 class="modal-title" id="">Lab Field Options</h4>
                        </div>
                        <div class="modal-body" id="view_consumables_mgt_body">

                        </div>

                    </div>
                </div>
            </div>

            <div class="modal inmodal fade" id="set_volume_sheet_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            <h4 class="modal-title" id="">Set Volume of Reagent or Film Sheet</h4>
                        </div>
                        <div class="modal-body" id="set_volume_sheet_body">

                        </div>

                    </div>
                </div>
            </div>


            <?php include("../inc/footer_scripts.php"); ?>

            <?php if (isset($_GET['sv'])) { ?>
                <script>
                    toastr.success('Added Successfully', 'Saved', {
                        timeOut: 5000
                    })
                </script>
            <?php } ?>

            <script>
                $("#consumable_c").change(function() {
                    var consumable_c = document.getElementById('consumable_c').value;

                    $.ajax({
                        url: "fetch_set.php",
                        method: "POST",
                        data: {
                            consumable_c: consumable_c
                        },
                        success: function(data) {
                            //	alert(data);
                            document.getElementById('vll').innerHTML = ': ' + data + 'ml/sheet';
                        }
                    });
                });


                $(".chosen-select").chosen({
                    allow_single_deselect: true,
                    enable_search_threshold: 10,
                    no_results_text: 'Oops, nothing found!',
                    width: "100%"
                });
                $('.chosen-drop').css({
                    "width": "100%",
                    "white-space": "nowrap"
                })


                $(document).on('click', '.delete_cnsumable_conf', function() {

                    $('#delete_cnsumable_conf_modal').modal('show');
                    $('#delete_cnsumable_conf_body').html(data);

                });


                $(document).on('click', '.re_agent_add', function() {
                    $('#Re_agent_form')[0].reset();
                    $('#agent_qty').val("");
                    $('#agent_name').val("");
                    $('#Re_agent_modal').modal('show');

                });




                $('#Re_agent_form').on("submit", function(event) {
                    event.preventDefault();
                    if ($('#agent_name').val() == "") {
                        swal("Re-agent name is required");
                    } else if ($('#agent_qty').val() == "") {
                        swal("Re-agent Quantity is required");
                    } else {
                        $.ajax({
                            url: "insert.php",
                            method: "POST",
                            data: $('#Re_agent_form').serialize(),
                            beforeSend: function() {
                                $('#add_btn').val("Adding");
                            },
                            success: function(data) {
                                //swal({ title: 'Success!', text: 'Save Successfully', timer: 250 })
                                // $('#specimen_notes').val("");
                                //  $('#Specimen').val(""); 
                                $('#Re_agent_modal').modal('hide');
                                $('#lab_reagent').html(data);
                                window.location.reload();

                            },
                            complete: function() {
                                $('#add_btn').val("Added");
                            },
                            error: function(data) {
                                alert("Oops...", "Something went wrong :(", "error");
                                swal({
                                    title: 'Oops...!',
                                    text: 'Something went wrong ',
                                    type: 'error',
                                    timer: 500
                                })
                            }
                        });

                    }
                });



                $('#add_new_lab_test_form').on("submit", function(event) {
                    event.preventDefault();
                    if ($('#lab_test').val() == "") {
                        swal("Lab Test name is required");
                    } else {
                        $.ajax({
                            url: "insert.php",
                            method: "POST",
                            data: $('#add_new_lab_test_form').serialize(),
                            beforeSend: function() {
                                $('#add').val("Adding");
                            },
                            success: function(data) {
                                //swal({ title: 'Success!', text: 'Save Successfully', timer: 250 })
                                // $('#specimen_notes').val("");
                                //  $('#Specimen').val(""); 
                                //$('#add_new_lab_test_modal').modal('hide');  
                                //$('#lab_reagent_labtest').html(data);
                                location.href = "cnsumbl.php?sv";

                            },
                            complete: function() {
                                $('#add_btn').val("Added");
                            },
                            error: function(data) {
                                alert("Oops...", "Something went wrong :(", "error");
                                swal({
                                    title: 'Oops...!',
                                    text: 'Something went wrong ',
                                    type: 'error',
                                    timer: 500
                                })
                            }
                        });

                    }
                });


                $(document).on('click', '.add_labtest', function() {
                    //$("#modal-body").html("");
                    // $("#modal-body").find("lable,input,textarea").val("");
                    // $(this).find(".modal-content").empty();

                    //  $('#add_new_lab_test_modal').removeData("modal")
                    //  $('.modal-title').find('lable,input,textarea').val('');
                    $('#add_new_lab_test_form')[0].reset();
                    $('#lab_test').val("");
                    $('#consumable').val("");
                    $('#whole_qty').val("");
                    $('.modal-title').text('Add a New Lab Test & Consumables');
                    $('#add_new_lab_test_modal').modal('show');

                });

                $(document).on('click', '.manage_consmbles', function() {
                    var consumable_id = $(this).attr("id");
                    var res = consumable_id.split("__");

                    if (consumable_id != '') {
                        $.ajax({
                            url: "fetch_set.php",
                            method: "POST",
                            data: {
                                consumable_id: res[0]
                            },
                            success: function(data) {

                                $('.modal-title').text('Manage Consumable for ' + res[1]);
                                $('#view_consumables_mgt_modal').modal('show');
                                $('#view_consumables_mgt_body').html(data);
                            }
                        });
                    }
                });





                $(document).on('click', '.add_consumables', function() {
                    $('#view_consumables_mgt_modal').modal('hide');

                    var consumable_id = $(this).attr("id");
                    //   var res = consumable_id.split("__");

                    if (consumable_id != '') {
                        $.ajax({
                            url: "fetch_labtest.php",
                            method: "POST",
                            data: {
                                consumable_id: consumable_id
                            },
                            dataType: "json",
                            success: function(data) {
                                $('#test_no').val(data.sn);
                                $('#test_name').val(data.test);

                                // $('.modal-title').text('Manage Consumable for ' + res[1]);
                                $('#add_consumable_modal').modal('show');
                                $('#add_consumable_body').html(data);
                            }
                        });
                    }
                });



                $(document).on('click', '.set_volume_sheet', function() {


                    var set_volume_sheet_id = $(this).attr("id");
                    if (set_volume_sheet_id != '') {
                        $.ajax({
                            url: "fetch_labtest.php",
                            method: "POST",
                            data: {
                                set_volume_sheet_id: set_volume_sheet_id
                            },
                            success: function(data) {
                                $('#set_volume_sheet_modal').modal('show');
                                $('#set_volume_sheet_body').html(data);
                            }
                        });
                    }
                });



                $('#add_consumable_form').on("submit", function(event) {

                    event.preventDefault();
                    if ($('#consumable').val() == "") {
                        swal("consumable is required");
                    } else if ($('#whole_qty').val() == "") {
                        swal("whole Quantity is required");
                    } else {
                        $.ajax({
                            url: "insert.php",
                            method: "POST",
                            data: $('#add_consumable_form').serialize(),
                            beforeSend: function() {
                                $('#add').val("Adding");
                            },
                            success: function(data) {
                                //swal({ title: 'Success!', text: 'Save Successfully', timer: 250 })
                                $('#add_consumable_modal').modal('hide');

                                var consumable_id = $("#test_no").val();
                                $('#add_consumable_form')[0].reset();
                                $('#view_consumables_mgt_modal').modal('show');

                                //$('#test_no').val();
                                //var consumable_id ="5";

                                $.ajax({
                                    url: "fetch_set.php",
                                    method: "POST",
                                    data: {
                                        consumable_id: consumable_id
                                    },
                                    success: function(data) {

                                        // $('.modal-title').text('Manage Consumable for ' + res[1]);
                                        $('#view_consumables_mgt_modal').modal('show');
                                        $('#view_consumables_mgt_body').html(data);
                                    }
                                });



                            },
                            complete: function() {
                                $('#add').val("Added");
                            },
                            error: function(data) {
                                alert("Oops...", "Something went wrong :(", "error");
                                swal({
                                    title: 'Oops...!',
                                    text: 'Something went wrong ',
                                    type: 'error',
                                    timer: 500
                                })
                            }
                        });

                    }
                });


                $(document).on('click', '.close_consumables', function() {

                    $('#add_consumable_form')[0].reset();
                    $('#view_consumables_mgt_modal').modal('hide');
                });



                $(document).on('click', '.consumable_del', function() {
                    var consumable_id_del = $(this).attr("id");
                    var res = consumable_id_del.split("__");

                    if (consumable_id_del != '') {
                        $.ajax({
                            url: "delete.php",
                            method: "POST",
                            data: {
                                consumable_id_del: res[0]
                            },
                            success: function(data) {
                                $.ajax({
                                    url: "fetch_set.php",
                                    method: "POST",
                                    data: {
                                        consumable_id: res[1]
                                    },
                                    success: function(data) {

                                        // $('.modal-title').text('Manage Consumable for ' + res[1]);
                                        $('#view_consumables_mgt_modal').modal('show');
                                        $('#view_consumables_mgt_body').html(data);
                                    }
                                });


                            }
                        });
                    }
                });
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