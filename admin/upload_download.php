<?php
if (isset($_POST['upload_data_button'])) {
	$typeoftable = $_POST['typeoftable'];
	if ($typeoftable == 'Pharmacy' or $typeoftable == 'Nursing Consumable' or $typeoftable == 'Store') {
		$stmt = $db->query("SELECT sn FROM department where department='$typeoftable'");
		if ($stmt->rowCount() > 0) {
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$dept_id = $row["sn"];
			include('upload_data_stocks_tbl.php');
		}
	} else {
		include('upload_data_investigation_tbl.php');
	}
	exit;
}


if (isset($_POST['delete_hmo_data_button'])) {
	$upload_hmo = $_POST['upload_hmo'];
}
if (isset($_POST['download_data_button'])) {
	$typeoftable = $_POST['typeoftable'];
	if ($typeoftable == 'Pharmacy' or $typeoftable == 'Nursing Consumable' or $typeoftable == 'Store') {
		include('download_data_stocks_tbl.php');
	} elseif ($typeoftable == 'Bed') {
		include('download_data_bed_tbl.php');
	} elseif ($typeoftable == 'Investigations') {
		include('download_data_invest_tbl.php');
	} else {
		include('download_data_price_tbl.php');
	}
	exit;
}



?>


<style>
	.danger-label {
		color: #b30000;
		font-weight: bold;
	}

	.flash {
		animation: flashRed 0.6s ease-in-out 3;
	}

	@keyframes flashRed {
		0% {
			background-color: transparent;
		}

		50% {
			background-color: #ffd6d6;
		}

		100% {
			background-color: transparent;
		}
	}
</style>
<div class="row">
	<div class="col-lg-12">

		<div class="ibox-title">
			<div class="ibox-tools">
			</div>
		</div>

		<div class="ibox-content" align="center">
			<p style="color:red; font-size:12px; "><b><i>PHARMACY ALERT: </i></b><br>Note — Set the quantity to zero if you do not want to update the inventory quantity.<br>
				Also, if you have already started inventory quantity tracking, any new update will not take effect,
				<br>whether you enter a non-zero quantity or not. The quantity update only takes effect when adding stock <b>before</b> inventory tracking has started.
			</p>
			<hr>

			<div class="row">
				<div class="col-lg-6">

					<p>
					<h3>Download Full Record or HMO/Corporate Folder Record</h3>
					<HR>

					</p>
					<input type="button" name="download" value="Download" data-target="#download_stock_modal" id="download_stock_btn" class="btn btn-primary" style="color: white; font-size: 14px; " />


				</div>
				<div class="col-lg-6">

					<h3>Upload Full Record or HMO/Corporate Folder Record</h3>



					<HR>

					<input type="button" name="upload" value="Upload" data-target="#upload_stock_modal" id="upload_stock_btn" class="btn btn-primary" style="color: white; font-size: 14px; " />

				</div>

			</div>
		</div>
		<hr>

		<div class="row">
			<div class="col-lg-6">
				<div class="ibox-content">
					<h2>Delete Existing Tariff</h2>

					<form id="hmoForm" method="POST" onsubmit="return false;">
						<?php
						$stmt = $db->query("
        SELECT insurance_no, insurance_name 
        FROM insurance_tbl 
        WHERE insurance_type IN ('PHIS', 'Corporate') 
        ORDER BY insurance_type, insurance_name");
						?>

						<label for="stock_upload_hmo" class="req">
							Select the HMO/Corporate Folder whose tariff you wish to delete
						</label>

						<select name="upload_hmo" id="stock_upload_hmo" class="form-control">
							<option value="">-- Select HMO/Corporate --</option>
							<?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) : ?>
								<option value="<?= htmlspecialchars($row['insurance_no']) ?>">
									<?= htmlspecialchars($row['insurance_name']) ?>
								</option>
							<?php endwhile; ?>
						</select>

						<div class="form_sep">
							<label for="typeoftable" class="req">Select Tariff Table</label>
							<select name="typeoftable" id="typeoftable" class="form-control" required>
								<option value="">-- SELECT TABLE TYPE --</option>
								<?php if ($_SESSION['stock_mgr_pharm'] == 1) { ?>
									<option value="Pharmacy">Pharmacy</option>
								<?php } ?>

								<?php if ($_SESSION['stock_mgr'] == 1) { ?>
									<option value="Store">General Store</option>
								<?php } ?>
								<option value="Bed">Bed / Accommodations</option>
								<option value="Nursing Consumable">Nursing Consumable</option>
								<option value="Consultation">Consultation</option>
								<option value="Medical Services">Medical Services</option>
								<option value="Nursing Services">Nursing Services</option>
								<option value="Investigations">Investigations</option>
								<option value="Other Services">Other Services</option>
							</select>
						</div>

						<br>

						<!-- Delete button -->
						<button type="button" id="delete_hmo_data_button" class="btn btn-danger" disabled>
							Delete HMO Tariff
						</button>
					</form>


				</div>
			</div>

			<div class="col-lg-6">
				<div align="center">


					<a href="index.php" class="btn btn-danger">Close</a>
				</div>
			</div>
		</div>



		<script>
			document.addEventListener('DOMContentLoaded', function() {
				const hmoSelect = document.getElementById('stock_upload_hmo');
				const tableSelect = document.getElementById('typeoftable');
				const deleteBtn = document.getElementById('delete_hmo_data_button');

				function toggleDeleteButton() {
					deleteBtn.disabled = (hmoSelect.value === '' || tableSelect.value === '');
				}

				hmoSelect.addEventListener('change', toggleDeleteButton);
				tableSelect.addEventListener('change', toggleDeleteButton);

				deleteBtn.addEventListener('click', function() {
					const hmoId = hmoSelect.value;
					const tableType = tableSelect.value;

					if (!hmoId || !tableType) {
						alert('Please select both an HMO and a Table.');
						return;
					}

					if (confirm(`Are you sure you want to delete all ${tableType} tariffs for this HMO?`)) {
						fetch('delete_hmo_data.php', {
								method: 'POST',
								headers: {
									'Content-Type': 'application/x-www-form-urlencoded'
								},
								body: 'insurance_no=' + encodeURIComponent(hmoId) +
									'&table_type=' + encodeURIComponent(tableType)
							})
							.then(res => res.text())
							.then(response => {
								alert(response);
								hmoSelect.value = '';
								tableSelect.value = '';
								toggleDeleteButton();
							})
							.catch(err => alert('Error deleting data: ' + err));
					}
				});
			});
		</script>
	</div>
