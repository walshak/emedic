<?php include('../Connections/Conn.php');
?>

<div align="center">
	<?php
	session_start();
	$n = 1;
	$s = 1;
	$claim_set = 0;
	$total_claim = 0;
	$total_pay = 0;
	$invoice_set = 0;
	$total_dsc = 0;
	$total_chr = 0;

	$disabled = '';


	if (isset($_POST['discharge_patient_id'])) {

		$hos_no_bed_no = $_POST['discharge_patient_id'];

		$partt = explode("___", $hos_no_bed_no);
		$hos_no = $partt[0];
		$room_bed_sn = $partt[1];

		$stmt = $db->query("SELECT claim_amt,sn
FROM patient_ap_services 
WHERE hospital_no='$hos_no' and 
serv_group='Nursing Services' and 
paystatus=0 and invoice_status =0  and claim_amt> 0 and (drug_status='1' or cr='1')");

		if ($stmt->rowCount() > 0) {
			while ($row_rs = $stmt->fetch(PDO::FETCH_ASSOC)) {

				$claim_amt = $row_rs['claim_amt'];
				$search = $row_rs['sn'];
				include_once("../inc/utilities.php");
				$invoice_no = INV();
				/// Update Table now and generate Inovoice 
				if ($claim_amt > 0) {
					$paystatus = '1';
				} else {
					$paystatus = '0';
				}


				$invoice_date = date('Y-m-d');
				$one = '1';
				$updateSQL = 'UPDATE patient_ap_services SET 
		invoice_no=:invoice_no,
		invoice_status=:invoice_status,
		drug_status=:drug_status, 
		invoice_by=:invoice_by, 
		invoice_date=:invoice_date, 
		paystatus=:paystatus 
		WHERE hospital_no=:hospital_no and sn=:sn';
				$sql = $db->prepare($updateSQL);
				$sql->bindParam(':invoice_no', $invoice_no, PDO::PARAM_STR);
				$sql->bindParam(':invoice_status', $one, PDO::PARAM_STR);
				$sql->bindParam(':drug_status', $one, PDO::PARAM_STR);
				$sql->bindParam(':invoice_by', $_SESSION['fullname'], PDO::PARAM_STR);
				$sql->bindParam(':invoice_date', $one, PDO::PARAM_STR);
				$sql->bindParam(':paystatus', $paystatus, PDO::PARAM_STR);
				$sql->bindParam(':hospital_no', $hos_no, PDO::PARAM_STR);
				$sql->bindParam(':sn', $search, PDO::PARAM_STR);
				$sql->execute();
			}
		}




		$stmt = $db->query("SELECT sum(claim_amt) as Total_claimt, sum(pay) as Total_Pay FROM patient_ap_services 
   		WHERE hospital_no='$hos_no' and paystatus='0' and (drug_status='1' or cr='1')");
		if ($stmt->rowCount() > 0) {
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
	?>
			<h2>Outstanding Payments</h2>

			<hr>
			<table width="100%" align="center" border="1">
				<tr>
					<td width="50%">
						<div align="center">
							<h3>AMOUNT PAYABLE</h3>
						</div>
					</td>
					<td width="50%">
						<div align="center">
							<h3>AMOUNT INSURED (CLAIM)</h3>
						</div>
					</td>
				</tr>
				<tr>
					<td>
						<h2>
							<div align="center"><?= number_format($row['Total_Pay']); ?></div>
						</h2>
					</td>
					<td>
						<h2>
							<div align="center"><?= number_format($row['Total_claimt']); ?></div>
						</h2>
					</td>
				</tr>
			</table>

		<?php

		}



		?>

		<br>
		<hr>
		<h2>Discharge this patient ?</h2>
		<hr>

		<form action="patient.php?hosp_no=<?php echo $hos_no; ?>" method='POST' id='subject' name='subject' enctype='multipart/form-data'>

			<button class='btn btn-success btn-lg' type='submit' name='discharge_patien' id='discharge_patien'>Discharge Now</button>
			<input type='hidden' value="<?php echo $hos_no; ?>" name='hosp_no'>
			<input type='hidden' value="<?php echo $room_bed_sn; ?>" name='room_bed_sn'>
			<input type='hidden' value="<?php echo $app_no; ?>" name='app_no'>
			<input type='hidden' value="<?php echo $row['Total_Pay']; ?>" name='Total_Pay'>
		</form>



</div>

<?php    } ?>

</div>