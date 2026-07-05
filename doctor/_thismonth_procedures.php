<hr>
<table width="100%">
    <tbody>
        <tr>
            <td align="center">
                <h3 style="color:#888"> PROCEDURES REQUESTS </h3>
            </td>
        </tr>
    </tbody>
</table>

<br>
<table class="table table-striped" border="2">
    <thead>
        <tr>
            <th>SN</th>
            <th>HOSP. No.</th>
            <th>Name / Insurance</th>
            <th>Procedures</th>
            <th>Time</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php
        $sn = 1;
        $year_month = date('Y-m');
        $stmt = $db->prepare("SELECT * from procedures ");
        $stmt->execute();
        $procedures = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($procedures as $key => $procedure) {

        ?>

            <tr>

                <td><?= $sn++; ?></td>
                <td><?= $procedure["hospital_no"]; ?></td>
                <td><?= $procedure["name"]; ?> <br> <strong>Insurance: </strong> <?= $procedure["insurance_type"]; ?></td>
                <td><?= str_replace('||', ' , ', $procedure["procedures"]);  ?> <br> <strong>Booked by: </strong> <?= $procedure["prepared_by"]; ?></BR></td>

                <td><?= $procedure["sDate"]; ?></td>
                <td>
                    <a href="procedures.php?hosp_no=<?= $procedure["hospital_no"]; ?>&pr=<?= base64_encode($procedure["sn"]); ?>" class=" btn btn-sm btn-primary">Open </a>
                </td>
            </tr>
        <?php
        }
        ?>
    </tbody>
</table>