</div>

<div class="modal inmodal fade" id="upload_stock_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Upload Stock</h4>
			</div>

			<div class="modal-body">

				<form action="upload_download.php" id="" method="POST" enctype="multipart/form-data">

					<div class="form_sep">
						<label for="reg_input_no" class="req"> Select Table</label>
						<select name="typeoftable" id="typeoftable" class="form-control" required>
							<option value="">--SELECT TABLE TYPE--</option>



							<?php if ($_SESSION['stock_mgr_pharm'] == 1) { ?>
								<option value="Pharmacy">Pharmacy</option>
							<?php } ?>

							<?php if ($_SESSION['stock_mgr'] == 1) { ?>
								<option value="Store">General Store</option>
							<?php } ?>

							<?php if ($_SESSION['price_mgr'] == 1) { ?>
								<option value="Bed">Bed / Accommodations</option>
								<option value="Nursing Consumable">Nursing Consumable</option>
								<option value="Consultation">Consultation</option>
								<option value="Medical Services">Medical Services</option>
								<option value="Nursing Services">Nursing Services</option>
								<option value="Investigations">Investigations</option>
								<option value="Other Services">Other Services</option>
							<?php } ?>
						</select>
					</div>




					<div class="form_sep">
						<label for="reg_input_no" class="req"> Select the type of record you wish to upload</label>
						<select name="upload_type" id="" class="form-control" onchange="show_hmo_field1(this.value)" required>
							<option value="">--select the type of record you wish to upload--</option>
							<option value="hmo">HMO/Corporate Folder Specific records</option>
							<option value="full">Full record</option>
						</select>
					</div>





					<label>
						<input type="checkbox" name="rollback" value="1">
						Rollback entire upload if any error occurs (Optional)
					</label>


					<label id="resetQtyLabel">
						<input type="checkbox" name="reset_qty" value="1" id="reset_qty">
						<u><b>PHARMACY/CONSUMABLES/STORE ONLY</b></u> Check this box if you want to reset the stock quantity and update it with the values from the uploaded Excel (CSV) file.
					</label>





					<div class="form_sep" id="hmo_select_field_upload" style="display: none;">
						<?php
						$stmt = $db->query("
								SELECT insurance_no, insurance_name 
								FROM insurance_tbl 
								WHERE insurance_type IN ('PHIS', 'Corporate') ORDER BY insurance_type,insurance_name");
						?>
						<label for="stock_upload_hmo" class="req">
							Select the HMO/Corporate Folder whose record you wish to upload
						</label>
						<select name="upload_hmo" id="stock_upload_hmo" class="form-control">
							<option value="">--select the Category of data you wish to upload--</option>
							<?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) : ?>
								<option value="<?= htmlspecialchars($row['insurance_no']) ?>">
									<?= htmlspecialchars($row['insurance_name']) ?>
								</option>
							<?php endwhile; ?>
						</select>
					</div>

					<div class="form_sep">
						<label for="">Select excel file</label>
						<input type="file" name="the_upload" class="form-control">
					</div>
					<div class="form_sep" id="upload_the_stock_spinner" style="display: none;">
						<div class="spinner-border" role="status">
							<span>Loading...</span>
						</div>
					</div>
					<div id="err_stock_upload"></div>
					<div class="form_sep" id="upload_the_stock_btn">
						<br>
						<hr>

						<div class="pull-left">
							<div class="form_sep">
								<input class="btn btn-primary" value="Upload" type="submit" name="upload_data_button" id="">
							</div>
						</div>


						<div class="pull-right">
							<div class="form_sep">
								<a href="index.php?updown" class="btn btn-danger">Close</a>
							</div>
						</div>







					</div>

				</form>
				<script>
					function show_hmo_field1(download_type) {
						//console.log(download_type);
						if (download_type == 'hmo') {
							$('#stock_upload_hmo').prop('required', true);
							$('#stock_upload_hmo').prop("disabled", false);
							$('#hmo_select_field_upload').show();

						} else {
							$('#hmo_select_field_upload').hide();
							$('#stock_upload_hmo').prop("disabled", true);
							$('#stock_upload_hmo').val('');
						}
					}
				</script>
			</div>
		</div>
	</div>
