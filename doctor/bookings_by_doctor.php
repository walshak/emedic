 <?php include("../Connections/Conn.php");

    if (isset($_POST['delete_booking_sn'])) {
        $delete_booking_sn = $_POST['delete_booking_sn'];
        $error_status = 1;
        $error_msg = "Oops! Something went wrong";
        $stmt = $db->prepare("DELETE FROM apptm_fellowup WHERE  sn = ? ");
        $delete = $stmt->execute(array($delete_booking_sn));
        if ($delete) {
            echo "Record deleted successfully. Please close and reopen to see the changes.";
        }
    } else {

        $interfc = null;

        if (!isset($_POST['delete_booking_sn']) && $_POST['delete_booking_sn'] == '' && $interfaccc == "front-desk") {

            $stmt = $db->prepare("SELECT f.*, e.surname, e.fname 
                      FROM apptm_fellowup f 
                      INNER JOIN enrollee e ON e.hospital_no = f.hospital_no 
                      WHERE f.date_time_stamp BETWEEN NOW() - INTERVAL 48 HOUR AND NOW() 
                      ORDER BY f.sn DESC");

            $interfc = 1;
        } else {
            $hospital_no = $_POST['___hospital_no'];
            $stmt = $db->prepare("SELECT * FROM apptm_fellowup WHERE hospital_no='$hospital_no' AND date_time_stamp BETWEEN NOW() - INTERVAL 48 HOUR AND NOW()  order by sn desc");
            $interfc = 2;
        }

        $stmt->execute();
        $n = 1;
        if ($stmt->rowCount() > 0) { ?>

         <h2>Other Requests</h2>
         <table class="table table-bordered" style="font-size: 16px;">
             <thead>
                 <tr>
                     <th><strong>#</strong></th>
                     <th width="">Date</th>
                     <?php if ($interfc == 1 && $interfaccc == "front-desk") { ?><th>Name</th><?php } ?>
                     <th width="">Patient Name</th>
                     <th width="">Notes</th>
                     <th width="">Noted By</th>
                     <?php if ($interfc == 1 && $interfaccc == "front-desk") { ?><th>Book</th><?php } ?>
                     <th width=""></th>
                 </tr>
             </thead>
             <tbody>
                 <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $sn = $row['sn'];
                    ?>
                     <tr>
                         <td><?= $n++; ?></td>
                         <td><?php echo date('d M,Y', strtotime($row['date_time'])) . '<br>' . date('h:i a', strtotime($row['date_time'])); ?></td>
                         <?php if ($interfc == 1) { ?><td><?php echo $row['fname'] . ' ' . $row['surname']; ?></td><?php } ?>
                         <td><?php echo '<b>' . strtoupper($row['service_type']) . '</b>' . '<br>' . $row['request_note_description']; ?> </td>
                         <td><?php echo $row['doctor_name']; ?></td>
                         <td><a href="?ptm=all/<?php echo $row['hospital_no']; ?>" class="btn btn-success btn-xs">Book</a></td>

                         <td><?php if ($row['status'] == 1) {
                                    echo '<b>Acknowledged</b>';
                                } elseif ($row['status'] == 0 && $interfc == 2) {
                                ?>
                                 <input type="button" name="Delete" value="Delete" onclick="delete_doc_booking('<?= $sn; ?>')"
                                     class="btn btn-danger btn-xs" />
                             <?php } elseif ($interfaccc == "front-desk") { ?>
                                 <input type="button" name="" value="Acknowledge" onclick="Accknl_booking('<?= $sn; ?>')"
                                     class="btn btn-warning btn-xs" />
                             <?php } ?>
                         </td>
                     </tr>
                 <?php     } ?>

             </tbody>
         </table>

 <?php }
    }
    ?>