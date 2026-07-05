<?php
// Start output buffering to avoid accidental output issues
ob_start();

// Database assumed as $db (PDO)

// Clear temp_claim table once per page load
$db->exec("DELETE FROM temp_claim");

// Capture form inputs safely
$Department = isset($_POST['Department']) ? trim($_POST['Department']) : '';
$validation_status = isset($_POST['validation_status2']) ? trim($_POST['validation_status2']) : '0';
$start = isset($_POST['from_date']) ? $_POST['from_date'] : date('Y-m-d');
$to = isset($_POST['to_date']) ? $_POST['to_date'] : date('Y-m-d');
$hmo_nhis = isset($_POST['hmo_nhis']) ? $_POST['hmo_nhis'] : [];
$hmo_type = isset($_POST['hmo_type']) ? trim($_POST['hmo_type']) : '';

$dept_s = $Department ? "AND ap.dept_id = :dept_id" : "";
$dept_s2 = $Department ? "AND dept_id = :dept_id" : "";

?>

<div class="row">
	<div class="col-lg-12">
		<div class="ibox float-e-margins">
			<div class="ibox-content" id="content">

				<!-- Hospital Header -->
				<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
					<tr>
						<td width="50%" align="left"><img src="img/logo.png" width="196" height="111"></td>
						<td width="50%" align="right">
							<div style="font-size:18px; font:Verdana, Geneva, sans-serif"><strong><?= $_SESSION['h_name'] ?></strong></div>
							<div style="font-size:14px"><?= $_SESSION['h_address'] ?><br><br> <?= $_SESSION['h_phone'] ?></div>
						</td>
					</tr>
				</table>
				<hr>

				<!-- Report Title -->
				<div align="center" style="font-size:20px; font:Verdana, Geneva, sans-serif;">
					<?php
					echo 'HMO/Corporate Reports: ' . htmlspecialchars($hmo_type) . '<br>';
					echo 'Between: ' . date('d M,Y', strtotime($start)) . ' - ' . date('d M,Y', strtotime($to)) . '<br>';
					echo $validation_status == '1' ? '<strong>Posted Claims</strong>' : 'Not Posted Claims';
					?>
				</div>
				<hr>

				<?php
				if (!empty($hmo_nhis)) {
					$Tclaim_hmo = 0;
					$Tinterest = 0;

					foreach ($hmo_nhis as $hmo_nhis_no_raw) {
						list($hmo_nhis_no, $hmo_name) = explode("__", $hmo_nhis_no_raw);
						$sql_enl = "SELECT DISTINCT enl.hospital_no, enl.surname, enl.fname
                            FROM enrollee AS enl
                            INNER JOIN patient_ap_services AS ap ON ap.hospital_no = enl.hospital_no
                            WHERE enl.hmo_no = :hmo_no AND enl.insurance = :hmo_type
                            AND DATE(ap.date_entry) BETWEEN :start AND :to
                            $dept_s";
						$stmt_enl = $db->prepare($sql_enl);
						$stmt_enl->bindValue(':hmo_no', $hmo_nhis_no);
						$stmt_enl->bindValue(':hmo_type', $hmo_type);
						$stmt_enl->bindValue(':start', $start);
						$stmt_enl->bindValue(':to', $to);
						if ($Department) $stmt_enl->bindValue(':dept_id', $Department);

						$stmt_enl->execute();

						if ($stmt_enl->rowCount() > 0) {
							///echo 'Count. ' . $stmt_enl->rowCount();
							echo '<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">';
							echo '<thead><tr bgcolor="#CCCCCC"><th>Hospital #</th><th>Patient Name</th><th>Total Claim</th><th>Total Interest</th><th>HMO</th></tr></thead><tbody>';

							while ($row_enl = $stmt_enl->fetch(PDO::FETCH_ASSOC)) {
								$hospital_no = $row_enl['hospital_no'];
								$NAME = htmlspecialchars($row_enl['surname'] . ' ' . $row_enl['fname']);

								$sql_claim = "SELECT SUM(claim_amt) AS t_claim, SUM(interest) AS t_interest
                                    FROM patient_ap_services
                                    WHERE hospital_no = :hospital_no AND process_claim = :validation_status
                                    AND DATE(date_entry) BETWEEN :start AND :to
                                    $dept_s2";
								$stmt_claim = $db->prepare($sql_claim);
								$stmt_claim->bindValue(':hospital_no', $hospital_no);
								$stmt_claim->bindValue(':validation_status', $validation_status);
								$stmt_claim->bindValue(':start', $start);
								$stmt_claim->bindValue(':to', $to);
								if ($Department) $stmt_claim->bindValue(':dept_id', $Department);

								$stmt_claim->execute();
								$row_claim = $stmt_claim->fetch(PDO::FETCH_ASSOC);

								if ($row_claim['t_claim'] > 0) {
									$Tclaim_hmo += $row_claim['t_claim'];
									$Tinterest += $row_claim['t_interest'];

									echo "<tr>
                                        <td>{$hospital_no}</td>
                                        <td>{$NAME}</td>
                                        <td>" . number_format($row_claim['t_claim']) . "</td>
                                        <td>" . number_format($row_claim['t_interest']) . "</td>
                                        <td>" . htmlspecialchars($hmo_name) . "</td>
                                    </tr>";

									$insert = $db->prepare("INSERT INTO temp_claim (a1, a2, a3, a4, a5) VALUES (:a1, :a2, :a3, :a4, :a5)");
									$insert->execute([
										':a1' => $hospital_no,
										':a2' => $NAME,
										':a3' => $row_claim['t_claim'],
										':a4' => $row_claim['t_interest'],
										':a5' => $hmo_name
									]);
								}
							}
							echo '</tbody></table>';
						} else {
							echo "<h3>NO RECORDS FOUND.</h3>";
						}
					}

					if ($Tclaim_hmo > 0) {
						$db->exec("INSERT INTO temp_claim (a3, a4) VALUES ('$Tclaim_hmo', '$Tinterest')");
						echo "<hr><h2>Grand Total (Claim): " . number_format($Tclaim_hmo, 2) . "</h2>";
						echo "<h2>Grand Total (Interest): " . number_format($Tinterest, 2) . "</h2>";
					}
				} else {
					echo 'Invalid selection';
				}
				?>
				<!-- Action Buttons -->
				<form action="download_code.php" method="POST" id="subject" name="subject">
					<div class="form_sep">
						<div class="pull-left" style="margin-right:100px;">
							<a href="index.php?cptclaim" style="font-size:20px;" class="btn btn-danger btn-large">Close</a>
						</div>

						<div class="pull-right" style="margin-right:100px;">
							<button class="btn btn-success btn-large" type="button" onclick="Clickheretoprint()"><i class="icon-print"></i> Print</button>
							&nbsp;&nbsp;|&nbsp;&nbsp;
							<button class="btn btn-primary btn-large" type="submit" name="apply_rpt">Download</button>
						</div>
					</div>
				</form>

			</div>
		</div>
	</div>
</div>

<script>
	function Clickheretoprint() {
		var disp_setting = "toolbar=yes,location=no,directories=yes,menubar=yes,scrollbars=yes,width=800,height=400,left=100,top=25";
		var content_value = document.getElementById("content").innerHTML;
		var docprint = window.open("", "", disp_setting);
		docprint.document.open();
		docprint.document.write('<body onLoad="self.print()" style="width: 800px; font-size: 13px; font-family: arial;">');
		docprint.document.write(content_value);
		docprint.document.close();
		docprint.focus();
	}
</script>

<?php ob_end_flush(); ?>