</div>

<div class="modal inmodal fade" id="download_stock_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">Download Stock</h4>
			</div>

			<div class="modal-body">
				<!--<form action="excel_stock_download.php" method="POST" id="download_stock_form">-->

				<form action="upload_download.php" method="POST" id="">
					<div class="form_sep">
						<label for="reg_input_no" class="req"> Select Table</label>
						<select name="typeoftable" id="typeoftable" class="form-control" onchange="show_hmo_field11(this.value)" required>
							<option value="">--SELECT TABLE TYPE--</option>

							<?php if ($_SESSION['stock_mgr_pharm'] == 1) { ?>
								<option value="Pharmacy">Pharmacy</option>
							<?php } ?>

							<?php if ($_SESSION['stock_mgr'] == 1) { ?>
								<option value="Store">General Store</option>
							<?php } ?>

							<?php if ($_SESSION['price_mgr'] == 1) { ?>
								<option value="Bed">Bed / Accommodations</option>
								<option value="Nursing Consumable">Nursing Consumable</option>
								<option value="Consultation">Consultation</option>
								<option value="Medical Services">Medical Services</option>
								<option value="Nursing Services">Nursing Services</option>
								<option value="Investigations">Investigations</option>
								<option value="Other Services">Other Services</option>
							<?php } ?>

						</select>
					</div>


					<div class="form_sep">
						<label><strong>Category</strong></label>
						<select class="form-control" name="category" id="category">
							<option value="">-- Not Applicable --</option>
						</select>
					</div>



					<div class="form_sep">
						<label for="reg_input_no" class="req"> Select the type record you wish to download</label>
						<select name="download_type" id="stock_download_type" class="form-control" required onchange="show_hmo_field(this.value)">
							<option value="">--select the type of record you wish to download--</option>
							<option value="hmo">HMO/Corporate Folder Specific records</option>
							<option value="full">Full record</option>
						</select>
					</div>
					<div class="form_sep" id="hmo_select_field" style="display: none;">
						<?php
						$stmt = $db->query("
        SELECT insurance_no, insurance_name 
        FROM insurance_tbl 
        WHERE insurance_type IN ('PHIS', 'Corporate') ORDER BY insurance_type,insurance_name");
						?>
						<label for="stock_download_hmo" class="req">
							Select the HMO/Corporate whose stock record you wish to download
						</label>
						<select name="download_hmo" id="stock_download_hmo" class="form-control">
							<option value="">--select the HMO whose data you wish to download--</option>
							<?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) : ?>
								<option value="<?= htmlspecialchars($row['insurance_no'] . '__' . $row['insurance_name']) ?>">
									<?= htmlspecialchars($row['insurance_name']) ?>
								</option>
							<?php endwhile; ?>
						</select>
					</div>


					<hr>

					<div class="pull-left">
						<div class="form_sep">
							<button class="btn btn-primary" type="submit" name="download_data_button">Download</button>
						</div>



					</div>


					<div class="pull-right">
						<div class="form_sep">
							<a href="index.php?updown" class="btn btn-danger">Close</a>
						</div>
					</div>


				</form>
				<script>
					function show_hmo_field11(download_type) {

						if (download_type != "") {
							$.ajax({
								url: "get-programmes.php",
								data: {
									download_type: download_type
								},
								type: 'POST',
								success: function(response) {
									var resp = $.trim(response);
									$("#category").html(resp);
								}
							});
						} else {
							$("#category").html("<option value=''>------- Select --------</option>");
						}


					}



					function show_hmo_field(download_type) {
						//console.log(download_type);
						if (download_type == 'hmo') {
							$('#stock_download_hmo').prop('required', true);
							$('#stock_download_hmo').prop("disabled", false);
							$('#hmo_select_field').show();


						} else {
							$('#hmo_select_field').hide();
							$('#stock_download_hmo').prop("disabled", true);
							$('#delete_hmo_data_button').prop("disabled", true);

							$('#stock_download_hmo').val('');
						}
					}
				</script>
			</div>
		</div>
	</div>
