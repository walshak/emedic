<?php



$stmt = $db->prepare("SELECT e.*, i.insurance_name, i.interest, i.insurance_type 
                      FROM enrollee as e 
                      INNER JOIN insurance_tbl as i ON e.hmo_no = i.insurance_no 
                      WHERE hospital_no = :hos_no AND status = 'active'");
$stmt->bindParam(':hos_no', $hos_no, PDO::PARAM_STR);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $row_rstSelect = $stmt->fetch(PDO::FETCH_ASSOC);
    $nhis_no = $row_rstSelect['nhis_no'];
    $nhis_no_ext = $row_rstSelect['nhis_no_ext'];
    $insurance = $row_rstSelect['insurance'];
    $interest = $row_rstSelect['interest'];
    $vip = $row_rstSelect['vip'];
    $validation_status = $row_rstSelect['validation_status'];
    $insurance_name = $row_rstSelect['insurance_name'];

?>


    <table class="table table-striped table-bordered table-hover dataTables-example">
        <tr style="background: #666; color: #FFF;">
            <td style="font:bold 14px 'Arial';" width="30%">Patient No: </td>
            <td style="font:bold 14px 'Arial';" width="30%">Patient Name: </td>
            <td style="font:bold 14px 'Arial';" width="30%">Gender: </td>

        </tr>
        <tr>
            <td><?php echo $row_rstSelect['hospital_no']; ?></td>
            <td><?php $name = '';
                if ($vip == 1) {
                    $stmtcv = $db->prepare("SELECT 1 FROM manage_patients_vip_staff WHERE hospital_no = ? AND user_id = ? AND status=1");
                    $stmtcv->execute([$hos_no, $_SESSION['id']]);
                    if ($stmtcv->rowCount() > 0 || $_SESSION['rights'] === 'MD') {
                        echo $row_rstSelect['surname'] . ', ' . $row_rstSelect['fname'] . ' ' . $row_rstSelect['oname'];
                        $name = trim($row_rstSelect['surname'] . ' ' . $row_rstSelect['fname'] . ' ' . $row_rstSelect['oname']);
                    } else {
                        $name = '';
                    }
                } else {
                    echo $row_rstSelect['surname'] . ', ' . $row_rstSelect['fname'] . ' ' . $row_rstSelect['oname'];
                    $name = trim($row_rstSelect['surname'] . ' ' . $row_rstSelect['fname'] . ' ' . $row_rstSelect['oname']);
                }



                ?></td>
            <td><?php echo $row_rstSelect['gender']; ?></td>
        </tr>
        <tr style="background: #666; color: #FFF;">
            <td style="font:bold 14px 'Arial';">Date of Birth: </td>
            <td style="font:bold 14px 'Arial';">Age: </td>
            <td style="font:bold 14px 'Arial';">Date: </td>
        </tr>
        <tr>
            <td><?php if ($row_rstSelect['dob'] == ''  or $row_rstSelect['dob'] == '0000-00-00') {
                    echo '';
                } else {
                    echo date('d M,Y', strtotime($row_rstSelect['dob']));
                } ?></td>
            <td><?php echo $row_rstSelect['age']; ?></td>
            <td><?php if ($row_rstSelect['date_capture'] == ''  or $row_rstSelect['date_capture'] == '0000-00-00') {
                    echo '';
                } else {
                    echo date('d M,Y h:i a', strtotime($row_rstSelect['date_capture']));
                } ?></td>
        </tr>
        <tr style="background: #666; color: #FFF;">
            <td style="font:bold 14px 'Arial';">Marital Status: </td>
            <td style="font:bold 14px 'Arial';">Blood Group: </td>
            <td style="font:bold 14px 'Arial';">Genotype: </td>
        </tr>
        <tr>
            <td><?php echo $row_rstSelect['marital_status']; ?></td>
            <td><?php echo $row_rstSelect['blood_g']; ?></td>
            <td><?php echo $row_rstSelect['geno_type']; ?></td>
        </tr>
        </tr>
        <tr style="background: #666; color: #FFF;">
            <td style="font:bold 14px 'Arial';">Tribe</td>
            <td style="font:bold 14px 'Arial';">State/LGA: </td>
            <td style="font:bold 14px 'Arial';">Nationality: </td>
        </tr>
        <tr>
            <td><?php echo $row_rstSelect['tribe']; ?></td>
            <td><?php echo $row_rstSelect['state_lga']; ?></td>
            <td><?php echo $row_rstSelect['nationality']; ?></td>
        </tr>

        </tr>
        <tr style="background: #666; color: #FFF;">
            <td style="font:bold 14px 'Arial';">Occupation</td>
            <td style="font:bold 14px 'Arial';">Patient Brief/Review Medical Info To</td>
            <td style="font:bold 14px 'Arial';"></td>
        </tr>
        <tr>
            <td><?php echo $row_rstSelect['occupation']; ?></td>
            <td><?php echo $row_rstSelect['patient_review']; ?></td>
            <td></td>
        </tr>
    </table>

    <?php if ($row_rstSelect['insurance'] != 'Private(Self)') { ?>

        <br>
        <h3 class="heading_a" style="color:#F00">Insurance / <?php echo $insurance_name ?></h3>
        <table class="table table-striped table-bordered table-hover dataTables-example">
            <tr style="background: #666; color: #FFF;">
                <td width="33%" style="font:bold 14px 'Arial';">Insurance Name: </td>
                <td width="34%" style="font:bold 14px 'Arial';">Membership/NHIS No.:</td>
                <td width="33%" style="font:bold 14px 'Arial';">Membership: </td>
            </tr>
            <tr>
                <td><?php echo $row_rstSelect['hmo_no'] . ' / ' . $row_rstSelect['insurance']; ?></td>
                <td><?php echo $row_rstSelect['nhis_no'] . '-' . $row_rstSelect['nhis_no_ext']; ?></td>
                <td><?php if ($row_rstSelect['nhis_no_ext'] == '0') {
                        echo 'Principal';
                    } elseif ($row_rstSelect['nhis_no_ext'] == '1') {
                        echo 'Spouse';
                    } elseif ($row_rstSelect['nhis_no_ext'] == '2') {
                        echo 'Dependant 2';
                    } elseif ($row_rstSelect['nhis_no_ext'] == '3') {
                        echo 'Dependant 3';
                    } elseif ($row_rstSelect['nhis_no_ext'] == '4') {
                        echo 'Dependant 4';
                    } else {
                        echo 'Extra Dependant';
                    }
                    ?></td>
            </tr>
        </table>

    <?php } else { ?>
        <br>
        <h3 class="heading_a" style="color:#F00">Insurance/PRIVATE (SELF)</h3>
    <?php } ?>


    <br>
    <h3 class="heading_a">Contact information</h3>
    <table class="table table-striped table-bordered table-hover dataTables-example">
        <tr style="background: #666; color: #FFF;">
            <td style="font:bold 14px 'Arial';" width="30%">Phone No: </td>
            <td style="font:bold 14px 'Arial';" width="30%">Email: </td>
            <td style="font:bold 14px 'Arial';" width="30%">Address: </td>
        </tr>
        <tr>
            <td><?php echo $row_rstSelect['phone']; ?></td>
            <td><?php echo $row_rstSelect['email']; ?></td>
            <td><?php echo $row_rstSelect['addr']; ?></td>
        </tr>
    </table>


    <br>
    <h3 class="heading_a">Next of Kin Information</h3>
    <?php
    $stmt = $db->query("SELECT * FROM guardian_tbl WHERE patient_id='$hos_no' order by guardian_id");
    if ($stmt->rowCount() > 0 && $name != '') { ?>

        <table class="table table-striped table-bordered table-hover dataTables-example">
            <tr style="background: #666; color: #FFF;">
                <td width="1%">#</td>
                <td style="font:bold 14px 'Arial';" width="20%">Next of Kin Name: </td>
                <td style="font:bold 14px 'Arial';" width="30%">Phone No / Relationship: </td>
                <td style="font:bold 14px 'Arial';" width="20%">Address: </td>
                <td style="font:bold 14px 'Arial';" width="20%">Action </td>
            </tr>

            <?php
            $n = 1;
            while ($row_rstSelect = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                    <td><?php echo $n; ?></td>
                    <td><?php echo $row_rstSelect['guardian_Name']; ?></td>
                    <td><?php echo $row_rstSelect['guardian_phone'] . ' / ' . $row_rstSelect['guardian_relationship']; ?></td>
                    <td><?php echo $row_rstSelect['guardian_address']; ?></td>
                    <td>
                        <?php if ($_SESSION['rights'] == 'RE') { ?>
                            <input type="button" name="del_gd" value="Delete" data-target="#modal" id="<?php echo $ptm . '/' . $ptm_2 . '/' . $hos_no . '__' . $row_rstSelect['guardian_id']; ?>" class="btn btn-danger btn-xs confirm_gd_delete" />

                            &nbsp;|&nbsp;
                            <input type="button" name="eidt_gd" value="Edit" data-target="#modal" id="<?php echo $row_rstSelect['guardian_id']; ?>" class="btn btn-warning btn-xs add_guardian_edit" />
                        <?php } ?>
                    </td>
                </tr>
            <?php $n++;
            } ?>

        </table>

    <?php } else { ?>
        <small>No Next of Kin Information</small>
        <div class="form_sep"></div>
        <div class="form_sep"></div>
<?php }
}

?>