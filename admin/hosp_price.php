<?php

// Delete from special_package
if (isset($_GET['service_del'])) {
    $service_del = intval($_GET['service_del']);
    $stmt = $db->prepare("DELETE FROM special_package WHERE sn = ?");
    $stmt->execute([$service_del]);
}

// Toggle setauth ON or OFF
if (isset($_GET['setauth']) || isset($_GET['setauth_del'])) {
    if (isset($_GET['setauth'])) {
        $setauth_sn = intval($_GET['setauth']);
        $setauth_val = '1';
    } else {
        $setauth_sn = intval($_GET['setauth_del']);
        $setauth_val = '0';
    }

    $stmt = $db->prepare("UPDATE prices_table SET nusing_setauth = ? WHERE sn = ?");
    $stmt->execute([$setauth_val, $setauth_sn]);

    $sv = 1;
}

// Update HMO prices
if (isset($_POST['update_hmo_prices_btn'])) {

    try {
        // Enable PDO error mode
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $table_tariff_name = $_POST['table_tariff_name'];
        $total = isset($_POST['all_']) ? intval($_POST['all_']) - 1 : -1;

        if ($total >= 0 && isset($_POST['prices'], $_POST['stock_sn'], $_POST['hmos'])) {

            // Validate table name — very important for security
            $allowed_tables = array('hmo_bed_tariff', 'hmo_investigation_tariff', 'hmo_medical_tariff', 'hmo_stocks_tariff');
            if (!in_array($table_tariff_name, $allowed_tables)) {
                throw new Exception("Invalid table name: " . htmlspecialchars($table_tariff_name));
            }

            $stmt = $db->prepare("UPDATE $table_tariff_name SET price = ? WHERE stock_sn = ? AND hmo = ?");

            $update_count = 0;

            for ($i = 0; $i <= $total; $i++) {
                if (isset($_POST['prices'][$i], $_POST['stock_sn'][$i], $_POST['hmos'][$i])) {
                    $price = $_POST['prices'][$i];
                    $stock_sn = $_POST['stock_sn'][$i];
                    $hmo = $_POST['hmos'][$i];

                    if (!is_numeric($price)) {
                        throw new Exception("Invalid price at index $i: $price");
                    }

                    $stmt->execute([$price, $stock_sn, $hmo]);
                    $update_count += $stmt->rowCount();
                } else {
                    throw new Exception("Missing parameters at index $i");
                }
            }

            echo "<div class='alert alert-success'>
        ✅ Successfully updated {$update_count} record(s) in table .
      </div>";
        } else {
            throw new Exception("No valid data received for update. (all_ = $total)");
        }
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>
            ❌ <strong>Database Error:</strong> " . htmlspecialchars($e->getMessage()) . "
          </div>";
    } catch (Exception $e) {
        echo "<div class='alert alert-warning'>
            ⚠️ <strong>Script Error:</strong> " . htmlspecialchars($e->getMessage()) . "
          </div>";
    }
}


if (isset($_POST["Update_Amount_reg_amt"])) {
    if (isset($_POST['file_amount']) && is_numeric($_POST['file_amount'])) {
        $file_amount = $_POST['file_amount'];
        $file_amount_nhis = $_POST['file_amount_nhis'];

        $stmt = $db->prepare("UPDATE prices_table SET hosp_price = ?, ext_price = ?, nhis_price = ? WHERE item_service = 'New File'");
        $stmt->execute([$file_amount, $file_amount, $file_amount_nhis]);

        $sv = 1;
    }
}



