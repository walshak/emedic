<?php
if (isset($_GET['grp_reset'])) {
    $grp_reset = $_GET['grp_reset'];

    // Use prepared statement to prevent SQL injection
    $stmt = $db->prepare("SELECT hospital_no FROM enrollee WHERE hmo_no = :grp_reset");
    $stmt->bindParam(':grp_reset', $grp_reset);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        while ($rowx = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $hosp_num = $rowx['hospital_no'];
            // Update discount status to 'Stop'
            $update_stmt = $db->prepare("
                    UPDATE patient_discount 
                    SET status = 'Stop' 
                    WHERE individual_group_no = :hosp_num");
            $update_stmt->bindParam(':hosp_num', $hosp_num);
            $update_stmt->execute();
        }
    }
}


if (isset($_GET['del'])) {
    $grp = $_GET['grp'];
    $update = "DELETE FROM patient_discount WHERE individual_group_no='$grp'";
    $db->exec($update);
    $update = "DELETE FROM patient_discount_services WHERE individual_group_no='$grp'";
    $db->exec($update);
}

if (isset($_GET['dsc'])) {
    $grp = $_GET['grp'];
    $add_data = "UPDATE patient_discount SET status='Stop' WHERE individual_group_no='$grp'";
    $db->exec($add_data);
}

if (isset($_GET['cont'])) {
    $grp = $_GET['grp'];
    $add_data = "UPDATE patient_discount SET status='On-going' WHERE individual_group_no='$grp'";
    $db->exec($add_data);
}


if (isset($_GET['change_specify'])) {
    $grp = $_GET['grp'];

    try {
        $db->beginTransaction();

        $add_data = "UPDATE patient_discount SET apply_to_services='Specify' WHERE individual_group_no=:individual_group_no";
        $stmt = $db->prepare($add_data);
        $stmt->bindParam(':individual_group_no', $grp);
        $stmt->execute();

        $db->commit();
    } catch (PDOException $e) {
        $db->rollBack();
        echo "Error: " . $e->getMessage();
    }
}



if (isset($_GET['change_all'])) {
    $grp = $_GET['grp'];

    try {
        $db->beginTransaction();

        $add_data = "UPDATE patient_discount SET apply_to_services='All Services' WHERE individual_group_no=:individual_group_no";
        $stmt = $db->prepare($add_data);
        $stmt->bindParam(':individual_group_no', $grp);
        $stmt->execute();

        $update = "DELETE FROM patient_discount_services WHERE individual_group_no=:individual_group_no";
        $stmt = $db->prepare($update);
        $stmt->bindParam(':individual_group_no', $grp);
        $stmt->execute();

        $db->commit();
    } catch (PDOException $e) {
        $db->rollBack();
        echo "Error: " . $e->getMessage();
    }
}


if (isset($_GET['plyr'])) {
    $plyr = $_GET['plyr'];
    $add_data = "UPDATE pharm_ext SET discount_set='1' WHERE referral='$plyr'";
    $db->exec($add_data);
}

if (isset($_GET['plyf'])) {
    $plyf = $_GET['plyf'];
    $add_data = "UPDATE enrollee SET discount_set='1' WHERE hmo_no='$plyf'";
    $db->exec($add_data);
}

