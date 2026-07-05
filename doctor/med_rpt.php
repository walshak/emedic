<?php
if (isset($_GET['md'])) {
    $hosp_no = $_GET['md'];

    $stmt = $db->query("SELECT e.*,i.insurance_name,i.interest,i.insurance_type FROM enrollee as e INNER JOIN insurance_tbl as i ON e.hmo_no=i.insurance_no WHERE hospital_no='$hosp_no' and status='active'");
    if ($stmt->rowCount() > 0) {
        $row_rstSelect = $stmt->fetch(PDO::FETCH_ASSOC);
        $nhis_no = $row_rstSelect['nhis_no'];
        $nhis_no_ext = $row_rstSelect['nhis_no_ext'];
        $insurance = $row_rstSelect['insurance'];
        $interest = $row_rstSelect['interest'];
        $validation_status = $row_rstSelect['validation_status'];
        $insurance_name = $row_rstSelect['insurance_name'];
    }
}

?>


<div class="row">
    <div class="col-lg-12">
        <div class="ibox ">


            <div class="ibox-content">
                <div class="row">
                    <div class="col-sm-6 b-r">

                        <table width="100%">
                            <tr>
                                <td>
                                    <span style="font-size:25px; font-family: 'Trebuchet MS', Arial, Helvetica, sans-serif;">Medical Report</span>
                                </td>
                                <td>

                                    <form action="index.php?md=<?php echo $hosp_no; ?>" method="POST" id="subject" name="subject">
                                        <div class="form_sep">
                                            <label for="reg_input_no" class="">Dates Range</label><br>
                                            <div class="form_sep" id="data_5">
                                                <div class="input-daterange input-group" id="datepicker">
                                                    <input type="text" class="input-sm form-control" name="start" value="<?php if (isset($_POST['start'])) {
                                                                                                                                echo $_POST['start'];
                                                                                                                            } ?>" required />
                                                    <span class="input-group-addon">to</span>
                                                    <input type="text" class="input-sm form-control" name="end" value="<?php if (isset($_POST['end'])) {
                                                                                                                            echo $_POST['end'];
                                                                                                                        } ?>" required />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form_sep">
                                            <button class="btn btn-primary btn-sm" type="submit" name="apply_date">Apply</button>
                                            &nbsp; | &nbsp; &nbsp;
                                            <a href="index.php?md=<?php echo $hosp_no; ?>" class="btn btn-default btn-sm"><span class="fa fa-refresh"></span>&nbsp;Refresh</a>


                                            &nbsp; | &nbsp; &nbsp;
                                            <a href="index.php?hosp_no=<?php echo $hosp_no; ?>" class="btn btn-warning btn-sm"><span class="fa fa-times"></span>&nbsp;Close</a>

                                        </div>
                                        <input type="hidden" name="hosp_no" id="hosp_no" value="<?php echo $hosp_no; ?>" />
                                    </form>


                                </td>
                            </tr>
                        </table>


                    </div>

                    <div class="col-sm-6">

                        <div class="panel-body">


                            <table>
                                <tr>
                                    <td style=" padding-right:10px;">
                                        <img src="<?php if (file_exists(enrollee_p . $hosp_no . '.' . 'jpg')) {
                                                        echo enrollee_p . $hosp_no . '.' . 'jpg';
                                                    } else {
                                                        echo '../img/no_photo.jpg';
                                                    } ?>" alt="" height="100" width="100" class="img-thumbnail user_avatar">
                                    </td>
                                    <td>
                                        <span style="font-size:18px"><?php echo $hosp_no; ?></span>
                                        <span style="font-size:25px"><?php echo ' / ' . $row_rstSelect['surname'] . ', ' . $row_rstSelect['fname'] . ' ' . $row_rstSelect['oname']; ?>
                                        </span>

                                        <br>

                                        <table class="table border">
                                            <tr>
                                                <td><strong>Gender:</strong>&nbsp;<?php echo $row_rstSelect['gender']; ?></td>
                                                <td><strong>Age:&nbsp;</strong><?php echo $row_rstSelect['age']; ?></td>
                                                <td><strong>Blood/Group:&nbsp;</strong><?php echo $row_rstSelect['blood_g']; ?></td>
                                            </tr>
                                            <tr>
                                            </tr>
                                        </table>

                                    </td>
                                </tr>
                            </table>




                        </div>
                    </div>


                    <?php

                    ///apply_date
                    if (isset($_POST['apply_date'])) {
                        $hosp_no = $_POST['hosp_no'];
                        $start = $_POST['start'];
                        $end = $_POST['end'];
                        $stmt = $db->prepare("SELECT DISTINCT (date(date_entry)) as dd FROM notes WHERE hospital_no=:hosp_no AND date(date_entry) BETWEEN :start AND :end ORDER BY date_entry DESC");
                        $stmt->bindParam(':hosp_no', $hosp_no);
                        $stmt->bindParam(':start', $start);
                        $stmt->bindParam(':end', $end);
                        $stmt->execute();
                    } else {
                        $stmt = $db->prepare("SELECT DISTINCT (date(date_entry)) as dd FROM notes WHERE hospital_no=:hosp_no ORDER BY date_entry DESC");
                        $stmt->bindParam(':hosp_no', $hosp_no);
                        $stmt->execute();
                    }
                    if ($stmt->rowCount() > 0) { ?>
                        <br>

                        <table class="footable table table-stripped toggle-arrow-tiny">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php
                                $n = 0001;
                                while ($row_visit = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    $date_entry = date('Y-m-d', strtotime($row_visit['dd']));
                                    $doc_name = $row_visit['prepared_by'];
                                    $entry_date = $row_visit['date_entry'];
                                    //echo $row_visit['dd'];
                                ?>
                                    <?php
                                    $stmt2 = $db->prepare("SELECT * FROM notes WHERE hospital_no=:hosp_no AND date(date_entry)=:date_entry ORDER BY sn DESC LIMIT 40");
                                    $stmt2->bindParam(':hosp_no', $hosp_no);
                                    $stmt2->bindParam(':date_entry', $date_entry);
                                    $stmt2->execute();
                                    if ($stmt2->rowCount() > 0) {
                                        $C = '';
                                        $D = '';
                                        $plan = '';
                                        $note = '';
                                        while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {

                                            if ($row['notes_type'] == 'C') {
                                                $C = $C . $row['notes'] . '<br>';
                                                $dr_name = $row['prepared_by'];
                                            }
                                            if ($row['notes_type'] == 'D') {
                                                $D = $D . $row['notes'] . '<br>';
                                            }
                                            if ($row['notes_type'] == 'plan') {
                                                $plan = $plan . $row['notes'] . '<br>';
                                            }
                                        }
                                    ?>

                                        <?php if ($C != '' or $D != '') { ?>
                                            <tr>
                                                <td width="7%"><?php echo date('d M Y', strtotime($row_visit['dd'])); ?></td>
                                                <td width="43%">
                                                    <?php if ($C != '') {
                                                        echo '<strong>Complaints</strong><br>' . $C . '' . "<i><b>Entered by: </b>" . $dr_name . '</i>';
                                                    } else {
                                                        echo '<strong>Complaints N/A</strong>';
                                                    } ?>
                                                </td>
                                                <td width="45%">
                                                    <?php if ($D != '') {
                                                        echo '<strong>Diagnosis</strong><br>' . $D;
                                                    } else {
                                                        echo '<strong> Diagnosis N/A</strong>';
                                                    } ?>
                                                </td>
                                            </tr>
                                        <?php } ?>

                                        <?php
                                        $stmt44 = $db->prepare("SELECT item_services, cat_type FROM patient_ap_services WHERE serv_group IN (:serv_group1, :serv_group2, :serv_group3) AND hospital_no=:hosp_no AND date(date_entry)=:date_entry ORDER BY sn DESC");
                                        $stmt44->bindParam(':serv_group1', 'Pharmacy');
                                        $stmt44->bindParam(':serv_group2', 'Laboratory');
                                        $stmt44->bindParam(':serv_group3', 'Radiology');
                                        $stmt44->bindParam(':hosp_no', $hosp_no);
                                        $stmt44->bindParam(':date_entry', $date_entry);
                                        $stmt44->execute();

                                        $medication = '';
                                        $lab = '';
                                        while ($row = $stmt44->fetch(PDO::FETCH_ASSOC)) {
                                            if ($row_details['serv_group'] == 'Pharmacy') {
                                                $medication = $medication . $row_details['item_services'] . ', ';
                                            }
                                            if ($row_details['serv_group'] == 'Laboratory' or $row_details['serv_group'] == 'Radiology') {
                                                $lab = $lab . $row_details['item_services'] . ', ';
                                            }
                                        }

                                        $note = "note";
                                        $stmt45 = $db->prepare("SELECT * FROM notes WHERE hospital_no=:hosp_no AND date(date_entry)=:date_entry AND notes_type=:notes_type ORDER BY sn DESC");
                                        $stmt45->bindParam(':hosp_no', $hosp_no);
                                        $stmt45->bindParam(':date_entry', $date_entry);
                                        $stmt45->bindParam(':notes_type', $note);
                                        $stmt45->execute();
                                        if ($stmt45->rowCount() > 0) {
                                            while ($row = $stmt45->fetch(PDO::FETCH_ASSOC)) {
                                                $note = $note . $row['notes'] . '<br>';
                                            }
                                        }
                                        ?>

                                        <?php if ($medication != '' or $lab != '' or $plan != '' or $note != '') { ?>
                                            <tr>
                                                <td width="7%">
                                                    <?php if ($C == '' and $D == '') {
                                                        echo date('d M Y', strtotime($row_visit['dd']));
                                                    } ?>
                                                </td>
                                                <td width="47%">
                                                    <?php if ($medication != '' or $lab != '' or $plan != '') { ?>
                                                        <?php if ($plan != '') {
                                                            echo '<strong>Plan: </strong><br>' . $plan . '<br>';
                                                        } ?>
                                                        <?php
                                                        if ($medication != '') {
                                                            echo '<strong>Medications: </strong><br>' . $medication . '<br>';
                                                        }
                                                        if ($lab != '') {
                                                            echo '<br><strong>Investigations: </strong><br>' . $lab . '<br>';
                                                        }
                                                        ?>
                                                    <?php } else {
                                                        echo '<strong>N/A</strong>';
                                                    } ?>
                                                </td>
                                                <td width="45%">
                                                    <?php if ($note != '') {
                                                        echo '<strong>Other Notes</strong><br>' . $note;
                                                    } ?>
                                                </td>
                                            </tr>
                                <?php             }
                                        $n++;
                                    }
                                }
                                ?>


                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2">
                                        <ul class="pagination pull-right"></ul>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>

                    <?php } ?>

                </div>
            </div>

        </div>
    </div>
</div>