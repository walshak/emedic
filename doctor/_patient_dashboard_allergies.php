<div class="row">
    <div class="col-lg-12">
        <?php
        $stmt = $db->prepare("SELECT * FROM c_d_remarks WHERE hospital_no='$hosp_no' and cat_type='DH'");
        $stmt->execute();
        if ($stmt->rowCount() > 0) { ?>

            <h2 style="color: red; ">Allergies</h2>
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th width="5%">S/N</th>
                        <th width="95%">Drug Name</th>


                    </tr>
                </thead>
                <tbody>
                    <?php
                    $n = 1;
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>

                        <tr>
                            <td><?php echo $n; ?></td>
                            <td><?php echo $row['complain'] . '<br><i>Captured by: ' . $row['prepared_by'] . '</i>'; ?></td>

                        </tr>
                    <?php $n = $n + 1;
                    } ?>

                </tbody>
            </table>
        <?php } ?>

    </div>
</div>