if (isset($_POST["save_discount"])) {


    if (isset($_POST["entity"]) and $_POST["entity"] != '') {

        if ($_POST["entity"] == 'Family' and $_POST["coop_fam"] != '') {
            $entity_name = $_POST["coop_fam"];
            $error = 0;
        } elseif ($_POST["entity"] == 'Referral' and $_POST["Referred"] != '') {
            $entity_name = $_POST["Referred"];
            $error = 0;
        } else {
            $error = 1;
            $error_title = 'Select Referral or Family/Corporate before you continue ... ';
        }
    } else {
        $error = 1;
        $error_title = 'Select Entity or Group before you continue ... ';
    }


    if ($error == 0) {
        ///////////// test for error ///////////////////////
        $part = explode("__", $entity_name);
        $number = $part[0];
        $name = $part[1];


        ////// check if one or member in this family was given sort of disount or charge	///////////////  FAMILY ----------------	
        if ($_POST["entity"] == 'Family') {
            $stmt = $db->query("SELECT hospital_no FROM enrollee where hmo_no='$number'");
            if ($stmt->rowCount() > 0) {

                $seen = 0;
                while ($rowx = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    /// check discount table
                    $hosp_num = $rowx['hospital_no'];
                    $stmt_chk = $db->query("SELECT individual_group_no FROM patient_discount where individual_group_no='$hosp_num' and status='On-going'");
                    if ($stmt_chk->rowCount() > 0) {
                        $seen = 1;
                        $error_title = 'Sorry you can not set discount/charge because one or more member(s) in this entity have been set to this action !!!';
                    } else {
                        /////// second checking ...................................
                        /*
                        $stmt_chk = $db->query("SELECT individual_group_no FROM patient_discount_services where individual_group_no='$hosp_num' and status='0'");
                        if ($stmt_chk->rowCount() > 0) {     
                            $seen = 1;
                            $error_title = 'Sorry you can not set discount/charge because one or more member(s) in this entity have been set to this action';
                        }

                        */
                        /////////////////// end second checking-------------------------------------------------------------------------
                    }
                    ///// ------------------------------------- end of system --------------------------------------
                }
                ///////////////////// looopiingggggggggggggggggggggggggggggggg --------------------------------------

            }
        }
        /////////////////////////  //// ---------------  end  family   -----------------------------------------------------


        ////// check if one or member in this family was given sort of disount or charge	///////////////  FAMILY ----------------	
        if ($_POST["entity"] == 'Referral') {

            $stmt = $db->query("SELECT transc_code FROM pharm_ext where referral='$number'");
            if ($stmt->rowCount() > 0) {
                //echo 'ide here'; ///
                $seen = 0;
                while ($rowx = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    /// check discount table
                    $hosp_num = $rowx['transc_code'];
                    $stmt_chk = $db->query("SELECT individual_group_no FROM patient_discount where individual_group_no='$hosp_num' and status='On-going'");
                    if ($stmt_chk->rowCount() > 0) {
                        $seen = 1;
                        $error_title = 'Sorry you can not set discount/charge because one or more member(s) in this entity have been set to this action';
                    } else {
                        /////// second checking ...................................
                        $stmt_chk = $db->query("SELECT individual_group_no FROM patient_discount_services where individual_group_no='$hosp_num' and status='0'");
                        if ($stmt_chk->rowCount() > 0) {
                            $seen = 1;
                            $error_title = 'Sorry you can not set discount/charge because one or more member(s) in this entity have been set to this action';
                        }
                        /////////////////// end second checking-------------------------------------------------------------------------
                    }
                    ///// ------------------------------------- end of system --------------------------------------
                }
                ///////////////////// looopiingggggggggggggggggggggggggggggggg --------------------------------------

            }
        }
        ////////////////// end of REFERRALS ''''''''''''''''''''''''''''''  			
    }

    ///////////// end test for error ///////////////////////	

    if ($seen == 1) {
        ///echo $error_title;
    } elseif ($error == 1) {
        //	echo $error_title;

    } else {


        $setdate = date("Y-m-d");
        if ($_POST['service_type'] == 'All Services') {
            $status = 'On-going';
        } else {
            $status = 'Pending';
        }

        if ($_POST['how_long'] == 'Specify' and ($_POST['specify_count'] == '' or $_POST['specify_count'] == '0')) {
            $specify_count = 1;
        } else {
            $specify_count = $_POST['specify_count'];
        }

        $history = ' - ' . $_POST['discount_charge'] . ': ' . $_POST['mode'] . ':' . $_POST['mode_value'] . '; Duration: ' . $_POST['how_long'] . '; Count: ' . $specify_count . '; Service Type: ' . $_POST['service_type'] . ';  Setup By: ' . $_SESSION['fullname'] . '; Dated: ' . $setdate;

        //	$emr=$_POST['emr'];
        $stmt = $db->prepare("SELECT * FROM patient_discount where individual_group_no=:individual_group_no");
        $stmt->bindParam(':individual_group_no', $number);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            $group = "group";
            $empty = "";

            $add_data = "INSERT INTO patient_discount(individual_group,individual_group_no,individual_group_name,discount_charge,percentage_flat,percentage_flat_value,duration,specify_count,apply_to_services,services_items,count_bal,setby,history,status_date,status,referal_or_famcom) VALUES (:individual_group,:individual_group_no,:individual_group_name,:discount_charge,:percentage_flat,:percentage_flat_value,:duration,:specify_count,:apply_to_services,:services_items,:count_bal,:setby,:history,:status_date,:status,:referal_or_famcom)";
            $stmt = $db->prepare($add_data);
            $stmt->bindParam(':individual_group', $group, PDO::PARAM_STR);
            $stmt->bindParam(':individual_group_no', $number, PDO::PARAM_STR);
            $stmt->bindParam(':individual_group_name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':discount_charge', $_POST['discount_charge'], PDO::PARAM_STR);
            $stmt->bindParam(':percentage_flat', $_POST['mode'], PDO::PARAM_STR);
            $stmt->bindParam(':percentage_flat_value', $_POST['mode_value'], PDO::PARAM_STR);
            $stmt->bindParam(':duration', $_POST['how_long'], PDO::PARAM_STR);
            $stmt->bindParam(':specify_count', $_POST['specify_count'], PDO::PARAM_STR);
            $stmt->bindParam(':apply_to_services', $_POST['service_type'], PDO::PARAM_STR);
            $stmt->bindParam(':services_items', $empty, PDO::PARAM_STR);
            $stmt->bindParam(':count_bal', $specify_count, PDO::PARAM_STR);
            $stmt->bindParam(':setby', $_SESSION['fullname'], PDO::PARAM_STR);
            $stmt->bindParam(':history', $history, PDO::PARAM_STR);
            $stmt->bindParam(':status_date', $setdate, PDO::PARAM_STR);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            $stmt->bindParam(':referal_or_famcom', $_POST["entity"], PDO::PARAM_STR);
            $stmt->execute();

            ///////////// lock discount 
            if ($_POST["entity"] == 'Family') {
                $stmt = $db->prepare("UPDATE enrollee SET discount_set='1' WHERE hmo_no=:hmo_no");
                $stmt->bindParam(':hmo_no', $number);
                $stmt->execute();
            } elseif ($_POST["entity"] == 'Referral') {
                $stmt = $db->prepare("UPDATE pharm_ext SET discount_set='1' WHERE referral=:referral");
                $stmt->bindParam(':referral', $number);
                $stmt->execute();
            } else {
                $stmt = $db->prepare("UPDATE enrollee SET discount_set='1' WHERE hospital_no=:hospital_no");
                $stmt->bindParam(':hospital_no', $number);
                $stmt->execute();
            }
        }
    }
}


