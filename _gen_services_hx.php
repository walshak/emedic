<h3><u>Documentation History:</u></h3>
<table class="table active">
    <thead>
        <tr>
            <th>#</th>
            <th>Service</th>
            <th>Date</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php
        $sn = 1;
        $stmt = $db->prepare("SELECT * FROM notes_services WHERE status = '1' AND hospital_no = ? ORDER BY id DESC");
        $stmt->execute([$hospital_no]);
        $notes = $stmt->fetchAll();
        foreach ($notes as $key => $note) {


        ?>
            <tr>
                <td><?= $sn++; ?></td>
                <td>
                    <strong><?= $note['service']; ?></strong><br>
                    <?= $note['isCompleted'] ? '<b><i>COMPLETED</i></b>' : 'Not Completed'; ?> /<br>
                    <?= $paystatus ? '<strong>Paid</strong>' : '<strong>Not Paid</strong>'; ?><br>
                    <strong>Captured By: </strong><?= $note['consultant_name']; ?>
                </td>
                <td><?= date('d M, Y', strtotime("" . $note['created_at'])); ?></td>
                <td>



                    <a href="gen_services.php?hosp_no=<?php echo base64_encode(base64_encode($hospital_no . '||' . $app_no)); ?>&edit=<?php echo base64_encode($note['id']); ?>" class="btn btn-primary"><i class="fa fa-edit"></i></a>
                    <a href="gen_services.php?hosp_no=<?php echo base64_encode(base64_encode($hospital_no . '||' . $app_no)); ?>&print=<?php echo base64_encode($note['id']); ?>" class="btn btn-success"><i class="fa fa-print"></i></a>
                </td>
            </tr>
        <?php
        }
        ?>
    </tbody>
</table>