</div>




<div class="modal inmodal fade" id="inventory_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title">Stocks Inventory and Purchase Order (PO) </h4>
			</div>

			<div class="modal-body" id="inventory_body">

			</div>
		</div>
	</div>
</div>
<div class="modal inmodal fade" id="edit_hmo_prices_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="">HMO/Corporate prices</h4>
			</div>
			<div class="modal-body" id="edit_hmo_prices_body">
			</div>


		</div>
	</div>
</div>


<script>
	document.getElementById('reset_qty').addEventListener('change', function() {
		var label = document.getElementById('resetQtyLabel');

		if (this.checked) {
			var confirmReset = confirm(
				"⚠️ WARNING!\n\n" +
				"This action will RESET stock quantities.\n" +
				"This cannot be undone.\n\n" +
				"Do you want to continue?"
			);

			if (!confirmReset) {
				this.checked = false;
				return;
			}

			label.classList.add('danger-label');
			label.classList.add('flash');

			setTimeout(function() {
				label.classList.remove('flash');
			}, 2000);

		} else {
			label.classList.remove('danger-label');
			label.classList.remove('flash');
		}
	});
</script>


<script>
	document.getElementById('typeoftable').addEventListener('change', function() {
		const selected = this.value;
		const stockOptions = document.getElementById('stockOptions');

		if (selected === 'Pharmacy' || selected === 'Store') {
			stockOptions.style.display = 'block';
		} else {
			stockOptions.style.display = 'none';

			// Optional: uncheck checkboxes when hidden
			document.querySelectorAll('#stockOptions input[type="checkbox"]').forEach(cb => {
				cb.checked = false;
			});
		}
	});
</script>