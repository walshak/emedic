<?php $other_doc = base64_encode('other_doc'); ?>
<div align="center"><A href="index.php" class="btn btn-danger btn-sm">Refresh For New Queue List</A> &nbsp; : &nbsp; <A href="index.php?c=<?= $other_doc ?>" class="btn btn-success btn-sm">See Other Doctor's Queue List</A></div>
<?php
if ($noOntheQueue > 0) { ?>

    <table class="table table-striped table-bordered table-hover dataTables-example" style="font-size:16px;">
        <thead>
            <tr>
                <th width='2%'>No</th>
                <th>Hospital No</th>
                <th>Name</th>
                <th>Service Name</th>
                <th>Date</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php
            echo $appt_table_tr;
            ?>
        </tbody>
    </table>
<?php
} else { ?>
    <h3 class="" align="center">[ No Patient On Queue for Consultation ]</h3>
<?php }
?>