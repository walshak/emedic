<?php if (isset($_GET['p'])) { ?>
    <a href="dialysis.php" class="btn btn-card btn-full btn-text-left pd-20-left <?= ($page == 'home' ? 'btn-card-active' : ''); ?>" style="font-size: 17px; ">&nbsp;&nbsp;<i class="fa fa-home"></i>&nbsp;&nbsp;Home</a>
<?php } ?>


<?php if (isset($_GET['patient'])) {
    $patient = $_GET['patient'];
?>

    <a href="../doctor/patient.php?hosp_no=<?php echo $patient; ?>" class="btn btn-card btn-full btn-text-left pd-20-left <?= ($page == 'New_request' ? 'btn-card-active' : ''); ?>" style="font-size: 16px; ">&nbsp;&nbsp;<i class="fa fa-file"></i>&nbsp;&nbsp;Patient Dashboard</a>
    <a href="<?= $FormAction_ . '?p=' . base64_encode('New_request'); ?>&patient=<?php echo $patient; ?>" class="btn btn-card btn-full btn-text-left pd-20-left <?= ($page == 'New_request' ? 'btn-card-active' : ''); ?>" style="font-size: 16px; ">&nbsp;&nbsp;<i class="fa fa-file"></i>&nbsp;&nbsp;New Request</a>
<?php } else { ?>
    <a href="<?= $FormAction_ . '?p=' . base64_encode('New_request'); ?>" class="btn btn-card btn-full btn-text-left pd-20-left <?= ($page == 'New_request' ? 'btn-card-active' : ''); ?>" style="font-size: 16px; ">&nbsp;&nbsp;<i class="fa fa-file"></i>&nbsp;&nbsp;New Request</a>
<?php } ?>

<a href="<?= $FormAction_ . '?p=' . base64_encode('Completed_List'); ?>" class="btn btn-card btn-full btn-text-left pd-20-left  <?= ($page == 'Completed_List' ? 'btn-card-active' : ''); ?>" style="font-size: 16px; ">&nbsp;&nbsp;<i class="fa fa-file"></i>&nbsp;&nbsp;Completed List</a>
<a href="<?= $FormAction_ . '?p=' . base64_encode('statistics'); ?>" class="btn btn-card btn-full btn-text-left pd-20-left  <?= ($page == 'statistics' ? 'btn-card-active' : ''); ?>" style="font-size: 16px; ">&nbsp;&nbsp;<i class="fa fa-print"></i>&nbsp;&nbsp;Dialysis Reports</a>