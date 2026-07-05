<div>
    <?php
    session_start();
    include("../Connections/Conn.php");
    if (isset($_POST['fullname'])) {
        $id = $_SESSION['id'];
        $fullname = $_POST['fullname'];
        if ($_SESSION['rights'] == 'MD') {
            $search = "vip=1 OR patient_manage_by != ''";
        } else {
            /// $search = "vip=0 AND (patient_manage_by = '$id' OR patient_manage_by = '$fullname')";
            $search = "(patient_manage_by = '$id' OR patient_manage_by = '$fullname' OR vip =1)";
        }

        $stmt2 = $db->query(" SELECT * FROM enrollee WHERE $search");
        if ($stmt2->rowCount() > 0) {

    ?>


            <table class='table table-striped table-bordered table-hover dataTables-example' style="font-size: 15px;">
                <thead>
                    <tr>
                        <th width='2%'>No</th>
                        <th>Hospital No</th>
                        <th>Patient</th>
                        <th>Phone</th>
                        <th>Manage History</th>
                        <th>Manage Type & By</th>
                        <th>Other Member(s)</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php


                    while ($row2 = $stmt2->fetch()) {
                        $is_vip = null;

                        if ($row2['vip'] == 1) {
                            $vip = '<b style="color:blue;">VIP</b>';
                            $hospital_no = $row2['hospital_no'];
                            $stmtd = $db->prepare("SELECT who_created FROM manage_patients_vip_staff WHERE hospital_no= :hospital_no AND user_id= :user_id AND status=1");
                            $stmtd->bindParam(':hospital_no', $hospital_no);
                            $stmtd->bindParam(':user_id', $_SESSION['id']);
                            $stmtd->execute();
                            $is_vip = $stmtd->rowCount();

                            if ($is_vip > 0) {
                                $rw_ = $stmtd->fetch(PDO::FETCH_ASSOC);
                                $who_created = $rw_['who_created'];
                            } else {
                                $who_created = $row2['patient_manage_by'];
                            }
                        } else {
                            $vip = '<b>Manage</b>';
                            $is_vip = 0;
                        }

                        $id_ = $row2['patient_manage_by'];
                        $familyStmt = $db->query(" SELECT fullname FROM admin_users WHERE id='$id_'");
                        if ($familyStmt->rowCount() > 0) {
                            $familyData = $familyStmt->fetch(PDO::FETCH_ASSOC);
                            $fullname = ' <br> ' . $familyData['fullname'];
                        } else {
                            $fullname = ' <br> ' . $row2['patient_manage_by'];
                        }


                        if ($row2['vip'] == 1 && $is_vip == 0 && $_SESSION['rights'] != 'MD') {
                        } else {



                            echo '
                                    <tr>
                                    <td>' . ++$sn . '</td>
                                    <td>' . $row2['hospital_no'] . '</td>           
									<td>' . $row2['surname'] . ' ' . $row2['fname'] . ' ' . $row2['oname'] . '</td>
                                    <td>' . $row2['phone'] . ' </td>
                                    <td>' . $row2['patient_manage_hx'] . ' </td>
                                    <td>' . $vip .  $fullname . ' </td>
                                    <td>';
                            if ($row2['vip'] == 1) {
                                $stmt_check = $db->prepare("SELECT f.fullname, m.* FROM manage_patients_vip_staff AS m 
                                    INNER JOIN admin_users AS f ON m.user_id = f.id WHERE who_created= '$who_created' AND m.rights!='MD' AND hospital_no='$hospital_no'");
                                $stmt_check->execute();
                                if ($stmt_check->rowCount() > 0) {
                                    while ($rw = $stmt_check->fetch(PDO::FETCH_ASSOC)) {
                                        echo $rw['fullname'] . ' -  ';
                                        if ($rw['status'] == 1) {
                                            echo '<b style="color:blue;">Active</b>';
                                        } else {
                                            echo '<b style="color:red;">Disabled</b>';
                                        }
                                        echo '<br>';
                                    }
                                }
                            }


                            echo  ' </td>
      
                                    <td class="text-center"><a href = "patient.php?hosp_no=' . $row2['hospital_no'] . '&app=' . $app_no . '" class="btn btn-sm btn-primary">View Patient</a></td>
                                </tr>
                                    ';
                        }
                    }
                    ?>
                </tbody>
            </table>
    <?php

        }
    }


    ?>
</div>