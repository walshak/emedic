<?php
$start = $_POST["start"];
$end = $_POST["end"];
$get_prev = $_POST["get_prev"];


if (isset($_GET['med'])) {
	$active = "active";
	$hos_no = $_GET['med'];
	$stmt = $db->prepare("SELECT e.*, i.insurance_name, i.interest, i.payment_mode, i.services_access, i.insurance_type 
		FROM enrollee as e 
		INNER JOIN insurance_tbl as i 
		ON e.hmo_no = i.insurance_no 
		WHERE hospital_no = :hospital_no AND status = :status AND vip=0");

	$stmt->bindParam(':hospital_no', $hos_no);
	$stmt->bindParam(':status', $active);

	$stmt->execute();

	if ($stmt->rowCount() > 0) {
		$row_rstSelect_d = $stmt->fetch(PDO::FETCH_ASSOC);
	} else {
		//header("location:");
	}
}


if (isset($_POST["med_reports"])) {
	$title = 'Patient Medical Report';
} elseif (isset($_POST["show_logs"])) {
	$title = 'Patient Logs';
}

?>


<div class="row">

	<div class="col-lg-12">
		<div class="ibox float-e-margins">
			<div class="ibox-title">
				<h5><?php echo $title; ?></h5>

			</div>

			<div class="ibox-content">

				<div class="col-sm-12" id="content">
					<div align="left" style="font:bold 14px 'Arial';">
					</div>
					<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;  ">
						<tr>
							<td width="50%" align="left"><img src="../img/logo.png" width="196" height="111"></td>
							<td width="50%" align="right"><img src="<?php if (file_exists(enrollee_p . $hos_no . '.' . 'jpg')) {
																		echo enrollee_p . $hos_no . '.' . 'jpg';
																	} else {
																		echo '../img/user_avatar_lg.png';
																	} ?>" alt="" height="100" width="100" class="img-thumbnail user_avatar"></td>
						</tr>

					</table>

					<div align="center">
						<div style="font:bold 18px 'Arial';"><?php echo $title; ?></div>
						<?php
						echo '<br>Dates: ' . date("d M,Y", strtotime($start));
						echo ' - ';
						echo  date("d M,Y", strtotime($end));
						?>
						<br></br>
					</div>

					<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
						<tr bgcolor="#FFCC66" style="font-weight:100">
							<td style="font:bold 14px 'Arial'; padding:5px;" width="30%">Patient No: </td>
							<td style="font:bold 14px 'Arial'; padding:5px;" width="30%">Insurance Coverage</td>
							<td style="font:bold 14px 'Arial'; padding:5px;" width="30%">Patient Name: </td>
						</tr>
						<tr>
							<td style="padding:5px;"><?php echo '<i><strong>Hospital Number:</strong></i>' . ' ' . $row_rstSelect_d['hospital_no']; ?></td>
							<td style="padding:5px;"><?php echo $row_rstSelect_d['nhis_no'] . ' (' . $row_rstSelect_d['insurance_type'] . ')'; ?></td>
							<td style="padding:5px;"><?php echo $row_rstSelect_d['surname'] . ', ' . $row_rstSelect_d['fname'] . ' ' . $row_rstSelect_d['oname']; ?></td>
						</tr>
						<tr bgcolor="#FFCC66">
							<td style="font:bold 14px 'Arial'; padding:5px;">Age: </td>
							<td style="font:bold 14px 'Arial'; padding:5px;">Sex: </td>
							<td style="font:bold 14px 'Arial'; padding:5px;">Last Presentation Date: </td>
						</tr>
						<tr>
							<td><?php echo $row_rstSelect_d['age'] . 'year(s)'; ?></td>
							<td><?php echo ucfirst($row_rstSelect_d['gender']); ?></td>
							<td></td>
						</tr>
					</table>


					<?php



					if (isset($_POST["med_reports_"])) {
						$status = 'medical';
						///exit;

						include_once("encounter.php");
					} elseif (isset($_POST["show_logs"])) {
						$status = 'logs';
						include_once("encounter.php");
					}

					?>



				</div>



				<div class="form_sep" align="right">
					<div class="pull-right" style="margin-right:100px;">
						<a href="javascript:Clickheretoprint()" style="font-size:20px;"><button class="btn btn-success btn-sm"><i class="icon-print"></i> Print</button></a>

						&nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;&nbsp;

						<a href="index.php?ptm=<?php echo $get_prev; ?>" style="font-size:20px;"><button class="btn btn-danger btn-sm"><i class="icon-print"></i> Close</button></a>
					</div>
				</div>




			</div>

		</div>
	</div>

</div>