?>



<div class="row">
    <div class="col-lg-4">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>Discount/Charges Settings</h5>
            </div>

            <div class="ibox-content">

                <strong style="color:#F00">What to do here ... </strong>
                <p>Give discount or add charges to family, corporate, referrals ... </p>
                <br>

                <form method="POST" action="index.php?discount">

                    <div class="form_sep">
                        <label for="reg_input_no" class="req">Select Entity/Group</label>
                        <select name="entity" id="entity" class="form-control" required>
                            <option selected="selected" value="">Select ...</option>
                            <option value="Family">Family/Corporate</option>
                            <option value="Referral">Referral</option>

                        </select>
                    </div>


                    <div class="form_sep" id="ref">
                        <label for="reg_input_no" class="req">Referred by</label>
                        <select name="Referred" id="Referred" class="form-control">
                            <option selected="selected" value="">Select Referring entity...</option>
                            <?php $stmt = $db->query("SELECT * FROM referrals");
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                <option value="<?php echo $row["sn"] . '__' . $row["name"]; ?>"><?php echo  $row["name"]; ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form_sep" id="fam">
                        <label for="reg_input_no" class="req">Corporate/Family Folder</label>
                        <select name="coop_fam" id="coop_fam" class="form-control">
                            <option selected="selected" value="">Select Corporate/Family Folder</option>
                            <?php $stmt = $db->query("SELECT * FROM insurance_tbl WHERE insurance_type='Family' or insurance_type='Corporate' ORDER BY  insurance_name");
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                <option value="<?php echo $row["insurance_no"] . '__' . $row["insurance_name"]; ?>"><?php echo  $row["insurance_name"]; ?></option>
                            <?php } ?>
                        </select>
                    </div>



                    <div class="form_sep">
                        <label for="reg_input_no" class="req">Select Discount or Charge</label>
                        <select name="discount_charge" id="discount_charge" class="form-control" required>
                            <option selected="selected" value="">Select ...</option>
                            <option value="Discount">Discount</option>
                            <option value="Charge">Charge</option>
                        </select>
                    </div>

                    <div class="form_sep">
                        <label for="reg_input_no" class="req">Mode of Discount/Charge</label>
                        <select name="mode" id="mode" class="form-control" required>
                            <option selected="selected" value="">Select ...</option>
                            <option value="Percentage">Percentage</option>
                            <option value="Flat">Flat</option>
                        </select>
                    </div>

                    <div class="form_sep">
                        <label for="reg_input_no" class="req">Enter Flat or Percentage Value</label>
                        <input type="text" id="mode_value" name="mode_value" class="form-control" maxlength="12" required>
                    </div>

                    <div class="form_sep">
                        <label for="reg_input_no" class="req">How long (Duration)</label>
                        <select name="how_long" id="how_long" class="form-control" required>
                            <option selected="selected" value="">Select ...</option>
                            <option value="Once">Once</option>
                            <option value="Limited">Limited</option>
                            <option value="Always">Always</option>
                        </select>
                    </div>

                    <div class="form_sep">
                        <label for="reg_input_no" class="">If Limited Selected Above. Enter Count Here</label>
                        <input type="number" id="specify_count" name="specify_count" class="form-control">
                    </div>

                    <div class="form_sep">
                        <label for="reg_input_no" class="req">Apply Discount/Charge Hospital Services</label>
                        <select name="service_type" id="service_type" class="form-control" required>
                            <option selected="selected" value="">Select ...</option>
                            <option value="All Services">All Service in the hospital</option>
                            <option value="specify">Selected Service in the hospital (Specify Later)</option>
                        </select>
                    </div>

                    <div class="form_sep">
                        <button
                            class="btn btn-primary btn-sm"
                            type="submit"
                            name="save_discount"
                            onclick="return confirm('Are you sure you want to save this discount? It can not be deleted, only discontinued');">
                            Save
                        </button>
                    </div>
                    <input type="hidden" name="emr" id="emr" value="<?php echo $emr; ?>" />
                    <input type="hidden" name="patient_name" id="patient_name" value="<?php echo $patient_name; ?>" />
                    <input type="hidden" name="MM_update" value="add_discount_insert" />

                </form>

            </div>
        </div>
    </div>


    <div class="col-lg-8">
        <div class="ibox float-e-margins">
            <div class="ibox-title">

                <h5>Discount and Additional Charges</h5>
            </div>
            <div class="ibox-content">

                <a rel="" href="index.php" class="btn btn-danger btn btn-sm"><i class="fa fa-times"></i>&nbsp;Close</a>
                <hr>
                <?php if ($seen == 1) { ?>
                    <div class="alert alert-danger"><?php echo $error_title; ?>
                        <br><a href="index.php?discount&grp_reset=<?php echo $number . '&dsc'; ?>" class="btn btn-danger btn-xs">Cancel / Stop </a> &nbsp;
                    </div>
                <?php    } elseif ($error == 1) { ?>
                    <div class="alert alert-danger"><?php echo $error_title; ?></div>
                <?php } ?>


                <?php
                // $stmt = $db->query("SELECT dsc.*, i.insurance_name FROM patient_discount dsc 
                // INNER JOIN insurance_tbl AS i ON i.insurance_no = dsc.individual_group_no  WHERE individual_group='group'");

                $stmt = $db->query("
    SELECT 
        dsc.*,
        i.insurance_name,
        r.name AS referral_name
    FROM patient_discount dsc
    LEFT JOIN insurance_tbl i 
        ON i.insurance_no = dsc.individual_group_no 
        AND dsc.referal_or_famcom = 'Family'
    LEFT JOIN referrals r
        ON r.sn = dsc.individual_group_no
        AND dsc.referal_or_famcom = 'Referral'
        WHERE individual_group='group'
");

                ?>

                <?php if ($stmt->rowCount() > 0) { ?>

                    <table class="table table-striped table-bordered table-hover dataTables-example">
                        <thead>
                            <tr>
                                <th data-toggle="true">Details</th>
                                <th data-toggle="true">Count/Balance</th>
                                <th data-toggle="true">Set By</th>
                                <th data-toggle="true">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                <tr>
                                    <td><?php




                                        if ($row['referal_or_famcom'] == 'Family') {
                                            $display_name = $row['insurance_name'];
                                        } elseif ($row['referal_or_famcom'] == 'Referral') {
                                            $display_name = $row['referral_name'];
                                        } else {
                                            $display_name = 'N/A';
                                        }







                                        echo $display_name
                                            . '<br> - ' . $row['discount_charge']
                                            . '<br> - ' . $row['percentage_flat'] . '(' . $row['percentage_flat_value'] . ')'
                                            . '<br> - <strong>Duration:</strong>' . ' ' .  $row['duration']
                                            . '<br>';


                                        if ($row['referal_or_famcom'] == 'Family') {
                                            $hmo_no = $row['individual_group_no'];
                                            $stmt_C = $db->query("SELECT hmo_no FROM enrollee WHERE hmo_no='$hmo_no' and discount_set='0'");
                                            if ($stmt_C->rowCount() > 0) { ?>
                                                <strong style="color:#F00">New Patient Pending: <?php echo $stmt_C->rowCount(); ?></strong><br>
                                                <a href="index.php?discount&plyf=<?php echo $hmo_no; ?>">Click to Apply</a>
                                            <?php }
                                        }


                                        if ($row['referal_or_famcom'] == 'Referral') {
                                            $referral_name = $row['individual_group_name'];
                                            $stmt_C = $db->query("SELECT sn FROM pharm_ext WHERE referral='$referral_name' and discount_set='0'");
                                            if ($stmt_C->rowCount() > 0) { ?>
                                                <strong style="color:#F00">New Patient Pending: <?php echo $stmt_C->rowCount(); ?></strong><br>
                                                <a href="index.php?discount&plyr=<?php echo $referral_name; ?>">Click to Apply</a>
                                        <?php }
                                        }

                                        ?>
                                    </td>
                                    <td><?php echo $row['specify_count'] . '/' . $row['count_bal']; ?></td>
                                    <td><?php echo $row['setby'] . '<br>' . date("d M y", strtotime($row['status_date'])); ?></td>
                                    <td><strong style="color: brown; font-size: 14px;"><?php echo $row['status']; ?></strong>
                                        <br>


                                        <input type="button" name="edit" value="Edit / Enable" data-toggle="modal" data-target="#myModal5" id="<?php echo $row["sn"]; ?>" class="btn btn-warning btn-xs edit_discount" />

                                        <?php if (strtoupper($row['individual_group_name']) != 'STAFF') { ?>
                                            <!-- <input type="button" name="edit" value="Delete" data-target="#modal" id="<?php ///echo $row["individual_group_no"] . '__' . $row['individual_group_name'] . '__dsc'; 
                                                                                                                            ?>" class="btn btn-danger btn-xs delete_confirm" /> -->
                                        <?php } ?>

                                        <?php if ($row['status'] == 'On-going' or $row['status'] == 'Pending') { ?>
                                            <br><a href="index.php?discount&grp=<?php echo $row['individual_group_no'] . '&dsc'; ?>" class="btn btn-warning btn-xs">Cancel / Stop </a> &nbsp;
                                        <?php } else { ?>
                                            <br><a href="index.php?discount&grp=<?php echo $row['individual_group_no'] . '&cont'; ?>" class="btn btn-success btn-xs">Continue </a> &nbsp;
                                        <?php } ?>



                                        <br /><?php
                                                if ($row['apply_to_services'] == 'All Services') { ?>
                                            <b>All Hospital Services</b>
                                            <a href="index.php?discount&grp=<?php echo $row['individual_group_no'] . '&change_specify'; ?>" class="btn btn-primary btn-xs" onclick="return confirm('Are you sure you want to change to Specific Services?');">Change Specify </a><br>

                                        <?php } else { ?>
                                            <a href="index.php?discount&grp=<?php echo $row['individual_group_no'] . '&change_all'; ?>" class="btn btn-primary btn-xs" onclick="return confirm('Are you sure you want to change to All Services?');">Change to All Services </a><br>
                                            <input type="button" name="edit" value="Add Services" data-toggle="modal" data-target="#myModal5" id="<?php echo $row["sn"] . '__' . $row['individual_group_no']; ?>" class="btn btn-success btn-xs add_services" />

                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php  } ?>
                    </table>

                <?php } ?>

            </div>
        </div>
    </div>
</div>


<div class="modal inmodal fade" id="add_services_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Add Services</h4>
            </div>
            <div class="modal-body" id="add_services_body">
            </div>

        </div>
    </div>
</div>


<div class="modal inmodal fade" id="delete_confirmation_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="">Delete Confirmation</h4>
            </div>
            <div class="modal-body" id="delete_confirmation_body">
            </div>
        </div>
    </div>
</div>