if (isset($_POST["Save_manage_price"])) {
    // Determine table name based on table_type
    switch ($_POST["table_type"]) {
        case 'Pharmacy':
        case 'Nursing Consumable':
            $table = 'stock_table';
            break;
        case 'invest':
            $table = 'lab_scan';
            break;
        default:
            $table = 'prices_table';
            break;
    }

    // Determine coverage and insurance_type
    if ($_POST["NHIS_price"] > 0) {
        $coverage = "PRIVATE/NHIS";
        $insurance_type = empty($_POST['insurance_type']) ? '1' : $_POST['insurance_type'];
    } else {
        $coverage = "PRIVATE";
        $insurance_type = "-";
    }

    // Prepare the SQL statement
    $sql = "UPDATE $table SET coverage = ?, insurance_type = ?, hosp_price = ?, ext_price = ?, nhis_price = ? WHERE sn = ?";
    $stmt = $db->prepare($sql);

    // Execute the statement with bound parameters
    $success = $stmt->execute([
        $coverage,
        $insurance_type,
        $_POST["hosp_price"],
        $_POST["external_price"],
        $_POST["NHIS_price"],
        $_POST["item_sn"]
    ]);

    if ($success) {
        $sv = 1;
    }
}





if (isset($_POST["add_category"])) {

    $cat_type = $_POST['category_type'];
    $Name = ucwords($_POST['catetory_name']);
    $edit_id = $_POST['edit_id'];

    if (!empty($edit_id)) {

        // 🔴 STEP 1: GET OLD NAME
        $getOld = $db->prepare("SELECT Name FROM prices_table_category WHERE sn=? AND cat_type=?");
        $getOld->execute([$edit_id, $cat_type]);
        $oldRow = $getOld->fetch(PDO::FETCH_ASSOC);

        $oldName = $oldRow['Name'];

        // 🔵 STEP 2: UPDATE CATEGORY TABLE
        $stmt = $db->prepare("UPDATE prices_table_category SET Name=? WHERE sn=? AND cat_type=?");
        $success = $stmt->execute([$Name, $edit_id, $cat_type]);

        if ($success) {

            // 🟡 STEP 3: UPDATE prices_table
            $stmt1 = $db->prepare("UPDATE prices_table SET category=? WHERE category=?");
            $stmt1->execute([$Name, $oldName]);

            // 🟡 STEP 4: UPDATE patient_ap_services
            $stmt2 = $db->prepare("UPDATE patient_ap_services SET cat_type=? WHERE cat_type=?");
            $stmt2->execute([$Name, $oldName]);

            $sv = 2; // updated
        }
    } else {

        // 🟢 CHECK DUPLICATE
        $chk = $db->prepare("SELECT 1 FROM prices_table_category 
                             WHERE Name=? AND cat_type=?");
        $chk->execute([$Name, $cat_type]);

        if ($chk->rowCount() == 0) {

            // INSERT
            $stmt = $db->prepare("INSERT INTO prices_table_category (Name, cat_type) 
                                 VALUES (?, ?)");
            $success = $stmt->execute([$Name, $cat_type]);

            if ($success) {
                $sv = 1; // inserted
            }
        } else {
            $sv = 3; // duplicate
        }
    }
}


if (isset($_POST["save_item"])) {

    $category_item = $_POST['category_item'];
    $item_dept = $_POST['item_dept'];
    $table_name = $_POST['table_name'];
    $price_table = $_POST['price_table'];
    $special_package = $_POST['special_package'];
    $is_sugical_procedure = $_POST['is_sugical_procedure'];
    $item_name = ucwords($_POST['item_name']);

    $item_name = trim($item_name);


    $charactersToRemove = '*/=%",;:\'.'; // Add '.' to the list
    $pattern = '/[' . preg_quote($charactersToRemove, '/') . ']/';
    $item_name = preg_replace($pattern, '', $item_name);

    $file_amount = ($_POST['file_amount']);
    $consultation_dura = ($_POST['consultation_dura']);
    $specialty = ($_POST['specialty']);
    $sn = $_POST['sn'];
    $mode = $_POST['mode'];

    if ($mode == 'new') {


        $sqlCheck = "SELECT * FROM prices_table WHERE item_service = :item_service AND price_table = :price_table";
        $stmtCheck = $db->prepare($sqlCheck);
        $stmtCheck->bindValue(':item_service', $item_name, PDO::PARAM_STR);
        $stmtCheck->bindValue(':price_table', $table_name, PDO::PARAM_STR);
        $stmtCheck->execute();

        if ($stmtCheck->rowCount() == 0) {
            // Insert the record if it doesn't exist
            $sqlInsert = "INSERT INTO prices_table (item_service, category, dept, price_table, duration, file_amt, specialist_id,special_package, is_sugical_procedure) 
                          VALUES (:item_service, :category, :dept, :price_table, :duration, :file_amt, :specialist_id, :special_package, :is_sugical_procedure)";
            $stmtInsert = $db->prepare($sqlInsert);
            $stmtInsert->bindValue(':item_service', $item_name, PDO::PARAM_STR);
            $stmtInsert->bindValue(':category', $category_item, PDO::PARAM_STR);
            $stmtInsert->bindValue(':dept', $item_dept, PDO::PARAM_STR);
            $stmtInsert->bindValue(':price_table', $table_name, PDO::PARAM_STR);
            $stmtInsert->bindValue(':duration', $consultation_dura, PDO::PARAM_STR);
            $stmtInsert->bindValue(':file_amt', $file_amount, PDO::PARAM_STR);
            $stmtInsert->bindValue(':specialist_id', $specialty, PDO::PARAM_STR);
            $stmtInsert->bindValue(':special_package', $special_package, PDO::PARAM_STR);
            $stmtInsert->bindValue(':is_sugical_procedure', $is_sugical_procedure, PDO::PARAM_STR);
            $stmtInsert->execute();

            $sv = 1;
        } else { ?>
            <script>
                alert('Item Already Exist')
            </script>
<?php }
    } else {
        ///////////// update 	
        // special_package


        $stmt = $db->prepare("UPDATE prices_table SET item_service=:item_name, category=:category_item, dept=:item_dept, price_table=:price_table, duration=:consultation_dura, file_amt=:file_amount, specialist_id=:specialty, special_package=:special_package, is_sugical_procedure=:is_sugical_procedure WHERE sn=:sn");

        $stmt->bindParam(':item_name', $item_name);
        $stmt->bindParam(':category_item', $category_item);
        $stmt->bindParam(':item_dept', $item_dept);
        $stmt->bindParam(':price_table', $price_table);
        $stmt->bindParam(':consultation_dura', $consultation_dura);
        $stmt->bindParam(':file_amount', $file_amount);
        $stmt->bindParam(':specialty', $specialty);
        $stmt->bindParam(':special_package', $special_package);
        $stmt->bindParam(':is_sugical_procedure', $is_sugical_procedure);
        $stmt->bindParam(':sn', $sn);

        $stmt->execute();




        /// WE SOME TABLE TO CONFIRM 
        $sql = "UPDATE special_package SET 
                service_title = :service_title 
            WHERE service_id = :service_id 
            AND service_type = 'medical_services'";

        $stmt = $db->prepare($sql);

        $params = [
            ':service_title' => ucwords($item_name),
            ':service_id'    => $sn
        ];

        $stmt->execute($params);



        $sv = 1;

        ///=================================================================================			

    }
}

if (isset($_POST["del_item_submit"])) {
    $item_sn = $_POST["item_sn"];

    // Check if item exists in patient_ap_services
    $chk = $db->prepare("SELECT 1 FROM patient_ap_services WHERE drug_sn = ?");
    $chk->execute([$item_sn]);

    if ($chk->rowCount() == 0) {
        // Delete from prices_table
        $stmt = $db->prepare("DELETE FROM prices_table WHERE sn = ?");
        $stmt->execute([$item_sn]);

        // Delete from special_package
        $stmt = $db->prepare("DELETE FROM special_package WHERE service_table_id = ?");
        $stmt->execute([$item_sn]);
    } else {
        // Item exists in billing table, show alert
        echo '<script>alert("Invalid Delete! Item Already Existing In the Billing Table");</script>';
    }
}

// Delete category from prices_table_category
if (isset($_GET["cat"])) {
    $stmt = $db->prepare("DELETE FROM prices_table_category WHERE sn = ?");
    $stmt->execute([$_GET["cat"]]);
}
?>

<?php if (isset($_GET['price'])) {
    $price = $_GET['price'];
} ?>



<?php if ($price == '') { ?>

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Price Manager Dashboard</h5>
                </div>


                <div class="ibox-content" align="center">

                    <table width="100%" align="center">
                        <tr>
                            <td width="33%" align="center">
                                <a href="index.php?price=c" class="btn btn-app">
                                    <i class="fa fa-stethoscope"></i> Consultation
                                </a>
                                <ul style="list-style-type: none; padding: 0; margin: 5px 0 0;">
                                    <li>Specialist Price</li>
                                    <li>General Consultation Price</li>
                                </ul>
                            </td>

                            <td width="33%" align="center">
                                <a href="index.php?price=m" class="btn btn-app">
                                    <i class="fa fa-user-md"></i> Medical Services
                                </a>
                                <ul style="list-style-type: none; padding: 0; margin: 5px 0 0;">
                                    <li>Operations/Surgeries</li>
                                    <li>Transplant</li>
                                    <li>Dialysis</li>
                                </ul>
                            </td>

                            <td width="33%" align="center">
                                <a href="index.php?price=n" class="btn btn-app">
                                    <i class="fa fa-bitbucket"></i> Nursing
                                </a>
                                <ul style="list-style-type: none; padding: 0; margin: 5px 0 0;">
                                    <li>Nursing Services</li>
                                    <li>Nursing Care</li>
                                </ul>
                            </td>
                        </tr>

                        <tr>
                            <td width="33%" align="center">
                                <a href="index.php?price=o" class="btn btn-app">
                                    <i class="fa fa-tasks"></i> Other Services
                                </a>
                                <ul style="list-style-type: none; padding: 0; margin: 5px 0 0;">
                                    <li>Front Desk</li>
                                </ul>
                            </td>

                            <td width="33%" align="center">
                                <a href="index.php?updown" class="btn btn-app">
                                    <i class="fa fa-tasks"></i> Download & Upload
                                </a>
                            </td>

                            <td width="33%" align="center">
                                <a href="index.php?price=iv" class="btn btn-app">
                                    <i class="fa fa-flask"></i> Investigations
                                </a>
                                <ul style="list-style-type: none; padding: 0; margin: 5px 0 0;">
                                    <li>Laboratory</li>
                                    <li>Radiology</li>
                                </ul>
                            </td>
                        </tr>
                    </table>

                    <div style="margin-top: 20px;">
                        <a href="index.php?Specialist" class="btn btn-app">
                            <i class="fa fa-user-md"></i> Specialist & Services
                        </a>
                        <ul style="list-style-type: none; padding: 0; margin: 5px 0 0;">
                            <li>Specialist Mapping</li>
                            <li>Front Desk</li>
                        </ul>
                    </div>

                </div>


            </div>
        </div>

    </div>



<?php
} else {

    if ($price == 'c') {
        $title = "Consultation";
        $table_type = 'price_table';
        $dept_srch = "(department_type='medical services' )";
    } elseif ($price == 'm') {
        $title = "Medical Services";
        $table_type = 'price_table';
        $dept_srch = "(department_type='medical services' )";
    } elseif ($price == 'n') {
        $title = "Nursing Services";
        $table_type = 'price_table';
        $dept_srch = "(department_type='medical services' )";
    } elseif ($price == 'o') {
        $title = "Other Services";
        $table_type = 'price_table';
        $dept_srch = "(department_type='other services' )";
    } elseif ($price == 'iv') {
        $title = "Investigations Services";
        $table_type = 'invest';
        $dept_srch = "(department_type='Laboratory' or department_type='Radiology' )";
    }
?>

    <div class="row">



        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <h5><?php echo $title; ?> Setup List</h5>


                    <div class="ibox-tools">

                        <?php if ($table_type == 'price_table') { ?>
                            <input type="button" name="edit" value="Add New <?php echo $title; ?> " data-target="#myModal5" id="<?php echo $title . '__' . $price . '__' . 'new' . '__new'; ?>" class="btn btn-success btn-xs add_new_item" style="font-size: 14px; color: white; " />
                            &nbsp;&nbsp; | &nbsp;&nbsp;

                            <input type="button" name="edit" value="Add Category" data-target="#myModal5" id="<?php echo $title . '__' . $price; ?>" class="btn btn-info btn-xs add_new_category" style="font-size: 14px; color: white; " />
                        <?php } elseif ($table_type == 'invest') { ?>

                            <strong style="color:#900">To Add/Edit/Delete Investigations. Goto Rad/Lab Module </strong>
                        <?php } else { ?>

                            <strong style="color:#900">To Add/Edit/Delete. Goto Stock Manager </strong>&nbsp; <a href="index.php?stock"> [ Stock Manager ] </a>
                        <?php } ?>
                    </div>
                </div>


                <div class="ibox-content">


                    <br>
                    <div class="row">
                        <form action="index.php?price=<?php echo $price; ?>" method="POST">

                            <div class="col-sm-3">

                                <div class="form_sep">
                                    <label for="reg_input_no" class="">Department</label>
                                    <select name="Department" id="Department" class="form-control" data-required="true">

                                        <option selected="selected" value="">Select ...</option>
                                        <?php
                                        $stmt = $db->query(sprintf("SELECT * FROM department WHERE  $dept_srch"));
                                        while ($row_rstdepartment = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                            <option value="<?php echo $row_rstdepartment['sn']; ?>"><?php echo $row_rstdepartment["department"]; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-3">

                                <div class="form_sep">
                                    <label for="reg_input_no" class="">Category</label>
                                    <select name="Cat" id="Cat" class="form-control" data-required="true">

                                        <option selected="selected" value="">Select ...</option>
                                        <?php
                                        $stmt = $db->query(sprintf("SELECT * FROM prices_table_category WHERE cat_type='$title'"));
                                        while ($row_rstdepartment = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                            <option value="<?php echo $row_rstdepartment['Name']; ?>"><?php echo $row_rstdepartment["Name"]; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>


                            </div>

                            <div class="col-sm-3">


                                <div class="form_sep">
                                    <label for="reg_input_no" class="">Coverage</label>
                                    <select name="coverage" id="coverage" class="form-control" data-required="true">

                                        <option selected="selected" value="">Select ...</option>
                                        <option value="n">NHIS</option>
                                        <option value="p">Others (Private Services)</option>
                                    </select>
                                </div>

                            </div>

                            <div class="col-sm-3">
                                <label for="reg_input_no" class="">.</label><br>
                                <input type="submit" name="apply_submit" value="Apply" class="btn btn-primary btn-sm" />

                                &nbsp;&nbsp; | &nbsp;&nbsp;





                            </div>
                            <input type="hidden" name="table_name" value="<?php echo $title; ?>" />
                            <input type="hidden" name="table_type" value="<?php echo $table_type; ?>" />

                        </form>


                    </div>
                    <?php if (isset($_GET['price']) and $_GET['price'] == 'c') { ?>
                        <br>
                        <?php $stmt = $db->query("SELECT hosp_price, nhis_price FROM prices_table WHERE item_service='New File' and hosp_price>0");
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        ?>
                        <table align="center">
                            <tr>
                                <td>
                                    <div style="font-size: 25px; " align="center">Registration / File Amount:<br>
                                        <?php echo 'GENERAL AMOUNT ' . number_format($row['hosp_price']); ?> &nbsp;: &nbsp; NHIS <?php echo number_format($row['nhis_price']); ?> </div>
                                </td>
                                <td> &nbsp;&nbsp;
                                    <input type="button" name="" value="Edit Fee" data-toggle="modal" data-target="#myModal5" id="1" class="btn btn-danger btn-sm manage_registration_price" />
                                </td>
                            </tr>
                        </table>


                    <?php } ?>



                    <?php

                    if ($table_type == 'price_table') {

                        if (isset($_POST["apply_submit"])) {

                            $table_name = $_POST["table_name"];
                            $table_type = $_POST["table_type"];

                            if (isset($_POST["Department"]) and $_POST["Department"] != '') {
                                $dept = $_POST["Department"];
                                $search_part = "price_table='$table_name' and dept='$dept'";
                            } elseif (isset($_POST["Cat"]) and $_POST["Cat"] != '') {
                                $Cat = $_POST["Cat"];
                                $search_part = "price_table='$table_name' and category='$Cat'";
                            } elseif (isset($_POST["coverage"]) and $_POST["coverage"] != '') {
                                $coverage = $_POST["coverage"];
                                if ($coverage == 'n') {
                                    $search_part = "price_table='$table_name' and coverage='PRIVATE/NHIS'";
                                } else {
                                    $search_part = "price_table='$table_name' and coverage='PRIVATE'";
                                }
                            } else {
                                $search_part = "price_table='$table_name'";
                            }
                            $stmt = $db->query(sprintf("SELECT ptbl.*, dept.department FROM prices_table as ptbl inner join department as dept on dept.sn=ptbl.dept where $search_part order by ptbl.sn DESC"));
                        } else {
                            $stmt = $db->query(sprintf("SELECT ptbl.*, dept.department FROM prices_table as ptbl inner join department as dept on dept.sn=ptbl.dept where price_table='$title' order by ptbl.sn DESC"));
                        }

                        if ($stmt->rowCount() > 0) { ?>

                            <div id="refresh">
                                <table class="table table-striped table-bordered table-hover dataTables-example" style="font-size: 14px;">


                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Item/Service</th>
                                            <th>Category</th>
                                            <th>Department</th>
                                            <th>Coverage</th>
                                            <th>INS</th>
                                            <th>HMO Price</th>
                                            <th>Private Price</th>
                                            <!-- <th>NHIS<br>Price</th>-->
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <?php
                                        $n = 1;
                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        ?>
                                            <tr>
                                                <td><?php echo $n; //['sn'];
                                                    $field = $row['sn']; ?></td>
                                                <td><?php echo $row['item_service']; ?>
                                                    <br>
                                                    <input type="button" name="delete_item" value="Delete" data-toggle="modal" data-target="#myModal5" id="<?php echo $row["sn"] . '__' . $price; ?>" class="btn btn-danger btn-xs confirm_item_del" />
                                                    &nbsp;|&nbsp;
                                                    <input type="button" name="edit_lab" value="Edit" data-toggle="modal" data-target="#myModal5" id="<?php echo $title . '__' . $price . '__' . $row["sn"] . '__edit'; ?>" class="btn btn-warning btn-xs add_new_item" />
                                                    &nbsp;|&nbsp;
                                                    <input type="button" name="Manage_price" value="Edit Price" data-toggle="modal" data-target="#myModal5" id="<?php echo $row["sn"] . '__' . $row['item_service']; ?>" class="btn btn-info btn-xs manage_price" />
                                                    &nbsp;|&nbsp;
                                                    <input type="button" name="edit" value="Edit Tariff" data-target="#myModal5" id="<?php echo $row["sn"]; ?>" class="btn btn-warning btn-xs edit_price_tariff" />
                                                    <?php

                                                    ///setauth_del
                                                    if ($price == 'n' and $row['nusing_setauth'] == 0) { ?>
                                                        &nbsp;|&nbsp;
                                                        <a href="index.php?price=n&setauth=<?php echo $row['sn'] ?>" onclick="return confirm('Are you sure you want to Set this item as authomatic Admission Option');">Set/Auth</a>
                                                    <?php } elseif ($price == 'n' and $row['nusing_setauth'] == 1) { ?>
                                                        &nbsp;|&nbsp;
                                                        <a href="index.php?price=n&setauth_del=<?php echo $row['sn'] ?>"><strong style="color: red; ">Del/Auth</strong></a>

                                                    <?php  } ?>

                                                </td>
                                                <td><?php echo $row['category']; ?>

                                                    <?php if ($row['special_package'] == '1') { ?> <br>
                                                        <input type="button" name="edit" value="Add Services" data-target="#myModal5" id="<?php echo $row["sn"]; ?>" class="btn btn-warning btn-xs add_service_id_package" />
                                                    <?php  } ?>

                                                </td>
                                                <td><?php echo $row['department']; ?></td>
                                                <td><?php echo $row['coverage']; ?></td>
                                                <td><?php if ($row['insurance_type'] == 1) {
                                                        echo 'PRI';
                                                    } elseif ($row['insurance_type'] == 2) {
                                                        echo 'SEC';
                                                    } else {
                                                        echo '-';
                                                    } ?></td>
                                                <td><?php echo $row['hosp_price']; ?></td>
                                                <td><?php echo $row['ext_price']; ?></td>

                                            </tr>
                                        <?php
                                            $n++;
                                        } ?>

                                    </tbody>
                                </table>

                        <?php } else {
                            echo ' <br>No Records Found';
                        }
                    }

                        ?>



                        <?php


                        if ($table_type == 'invest') {

                            if (isset($_POST["apply_submit"])) {

                                //$table_name=$_POST["table_name"];
                                $table_type = $_POST["table_type"];


                                if (isset($_POST["Department"]) and $_POST["Department"] != '') {
                                    $dept = $_POST["Department"];
                                    $search_part = " dept='$dept'";
                                } elseif (isset($_POST["Cat"]) and $_POST["Cat"] != '') {
                                    $Cat = $_POST["Cat"];
                                    $search_part = " category='$Cat'";
                                } elseif (isset($_POST["coverage"]) and $_POST["coverage"] != '') {
                                    $coverage = $_POST["coverage"];
                                    if ($coverage == 'n') {
                                        $search_part = "coverage='PRIVATE/NHIS'";
                                    } else {
                                        $search_part = "coverage='PRIVATE'";
                                    }
                                } else {
                                    $search_part = "";
                                }



                                $stmt = $db->query(sprintf("SELECT ptbl.*, dept.department FROM lab_scan as ptbl inner join department as dept on dept.sn=ptbl.dept where $search_part order by sn DESC"));
                                ///echo 'wewewe';
                            } else {
                                $stmt = $db->query(sprintf("SELECT ptbl.*, dept.department FROM lab_scan as ptbl inner join department as dept on dept.sn=ptbl.dept order by sn DESC"));
                            }

                            if ($stmt->rowCount() > 0) { ?>

                                <div id="refresh">
                                    <table class="table table-striped table-bordered table-hover dataTables-example" style="font-size: 14px; ">


                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Item/Service</th>
                                                <th>Category</th>
                                                <th>Department</th>
                                                <th>Coverage</th>
                                                <th>INS</th>
                                                <th>HMO Price</th>
                                                <th>Private Price</th>

                                            </tr>
                                        </thead>
                                        <tbody>

                                            <?php
                                            $n = 1;
                                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            ?>
                                                <tr>
                                                    <td><?php echo $row['sn'];
                                                        $field = $row['sn']; ?></td>
                                                    <td><?php echo $row['test']; ?><br>
                                                        <input type="button" name="Manage_price" value="Edit Price" data-toggle="modal" data-target="#myModal5" id="<?php echo $row["sn"] . '__' . $row['item_service']; ?>" class="btn btn-info btn-xs manage_price_invest" />
                                                        &nbsp;|&nbsp;
                                                        <input type="button" name="edit" value="Edit Tariff" data-target="#myModal5" id="<?php echo $row["sn"]; ?>" class="btn btn-warning btn-xs edit_investigation_tariff" />

                                                    </td>
                                                    <td><?php echo $row['category']; ?></td>
                                                    <td><?php echo $row['department']; ?></td>
                                                    <td><?php echo $row['coverage']; ?></td>
                                                    <td><?php if ($row['insurance_type'] == 1) {
                                                            echo 'PRI';
                                                        } elseif ($row['insurance_type'] == 2) {
                                                            echo 'SEC';
                                                        } else {
                                                            echo '-';
                                                        } ?></td>
                                                    <td><?php echo $row['hosp_price']; ?></td>
                                                    <td><?php echo $row['ext_price']; ?></td>


                                                </tr>
                                            <?php
                                                $n++;
                                            } ?>

                                        </tbody>
                                    </table>

                            <?php } else {
                                echo ' <br>No Records Found';
                            }
                        }
                            ?>







                                </div>
                            </div>

                </div>


            </div>

        <?php } ?>


        <div class="modal inmodal fade" id="add_new_item_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title" id=""></h4>
                    </div>
                </div>
                <div class="modal-body" id="add_new_item_form">

                </div>
            </div>
        </div>

        <div class="modal inmodal fade" id="edit_price_tariff_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title" id=""></h4>
                    </div>
                </div>
                <div class="modal-body" id="edit_price_tariff_body">

                </div>
            </div>
        </div>


        <div class="modal inmodal fade" id="confirm_item_serv_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title" id=""></h4>
                    </div>
                    <div class="modal-body" id="confirm_item_serv_form">
                    </div>

                </div>
            </div>
        </div>



        <div class="modal inmodal fade" id="add_new_category_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title" id=""></h4>
                    </div>
                    <div class="modal-body" id="add_new_category_form">
                    </div>

                </div>
            </div>
        </div>


        <div class="modal inmodal fade" id="manage_reg_price_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title" id=""></h4>
                    </div>
                    <div class="modal-body" id="manage_reg_price_form">
                    </div>

                </div>
            </div>
        </div>



        <div class="modal inmodal fade" id="manage_price_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title" id=""></h4>
                    </div>

                    <div class="modal-body">

                        <form method="POST" id="" action="index.php?price=<?php echo $price; ?>">

                            <div class="form_sep">
                                <label for="reg_input_no" class="">Item/Service #</label>
                                <input type="text" id="item_sn" name="item_sn" class="form-control" readonly>
                            </div>

                            <div class="form_sep">
                                <label for="reg_input_no" class="">Item/Service Name</label>
                                <input type="text" id="item_service_name" name="item_service_name" class="form-control" readonly>
                            </div>

                            <div class="form_sep">
                                <label for="reg_input_no" class="req">HMO Price</label>
                                <input type="number" id="hosp_price" name="hosp_price" class="form-control" required>
                            </div>

                            <div class="form_sep">
                                <label for="reg_input_no" class="req">Private Patient Price</label>
                                <input type="number" id="external_price" name="external_price" class="form-control" required>
                            </div>

                            <div class="form_sep">
                                <label for="reg_input_no" class="">NHIS Price (Enter Price if Covered by NHIS or Skip it)</label>
                                <input type="number" id="NHIS_price" name="NHIS_price" class="form-control">
                            </div>

                            <div class="form_sep">
                                <label for="reg_input_no" class="">NHIS Care Service Type (Covarage Extension)</label>
                                <select name="insurance_type" id="insurance_type" class="form-control">
                                    <option selected="selected" value="">Select...</option>

                                    <option value="1">Primary Care (Without Authorization Code)</option>
                                    <option value="2">Secondary Care (Authorization Code Required)</option>
                                </select>
                            </div>
                            <div class="form_sep"></div>


                            <div class="pull-left">
                                <input type="submit" name="Save_manage_price" id="Save_manage_price" value="Save Price" class="btn btn-success" />
                            </div>


                            <div class="pull-right">
                                <button class="btn btn-danger" data-dismiss="modal">Close</button>
                            </div>

                            <input type="hidden" name="table_type" id="table_type" />
                        </form>


                    </div>

                </div>
            </div>
        </div>

        <div class="modal inmodal fade" id="add_service_id_package_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title" id="">HMO/Corporate prices</h4>
                    </div>
                    <div class="modal-body" id="add_service_id_package_body">
                    </div>


                </div>
            </div>
        </div>