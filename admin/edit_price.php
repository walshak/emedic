<?php


if (isset($_POST['apply_income'])) {

	$sn_edit_price = $_POST['sn_edit_price'];
	$new_price = $_POST['new_price'];
	$table_to_update = $_POST['table_to_update'];

	if (is_numeric($new_price)) {
		///    echo "Valid number";



		$stmt3 = $db->query("SELECT qty,drug_sn FROM patient_ap_services where sn='$sn_edit_price'");
		if ($stmt3->rowCount() > 0) {

			$rowxx = $stmt3->fetch(PDO::FETCH_ASSOC);
			$qty = $rowxx['qty'];
			$drug_sn = $rowxx['drug_sn'];

			$new_pay = $qty * $new_price;

			$delete = $db->prepare("UPDATE patient_ap_services SET pay = '$new_pay', hosp_price='$new_price' WHERE sn='$sn_edit_price'");
			$deleted = $delete->execute();


			if ($table_to_update == 'Pharmacy') {

				$delete = $db->prepare("UPDATE stock_table SET hosp_price = '$new_price', cash_price='$new_price' WHERE sn='$drug_sn'");
				$deleted = $delete->execute();
			} else {

				$delete = $db->prepare("UPDATE prices_table SET hosp_price = '$new_price', ext_price='$new_price' WHERE sn='$drug_sn'");
				$deleted = $delete->execute();
			}
		}
?>

		<div align="center">
			<h1>Successfully</h1>
			<a href="index.php?edit_price" class="btn btn-success">Return</a>
		</div>
<?php

	} else {
		echo "Invalid number";
	}
}


?>


<script>
	function confirmSubmit() {
		var result = confirm("Are you sure you want to submit the form?");
		if (result) {
			return true; // Allow form submission
		} else {
			return false; // Cancel form submission
		}
	}
</script>


<div class="row">
	<?php

	$stmt = $db->query("SELECT * FROM patient_ap_services where hosp_price>='20000000'");
	if ($stmt->rowCount() > 0) {



	?>

		<div class="col-lg-12">
			<div class="ibox float-e-margins">
				<div class="ibox-title">
					<h5>Select Edit Price</h5>
				</div>

				<div class="ibox-content">


					<div class="form_sep">
						<h2 style="color: red; ">Select Item Change Price</h2>
						<select name="cat" id="cat" onchange="window.open(this.options[this.selectedIndex].value,'_top')" class="form-control" data-required="true" style="font-size:18px;">
							<option selected="selected" value="">-- Select--</option>
							<?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
								$items_sn = $row['sn'];
								$items = $row['item_services'];
							?>
								<option value="index.php?edit_price=<?php echo $items_sn; ?>"><?= $items; ?></option>

							<?php } ?>

						</select>
					</div>


				</div>

			</div>
		</div>


		<?php

		if (isset($_GET['edit_price'])) {
			$sn_edit_price = $_GET['edit_price'];




			$stmt3 = $db->query("SELECT item_services,serv_group FROM patient_ap_services where sn='$sn_edit_price'");


			$rowxx = $stmt3->fetch(PDO::FETCH_ASSOC);
			$item_services = $rowxx['item_services'];
			$serv_group = $rowxx['serv_group'];
			///	$drug_sn = $rowxx['drug_sn'];



		}


		?>

		<div class="col-lg-12">
			<div class="ibox float-e-margins">
				<div class="ibox-title">
					<h5>Price Management </h5>
				</div>

				<div class="ibox-content">

					<form action="index.php?edit_price" method="post" onsubmit="return confirmSubmit();">
						<h1><?= $item_services; ?></h1>
						<hr>

						<div class="form_sep">
							<h2 style="color: red; ">Enter Correct Price</h2>
							<input type="number" step="any" id="new_price" name="new_price" class="form-control" min="1" required style="font-size: 19px; ">
						</div>


						<input type="hidden" value="<?= $sn_edit_price; ?>" name="sn_edit_price" />
						<input type="hidden" value="<?= $serv_group; ?>" name="table_to_update" />


						<hr>



						<div class="form_sep"> <label for="reg_input_no" class="">.</label><br>
							<input type="submit" class="btn btn-info" type="submit" name="apply_income" value="SAVE NEW PRICE">
						</div>




					</form>




				</div>

			</div>
		</div>

	<?php } else { ?>

		<h3>No Records to Edit </h3>

	<?php } ?>
</div>