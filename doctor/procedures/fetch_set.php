<?php include("../Connections/Conn.php"); ?>
<?php
session_start();
echo 'sddddddddddddddddddddddddddddd';
if (isset($_POST["procedure_sn"])) {

	echo $approve_request_id = $_POST["procedure_sn"];
?>


	<div class="form_sep" id="data_1">
		<label class="req"> Type of Procedure: </label>
		<select data-placeholder="Choose Procedures" class="chosen-select" name="procedure_list" style="width:350px;" tabindex="4" required>
			<option value="">Select</option>

			<?php
			$stmt = $db->prepare("SELECT sn,item_service,hosp_price,ext_price FROM `prices_table` WHERE `price_table` = 'Medical Services' and hosp_price > 0 and ext_price > 0 ORDER BY item_service ASC");
			$stmt->execute();
			// $procedure_lists = json_decode(json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)));

			while ($procedure = $stmt->fetch(PDO::FETCH_ASSOC)) {
			?>
				<option value="<?= $procedure['sn']; ?>"><?= $procedure['item_service'] . ' - N' . number_format($procedure['ext_price']); ?></option>
			<?php


			}
			?>


		</select>
	</div>


<?php

}
?>