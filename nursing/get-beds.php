<?php
session_start();
require_once('../Connections/Conn.php'); ?>

<?php

if (isset($_POST['floor_id'])) {

	$floor_id = $_POST['floor_id'];
	$status = '0';
	$stmtx = $db->prepare("SELECT * FROM bed_mgt WHERE status=:status and tips=:tips and hosp_price > 0 and ext_price > 0 order by room_name,bed_no");
	$stmtx->bindValue(':status', $status, PDO::PARAM_STR);
	$stmtx->bindValue(':tips', $floor_id, PDO::PARAM_STR);
	$stmtx->execute();
	$stmt_logCOUNT = $stmtx->rowCount();
	if ($stmt_logCOUNT > 0) {
		echo "<option value=''>-- Select --</option>";
		while ($row_bed = $stmtx->fetch(PDO::FETCH_ASSOC)) { ?>
			<option value="<?php echo $row_bed["room_name"] . '(bed ' . $row_bed["bed_no"] . ')' . '_' . $row_bed["hosp_price"] . '_' . $row_bed["nhis_price"] . '_' . $row_bed["access"] . '_' . $row_bed["sn"] . '_' . $row_bed["ext_price"] . '_' . $row_bed["dept_id"]; ?>"><?php echo $row_bed["room_name"] . '(bed ' . $row_bed["bed_no"] . ') - =N=' . number_format($row_bed["hosp_price"]); ?> <?php if ($row_bed["access"] == '1' and $ap_type <= 2) {
																																																																																																			echo ' - NHIS Covered';
																																																																																																		} ?></option>
<?php }
	}
}
?>