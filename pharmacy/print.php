<?php

function formatQuantity($qty, $units)
{
	if ($units > 1) {
		$pack = floor($qty / $units);
		$pcs  = $qty % $units;

		$parts = [];
		if ($pack > 0) $parts[] = "$pack (pck)";
		if ($pcs > 0)  $parts[] = "$pcs (pcs)";

		return implode(' & ', $parts);
	} else {
		return $qty . ' (pcs)';
	}
}
?>


<div class="row">

	<div class="col-lg-12">
		<div class="ibox float-e-margins">
			<div class="ibox-title">
				<h5>Report</h5>
			</div>
			<div class="ibox-content" id="content">

				<?php

				$stmt = $db->query("SELECT sn FROM department WHERE department='Pharmacy'");
				$row_rstSelect = $stmt->fetch(PDO::FETCH_ASSOC);
				$dept_id = $row_rstSelect['sn']; ?>

				<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
					<tr>
						<td width="50%" align="left"><img src="../img/logo.png" width="196" height="111"></td>
						<td width="50%" align="right">
							<div style="font-size:18px; font:Verdana, Geneva, sans-serif"><strong><?php echo $_SESSION['h_name']; ?></strong></div> <br>
							<div style="font-size:14px"><?php echo $_SESSION['h_address']; ?><br><br> <?php echo $_SESSION['h_phone']; ?></div>
						</td>
					</tr>
				</table>
				<hr>


				<div align="center" style="font-size:20px; font:Verdana, Geneva, sans-serif;">
					<?php

					$Pharmacy = 'Pharmacy';
					$one = 1;
					$zero = 0;
					$qty = 0;
					$pay_credit = null;
					$colordecide = null;
					$staff2 = null;
					$t_qty = null;
					$dsp_by = null;


					if (isset($_POST['next1'])) {
						header("location:pharm_rpt.php");
					}

					$desccc = !empty($_POST['desccc']) ? $_POST['desccc'] : null;
					$rpt_type = !empty($_POST['rpt_type']) ? $_POST['rpt_type'] : null;
					$searchdrug_inv = !empty($_POST['searchdrug_inv']) ? $_POST['searchdrug_inv'] : null;



					if (isset($_POST['staff']) and $_POST['staff'] != '') {

						if ($_POST['staff'] == 'All Staff') {
							$staff_name = $_SESSION['fullname'] . ' (All Staff)';
							$staff = "";
						} else {
							$fullname = $_POST['staff'];

							if ($rpt_type == 'at' or $rpt_type == 'ap') {
								$staff = " and inv.enter_by='$fullname'";
							}
							if ($rpt_type == 'ps' or $rpt_type == 'dcr' or $rpt_type == 'd_r') {
								$staff = " and dsp_by='$fullname'";
							}
							if ($rpt_type == 'inv') {
								// staff filters (example from your snippet)
								if ($rpt_type == 'inv') {
									$staff  = " AND dsp_by = '$fullname'";
									$staff2 = " AND enter_by = '$fullname'";
								} else {
									$staff  = "";
									$staff2 = "";
								}
							}
							if ($rpt_type == 'inv_summary') {
								// staff filters (example from your snippet)
								if ($rpt_type == 'inv_summary') {
									$staff  = " AND dsp_by = '$fullname'";
									$staff2 = " AND enter_by = '$fullname'";
								} else {
									$staff  = "";
									$staff2 = "";
								}
							}
							$staff_name = $fullname;
						}
						//echo $fullname  . 'ggg';
					} else {
						$fullname = $_SESSION['fullname'];
						$staff_name = $_SESSION['fullname'];
					}

					//


					if (isset($_GET['all'])) { ?>
						All Drugs In-stocks
					<?php	} elseif ($desccc == 'Expired') { ?>
						All Expired Drugs
					<?php	} elseif ($desccc == 'rorder') { ?>
						Drugs Re-order list

					<?php } elseif ($rpt_type == 'at') { ?>
						Today's Sales Report (Summary) (<?php
														$start = $_POST['from_date'];
														$to = $_POST['to_date'];
														if ($start != '' and $to != '') {
															echo 'between: ' . date('d M,Y', strtotime($start)) . ' - ' . date('d M,Y', strtotime($to));
															$ddset = 1;
														} else {
															$setdate = date('Y-m-d');
															echo date('d M,Y', strtotime($setdate));
															$ddset = 0;
														} ?>)
					<?php } elseif ($desccc == 'Expiring') {
						$days = ($_POST['days']);
						echo 'List of drugs expiring in ' . $days . ' day(s)';
					} elseif ($rpt_type == 'ap') { ?>
						Today's Sales Report (Summary by Drug Name) (<?php
																		$start = $_POST['from_date'];
																		$to = $_POST['to_date'];
																		if ($start != '' and $to != '') {
																			echo 'between: ' . date('d M,Y', strtotime($start)) . ' - ' . date('d M,Y', strtotime($to));
																			$ddset = 1;
																		} else {
																			$setdate = date('Y-m-d');
																			echo date('d M,Y', strtotime($setdate));
																			$ddset = 0;
																		} ?>)
					<?php } elseif ($desccc == 'Expiring') {
						$days = ($_POST['days']);
						echo 'List of drugs expiring in ' . $days . ' day(s)';
					} elseif ($rpt_type == 'doc_pres') {
						$start = $_POST['from_date'];
						$to = $_POST['to_date'];
						if ($start != '' and $to != '') {
							echo 'Report of Prescription Dispense/Not Dispense Status for Patients<br>';
							echo 'between: ' . date('d M,Y', strtotime($start)) . ' - ' . date('d M,Y', strtotime($to));
							$ddset = 1;
						} else {
							$setdate = date('Y-m-d');
							echo 'Today, ' . date('d,M Y', strtotime($setdate)) . '<br>Report of Prescription Dispense/Not Dispense Status for Patients';
							$ddset = 0;
						}
					} elseif ($rpt_type == 'ps') {

						$start = $_POST['from_date'];
						$to = $_POST['to_date'];
						if ($start != '' and $to != '') {
							echo 'Report of Patients Seen and Drugs Details<br>';
							echo 'between: ' . date('d M,Y', strtotime($start)) . ' - ' . date('d M,Y', strtotime($to));
							$ddset = 1;
						} else {
							$setdate = date('Y-m-d');
							echo 'Today, ' . date('d,M Y', strtotime($setdate)) . '<br>Report of Patients Seen and Drugs Details';
							$ddset = 0;
						}
					} elseif ($rpt_type == 'dcr') {
						$start = $_POST['from_date'];
						$to = $_POST['to_date'];
						if ($start != '' and $to != '') {
							echo 'Report of Dispensed on Credit<br>';
							echo 'between: ' . date('d M,Y', strtotime($start)) . ' - ' . date('d M,Y', strtotime($to));
							$ddset = 1;
						} else {
							$setdate = date('Y-m-d');
							echo 'Today, ' . date('d,M Y', strtotime($setdate)) . '<br>Report of Patients Dispensed on Credit';
							$ddset = 0;
						}
					} elseif ($rpt_type == 'inv') {

						$start = $_POST['from_date'];
						$to = $_POST['to_date'];
						if ($start != '' and $to != '') {
							echo 'Inventory Report<br>';
							echo 'between: ' . date('d M,Y', strtotime($start)) . ' - ' . date('d M,Y', strtotime($to));
							$ddset = 1;
						} else {
							$setdate = date('Y-m-d');
							echo 'Today, ' . date('d,M Y', strtotime($setdate)) . '<br>Inventory Report';
							$ddset = 0;
						}
					} elseif ($rpt_type == 'inv_summary') {

						$start = $_POST['from_date'];
						$to = $_POST['to_date'];
						if ($start != '' and $to != '') {
							echo 'Inventory Report<br>';
							echo 'between: ' . date('d M,Y', strtotime($start)) . ' - ' . date('d M,Y', strtotime($to));
							$ddset = 1;
						} else {
							$setdate = date('Y-m-d');
							echo 'Today, ' . date('d,M Y', strtotime($setdate)) . '<br>Inventory Report';
							$ddset = 0;
						}
					} elseif ($rpt_type == 'd_r') {

						$start = $_POST['from_date'];
						$to = $_POST['to_date'];
						if ($start != '' and $to != '') {
							echo 'Drugs Returned<br>';
							echo 'between: ' . date('d M,Y', strtotime($start)) . ' - ' . date('d M,Y', strtotime($to));
							$ddset = 1;
						} else {
							$setdate = date('Y-m-d');
							echo 'Today, ' . date('d,M Y', strtotime($setdate)) . '<br>Drugs Returned';
							$ddset = 0;
						}
					} elseif ($rpt_type == 'inv_drug') {

						$start = $_POST['from_date'];
						$to = $_POST['to_date'];
						if ($start != '' and $to != '') {
							echo 'Stock Inventory<br>';
							echo 'between: ' . date('d M,Y', strtotime($start)) . ' - ' . date('d M,Y', strtotime($to));
							$ddset = 1;
						} else {
							$setdate = date('Y-m-d');
							echo 'Today, ' . date('d,M Y', strtotime($setdate)) . '<br>Stock Inventory: ' . $searchdrug_inv;
							$ddset = 0;
						}
					} elseif ($rpt_type == 'rhmo') {

						$start = $_POST['from_date'];
						$to = $_POST['to_date'];

						$hmo_nhis = isset($_POST['hmo_nhis']) && !empty($_POST['hmo_nhis']) ? $_POST['hmo_nhis'] : '';
						$hmo_type = isset($_POST['hmo_type']) && !empty($_POST['hmo_type']) ? $_POST['hmo_type'] : '';


						if ($start != '' and $to != '') {
							echo 'HMO/Corporate Reports: ' . $hmo_type . '<br>';
							echo 'between: ' . date('d M,Y', strtotime($start)) . ' - ' . date('d M,Y', strtotime($to));
							$ddset = 1;
						} else {
							$setdate = date('Y-m-d');
							echo 'Today, ' . date('d,M Y', strtotime($setdate)) . '<br>HMO/Corporate Reports: ' . $hmo_type;
							$ddset = 0;
						}
					} elseif ($rpt_type == 'visit') {

						$start = $_POST['from_date'];
						$to = $_POST['to_date'];

						if ($start != '' and $to != '') {
							echo 'Patient Visit Reports<br>';
							echo 'between: ' . date('d M,Y', strtotime($start)) . ' - ' . date('d M,Y', strtotime($to));
							$ddset = 1;
						} else {
							$setdate = date('Y-m-d');
							echo 'Today, ' . date('d,M Y', strtotime($setdate)) . '<br>Patient visit reports';
							$ddset = 0;
						}
					}


					?>

				</div>

				<hr>


				<?php


				$setdate = date('Y-m-d');
				$desccc = !empty($_POST['desccc']) ? $_POST['desccc'] : null;
				if (isset($_GET['all']) or $desccc == 'Expired' or $desccc == 'rorder' or $desccc == 'Expiring') {

					if (isset($_GET['all'])) {
						$stmt = $db->prepare("SELECT * FROM stock_table WHERE stock_table = :stock_table ORDER BY product_name");
						$stmt->bindValue(':stock_table', $Pharmacy, PDO::PARAM_STR);
					} elseif ($desccc == 'Expired') {
						$stmt = $db->prepare("SELECT s.* FROM stock_table s 
							  INNER JOIN stock_table_procurment as p ON s.sn = p.stock_sn 
							  WHERE date(p.expiry_date) <= :setdate 
							  AND finish_status = '0' 
							  AND p.status = 'yes' 
							  AND s.stock_table = 'Pharmacy' 
							  ORDER BY s.product_name");
						$stmt->bindValue(':setdate', $setdate, PDO::PARAM_STR);
					} elseif ($desccc == 'Expiring') {
						$setdate = date('Y-m-d');
						$days = !empty($_POST['days']) ? $_POST['days'] : 1;
						$date = date('Y-m-d', strtotime($setdate . " + $days days"));

						$stmt = $db->prepare("SELECT s.* FROM stock_table s 
							  INNER JOIN stock_table_procurment as p ON s.sn = p.stock_sn 
							  WHERE p.expiry_date <= :date 
							  AND finish_status = '0' 
							  AND p.status = 'yes' 
							  AND s.stock_table = 'Pharmacy' 
							  ORDER BY s.product_name");
						$stmt->bindValue(':date', $date, PDO::PARAM_STR);
					} elseif ($desccc == 'rorder') {
						$stmt = $db->prepare("SELECT * FROM stock_table WHERE stock_table = :stock_table 
							  AND qty = reorder_level 
							  ORDER BY product_name, dosage");
						$stmt->bindValue(':stock_table', $Pharmacy, PDO::PARAM_STR);
					}

					$stmt->execute();

					if (($desccc == 'Expired' or $desccc == 'Expiring') && $stmt->rowCount() == 0) {

						if ($desccc == 'Expired') {
							$stmt = $db->prepare("SELECT * FROM stock_table WHERE p.expiry_date <= :date 		
						AND status = 'active' 
						AND stock_table = 'Pharmacy' 
						ORDER BY product_name");
							$stmt->bindValue(':setdate', $setdate, PDO::PARAM_STR);
						} else {
							$setdate = date('Y-m-d');
							$days = !empty($_POST['days']) ? $_POST['days'] : 1;
							$date = date('Y-m-d', strtotime($setdate . " + $days days"));
							$stmt = $db->prepare("SELECT * FROM stock_table 
							WHERE date(expire_date) BETWEEN '$setdate' AND '$date' AND status = 'active' AND stock_table = 'Pharmacy' ORDER BY product_name");
						}
						$stmt->execute();
					}


					if ($stmt->rowCount() > 0) { ?>

						<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
							<thead>
								<tr bgcolor="#CCCCCC">
									<th width="2%">#</th>
									<th width="20%">Name </th>
									<th width="10%">Formulation </th>
									<th width="7%">Buying cost </th>
									<th width="7%">NHIS</th>
									<th width="7%">Hosp. Price</th>
									<th width="7%">Qty</th>
									<th width="10%">Expired<br>Date</th>

								</tr>
							</thead>
							<tbody>


								<?php $n = 1;
								while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
									<tr class="record">
										<td style="border-bottom: 1px solid #ddd;"><?php echo $n; ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?php echo $row['product_name']; ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?php echo $row['dosage']; ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?php echo $row['buying_cost']; ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?php echo $row['nhis_price']; ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?php echo $row['hosp_price']; ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?php echo $row['qty']; ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?php echo $row['expire_date']; ?></td>
									</tr>
								<?php
									$n++;
								}
								?>
							</tbody>
						</table>
						<div><b><i><?php echo 'Total row(s) found: ' . $stmt->rowCount(); ?></i></b></div>
					<?php }
				} elseif ($rpt_type == 'at') {

					$setdate = date('Y-m-d');

					// Prepare the base query to get distinct drugs
					$drugQuery = "SELECT DISTINCT p.drug_sn 
								  FROM patient_ap_services AS p 
								  INNER JOIN stock_table_inven AS inv ON inv.stock_sn = p.drug_sn 
								  WHERE p.paystatus = :paystatus 
								  AND (p.dept_id = :dept_id OR p.dept_dispensory_id = :dept_dispensory_id) 
								  $staff";

					// Add date condition based on $ddset
					if ($ddset == 1) {
						$drugQuery .= " AND date(p.transact_date) BETWEEN :start_date AND :end_date";
					} else {
						$drugQuery .= " AND date(p.transact_date) = :setdate";
					}

					$drugStmt = $db->prepare($drugQuery);
					$drugStmt->bindValue(':paystatus', $one, PDO::PARAM_STR);
					$drugStmt->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
					$drugStmt->bindValue(':dept_dispensory_id', $dept_id, PDO::PARAM_STR);

					if ($ddset == 1) {
						$drugStmt->bindValue(':start_date', $start, PDO::PARAM_STR);
						$drugStmt->bindValue(':end_date', $to, PDO::PARAM_STR);
					} else {
						$drugStmt->bindValue(':setdate', $setdate, PDO::PARAM_STR);
					}

					$drugStmt->execute();

					if ($drugStmt->rowCount() > 0) {
						// Prepare the transaction query template (we'll reuse it)
						$txnQuery = "SELECT p.qty AS p_qty, p.serv_group, p.claim_amt, p.pay, 
									d.product_name, d.qty AS d_qty
									FROM patient_ap_services AS p
									INNER JOIN stock_table AS d ON d.sn = p.drug_sn
									INNER JOIN stock_table_inven AS inv ON p.sn = inv.sale_sn
									WHERE p.paystatus = :paystatus 
									AND p.drug_sn = :drug_sn 
									AND (p.dept_id = :dept_id OR p.dept_dispensory_id = :dept_dispensory_id) 
									$staff";

						if ($ddset == 1) {
							$txnQuery .= " AND date(p.transact_date) BETWEEN :start_date AND :end_date";
						} else {
							$txnQuery .= " AND date(p.transact_date) = :setdate";
						}

						$txnStmt = $db->prepare($txnQuery);
						$txnStmt->bindValue(':paystatus', $one, PDO::PARAM_STR);
						$txnStmt->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
						$txnStmt->bindValue(':dept_dispensory_id', $dept_id, PDO::PARAM_STR);

						// Initialize totals
						$t_pos = $t_nhis = $t_pvt = $t_claim = $t_nhis_90 = 0;
					?>

						<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px; text-align: left; width: 100%;">
							<thead>
								<tr bgcolor="#CCCCCC">
									<th width="20%">Name</th>
									<th width="7%">Quantity<br>Dispensed</th>
									<th width="7%">Quantity<br>in-stock</th>
									<th width="40%">
										<table width="100%">
											<tr>
												<td colspan="4" align="center">&nbsp;</td>
											</tr>
											<tr>
												<td align="right" width="25%">POS <br>(External Sales)</td>
												<td align="right" width="25%">Private<br>(Patients)</td>
												<td align="right" width="25%">NHIS<br>(10% Payments)</td>
												<td align="right" width="25%">CLAIM<br>(Zero Payable)</td>
											</tr>
										</table>
									</th>
								</tr>
							</thead>
							<tbody>
								<?php while ($drug = $drugStmt->fetch(PDO::FETCH_ASSOC)) {
									$drug_sn = $drug['drug_sn'];

									// Bind parameters and execute transaction query
									$txnStmt->bindValue(':drug_sn', $drug_sn, PDO::PARAM_STR);
									if ($ddset == 1) {
										$txnStmt->bindValue(':start_date', $start, PDO::PARAM_STR);
										$txnStmt->bindValue(':end_date', $to, PDO::PARAM_STR);
									} else {
										$txnStmt->bindValue(':setdate', $setdate, PDO::PARAM_STR);
									}
									$txnStmt->execute();

									// Initialize drug totals
									$qty = $pos = $nhis = $pvt = $claim = $nhis_90 = 0;
									$drug_name = '';
									$d_qty = 0;

									while ($txn = $txnStmt->fetch(PDO::FETCH_ASSOC)) {
										$qty += $txn['p_qty'];
										$drug_name = $txn['product_name'];
										$d_qty = $txn['d_qty'];

										if ($txn['serv_group'] === 'EX') {
											$pos += $txn['pay'];
										} elseif ($txn['claim_amt'] > 0 && $txn['pay'] > 0) {
											$nhis += $txn['pay'];
											$nhis_90 += $txn['claim_amt'];
										} elseif ($txn['claim_amt'] == 0 && $txn['pay'] > 0) {
											$pvt += $txn['pay'];
										} elseif ($txn['claim_amt'] > 0 && $txn['pay'] == 0) {
											$claim += $txn['claim_amt'];
										}
									}

									// Update grand totals
									$t_pos += $pos;
									$t_nhis += $nhis;
									$t_nhis_90 += $nhis_90;
									$t_pvt += $pvt;
									$t_claim += $claim;
								?>
									<tr class="record">
										<td style="border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($drug_name); ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?php echo $qty; ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?php echo $d_qty; ?></td>
										<td width="40%" style="border-bottom: 1px solid #ddd;">
											<table width="100%">
												<tr>
													<td width="25%" align="right"><?php echo number_format($pos, 2); ?></td>
													<td width="25%" align="right"><?php echo number_format($pvt, 2); ?></td>
													<td width="25%" align="right"><?php echo number_format($nhis, 2); ?></td>
													<td width="25%" align="right"><?php echo number_format($claim, 2); ?></td>
												</tr>
											</table>
										</td>
									</tr>
								<?php } ?>

								<tr class="record">
									<td style="border-bottom: 1px solid #ddd;"><b>Summary (Total):</b></td>
									<td style="border-bottom: 1px solid #ddd;"></td>
									<td style="border-bottom: 1px solid #ddd;"></td>
									<td width="40%" style="border-bottom: 1px solid #ddd;">
										<table width="100%">
											<tr>
												<td width="25%" align="right"><strong><?php echo number_format($t_pos, 2); ?></strong></td>
												<td width="25%" align="right"><strong><?php echo number_format($t_pvt, 2); ?></strong></td>
												<td width="25%" align="right">
													<strong>
														<?php echo number_format($t_nhis, 2); ?> (10%)<br>
														<?php echo number_format($t_nhis_90, 2); ?> (90%)
													</strong>
												</td>
												<td width="25%" align="right"><strong><?php echo number_format($t_claim, 2); ?></strong></td>
											</tr>
										</table>
									</td>
								</tr>
							</tbody>
						</table>

						<br><br>
						<div style="font-size:14px"><strong><i>GENERATED BY:</i></strong></div>
						<strong style="font-size:14px;"><?php echo htmlspecialchars($staff_name); ?></strong>
					<?php } else { ?>
						<strong style="font-size:14px;">No Data Found</strong>
						<?php }
				} elseif ($rpt_type == 'ap') {
					$one = 1;
					$start_dt = $start . ' 00:00:00';
					$end_dt = $to . ' 23:59:59';

					if ($ddset == 1) {
						$query = "
							SELECT 
								p.drug_sn,
								p.item_services,
								p.date_entry,
								p.prepared_by,
								p.invoice_no,
								p.dsp_by,
								p.hospital_no,
								p.qty AS p_qty,
								p.serv_group,
								p.claim_amt,
								p.pay
							FROM patient_ap_services AS p
							WHERE p.paystatus = :paystatus
							  AND p.drug_status = :drug_status
							  AND (p.dept_id = :dept_id OR p.dept_dispensory_id = :dept_dispensory_id)
							  $staff
							  AND p.transact_date BETWEEN :start AND :end
							ORDER BY p.drug_sn, p.hospital_no
						";
						$stmt = $db->prepare($query);
						$stmt->bindValue(':paystatus', $one, PDO::PARAM_INT);
						$stmt->bindValue(':drug_status', $one, PDO::PARAM_INT);
						$stmt->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
						$stmt->bindValue(':dept_dispensory_id', $dept_id, PDO::PARAM_STR);
						$stmt->bindValue(':start', $start_dt, PDO::PARAM_STR);
						$stmt->bindValue(':end', $end_dt, PDO::PARAM_STR);
					} else {
						$query = "
							SELECT 
								p.drug_sn,
								p.item_services,
								p.date_entry,
								p.prepared_by,
								p.invoice_no,
								p.dsp_by,
								p.hospital_no,
								p.qty AS p_qty,
								p.serv_group,
								p.claim_amt,
								p.pay
							FROM patient_ap_services AS p
							WHERE DATE(p.transact_date) = :setdate
							  AND p.paystatus = :paystatus
							  AND p.drug_status = :drug_status
							  AND (p.dept_id = :dept_id OR p.dept_dispensory_id = :dept_dispensory_id)
							  $staff
							ORDER BY p.drug_sn, p.hospital_no
						";
						$stmt = $db->prepare($query);
						$stmt->bindValue(':setdate', $setdate, PDO::PARAM_STR);
						$stmt->bindValue(':paystatus', $one, PDO::PARAM_INT);
						$stmt->bindValue(':drug_status', $one, PDO::PARAM_INT);
						$stmt->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
						$stmt->bindValue(':dept_dispensory_id', $dept_id, PDO::PARAM_STR);
					}

					$stmt->execute();

					if ($stmt->rowCount() > 0) {
						$data_by_drug = [];

						while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
							$drug_sn = $row['drug_sn'];
							if (!isset($data_by_drug[$drug_sn])) {
								$data_by_drug[$drug_sn] = [];
							}
							$data_by_drug[$drug_sn][] = $row;
						}

						$t_pos = $t_nhis = $t_pvt = $t_claim = $t_nhis_90 = $t_qty = 0;

						foreach ($data_by_drug as $drug_sn => $records) {
							$drug_name = '';
							$qty = $pos = $nhis = $nhis_90 = $pvt = $claim = 0;
						?>

							<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width:100%;">
								<thead>
									<tr bgcolor="#CCCCCC">
										<th width="7%">Invoice #</th>
										<th width="7%">Hospital #</th>
										<th width="7%">Qty</th>
										<th width="7%">Claim</th>
										<th width="7%">Amt Paid</th>
										<th width="7%">Date</th>
										<th width="7%">Entered/Disp. by</th>
									</tr>
								</thead>
								<tbody>

									<?php
									foreach ($records as $roww) {
										$qty += $roww['p_qty'];
										$drug_name = $roww['item_services'];

										if ($roww['serv_group'] == 'EX') {
											$pos += $roww['pay'];
										}

										if ($roww['claim_amt'] > 0 && $roww['pay'] > 0) {
											$nhis += $roww['pay'];
											$nhis_90 += $roww['claim_amt'];
										}

										if ($roww['claim_amt'] == 0 && $roww['pay'] > 0 && $roww['serv_group'] != 'EX') {
											$pvt += $roww['pay'];
										}

										if ($roww['claim_amt'] > 0 && $roww['pay'] == 0) {
											$claim += $roww['claim_amt'];
										}
									?>
										<tr class="record">
											<td style="border-bottom: 1px solid #ddd;"><?php echo $roww['invoice_no']; ?></td>
											<td style="border-bottom: 1px solid #ddd;"><?php echo $roww['hospital_no']; ?></td>
											<td style="border-bottom: 1px solid #ddd;"><?php echo $roww['p_qty']; ?></td>
											<td style="border-bottom: 1px solid #ddd;"><?php echo $roww['claim_amt']; ?></td>
											<td style="border-bottom: 1px solid #ddd;"><?php echo $roww['pay']; ?></td>
											<td style="border-bottom: 1px solid #ddd;"><?php echo date('d M,Y', strtotime($roww['date_entry'])); ?></td>
											<td style="border-bottom: 1px solid #ddd;"><?php echo $roww['prepared_by'] . ' / ' . $roww['dsp_by']; ?></td>
										</tr>
									<?php
									}

									// Totals per drug
									$t_pos += $pos;
									$t_nhis += $nhis;
									$t_nhis_90 += $nhis_90;
									$t_pvt += $pvt;
									$t_claim += $claim;
									$t_qty += $qty;
									?>
									<tr class="record">
										<td style="border-bottom: 1px solid #ddd;"></td>
										<td colspan="2" style="border-bottom: 1px solid #ddd;">
											<table width="100%">
												<tr>
													<td colspan="2"><b><?php echo $drug_name; ?></b></td>
												</tr>
												<tr>
													<td colspan="2"><?php echo $qty; ?> - Qty Dispensed</td>
												</tr>
											</table>
										</td>
										<td colspan="2" style="border-bottom: 1px solid #ddd;">
											<table width="100%">
												<tr>
													<td align="right"><b>Sales:</b></td>
													<td><?php echo 'N' . number_format($pos, 2); ?></td>
												</tr>
												<tr>
													<td align="right"><b>Claim:</b></td>
													<td><?php echo 'N' . number_format($claim, 2); ?></td>
												</tr>
											</table>
										</td>
										<td colspan="2" style="border-bottom: 1px solid #ddd;">
											<table width="100%">
												<tr>
													<td align="right"><b>NHIS(10%) Paid:</b></td>
													<td><?php echo 'N' . number_format($nhis, 2); ?></td>
												</tr>
												<tr>
													<td align="right"><b>NHIS(90%) Billed:</b></td>
													<td><?php echo 'N' . number_format($nhis_90, 2); ?></td>
												</tr>
												<tr>
													<td align="right"><b>Private Paid:</b></td>
													<td><?php echo 'N' . number_format($pvt, 2); ?></td>
												</tr>
											</table>
										</td>
									</tr>
								</tbody>
							</table>
							<br><br>
						<?php
						} // end foreach
						?>
						<div style="font-size:14px"><strong><i>GENERATED BY:</i></strong></div>
						<strong style="font-size:14px;"><?php echo $staff_name; ?></strong>
					<?php
					} else {
						echo '<strong style="font-size:14px;">No Data Found</strong>';
					}
				} elseif ($rpt_type == 'doc_pres') {
					$setdate = date('Y-m-d');

					if ($ddset == 1) {
						$query = "
							SELECT 
								hospital_no, 
								item_services, 
								drug_status, 
								dsp_by
							FROM patient_ap_services
							WHERE (dept_id = :dept_id OR dept_dispensory_id = :dept_dispensory_id)
							AND DATE(date_entry) BETWEEN :start AND :to
						";
					} else {
						$query = "
							SELECT 
								hospital_no, 
								item_services, 
								drug_status, 
								dsp_by
							FROM patient_ap_services
							WHERE (dept_id = :dept_id OR dept_dispensory_id = :dept_dispensory_id)
							AND DATE(date_entry) = :setdate
						";
					}

					$stmt = $db->prepare($query);
					$stmt->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
					$stmt->bindValue(':dept_dispensory_id', $dept_id, PDO::PARAM_STR);
					if ($ddset == 1) {
						$stmt->bindValue(':start', $start, PDO::PARAM_STR);
						$stmt->bindValue(':to', $to, PDO::PARAM_STR);
					} else {
						$stmt->bindValue(':setdate', $setdate, PDO::PARAM_STR);
					}

					$stmt->execute();
					$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

					if (count($results) > 0) {
						$grouped = array();
						$dispensed_count = array();
						$non_dispensed_count = array();

						foreach ($results as $row) {
							$hospital_no = $row['hospital_no'];
							$item = $row['item_services'];
							$status = $row['drug_status'];
							$dsp_by = $row['dsp_by'];

							if (!isset($grouped[$hospital_no])) {
								$grouped[$hospital_no] = array(
									'dispensed' => '',
									'not_dispensed' => ''
								);
							}

							if ($status == 1) {
								$grouped[$hospital_no]['dispensed'] .= $item . ' by <b>' . $dsp_by . '</b><br>';

								if (!isset($dispensed_count[$item])) {
									$dispensed_count[$item] = 1;
								} else {
									$dispensed_count[$item]++;
								}
							} else {
								$grouped[$hospital_no]['not_dispensed'] .= $item . '<br>';

								if (!isset($non_dispensed_count[$item])) {
									$non_dispensed_count[$item] = 1;
								} else {
									$non_dispensed_count[$item]++;
								}
							}
						}

						// Display main table
						echo '<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
							<thead>
								<tr bgcolor="#CCCCCC">
									<th>Hospital #</th>
									<th>Dispense Drugs</th>
									<th>Not Dispense Drugs</th>
								</tr>
							</thead>
							<tbody>';

						foreach ($grouped as $hospital_no => $data) {
							echo '<tr class="record">
								<td style="border-bottom: 1px solid #ddd;">' . htmlspecialchars($hospital_no) . '</td>
								<td style="border-bottom: 1px solid #ddd;">' . $data['dispensed'] . '</td>
								<td style="border-bottom: 1px solid #ddd;">' . $data['not_dispensed'] . '</td>
							</tr>';
						}

						echo '</tbody></table>';

						// Display summary
						echo '<hr><h3>Summary:</h3>';
						echo '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
						echo '<tr><th>No.</th><th>Name of Drug</th><th>Total Dispense</th><th>Total Not Dispense</th></tr>';

						$all_items = array_unique(array_merge(array_keys($dispensed_count), array_keys($non_dispensed_count)));
						$sn = 1;

						foreach ($all_items as $item) {
							$dispensed_total = isset($dispensed_count[$item]) ? $dispensed_count[$item] : 0;
							$non_dispensed_total = isset($non_dispensed_count[$item]) ? $non_dispensed_count[$item] : 0;

							echo '<tr>';
							echo '<td>' . $sn++ . '</td>';
							echo '<td>' . htmlspecialchars($item) . '</td>';
							echo '<td>' . $dispensed_total . '</td>';
							echo '<td>' . $non_dispensed_total . '</td>';
							echo '</tr>';
						}

						echo '</table>';
					} else {
						echo '<strong style="font-size:14px;">No Data Found</strong>';
					}
				} elseif ($rpt_type == 'ps') {

					$setdate = date('Y-m-d');

					$bindParams = [
						':drug_status' => $one,
						':dept_id' => $dept_id,
						':dept_dispensory_id' => $dept_id
					];

					$whereClause = "p.drug_status = :drug_status 
                    AND (p.dept_id = :dept_id OR p.dept_dispensory_id = :dept_dispensory_id)";

					$dateFilter = ($ddset == 1)
						? "AND DATE(p.date_entry) BETWEEN :start AND :to"
						: "AND DATE(p.date_entry) = :setdate";

					if ($ddset == 1) {
						$bindParams[':start'] = $start;
						$bindParams[':to'] = $to;
					} else {
						$bindParams[':setdate'] = $setdate;
					}

					$sql = "SELECT p.hospital_no, p.item_services, p.qty, p.claim_amt, p.pay, 
                   p.serv_group, p.paystatus, p.prepared_by, p.dsp_by, p.date_entry
            FROM patient_ap_services AS p
            WHERE $whereClause $dateFilter $staff
            ORDER BY p.hospital_no, p.date_entry ASC";

					$stmt = $db->prepare($sql);
					foreach ($bindParams as $key => $val) {
						$stmt->bindValue($key, $val);
					}
					$stmt->execute();
					$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

					/* =======================
       CSV EXPORT (MUST BE HERE)
       ======================= */
					if (isset($_GET['export']) && $_GET['export'] === 'csv' && count($data) > 0) {

						header('Content-Type: text/csv; charset=utf-8');
						header('Content-Disposition: attachment; filename=patient_sales_' . date('Ymd_His') . '.csv');
						header('Pragma: no-cache');
						header('Expires: 0');

						$output = fopen('php://output', 'w');

						fputcsv($output, [
							'Hospital No',
							'Drug Name',
							'Qty',
							'Claim Amount',
							'Amount Paid',
							'Service Group',
							'Pay Status',
							'Prepared By',
							'Dispensed By',
							'Date'
						]);

						foreach ($data as $row) {
							fputcsv($output, [
								$row['hospital_no'],
								$row['item_services'],
								$row['qty'],
								$row['claim_amt'],
								$row['pay'],
								$row['serv_group'],
								$row['paystatus'],
								$row['prepared_by'],
								$row['dsp_by'],
								date('Y-m-d H:i:s', strtotime($row['date_entry']))
							]);
						}

						fclose($output);
						exit;
					}
					/* ======================= */

					if (count($data) > 0):

						$grouped = [];
						foreach ($data as $row) {
							$grouped[$row['hospital_no']][] = $row;
						}

						$t_pos = $t_nhis = $t_pvt = $t_claim = $t_nhis_90 = $t_pay_credit = 0;
					?>
						<!-- DOWNLOAD BUTTON -->
						<a href="export_ps_csv.php?rpt_type=ps&ddset=<?= $ddset ?>&start=<?= $start ?>&to=<?= $to ?>&dept_id=<?= $dept_id ?>&staff=<?= urlencode($staff) ?>"
							class="btn btn-primary mb-3">
							Export to CSV</a>

						<table cellpadding="5" cellspacing="5" border="0" width="100%" style="font-family:arial;font-size:13px;">
							<thead>
								<tr bgcolor="#CCCCCC">
									<th>Hospital #</th>
									<th>Drug Name</th>
									<th>Qty</th>
									<th>Claim</th>
									<th>Amount</th>
									<th>Entered / Disp.By</th>
									<th>Date</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($grouped as $hospital_no => $records):
									$pos = $nhis = $pvt = $claim = $nhis_90 = $pay_credit = 0;
									foreach ($records as $row):

										if ($row['serv_group'] === 'EX' && $row['paystatus'] == 1) $pos += $row['pay'];
										if ($row['claim_amt'] > 0 && $row['pay'] > 0 && $row['paystatus'] == 1) {
											$nhis += $row['pay'];
											$nhis_90 += $row['claim_amt'];
										}
										if ($row['claim_amt'] == 0 && $row['pay'] > 0 && $row['serv_group'] !== 'EX' && $row['paystatus'] == 1) {
											$pvt += $row['pay'];
										}
										if ($row['claim_amt'] > 0 && $row['pay'] == 0 && $row['paystatus'] == 1) {
											$claim += $row['claim_amt'];
										}
										if ($row['paystatus'] == 0) {
											$pay_credit += $row['pay'];
										}
								?>
										<tr>
											<td><?= $hospital_no ?></td>
											<td><?= $row['item_services'] ?></td>
											<td><?= $row['qty'] ?></td>
											<td><?= $row['claim_amt'] ?></td>
											<td><?= $row['pay'] ?></td>
											<td><?= $row['prepared_by'] . ' / ' . $row['dsp_by'] ?></td>
											<td><?= date('d,M Y h:i:s a', strtotime($row['date_entry'])) ?></td>
										</tr>
									<?php endforeach; ?>
									<tr>
										<td colspan="7"><b>Patient Sub Total: <?= number_format($pvt, 2) ?></b></td>
									</tr>
								<?php
									$t_pos += $pos;
									$t_nhis += $nhis;
									$t_nhis_90 += $nhis_90;
									$t_pvt += $pvt;
									$t_claim += $claim;
									$t_pay_credit += $pay_credit;
								endforeach;
								?>
							</tbody>
						</table>

						<br>
						<strong>GENERATED BY:</strong> <?= $staff_name ?>

					<?php else: ?>
						<strong>No Data Found</strong>
					<?php endif;
				} elseif ($rpt_type == 'dcr') {
					$setdate = date('Y-m-d');

					// Prepare the base query
					$query = "SELECT pas.* 
							  FROM patient_ap_services pas
							  WHERE pas.cr = '1' 
							  AND (pas.dept_id = :dept_id OR pas.dept_dispensory_id = :dept_dispensory_id) 
							  $staff 
							  AND pas.paystatus = '0'";

					// Add date condition based on $ddset
					if ($ddset == 1) {
						$query .= " AND DATE(pas.date_entry) BETWEEN :start AND :to";
					} else {
						$query .= " AND DATE(pas.date_entry) = :setdate";
					}

					// Prepare and execute the query
					$stmt = $db->prepare($query);
					$stmt->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
					$stmt->bindValue(':dept_dispensory_id', $dept_id, PDO::PARAM_STR);

					if ($ddset == 1) {
						$stmt->bindValue(':start', $start, PDO::PARAM_STR);
						$stmt->bindValue(':to', $to, PDO::PARAM_STR);
					} else {
						$stmt->bindValue(':setdate', $setdate, PDO::PARAM_STR);
					}

					$stmt->execute();

					if ($stmt->rowCount() > 0) { ?>
						<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
							<thead>
								<tr bgcolor="#CCCCCC">
									<th>A/P#</th>
									<th>Hospital #</th>
									<th>Dept</th>
									<th>Drug Name</th>
									<th>Qty</th>
									<th>Claim</th>
									<th>Amount</th>
									<th>Entered/Disp.by</th>
									<th>Date</th>
								</tr>
							</thead>
							<tbody>
								<?php
								$t_pos = 0;
								$t_nhis = 0;
								$t_pvt = 0;
								$t_claim = 0;
								$t_nhis_90 = 0;
								$t_pay_credit = 0;

								$current_hospital_no = null;

								while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
									// Display patient separator if this is a new patient
									if ($current_hospital_no !== $row['hospital_no']) {
										// If this isn't the first patient, show subtotals for the previous one
										if ($current_hospital_no !== null) {
								?>
											<tr class="record">
												<td style="border-bottom: 1px solid #ddd;"></td>
												<td style="border-bottom: 1px solid #ddd;"></td>
												<td style="border-bottom: 1px solid #ddd;"></td>
												<td style="border-bottom: 1px solid #ddd;"><strong>Patient Sub Total:</strong></td>
												<td style="border-bottom: 1px solid #ddd;"></td>
												<td style="border-bottom: 1px solid #ddd;">
													<?php if ($pay_credit > 0) {
														echo '<b>Dispense on Credit</b><br>N ' . number_format($pay_credit, 2, '.', ',');
													} ?>
												</td>
												<td style="border-bottom: 1px solid #ddd;">
													<?php if ($pos > 0) {
														echo '<b>POS (External Credit)</b><br>N ' . number_format($pos, 2, '.', ',');
													} ?>
												</td>
												<td style="border-bottom: 1px solid #ddd;">
													<?php if ($nhis > 0) {
														echo '<b>NHIS (10% Payable)</b><br>N ' . number_format($nhis, 2, '.', ',');
													} ?>
												</td>
												<td style="border-bottom: 1px solid #ddd;">
													<?php if ($pvt > 0) {
														echo '<b>Private Credit </b><br>N ' . number_format($pvt, 2, '.', ',');
													} ?>
												</td>
												<td style="border-bottom: 1px solid #ddd;">
													<?php if ($claim > 0) {
														echo '<b>Claim(100% Insurance Bills)</b><br>N ' . number_format($claim, 2, '.', ',');
													} ?>
												</td>
											</tr>
									<?php
										}

										// Reset patient-specific totals
										$pos = 0;
										$nhis = 0;
										$pvt = 0;
										$claim = 0;
										$nhis_90 = 0;
										$pay_credit = 0;

										$current_hospital_no = $row['hospital_no'];
									}

									// Process the current row
									if ($row['serv_group'] == 'EX') {
										$pos += $row['pay'];
									}
									if ($row['claim_amt'] > 0 && $row['pay'] > 0) {
										$nhis += $row['pay'];
										$nhis_90 += $row['claim_amt'];
									}
									if ($row['claim_amt'] == 0 && $row['pay'] > 0 && $row['serv_group'] != 'EX') {
										$pvt += $row['pay'];
									}
									if ($row['claim_amt'] > 0 && $row['pay'] == 0) {
										$claim += $row['claim_amt'];
									}
									?>
									<tr class="record">
										<td style="border-bottom: 1px solid #ddd;"><?= htmlspecialchars($row['app_no']) ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?= htmlspecialchars($row['hospital_no']) ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?= htmlspecialchars($row['serv_group']) ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?= htmlspecialchars($row['item_services']) ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?= htmlspecialchars($row['qty']) ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?= htmlspecialchars($row['claim_amt']) ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?= htmlspecialchars($row['pay']) ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?= htmlspecialchars($row['prepared_by'] . ' / ' . $row['dsp_by']) ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?= date('d,M Y h:i:s a', strtotime($row['date_entry'])) ?></td>
									</tr>
								<?php

									// Update grand totals
									$t_pos += $pos;
									$t_nhis += $nhis;
									$t_nhis_90 += $nhis_90;
									$t_pvt += $pvt;
									$t_claim += $claim;
									$t_pay_credit += $pay_credit;
								}

								// Display final patient's subtotals if there were any records
								if ($current_hospital_no !== null) {
								?>
									<tr class="record">
										<td style="border-bottom: 1px solid #ddd;"></td>
										<td style="border-bottom: 1px solid #ddd;"></td>
										<td style="border-bottom: 1px solid #ddd;"></td>
										<td style="border-bottom: 1px solid #ddd;"><strong>Patient Sub Total:</strong></td>
										<td style="border-bottom: 1px solid #ddd;"></td>
										<td style="border-bottom: 1px solid #ddd;">
											<?php if ($pay_credit > 0) {
												echo '<b>Dispense on Credit</b><br>N ' . number_format($pay_credit, 2, '.', ',');
											} ?>
										</td>
										<td style="border-bottom: 1px solid #ddd;">
											<?php if ($pos > 0) {
												echo '<b>POS (External Credit)</b><br>N ' . number_format($pos, 2, '.', ',');
											} ?>
										</td>
										<td style="border-bottom: 1px solid #ddd;">
											<?php if ($nhis > 0) {
												echo '<b>NHIS (10% Payable)</b><br>N ' . number_format($nhis, 2, '.', ',');
											} ?>
										</td>
										<td style="border-bottom: 1px solid #ddd;">
											<?php if ($pvt > 0) {
												echo '<b>Private Credit </b><br>N ' . number_format($pvt, 2, '.', ',');
											} ?>
										</td>
										<td style="border-bottom: 1px solid #ddd;">
											<?php if ($claim > 0) {
												echo '<b>Claim(100% Insurance Bills)</b><br>N ' . number_format($claim, 2, '.', ',');
											} ?>
										</td>
									</tr>
								<?php
								}
								?>
							</tbody>
						</table>
						<div align="right">
							<div style="font-size:18px"><b>Summary:</b></div>
							<table width="" cellpadding="3">
								<tr>
									<td width="">Dispensed On Credit</td>
									<td width="">
										<div align="right"><strong><?= number_format($t_pay_credit, 2, '.', ',') ?></strong></div>
									</td>
								</tr>
								<td width="">External Sale (POS)</td>
								<td width="">
									<div align="right"><strong><?= number_format($t_pos, 2, '.', ',') ?></strong></div>
								</td>
								</tr>
								<tr>
									<td width="">Private Patients :</td>
									<td width="">
										<div align="right"><strong><?= number_format($t_pvt, 2, '.', ',') ?></strong></div>
									</td>
								</tr>
								<tr>
									<td width="">NHIS (10% Payable/Recieved)</td>
									<td width="25%">
										<div align="right"><strong><?= number_format($t_nhis, 2, '.', ',') ?></strong></div>
									</td>
								</tr>
								<tr>
									<td width="">NHIS (90% Claim)</td>
									<td width="25%">
										<div align="right"><strong><?= number_format($t_nhis_90, 2, '.', ',') ?></strong></div>
									</td>
								</tr>
								<tr>
									<td width="">Total Claim/Insurance Bills</td>
									<td width="25%">
										<div align="right"><strong><?= number_format($t_claim, 2, '.', ',') ?></strong></div>
									</td>
								</tr>
							</table>
						</div>

						<br><br>
						<div style="font-size:14px"><strong><i>GENERATED BY</i>:</strong></div>
						<strong style="font-size:14px;"><?= htmlspecialchars($staff_name) ?></strong>
					<?php } else {
						echo '<strong style="font-size:14px;">No Data Found</strong>';
					}
				} elseif ($rpt_type == 'inv_summary') {
					// ----------------------
					// Setup
					// ----------------------
					$setdate  = date('Y-m-d');
					$dept_id  = isset($_SESSION['dept_id']) ? $_SESSION['dept_id'] : 0;

					// ----------------------
					// Base query: latest row per stock_sn + totals
					// ----------------------
					$drugQuery = "
    SELECT 
        st.product_name AS item_services,
        st.stock_total_unit,
        st.buying_cost,
        st.hosp_price,
        st.price_markup,
        st.sn,
        inv.inven_desc,
        inv.buy,
        inv.sale,
        inv.qtyIN,
        inv.qtyOUT,
        inv.bal,
        inv.insertion_date_time,
        inv.enter_by,
        totals.total_in,
        totals.total_out
    FROM stock_table st
    INNER JOIN stock_table_inven inv 
        ON st.sn = inv.stock_sn
    INNER JOIN (
        SELECT stock_sn, MAX(sn) AS last_sn
        FROM stock_table_inven
        GROUP BY stock_sn
    ) latest 
        ON inv.stock_sn = latest.stock_sn 
       AND inv.sn = latest.last_sn
    INNER JOIN (
        SELECT stock_sn, 
               SUM(qtyIN) AS total_in, 
               SUM(qtyOUT) AS total_out
        FROM stock_table_inven
        WHERE cust_patient_id = :dept_id_sub
";

					if ($ddset == 1 && !empty($start) && !empty($to)) {
						$drugQuery .= " AND DATE(captured_date) BETWEEN :start_sub AND :to_sub";
					} else {
						$drugQuery .= " AND DATE(captured_date) = :setdate_sub";
					}

					$drugQuery .= " GROUP BY stock_sn
    ) totals 
    ON inv.stock_sn = totals.stock_sn
    WHERE inv.cust_patient_id = :dept_id_main";

					if ($ddset == 1 && !empty($start) && !empty($to)) {
						$drugQuery .= " AND DATE(inv.captured_date) BETWEEN :start_main AND :to_main";
					} else {
						$drugQuery .= " AND DATE(inv.captured_date) = :setdate_main";
					}

					$drugQuery .= " ORDER BY st.product_name";

					// ----------------------
					// Prepare + Bind
					// ----------------------
					$drugStmt = $db->prepare($drugQuery);

					// Bind department separately for subquery and main query
					$drugStmt->bindValue(':dept_id_sub', $dept_id, PDO::PARAM_INT);
					$drugStmt->bindValue(':dept_id_main', $dept_id, PDO::PARAM_INT);

					if ($ddset == 1 && !empty($start) && !empty($to)) {
						$drugStmt->bindValue(':start_sub', $start, PDO::PARAM_STR);
						$drugStmt->bindValue(':to_sub', $to, PDO::PARAM_STR);

						$drugStmt->bindValue(':start_main', $start, PDO::PARAM_STR);
						$drugStmt->bindValue(':to_main', $to, PDO::PARAM_STR);
					} else {
						$drugStmt->bindValue(':setdate_sub', $setdate, PDO::PARAM_STR);
						$drugStmt->bindValue(':setdate_main', $setdate, PDO::PARAM_STR);
					}

					$drugStmt->execute();

					// ----------------------
					// Helper function
					// ----------------------
					function percentage_markup_cal($purchase_cost, $units, $percentage_markup)
					{
						$amt = $units > 0 ? ($purchase_cost / $units) : 0;
						$markup_amount = ($amt * $percentage_markup) / 100;
						$selling_price = $amt + $markup_amount;
						if ($selling_price > 0) {
							$hosp_price = $selling_price;
						} else {
							$hosp_price = 0;
						}
						return array('hosp_price' => $hosp_price);
					}

					// ----------------------
					// Output
					// ----------------------
					if ($drugStmt->rowCount() > 0) {
						$sn = 1;
					?>
						<table cellpadding="5" cellspacing="5" border="0"
							style="font-family: arial; font-size: 13px; text-align:left; width:100%;">
							<thead>
								<tr bgcolor="#CCCCCC">
									<th>sn</th>
									<th>Item Services</th>
									<th>Description</th>
									<th>Buy</th>
									<th>Sell</th>
									<th>IN</th>
									<th>OUT</th>
									<th>Bal (Pcs)</th>
									<th>Bal (Pack)</th>
									<th>Total Buying Cost Accrued</th>
									<th>Total Selling Cost Accrued</th>
									<th>Date</th>
									<th>Dispensed By</th>
									<th>Total IN</th>
									<th>Total OUT</th>
								</tr>
							</thead>
							<tbody>
								<?php
								$grand_total_buying = 0;
								$grand_total_selling = 0;

								while ($row = $drugStmt->fetch(PDO::FETCH_ASSOC)) {
									// ----------------------
									// BUYING COST
									// ----------------------
									$buy_price = (!empty($row['buy']) && $row['buy'] > 0)
										? $row['buy']
										: ($row['stock_total_unit'] > 0 ? $row['buying_cost'] / $row['stock_total_unit'] : 0);

									$buy_source = (!empty($row['buy']) && $row['buy'] > 0) ? "IBP" : "SBC";
									$total_buying = $row['bal'] * $buy_price;
									$grand_total_buying += $total_buying;

									// ----------------------
									// SELLING COST
									// ----------------------
									$sell_price = 0;
									$sell_source = "";

									if (!empty($row['sale']) && $row['sale'] > 0) {
										$sell_price = $row['sale'];
										$sell_source = "ISP";
									} else {
										$priceStmt = $db->prepare("
            SELECT hosp_price 
            FROM patient_ap_services 
            WHERE serv_group='Pharmacy' 
              AND drug_sn = :sn
            ORDER BY sn DESC LIMIT 1
        ");
										$priceStmt->bindValue(':sn', $row['sn'], PDO::PARAM_INT);
										$priceStmt->execute();
										$last_price = $priceStmt->fetchColumn();

										if (!empty($last_price) && $last_price > 0) {
											$sell_price = $last_price;
											$sell_source = "LPSP";
										} else {
											if (!empty($row['price_markup']) && $row['price_markup'] > 0) {
												$calc = percentage_markup_cal($row['buying_cost'], $row['stock_total_unit'], $row['price_markup']);
												$sell_price = $calc['hosp_price'];
												$sell_source = "CM";
											} else {
												$sell_price = $row['hosp_price'];
												$sell_source = "DHP";
											}
										}
									}

									$total_selling = $row['bal'] * $sell_price;
									$grand_total_selling += $total_selling;
								?>
									<tr>
										<td><?php echo $sn++; ?></td>
										<td><?php echo htmlspecialchars($row['item_services']); ?></td>
										<td><?php echo htmlspecialchars($row['inven_desc']); ?></td>
										<td><?php echo number_format($row['buy']); ?></td>
										<td><?php echo number_format($row['sale']); ?></td>
										<td><?php echo $row['qtyIN']; ?></td>
										<td><?php echo $row['qtyOUT']; ?></td>
										<td><?php echo $row['bal']; ?></td>
										<td>
											<?php
											echo ($row['bal'] > 1 && $row['stock_total_unit'] > 1)
												? formatQuantity($row['bal'], $row['stock_total_unit'])
												: 0;
											?>
										</td>
										<td><?php echo number_format($total_buying, 2) . " <small>($buy_source)</small>"; ?></td>
										<td><?php echo number_format($total_selling, 2) . " <small>($sell_source)</small>"; ?></td>
										<td><?php echo date('d,M Y', strtotime($row['insertion_date_time'])); ?></td>
										<td><?php echo htmlspecialchars($row['enter_by']); ?></td>
										<td>
											<?php
											if ($row['total_in'] > 0) {
												echo ($row['total_in'] > 1 && $row['stock_total_unit'] > 1)
													? formatQuantity($row['total_in'], $row['stock_total_unit'])
													: $row['total_in'] . ' (pcs)';
											} else {
												echo '0';
											}
											?>
										</td>
										<td>
											<?php
											if ($row['total_out'] > 0) {
												echo ($row['total_out'] > 1 && $row['stock_total_unit'] > 1)
													? formatQuantity($row['total_out'], $row['stock_total_unit'])
													: $row['total_out'] . ' (pcs)';
											} else {
												echo '0';
											}
											?>
										</td>
									</tr>
								<?php
								}
								?>
							</tbody>
							<tfoot>
								<tr style="background:#eee; font-weight:bold;">
									<td colspan="9" align="right">GRAND TOTAL:</td>
									<td><?php echo number_format($grand_total_buying, 2); ?></td>
									<td><?php echo number_format($grand_total_selling, 2); ?></td>
									<td colspan="4"></td>
								</tr>
							</tfoot>
						</table>

						<!-- Legend -->
						<div style="margin-top:15px; font-size:13px;">
							<strong>KEY:</strong><br>
							IBP = Inventory Buy Price<br>
							SBC = Stock Buying Cost<br>
							ISP = Inventory Sale Price<br>
							DHP = Default Hospital Price<br>
							CM = Calculated Markup<br>
							LPSP = Last Patient Service Price
						</div>


						<br><br>
						<div style="font-size:14px"><strong><i>GENERATED BY</i>:</strong></div>
						<strong style="font-size:14px;"><?php echo htmlspecialchars($staff_name); ?></strong>
						<form action="download_stock_csv.php" method="post" target="_blank">
							<input type="hidden" name="ddset" value="<?php echo $ddset; ?>">
							<input type="hidden" name="start" value="<?php echo $start; ?>">
							<input type="hidden" name="to" value="<?php echo $to; ?>">
							<button type="submit" style="margin:10px; padding:5px 15px; cursor:pointer;">
								Download CSV
							</button>
						</form>
						<?php
					} else {
						echo '<strong style="font-size:14px;">No Data Found</strong>';
					}
				} elseif ($rpt_type == 'inv') {
					// ----------------------
					// Setup
					// ----------------------
					$setdate = date('Y-m-d');


					// ----------------------
					// Optimized Join Query
					// ----------------------
					$drugQuery = "
    SELECT 
        pas.drug_sn,
        pas.item_services,
        inv.inven_desc,
        inv.buy,
        inv.sale,
        inv.batch,
        inv.qtyIN,
        inv.qtyOUT,
        inv.bal,
        inv.insertion_date_time,
        inv.enter_by
    FROM patient_ap_services pas
    INNER JOIN stock_table_inven inv 
        ON pas.drug_sn = inv.stock_sn
    WHERE pas.serv_group = 'Pharmacy'
      AND pas.drug_status = '1'
      $staff
      $staff2
";

					// Add date condition with unique parameter names
					if ($ddset == 1) {
						$drugQuery .= " AND DATE(pas.date_entry) BETWEEN :start1 AND :to1 
                    AND DATE(inv.captured_date) BETWEEN :start2 AND :to2";
					} else {
						$drugQuery .= " AND DATE(pas.date_entry) = :setdate1 
                    AND DATE(inv.captured_date) = :setdate2";
					}

					$drugQuery .= " ORDER BY pas.drug_sn, inv.sn";

					// ----------------------
					// Prepare and Bind
					// ----------------------
					$drugStmt = $db->prepare($drugQuery);

					if ($ddset == 1) {
						$drugStmt->bindValue(':start1', $start, PDO::PARAM_STR);
						$drugStmt->bindValue(':to1', $to, PDO::PARAM_STR);
						$drugStmt->bindValue(':start2', $start, PDO::PARAM_STR);
						$drugStmt->bindValue(':to2', $to, PDO::PARAM_STR);
					} else {
						$drugStmt->bindValue(':setdate1', $setdate, PDO::PARAM_STR);
						$drugStmt->bindValue(':setdate2', $setdate, PDO::PARAM_STR);
					}

					$drugStmt->execute();

					// ----------------------
					// Process and Group By drug_sn
					// ----------------------
					if ($drugStmt->rowCount() > 0) {
						$results = array();
						while ($row = $drugStmt->fetch(PDO::FETCH_ASSOC)) {
							$drug_sn = $row['drug_sn'];
							if (!isset($results[$drug_sn])) {
								$results[$drug_sn] = array(
									'item_services' => $row['item_services'],
									'rows' => array()
								);
							}
							$results[$drug_sn]['rows'][] = $row;
						}

						$dn = 1;
						foreach ($results as $drug_sn => $drugData) {
							echo '<strong style="font-size:16px">' . $dn . '. ' . htmlspecialchars($drugData['item_services']) . '</strong>';
						?>
							<table cellpadding="5" cellspacing="5" border="0"
								style="font-family: arial; font-size: 13px; text-align:left; width:100%;">
								<thead>
									<tr bgcolor="#CCCCCC">
										<th>#</th>
										<th>Description</th>
										<th>Buy</th>
										<th>Sell</th>
										<th>Batch No</th>
										<th>IN</th>
										<th>OUT</th>
										<th>Balance</th>
										<th>Date</th>
										<th>Dispensed By</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$n = 1;
									foreach ($drugData['rows'] as $inventoryRow) {
									?>
										<tr class="record" style="background:<?php echo ($n % 2 == 0) ? '#F4F4F4' : '#FFFFFF'; ?>">
											<td style="border-bottom: 1px solid #ddd;"><?php echo $n; ?></td>
											<td style="border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($inventoryRow['inven_desc']); ?></td>
											<td style="border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($inventoryRow['buy']); ?></td>
											<td style="border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($inventoryRow['sale']); ?></td>
											<td style="border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($inventoryRow['batch']); ?></td>
											<td style="border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($inventoryRow['qtyIN']); ?></td>
											<td style="border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($inventoryRow['qtyOUT']); ?></td>
											<td style="border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($inventoryRow['bal']); ?></td>
											<td style="border-bottom: 1px solid #ddd;"><?php echo date('d,M Y h:i:s a', strtotime($inventoryRow['insertion_date_time'])); ?></td>
											<td style="border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($inventoryRow['enter_by']); ?></td>
										</tr>
									<?php
										$n++;
									}
									?>
								</tbody>
							</table><br>
						<?php
							$dn++;
						}
						?>
						<br><br>
						<div style="font-size:14px"><strong><i>GENERATED BY</i>:</strong></div>
						<strong style="font-size:14px;"><?php echo htmlspecialchars($staff_name); ?></strong>
					<?php
					} else {
						echo '<strong style="font-size:14px;">No Data Found</strong>';
					}
				} elseif ($rpt_type == 'd_r') {

					/// PATIENT SEEN
					$setdate = date('Y-m-d');

					if ($ddset == 1) {
						$stmt = $db->prepare("SELECT * FROM patient_ap_services 
							 WHERE drug_status = :drug_status 
							 AND (dept_id = :dept_id or dept_dispensory_id = :dept_dispensory_id)
							 $staff 
							 AND DATE(date_entry) BETWEEN :start AND :to 
							 ORDER BY item_services");
						$stmt->bindValue(':drug_status', '1', PDO::PARAM_STR);
						$stmt->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
						$stmt->bindValue(':dept_dispensory_id', $dept_id, PDO::PARAM_STR);
						$stmt->bindValue(':start', $start, PDO::PARAM_STR);
						$stmt->bindValue(':to', $to, PDO::PARAM_STR);
					} else {
						// Assuming $setdate is defined earlier
						$stmt = $db->prepare("SELECT * FROM patient_ap_services 
							 WHERE drug_status = :drug_status 
							 AND (dept_id = :dept_id or dept_dispensory_id = :dept_dispensory_id) 
							 $staff 
							 AND DATE(date_entry) = :setdate 
							 ORDER BY item_services");
						$stmt->bindValue(':drug_status', '1', PDO::PARAM_STR);
						$stmt->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
						$stmt->bindValue(':dept_dispensory_id', $dept_id, PDO::PARAM_STR);
						$stmt->bindValue(':setdate', $setdate, PDO::PARAM_STR);
					}

					$stmt->execute();
					if ($stmt->rowCount() > 0) { ?>

						<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
							<thead>
								<tr bgcolor="#CCCCCC">
									<th>A/P#</th>
									<th>Hospital #</th>
									<th>Dept</th>
									<th>Drug Name</th>
									<th>Qty</th>
									<th>Claim</th>
									<th>Amount</th>
									<th>Entered/Disp by</th>
									<th>Date</th>
								</tr>
							</thead>
							<tbody>
								<?php

								while ($roww = $stmt->fetch(PDO::FETCH_ASSOC)) {
								?>
									<tr class="record">
										<td style="border-bottom: 1px solid #ddd;"><?php echo $roww['app_no']; ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?php echo $roww['hospital_no']; ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?php echo $roww['serv_group']; ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?php echo $roww['item_services']; ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?php echo $roww['qty']; ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?php echo $roww['claim_amt']; ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?php echo $roww['pay']; ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?php echo $roww['prepared_by'] . ' / ' . $roww['dsp_by']; ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?php echo date('d,M Y h:i:s a', strtotime($roww['date_entry'])); ?></td>
									</tr>
								<?php }  ?>
							</tbody>
						</table>

						<br><br>
						<div style="font-size:14px"><strong><i>GENERATED BY</i>:</strong></div>
						<strong style="font-size:14px;"><?php echo $staff_name; ?></strong>
						<?php
					} else {
						echo '<strong style="font-size:14px;">No Data Found</strong>';
					}
				} elseif ($rpt_type == 'inv_drug') {
					$setdate = date('Y-m-d');

					// Prepare the product query
					$productStmt = $db->prepare("
    SELECT sn, product_name, qty, reorder_level, expire_date 
    FROM stock_table 
    WHERE sn = :searchdrug_inv");
					$productStmt->bindValue(':searchdrug_inv', $searchdrug_inv, PDO::PARAM_STR);
					$productStmt->execute();

					if ($productStmt->rowCount() > 0) {
						// Inventory query template
						$inventoryQuery = "
        SELECT sn, inven_desc, batch, qtyIN, qtyOUT, bal, insertion_date_time, enter_by 
        FROM stock_table_inven 
        WHERE stock_sn = :product_sn";

						// Add date condition
						if ($ddset == 1) {
							$inventoryQuery .= " AND DATE(captured_date) BETWEEN :start AND :to";
						} else {
							$inventoryQuery .= " AND DATE(captured_date) = :setdate";
						}
						$inventoryQuery .= " ORDER BY sn";

						$inventoryStmt = $db->prepare($inventoryQuery);

						while ($productRow = $productStmt->fetch(PDO::FETCH_ASSOC)) {
							$product_sn = $productRow['sn'];
							$item_services = $productRow['product_name'];
							$currentBalance = 0;

							// Bind params once per product
							$inventoryStmt->bindValue(':product_sn', $product_sn, PDO::PARAM_STR);
							if ($ddset == 1) {
								$inventoryStmt->bindValue(':start', $start, PDO::PARAM_STR);
								$inventoryStmt->bindValue(':to', $to, PDO::PARAM_STR);
							} else {
								$inventoryStmt->bindValue(':setdate', $setdate, PDO::PARAM_STR);
							}
							$inventoryStmt->execute();

							if ($inventoryStmt->rowCount() > 0) {
								echo '<strong style="font-size:16px">' . htmlspecialchars($item_services) . '</strong>';
						?>
								<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width:100%;">
									<thead>
										<tr bgcolor="#CCCCCC">
											<th>#</th>
											<th>Description</th>
											<th>Batch No</th>
											<th>IN</th>
											<th>OUT</th>
											<th>Balance</th>
											<th>Date</th>
											<th>Dispensed By</th>
										</tr>
									</thead>
									<tbody>
										<?php
										$n = 1;
										$colordecide = 0;

										while ($inventoryRow = $inventoryStmt->fetch(PDO::FETCH_ASSOC)) {
											$bgcolor = ($colordecide % 2 == 0) ? "#F4F4F4" : "#FFFFFF";
											$currentBalance = $inventoryRow['bal'];
										?>
											<tr class="record" style="background:<?= $bgcolor ?>">
												<td style="border-bottom: 1px solid #ddd;"><?= $n ?></td>
												<td style="border-bottom: 1px solid #ddd;"><?= htmlspecialchars($inventoryRow['inven_desc']) ?></td>
												<td style="border-bottom: 1px solid #ddd;"><?= htmlspecialchars($inventoryRow['batch']) ?></td>
												<td style="border-bottom: 1px solid #ddd;"><?= htmlspecialchars($inventoryRow['qtyIN']) ?></td>
												<td style="border-bottom: 1px solid #ddd;"><?= htmlspecialchars($inventoryRow['qtyOUT']) ?></td>
												<td style="border-bottom: 1px solid #ddd;"><?= htmlspecialchars($inventoryRow['bal']) ?></td>
												<td style="border-bottom: 1px solid #ddd;"><?= date('d,M Y h:i:s a', strtotime($inventoryRow['insertion_date_time'])) ?></td>
												<td style="border-bottom: 1px solid #ddd;"><?= htmlspecialchars($inventoryRow['enter_by']) ?></td>
											</tr>
										<?php
											$colordecide++;
											$n++;

											// Slow down rendering intentionally (100ms delay per row)
											usleep(100000);
										}
										?>
									</tbody>
								</table>
								<br>
								<table cellpadding="5" cellspacing="5" border="0" width="100%">
									<tr class="record">
										<td style="border-bottom: 1px solid #ddd; font-size:18px">Quantity: <?= htmlspecialchars($currentBalance) ?></td>
										<td style="border-bottom: 1px solid #ddd; font-size:18px">Re-order Level: <?= htmlspecialchars($productRow['reorder_level']) ?></td>
										<td style="border-bottom: 1px solid #ddd; font-size:18px">
											Expiring Date: <?= !empty($productRow['expire_date']) ? date('d,M Y', strtotime($productRow['expire_date'])) : 'N/A' ?>
										</td>
									</tr>
								</table>
						<?php
							}

							// Optional delay after each product
							sleep(1);
						}
						?>
						<br><br>
						<div style="font-size:14px"><strong><i>GENERATED BY</i>:</strong></div>
						<strong style="font-size:14px;"><?= htmlspecialchars($_SESSION['fullname']) ?></strong>
						<?php
					} else {
						echo '<strong style="font-size:14px;">No Data Found</strong>';
					}
				} elseif ($rpt_type == 'rhmo') {
					$setdate = date('Y-m-d');
					if (isset($_POST["hmo_nhis"]) && !empty($_POST["hmo_nhis"])) {
						$Tclaim_hmo = 0;
						$Tpay_hmo = 0;

						// Prepare the base query once (we'll reuse it)
						$query = "SELECT enl.hospital_no, ap.* 
								  FROM enrollee AS enl 
								  INNER JOIN patient_ap_services AS ap ON ap.hospital_no = enl.hospital_no 
								  WHERE enl.hmo_no = :hmo_no 
								  AND enl.insurance = :insur_type 
								  AND (ap.dept_id = :dept_id OR ap.dept_dispensory_id = :dept_dispensory_id) 
								  AND ap.drug_status = '1'";

						// Add date condition based on $ddset
						if ($ddset == 1) {
							$query .= " AND DATE(ap.date_entry) BETWEEN :start_date AND :end_date";
						} else {
							$query .= " AND DATE(ap.date_entry) = :set_date";
						}

						$stmt = $db->prepare($query);
						$stmt->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
						$stmt->bindValue(':dept_dispensory_id', $dept_id, PDO::PARAM_STR);
						$stmt->bindValue(':insur_type', $hmo_type, PDO::PARAM_STR);

						foreach ($_POST["hmo_nhis"] as $hmo_nhis_no) {
							$parts = explode("__", $hmo_nhis_no);
							$hmo_no = $parts[0];
							$hmo_name = isset($parts[1]) ? $parts[1] : '';

							$claim_hmo = 0;
							$pay_hmo = 0;

							// Bind parameters and execute
							$stmt->bindValue(':hmo_no', $hmo_no, PDO::PARAM_STR);
							if ($ddset == 1) {
								$stmt->bindValue(':start_date', $start, PDO::PARAM_STR);
								$stmt->bindValue(':end_date', $to, PDO::PARAM_STR);
							} else {
								$stmt->bindValue(':set_date', $setdate, PDO::PARAM_STR);
							}

							$stmt->execute();

							if ($stmt->rowCount() > 0) {
						?>
								<h3><?= htmlspecialchars($hmo_name) ?></h3>
								<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px; text-align:left; width:100%;">
									<thead>
										<tr bgcolor="#CCCCCC">
											<th>A/P#</th>
											<th>Hospital #</th>
											<th>Drug Name</th>
											<th>Qty</th>
											<th>Claim</th>
											<th>Amount</th>
											<th>Entered/Disp by</th>
											<th>Date</th>
										</tr>
									</thead>
									<tbody>
										<?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
											$claim_hmo += $row['claim_amt'];
											$Tclaim_hmo += $row['claim_amt'];
											$pay_hmo += $row['pay'];
											$Tpay_hmo += $row['pay'];
										?>
											<tr class="record">
												<td style="border-bottom: 1px solid #ddd;"><?= htmlspecialchars($row['app_no']) ?></td>
												<td style="border-bottom: 1px solid #ddd;"><?= htmlspecialchars($row['hospital_no']) ?></td>
												<td style="border-bottom: 1px solid #ddd;"><?= htmlspecialchars($row['item_services']) ?></td>
												<td style="border-bottom: 1px solid #ddd;"><?= htmlspecialchars($row['qty']) ?></td>
												<td style="border-bottom: 1px solid #ddd;"><?= htmlspecialchars($row['claim_amt']) ?></td>
												<td style="border-bottom: 1px solid #ddd;"><?= htmlspecialchars($row['pay']) ?></td>
												<td style="border-bottom: 1px solid #ddd;"><?= htmlspecialchars($row['prepared_by'] . ' / ' . $row['dsp_by']) ?></td>
												<td style="border-bottom: 1px solid #ddd;"><?= date('d,M Y h:i:s a', strtotime($row['date_entry'])) ?></td>
											</tr>
										<?php } ?>
									</tbody>
								</table>
								<h3>
									<strong>Total Claim:</strong> <?= number_format($claim_hmo, 2) ?>
									&nbsp;|&nbsp;
									<strong>Cash Received: </strong><?= number_format($pay_hmo, 2) ?>
								</h3>
								<br>
							<?php }
						}

						if ($Tclaim_hmo > 0) { ?>
							<hr>
							<h2>Grand Total (Claim): <?= number_format($Tclaim_hmo, 2) ?></h2>
							<h2>Grand Total (Paid Amount): <?= number_format($Tpay_hmo, 2) ?></h2>
						<?php }
					} else {
						echo 'Invalid selection';
					}
				} elseif ($rpt_type == 'visit') {
					$setdate = date('Y-m-d');

					// Set date range
					if ($ddset != 1) {
						$start = $setdate;
						$to = $setdate;
					}

					$drug_status = '1';
					$TotalP_claim = 0;
					$Tp_pay = 0;
					$Tvisit = 0;

					// Prepare the main query to get distinct patients
					$patientQuery = "SELECT DISTINCT hospital_no 
									FROM patient_ap_services 
									WHERE drug_status = :drug_status 
									AND (dept_id = :dept_id OR dept_dispensory_id = :dept_dispensory_id) 
									$staff 
									AND DATE(date_entry) BETWEEN :start_date AND :end_date 
									ORDER BY item_services";

					$patientStmt = $db->prepare($patientQuery);
					$patientStmt->bindValue(':drug_status', $drug_status, PDO::PARAM_STR);
					$patientStmt->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
					$patientStmt->bindValue(':dept_dispensory_id', $dept_id, PDO::PARAM_STR);
					$patientStmt->bindValue(':start_date', $start, PDO::PARAM_STR);
					$patientStmt->bindValue(':end_date', $to, PDO::PARAM_STR);
					$patientStmt->execute();

					if ($patientStmt->rowCount() > 0) {
						// Prepare the summary query (for claim/pay totals)
						$summaryQuery = "SELECT 
										SUM(claim_amt) as P_claim, 
										SUM(pay) as p_pay 
										FROM patient_ap_services 
										WHERE drug_status = :drug_status 
										AND (dept_id = :dept_id OR dept_dispensory_id = :dept_dispensory_id) 
										AND hospital_no = :hospital_no 
										$staff 
										AND DATE(date_entry) BETWEEN :start_date AND :end_date";

						$summaryStmt = $db->prepare($summaryQuery);
						$summaryStmt->bindValue(':drug_status', $drug_status, PDO::PARAM_STR);
						$summaryStmt->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
						$summaryStmt->bindValue(':dept_dispensory_id', $dept_id, PDO::PARAM_STR);
						$summaryStmt->bindValue(':start_date', $start, PDO::PARAM_STR);
						$summaryStmt->bindValue(':end_date', $to, PDO::PARAM_STR);

						// Prepare the visit count query
						$visitQuery = "SELECT COUNT(DISTINCT app_no) as visit_count 
									  FROM patient_ap_services 
									  WHERE hospital_no = :hospital_no 
									  AND drug_status = :drug_status 
									  AND (dept_id = :dept_id OR dept_dispensory_id = :dept_dispensory_id)
									  $staff 
									  AND DATE(date_entry) BETWEEN :start_date AND :end_date";

						$visitStmt = $db->prepare($visitQuery);
						$visitStmt->bindValue(':drug_status', $drug_status, PDO::PARAM_STR);
						$visitStmt->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
						$visitStmt->bindValue(':dept_dispensory_id', $dept_id, PDO::PARAM_STR);
						$visitStmt->bindValue(':start_date', $start, PDO::PARAM_STR);
						$visitStmt->bindValue(':end_date', $to, PDO::PARAM_STR);
						?>

						<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px; text-align:left; width:100%;">
							<thead>
								<tr bgcolor="#CCCCCC">
									<th>Hospital #</th>
									<th>Total Visit</th>
									<th>Claim</th>
									<th>Amount</th>
								</tr>
							</thead>
							<tbody>
								<?php while ($patient = $patientStmt->fetch(PDO::FETCH_ASSOC)) {
									$hospital_no = $patient['hospital_no'];

									// Get summary for this patient
									$summaryStmt->bindValue(':hospital_no', $hospital_no, PDO::PARAM_STR);
									$summaryStmt->execute();
									$summary = $summaryStmt->fetch(PDO::FETCH_ASSOC);

									// Get visit count for this patient
									$visitStmt->bindValue(':hospital_no', $hospital_no, PDO::PARAM_STR);
									$visitStmt->execute();
									$visitCount = $visitStmt->fetchColumn();

									// Update totals - using isset() instead of ?? operator
									$currentClaim = isset($summary['P_claim']) ? $summary['P_claim'] : 0;
									$currentPay = isset($summary['p_pay']) ? $summary['p_pay'] : 0;

									$TotalP_claim += $currentClaim;
									$Tp_pay += $currentPay;
									$Tvisit += $visitCount;
								?>

									<tr class="record">
										<td style="border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($hospital_no); ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?php echo $visitCount; ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?php echo number_format($currentClaim, 2); ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?php echo number_format($currentPay, 2); ?></td>
									</tr>
								<?php } ?>

								<tr class="record">
									<td style="border-bottom: 1px solid #ddd;"><strong>Total: <?php echo $patientStmt->rowCount(); ?></strong></td>
									<td style="border-bottom: 1px solid #ddd;"><strong><?php echo $Tvisit; ?></strong></td>
									<td style="border-bottom: 1px solid #ddd;"><strong><?php echo number_format($TotalP_claim, 2); ?></strong></td>
									<td style="border-bottom: 1px solid #ddd;"><strong><?php echo number_format($Tp_pay, 2); ?></strong></td>
								</tr>
							</tbody>
						</table>

						<br><br>
						<div style="font-size:14px"><strong><i>GENERATED BY</i>:</strong></div>
						<strong style="font-size:14px;"><?php echo htmlspecialchars($staff_name); ?></strong>
					<?php } else { ?>
						<strong style="font-size:14px;">No Data Found</strong>
				<?php }
				}

				?>


				<div class="form_sep">
					<div class="pull-left" style="margin-right:100px;">
						<a href="index.php?rpt" style="font-size:20px;"><button class="btn btn-danger btn-large">Close</button></a>
					</div>

					<div class="pull-right" style="margin-right:100px;">
						<a href="javascript:Clickheretoprint()" style="font-size:20px;"><button class="btn btn-success btn-large"><i class="icon-print"></i> Print</button></a>
					</div>
				</div>




			</div>

		</div>
	</div>

</div>

<script language="javascript">
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