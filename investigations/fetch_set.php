<?php include("../Connections/Conn.php");
session_start();
$dept_id = $_SESSION['dept_id'];



if (isset($_POST["lab_test_no"])) {
    $stmt = $db->prepare("SELECT * FROM lab_scan WHERE sn = :sn ORDER BY sn");
    $stmt->bindParam(':sn', $_POST["lab_test_no"], PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($row);
}

if (isset($_POST["consumable_c"])) {
    $stock_sn = $_POST["consumable_c"];
    $stmt = $db->prepare("SELECT ml_1sheet FROM stock_table WHERE sn = :stock_sn");
    $stmt->bindParam(':stock_sn', $stock_sn, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row || empty($row['ml_1sheet'])) {
        echo 'Volume quantity or Measuring Value not set up';
    } else {
        echo $row['ml_1sheet'];
    }
}

if (isset($_POST["field_no_option_add"])) {
    $stmt = $db->prepare("SELECT * FROM lab_scan_rlts_opt WHERE field_id_no = :field_id_no ORDER BY sn");
    $stmt->bindParam(':field_id_no', $_POST["field_no_option_add"], PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($row);
}

if (isset($_POST["bio_data_id"])) {
    $hos_no = $_POST["bio_data_id"];
    include("../inc/bio_data.php");
    exit;
}

if (isset($_POST["combos_id"])) {

    $_POST["combos_id"];
    $combos_id = $_POST["combos_id"];
    $part = explode("__", $combos_id);
    $com_id = $part['0'];
    $com_name = $part['1'];

    echo '<strong>Combination Title: </strong>' . $com_name;
    echo '<br>';


    $rstSelect = $db->query("SELECT lab_scan.test, lab_combos_items.sn FROM lab_combos_items 
	inner join lab_scan on lab_scan.sn=lab_combos_items.test_id WHERE combos_id='$com_id' order by sn");
    if ($rstSelect->rowCount() > 0) { ?>

        <div id="test_fields">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th data-toggle="true">No</th>
                        <th data-toggle="true">Lab Test Name</th>
                        <th data-toggle="true">Manage</th>
                    </tr>
                </thead>
                <tbody>

                    <?php
                    $n = 1;
                    while ($roww = $rstSelect->fetch(PDO::FETCH_ASSOC)) {
                        ///  while($roww=mysql_fetch_array($rstSelect2)) { 
                    ?>
                        <tr>
                            <td><?php echo $roww['sn']; ?></td>
                            <td><?php echo $roww['test']; ?></td>
                            <td>
                                <input type="button" name="Delete" value="Delete" id="<?php echo $roww["sn"] . '__' . $com_id . '__' .    $com_name; ?>"
                                    class="btn btn-danger btn-xs combos_list_del" data-target="#myModal5" />
                            </td>
                        </tr>
                    <?php
                        $n++;
                    } ?>

                </tbody>
            </table>
        <?php } else { ?>
            <br><br><strong>No Combos List Added. Search & Select Lab Test to add Combo List .</strong><br><br>
        <?php } ?>
        <br>
        <form method="POST" id="add_combos_form">

            <input type="hidden" id="com_id" name="com_id" class="form-control" value="<?php echo $com_id; ?>">
            <input type="hidden" id="com_name" name="com_name" class="form-control" value="<?php echo $com_name; ?>">

            <?php
            $rstSelect = $db->query("SELECT lab.*, d.department, d.sn as dept_id FROM lab_scan as lab inner join department as d on d.sn=lab.dept
		  WHERE combo_test='0'");

            ?>

            <div class="form_sep">
                <label for="reg_input_no" class="req">Lab Test</label>
                <select name="lab_request[]" data-placeholder="Search and Select Lab..." class="chosen-select" multiple style="width:350px;" tabindex="4">

                    <?php while ($row = $rstSelect->fetch(PDO::FETCH_ASSOC)) { ?>
                        <option value="<?php echo $row["sn"]; ?>"><?php echo $row["test"] . ' (' . $row["department"]  . ')'; ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="form_sep">
                <input type="submit" name="Add" id="Add" value="Add Lab Test" class="btn btn-success btn-xs" />
                <input type="hidden" name="MM_update" value="add_combos_option" />
            </div>
        </form>


        <?php  }

    if (isset($_POST["field_no_values"])) {
        $field_id_no = $_POST["field_no_values"];

        $stmt = $db->prepare("SELECT * FROM lab_scan_rlts_values WHERE field_id_no = :field_id_no ORDER BY sn");
        $stmt->bindParam(':field_id_no', $field_id_no, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
        ?>
            <div id="test_fields">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Field ID</th>
                            <th>Input box Title</th>
                            <th>Reference</th>
                            <th>Manage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $n = 1;
                        while ($roww = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        ?>
                            <tr>
                                <td><?php echo $roww['sn']; ?></td>
                                <td><?php echo $roww['field_id_no']; ?></td>
                                <td><?php echo $roww['options']; ?></td>
                                <td><?php echo $roww['reference']; ?></td>
                                <td>
                                    <input type="button" name="Delete" value="Delete" id="<?php echo $roww["sn"]; ?>"
                                        class="btn btn-danger btn-xs lab_scan_values_del" data-target="#myModal5" />
                                </td>
                            </tr>
                        <?php
                            $n++;
                        } ?>

                    </tbody>
                </table>
            <?php } else { ?>
                <br><br><strong>No Values(s) Added To The Lab Test Field Name. Click Add Button to Add Items.</strong><br><br>
            <?php } ?>
            <br>
            <form method="POST" id="add_field_values_form">

                <input type="hidden" id="field_opt_no" name="field_opt_no" class="form-control" value="<?php echo $_POST["field_no_values"]; ?>">

                <div class="form_sep">
                    <label for="reg_input_no" class="">Input box Title value name</label>
                    <input type="text" id="options" name="options" class="form-control">
                </div>

                <div class="form_sep">
                    <label for="reg_input_no" class="">Reference</label>
                    <input type="text" id="Reference" name="Reference" class="form-control">
                </div>

                <div class="form_sep">
                    <input type="submit" name="insert" id="insert" value="Insert" class="btn btn-success btn-xs" />
                    <input type="hidden" name="MM_update" value="add_field_values" />
                </div>
            </form>

            <?php

        }

        if (isset($_POST["field_no_option"])) {

            $field_no_option = $_POST["field_no_option"];

            $stmt = $db->query("SELECT * FROM lab_scan_rlts_opt WHERE field_id_no='$field_no_option'");
            if ($stmt->rowCount() > 0) { ?>

                <div id="test_fields">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th data-toggle="true">No</th>
                                <th data-toggle="true">Field ID</th>
                                <th data-toggle="true">Options</th>
                                <th data-toggle="true">Manage</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php
                            $n = 1;
                            while ($roww = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            ?>
                                <tr>
                                    <td><?php echo $roww['sn']; ?></td>
                                    <td><?php echo $roww['field_id_no']; ?></td>
                                    <td><?php echo $roww['options']; ?></td>
                                    <td>
                                        <input type="button" name="Delete" value="Delete" id="<?php echo $roww["sn"]; ?>"
                                            class="btn btn-danger btn-xs lab_scan_options_del" data-target="#myModal5" />
                                    </td>
                                </tr>
                            <?php
                                $n++;
                            } ?>

                        </tbody>
                    </table>
                <?php } else { ?>
                    <br><br><strong>No Options(s) Added To The Lab Test Field Name. Click Add Button to Add Items.</strong><br><br>
                <?php } ?>
                <br>
                <form method="POST" id="add_field_option_form">

                    <input type="hidden" id="field_opt_no" name="field_opt_no" class="form-control" value="<?php echo $_POST["field_no_option"]; ?>">

                    <div class="form_sep">
                        <label for="reg_input_no" class="">Options</label>
                        <input type="text" id="options" name="options" class="form-control">
                    </div>

                    <div class="form_sep">
                        <input type="submit" name="insert" id="insert" value="Insert" class="btn btn-success btn-xs" />
                        <input type="hidden" name="MM_update" value="add_field_option" />
                    </div>
                </form>

            <?php  }


        if (isset($_POST["lab_scan_fields_del"])) {
            $del_id = $_POST["lab_scan_fields_del"];

            // Assuming $db is your PDO database connection object
            $stmt = $db->prepare("DELETE FROM lab_scan_fields WHERE sn = :del_id");
            $stmt->bindParam(':del_id', $del_id, PDO::PARAM_INT);

            // Execute the query
            $stmt->execute();

            // Check if the deletion was successful
            if ($stmt->rowCount() > 0) {
                echo "Record deleted successfully.";
            } else {
                echo "Error deleting record.";
            }
        }


        if (isset($_POST["field_no"])) {

            $field = $_POST["field_no"];
            $part = explode("__", $field);
            $test_id = $part[0];
            $test_name = $part[1];

            //echo '<strong>Test Name: </strong>'  . $test_name;
            include("refresh.php");
            $sub = lab_scan_fields($test_id, $test_id, $test_name, $field);

            ?>

                <input type="button" name="Cancel" value="Close" data-target=".slacker-modal" id="" class="btn btn-warning btn-xs add_fields_items_close" />
            <?php }  ?>

            <?php


            if (isset($_POST["consumable_id"])) {
                $lab_testno = $_POST["consumable_id"];

                // Prepare the SQL query using PDO prepared statements
                $stmt = $db->prepare("SELECT C.*, S.product_name, S.ml_1sheet 
                               FROM lab_test_consumble as C 
                               INNER JOIN stock_table AS S on S.sn = C.consumable_no 
                               WHERE lab_test_no = :lab_testno 
                               ORDER BY product_name");

                // Bind the parameter
                $stmt->bindParam(':lab_testno', $lab_testno, PDO::PARAM_INT);

                // Execute the query
                $stmt->execute();

                // Check if there are rows returned
                if ($stmt->rowCount() > 0) { ?>

                    <div id="test_fields">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th data-toggle="true">No</th>
                                    <th data-toggle="true">Consumable Name</th>
                                    <th data-toggle="true">Qty</th>
                                    <th data-toggle="true">Manage</th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php
                                $n = 1;
                                while ($roww = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                ?>
                                    <tr>
                                        <td><?php echo $n; ?></td>
                                        <td><?php echo $roww['product_name']; ?></td>
                                        <td><?php echo $roww['qty_test'] . ' (' . $roww['ml_1sheet'] . ' ml/sheet)'; ?></td>
                                        <td>
                                            <input type="button" name="Delete" value="Delete" id="<?php echo $roww["sn"] . "__" . $lab_testno; ?>"
                                                class="btn btn-danger btn-xs consumable_del" data-target="#myModal5" />
                                        </td>
                                    </tr>
                                <?php
                                    $n++;
                                } ?>

                            </tbody>
                        </table>
                    <?php } else { ?>
                        <br><br><strong>No Consumables Added</strong><br><br>
                    <?php } ?>
                    <hr>

                    <input type="button" name="Add" value="Add" data-target=".slacker-modal" id="<?php echo $_POST["consumable_id"]; ?>" class="btn btn-success btn-xs add_consumables" /> | <input type="button" name="Cancel" value="Close" data-target=".slacker-modal" id="" class="btn btn-warning btn-xs close_consumables" />

                <?php  }  ?>


                <?php

                if (isset($_POST["device_id"])) {

                    $_POST["device_id"];
                    $device_id = $_POST["device_id"];
                    $part = explode("__", $device_id);
                    $device_no = $part['0'];
                    $device_name = $part['1'];

                    echo '<strong>Device Name: </strong>' . $device_name;
                    echo '<br>';
                    $stmt = $db->query("SELECT * FROM invsti_machine_settings where device_no='$device_no' order by sn");
                    if ($stmt->rowCount() > 0) { ?>

                        <div id="test_fields">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th data-toggle="true">No</th>
                                        <th data-toggle="true">Investigaton Name</th>
                                        <th data-toggle="true">Manage</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php
                                    $n = 1;
                                    while ($roww = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    ?>
                                        <tr>
                                            <td><?php echo $n; ?></td>
                                            <td><?php echo $roww['investigation_name']; ?></td>
                                            <td>
                                                <input type="button" name="Delete" value="Delete" id="<?php echo $roww["sn"] . '__' . $device_no . '__' .    $device_name; ?>"
                                                    class="btn btn-danger btn-xs assign_test_del" data-target="#myModal5" />
                                            </td>
                                        </tr>
                                    <?php
                                        $n++;
                                    } ?>

                                </tbody>
                            </table>
                        <?php } else { ?>
                            <br><br><strong>No Investigation List Added. Search & Select Investigation to add List .</strong><br><br>
                        <?php } ?>
                        <br>


                        <form method="POST" id="assign_device_invest_form">

                            <div class="form_sep">
                                <label for="reg_input_no" class="req">Investigation Name</label>
                                <select name="invest_name" class="chosen-select" style="width:350px;">
                                    <option selected="selected" value="">Search and Select ... </option>
                                    <?php
                                    $stmt = $db->query("SELECT test FROM lab_scan order by test");
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                        <option value="<?php echo $row["test"]; ?>"><?php echo $row["test"]; ?></option>
                                    <?php } ?>
                                </select>
                            </div>




                            <div class="form_sep">
                                <input type="submit" name="insert" id="insert" value="Insert" class="btn btn-success btn-xs" />
                                <input type="hidden" name="MM_update" value="add_device_invest" />
                            </div>
                            <input type="hidden" id="device_no" name="device_no" class="form-control" value="<?php echo $device_no; ?>" />
                            <input type="hidden" id="device_name" name="device_name" class="form-control" value="<?php echo $device_name; ?>" />

                        </form>








                    <?php  } ?>





                    <script>
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


                        $('#add_field_option_form').on("submit", function(event) {
                            event.preventDefault();

                            $.ajax({
                                url: "insert.php",
                                method: "POST",
                                data: $('#add_field_option_form').serialize(),
                                beforeSend: function() {
                                    $('#Save').val("Updating");
                                },
                                success: function(data) {
                                    $('#view_lab_option_modal').modal('hide');

                                    //$('#test_fields').html(data);  
                                },
                                complete: function() {
                                    $('#insert').val("Insert");
                                },
                                error: function(data) {

                                    alert("Oops...", "Something went wrong :(", "error");
                                    //swal({ title: 'Oops...!', text: 'Something went wrong ', type: 'error', timer: 500 })
                                }
                            });
                        });


                        $('#add_field_values_form').on("submit", function(event) {
                            event.preventDefault();

                            $.ajax({
                                url: "insert.php",
                                method: "POST",
                                data: $('#add_field_values_form').serialize(),
                                beforeSend: function() {
                                    $('#Save').val("Updating");
                                },
                                success: function(data) {
                                    $('#view_lab_values_modal').modal('hide');

                                    //$('#test_fields').html(data);  
                                },
                                complete: function() {
                                    $('#insert').val("Insert");
                                },
                                error: function(data) {

                                    alert("Oops...", "Something went wrong :(", "error");
                                    //swal({ title: 'Oops...!', text: 'Something went wrong ', type: 'error', timer: 500 })
                                }
                            });
                        });


                        $('#add_combos_form').on("submit", function(event) {
                            event.preventDefault();

                            $.ajax({
                                url: "insert.php",
                                method: "POST",
                                data: $('#add_combos_form').serialize(),
                                beforeSend: function() {
                                    $('#Add').val("Adding");
                                },
                                success: function(data) {
                                    //   

                                    var com_id = $('#com_id').val();
                                    var com_name = $('#com_name').val();
                                    var combos_id = com_id + '__' + com_name;

                                    $.ajax({
                                        url: "fetch_set.php",
                                        method: "POST",
                                        data: {
                                            combos_id: combos_id
                                        },
                                        success: function(data) {

                                            $('#view_combos_modal').modal('show');
                                            $('#view_combos_body').html(data);
                                        }
                                    });

                                    // $('#view_combos_modal').modal('hide');  
                                    //$('#test_fields').html(data);  
                                },
                                complete: function() {
                                    $('#Add').val("Saved");
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
                        });


                        $('#assign_device_invest_form').on("submit", function(event) {
                            event.preventDefault();

                            $.ajax({
                                url: "insert.php",
                                method: "POST",
                                data: $('#assign_device_invest_form').serialize(),
                                beforeSend: function() {
                                    $('#insert').val("Inserting");
                                },
                                success: function(data) {

                                    var device_no = $('#device_no').val();
                                    var device_name = $('#device_name').val();
                                    var device_no = device_no + '__' + device_name;

                                    $.ajax({
                                        url: "fetch_set.php",
                                        method: "POST",
                                        data: {
                                            device_id: device_no
                                        },
                                        success: function(data) {

                                            $('#assign_invest_modal').modal('show');
                                            $('#assign_invest_form').html(data);
                                        }
                                    });


                                },
                                complete: function() {
                                    $('#insert').val("Saved");
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
                        });
                    </script>