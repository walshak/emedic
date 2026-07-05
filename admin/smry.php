<div class="row">

	<div class="col-lg-12">
		<div class="ibox float-e-margins">
			<div class="ibox-title">
				<h5></h5>
			</div>

			<div class="ibox-content">

				<div id="content">
					<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
						<tr>
							<td width="50%" align="left"><img src="../img/logo.png" width="196" height="111"></td>
							<td width="50%" align="right">
								<div style="font-size:18px; font:Verdana, Geneva, sans-serif""><strong><?php echo $_SESSION['h_name']; ?></strong></div> <br><div style=" font-size:14px"><?php echo $_SESSION['h_address']; ?><br><br> <?php echo $_SESSION['h_phone']; ?></div>
							</td>
						</tr>
					</table>
					<hr>



					<table border="0" style="font-family: arial; font-size: 13px;text-align:left;width:100%; padding:10px; ">
						<thead>
							<tr>
								<th style="border-bottom: 1px solid #ddd; padding:5px;">No</th>
								<th style="border-bottom: 1px solid #ddd; padding:5px;">Doctor Name</th>
								<th style="border-bottom: 1px solid #ddd; padding:5px;">Consultation/<br>Other Services</th>
								<th style="border-bottom: 1px solid #ddd; padding:5px;">Investigations</th>
								<th style="border-bottom: 1px solid #ddd; padding:5px;">Medications<br>Consumables</th>
								<th style="border-bottom: 1px solid #ddd; padding:5px;">Surgeries/<br>Procedures</th>
								<th style="border-bottom: 1px solid #ddd; padding:5px;">Nursing Services</th>
								<th style="border-bottom: 1px solid #ddd; padding:5px;">All Services</th>
								<th style="border-bottom: 1px solid #ddd; padding:5px;">Total</th>
							</tr>
						</thead>
						<tbody>

							<?php

							$fullname = $_SESSION['fullname'];
							$n = 1;
							$stmt = $db->query("SELECT distinct doctor_name FROM temp_doc_income where staffname='$fullname'");
							while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) {
								$doctor_name = $rwx['doctor_name'];

								$stmt2 = $db->query("SELECT * FROM temp_doc_income where staffname='$fullname' and doctor_name='$doctor_name' order by doctor_name");

								$Consultation = 0;
								$Investigations = 0;
								$Medications = 0;
								$Surgeries = 0;
								$Nursing = 0;

								while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
									if ($row['services_type'] == 'Other Services') {
										$Consultation = $Consultation + $row['total_amount'];
										$TConsultations = $TConsultations + $row['total_amount'];
									}
									if ($row['services_type'] == 'Consultation') {
										$Consultation = $Consultation + $row['total_amount'];
										$TConsultations = $TConsultations + $row['total_amount'];
									}

									if ($row['services_type'] == 'Investigations') {
										$Investigations = $Investigations + $row['total_amount'];
										$TInvestigations = $TInvestigations + $row['total_amount'];
									}
									if ($row['services_type'] == 'Medications/Consumables') {
										$Medications = $Medications + $row['total_amount'];
										$TMedications = $TMedications + $row['total_amount'];
									}
									if ($row['services_type'] == 'Surgeries/Procedures') {
										$Surgeries = $Surgeries + $row['total_amount'];
										$TSurgeries = $TSurgeries + $row['total_amount'];
									}
									if ($row['services_type'] == 'Nursing Services') {
										$Nursing = $Nursing + $row['total_amount'];
										$TNursing = $TNursing + $row['total_amount'];
									}

									if ($row['services_type'] == 'All Services') {
										$AllServices = $AllServices + $row['total_amount'];
										$TAllServices = $TAllServices + $row['total_amount'];
									}
								}
							?>

								<tr>
									<td style="border-bottom: 1px solid #ddd;"><?php echo $n; ?></td>
									<td style="border-bottom: 1px solid #ddd;"><?php echo $doctor_name; ?></td>
									<td style="border-bottom: 1px solid #ddd;"><?php echo number_format($Consultation); ?></td>
									<td style="border-bottom: 1px solid #ddd;"><?php echo number_format($Investigations); ?></td>
									<td style="border-bottom: 1px solid #ddd;"><?php echo number_format($Medications); ?></td>
									<td style="border-bottom: 1px solid #ddd;"><?php echo number_format($Surgeries); ?></td>
									<td style="border-bottom: 1px solid #ddd;"><?php echo number_format($Nursing); ?></td>
									<td style="border-bottom: 1px solid #ddd;"><?php echo number_format($AllServices); ?></td>
									<td style="border-bottom: 1px solid #ddd;"><?php $Total = $Consultation + $Investigations + $Medications + $Surgeries + $Nursing + $AllServices;
																				echo number_format($Total) ?></td>
								</tr>

							<?php $n = $n + 1;
							} ?>


						</tbody>
					</table>
					<HR>
					<strong style="font-size:16px;">GRAND TOTAL</strong>

					<table width="100%">
						<tr>
							<td>
								<strong style="font-size:16px;">Consultation: <?php echo number_format($TConsultations); ?></strong>
							</td>
							<td>
								<strong style="font-size:16px;">Investigations: <?php echo number_format($TInvestigations); ?></strong>
							</td>
							<td>
								<strong style="font-size:16px;">Medications/<br>Consumables: <?php echo number_format($TMedications); ?></strong>
							</td>
							<td>
								<strong style="font-size:16px;">Surgeries: <?php echo number_format($TSurgeries); ?></strong>
							</td>
							<td>
								<strong style="font-size:16px;">Nursing Services: <?php echo number_format($TNursing); ?></strong>
							</td>
							<td>
								<strong style="font-size:16px;">All Services: <?php echo number_format($AllServices); ?></strong>
							</td>
						</tr>
					</table>
					<strong style="font-size:16px;">Total: <?php
															$TTotal = $TConsultations + $ITnvestigations + $TMedications + $TSurgeries + $TNursing + $AllServices;
															echo number_format($TTotal); ?></strong>





				</div>
				<hr>

				<a href="javascript:Clickheretoprint()" target="_blank" class="btn btn-primary btn-xs"><i class="fa fa-print"></i> Print Report </a>

				&nbsp;&nbsp; | &nbsp;&nbsp;

				<a href="index.php?doc" class="btn btn-danger btn-xs"><i class="fa fa-times"></i> Close Report </a>

			</div>

		</div>
	</div>


</div>


<script>
	function Clickheretoprint() {
		var disp_setting = "toolbar=yes,location=no,directories=yes,menubar=yes,";
		disp_setting += "scrollbars=yes,width=800, height=400, left=100, top=25";
		var content_vlue = document.getElementById("content").innerHTML;

		var docprint = window.open("", "", disp_setting);
		docprint.document.open();
		docprint.document.write('</head><body onLoad="self.print()" style="width: 800px; font-size: 13px; font-family: arial;">');
		docprint.document.write(content_vlue);
		docprint.document.close();
		docprint.focus();
	}
</script>