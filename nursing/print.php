<div class="row">

	<div class="col-lg-12">
		<div class="ibox float-e-margins">
			<div class="ibox-title">
				<h5>Report</h5>
			</div>

			<div class="ibox-content" id="content">
				<?php
				if ($_SESSION['rights'] == 'NS') {
					$dept_id = $_SESSION['dept_id'];
				} else {
					$stmt = $db->query("SELECT sn FROM department WHERE department LIKE '%nursing%' ");
					$row_rstSelect = $stmt->fetch(PDO::FETCH_ASSOC);
					$dept_id = $row_rstSelect['sn'];
				}
				?>

				<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
					<tr>
						<td width="50%" align="left"><img src="img/logo.png" width="196" height="111"></td>
						<td width="50%" align="right">
							<div style="font-size:18px; font:Verdana, Geneva, sans-serif"><strong><?php echo $_SESSION['h_name']; ?></strong></div> <br>
							<div style="font-size:14px"><?php echo $_SESSION['h_address']; ?><br><br> <?php echo $_SESSION['h_phone']; ?></div>
						</td>
					</tr>
				</table>
				<hr>


				<div align="center" style="font-size:20px; font:Verdana, Geneva, sans-serif;">
					<?php



					if (isset($_POST['next1'])) {
						header("location:pharm_rpt.php");
					}

					$desccc = $_POST['desccc'];
					$rpt_type = $_POST['rpt_type'];
					$searchdrug_inv = $_POST['searchdrug_inv'];

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
								$staff = " and dsp_by='$fullname'";
								$staff2 = " and enter_by='$fullname'";
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
						All Consumables In-Store
					<?php	} elseif ($desccc == 'Expired') { ?>
						All Expired Consumables
					<?php	} elseif ($desccc == 'rorder') { ?>
						Cosumable Re-order list

					<?php } elseif ($rpt_type == 'at') { ?>
						Today's Transaction Report (Summary) (<?php
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
						Today's Transaction Report (Summary by Consumables) (<?php
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
					} elseif ($rpt_type == 'ps') {

						$start = $_POST['from_date'];
						$to = $_POST['to_date'];
						if ($start != '' and $to != '') {
							echo 'Report of Patients Seen and Other Details<br>';
							echo 'between: ' . date('d M,Y', strtotime($start)) . ' - ' . date('d M,Y', strtotime($to));
							$ddset = 1;
						} else {
							$setdate = date('Y-m-d');
							echo 'Today, ' . date('d,M Y', strtotime($setdate)) . '<br>Report of Patients Seen and Other Details';
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
					} elseif ($rpt_type == 'd_r') {

						$start = $_POST['from_date'];
						$to = $_POST['to_date'];
						if ($start != '' and $to != '') {
							echo 'Consumables Returned<br>';
							echo 'between: ' . date('d M,Y', strtotime($start)) . ' - ' . date('d M,Y', strtotime($to));
							$ddset = 1;
						} else {
							$setdate = date('Y-m-d');
							echo 'Today, ' . date('d,M Y', strtotime($setdate)) . '<br>Consumable Returned';
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
						$hmo_nhis = $_POST['hmo_nhis'];
						$hmo_type = $_POST['hmo_type'];

						if ($start != '' and $to != '') {
							echo 'HMO/Corporate Reports: ' . $hmo_type . '<br>';
							echo 'between: ' . date('d M,Y', strtotime($start)) . ' - ' . date('d M,Y', strtotime($to));
							$ddset = 1;
						} else {
							$setdate = date('Y-m-d');
							echo 'Today, ' . date('d,M Y', strtotime($setdate)) . '<br>HMO/Corporate Reports: ' . $hmo_type;
							$ddset = 0;
						}
					} elseif ($rpt_type == 'visit' or $rpt_type == 'adm') {

						$start = $_POST['from_date'];
						$to = $_POST['to_date'];

						if ($start != '' and $to != '') {
							echo 'Patient Visit/Admission Reports: ' . $hmo_type . '<br>';
							echo 'between: ' . date('d M,Y', strtotime($start)) . ' - ' . date('d M,Y', strtotime($to));
							$ddset = 1;
						} else {
							$setdate = date('Y-m-d');
							echo 'Today, ' . date('d,M Y', strtotime($setdate)) . '<br>Patient visit/Admission reports: ' . $hmo_type;
							$ddset = 0;
						}
					}


					?>

				</div>

				<hr>


				<?php



				$setdate = date('Y-m-d');
				$desccc = $_POST['desccc'];
				if (isset($_GET['all']) or $desccc == 'Expired' or $desccc == 'rorder' or $desccc == 'Expiring') {

					if (isset($_GET['all'])) {
						$rstSelect = $db->query("SELECT * FROM stock_table where navigation='nursing' order by product_name");
					} elseif ($desccc == 'Expired') {
						$rstSelect = $db->query("SELECT s.* FROM stock_table s inner join stock_table_procurment as p on s.sn=p.stock_sn 
WHERE p.expiry_date<='$setdate' and finish_status='0' and  p.status='yes' and s.navigation='nursing' order by s.product_name");
					} elseif ($desccc == 'Expiring') {
						$setdate = date('Y-m-d');
						$d = ($_POST['days']);
						echo '<br>';
						$days = ($_POST['days']);
						if ($d == '') {
							$days = 1;
						} else {
							$days = $d;
						}
						$date = date('Y-m-d', strtotime($setdate . " + $days days"));
						$rstSelect = $db->query("SELECT s.* FROM stock_table s inner join stock_table_procurment as p on s.sn=p.stock_sn 
WHERE p.expiry_date<='$date' and finish_status='0' and p.status='yes' and s.navigation='nursing' order by product_name");
					} elseif ($desccc == 'rorder') {
						$rstSelect = $db->query("SELECT * FROM stock_table where navigation='nursing' and qty=reorder_level order by product_name,dosage");
					}

					if ($rstSelect->rowCount() > 0) { ?>

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
								<?php
								$n = 1;
								while ($row = $rstSelect->fetch(PDO::FETCH_ASSOC)) { ?>
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
						<div><b><i><?php echo 'Total row(s) found: ' . $rstSelect->rowCount(); ?></i></b></div>
					<?php }
				} elseif ($rpt_type == 'at') {

					if ($ddset == 1) {

						$rstSelect = $db->query("SELECT distinct p.drug_sn FROM patient_ap_services as p 
			inner join stock_table_inven as inv on inv.stock_sn = p.drug_sn 
			where p.paystatus='1' and p.dept_id='$dept_id' $staff and transact_date between '$start' AND '$to'");
					} else {
						$rstSelect = $db->query("SELECT distinct p.drug_sn FROM patient_ap_services as p 
			inner join stock_table_inven as inv on inv.stock_sn = p.drug_sn 
			where transact_date='$setdate' and p.paystatus='1' and p.dept_id='$dept_id' $staff");
					}
					if ($rstSelect->rowCount() > 0) { ?>

						<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
							<thead>
								<tr bgcolor="#CCCCCC">
									<th width="20%">Name </th>
									<th width="7%">Quantity<br>Dispensed </th>
									<th width="7%">Quantity<br>in-stock</th>
									<th width="40%">
										<table width="100%">
											<tr>
												<td colspan="4">
													<div align="center">&nbsp;</div>
												</td>
											</tr>
											<tr>
												<td width="25%">
													<div align="right"><?php echo 'POS <br>(External Sales)'; ?></div>
												</td>
												<td width="25%">
													<div align="right"><?php echo 'Private<br>(Patients)'; ?></div>
												</td>
												<td width="25%">
													<div align="right"><?php echo 'NHIS<br>(10% Payments)'; ?></div>
												</td>
												<td width="25%">
													<div align="right"><?php echo 'CLAIM<br>(Zero Payable)'; ?></div>
												</td>
											</tr>
										</table>
									</th>
								</tr>
							</thead>
							<tbody>
								<?php $t_pos = 0;
								$t_nhis = 0;
								$t_pvt = 0;
								$t_claim = 0;
								$t_nhis_90 = 0;
								$pos = 0;
								$nhis = 0;
								$pvt = 0;
								$claim = 0;
								$nhis_90 = 0;
								//$row=mysql_fetch_array($rstSelect)) {
								while ($row = $rstSelect->fetch(PDO::FETCH_ASSOC)) {

									$drug_sn = $row['drug_sn'];  /// target ////
									if ($ddset == 1) {

										$query_rstSelect2 = $db->query("SELECT 
			p.qty as p_qty,
			p.serv_group,
			p.claim_amt,
			p.pay,
			d.product_name,
			d.qty as d_qty 
			FROM patient_ap_services as p 
			INNER JOIN stock_table as d ON d.sn=p.drug_sn 
			INNER JOIN stock_table_inven as inv ON p.sn=inv.sale_sn 
			where p.paystatus='1' and p.drug_sn='$drug_sn' and p.dept_id='$dept_id' $staff and p.transact_date between '$start' AND '$to'");
									} else {

										$query_rstSelect2 = $db->query("SELECT 
			p.qty as p_qty,
			p.serv_group,
			p.claim_amt,
			p.pay,
			d.product_name,
			d.qty as d_qty 
			FROM patient_ap_services as p 
			INNER JOIN stock_table as d ON d.sn=p.drug_sn 
			INNER JOIN stock_table_inven as inv ON p.sn=inv.sale_sn 
			where p.transact_date='$setdate' and p.paystatus='1' and p.drug_sn='$drug_sn' and p.dept_id='$dept_id' $staff");
									}

									if ($query_rstSelect2->rowCount() > 0) {  ?>
									<?php  // $pos=0;  $nhis=0;      
										while ($roww = $query_rstSelect2->fetch(PDO::FETCH_ASSOC)) {
											$qty = $qty + $roww['p_qty'];
											if ($roww['serv_group'] == 'EX') {
												$pos = $pos + $roww['pay'];
											}
											if ($roww['claim_amt'] > 0 and $roww['pay'] > 0) {
												$nhis = $nhis + $roww['pay'];
												$nhis_90 = $nhis_90 + $roww['claim_amt'];
											}
											if ($roww['claim_amt'] == 0 and $roww['pay'] > 0 and $roww['serv_group'] != 'EX') {
												$pvt = $pvt + $roww['pay'];
											}
											if ($roww['claim_amt'] > 0 and $roww['pay'] == 0) {
												$claim = $claim + $roww['claim_amt'];
											}
											$drug_name = $roww['product_name'];
											$d_qty = $roww['d_qty'];
										}
									} ?>
									<tr class="record">
										<td style="border-bottom: 1px solid #ddd;"><?php echo $drug_name; ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?php echo $qty;
																					$qty = 0; ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?php echo $d_qty; ?></td>
										<td width="40%" style="border-bottom: 1px solid #ddd;">
											<table width="100%">
												<tr>
													<td width="25%">
														<div align="right"><?php echo number_format($pos, 2, '.', ','); ?></div>
													</td>
													<td width="25%">
														<div align="right"><?php echo number_format($pvt, 2, '.', ','); ?></div>
													</td>
													<td width="25%">
														<div align="right"><?php echo number_format($nhis, 2, '.', ','); ?></div>
													</td>
													<td width="25%">
														<div align="right"><?php echo number_format($claim, 2, '.', '.'); ?></div>
													</td>
												</tr>
											</table>
										</td>
									</tr>

								<?php
									$t_pos = $t_pos + $pos;
									$t_nhis = $t_nhis + $nhis;
									$t_nhis_90 = $t_nhis_90 + $nhis_90;
									$t_pvt = $t_pvt + $pvt;
									$t_claim = $t_claim + $claim;
									$pos = 0;
									$nhis = 0;
									$pvt = 0;
									$pvt = 0;
									$claim = 0;
								} ?>

								<tr class="record">
									<td style="border-bottom: 1px solid #ddd;"><b>Summary(Total):</b></td>
									<td style="border-bottom: 1px solid #ddd;"></td>
									<td style="border-bottom: 1px solid #ddd;"></td>
									<td width="40%" style="border-bottom: 1px solid #ddd;">
										<table width="100%">
											<tr>
												<td width="25%">
													<div align="right"><strong><?php echo number_format($t_pos, 2, '.', ','); ?></strong></div>
												</td>
												<td width="25%">
													<div align="right"><strong><?php echo number_format($t_pvt, 2, '.', ','); ?></strong></div>
												</td>
												<td width="25%">
													<div align="right"><strong><?php echo number_format($t_nhis, 2, '.', ',') . '(10%)' . '<br>' . number_format($t_nhis, 2, '.', ',') . '(90%)'; ?></strong></div>
												</td>
												<td width="25%">
													<div align="right"><strong><?php echo number_format($t_claim, 2, '.', ','); ?></strong></div>
												</td>
											</tr>
										</table>
									</td>
							</tbody>
						</table>
						<br><br>
						<div style="font-size:14px"><strong><i>GENERATED BY</i>:</strong></div>
						<strong style="font-size:14px;"><?php echo $staff_name; ?></strong>

					<?php } else {
						echo '<strong style="font-size:14px;">No Data Found</strong>';
					}
				} elseif ($rpt_type == 'ap') { ?>

					<?php
					if ($ddset == 1) {

						$query_rstSelect2 = $db->query("SELECT distinct p.drug_sn 
	FROM patient_ap_services as p 
	inner join stock_table_inven as inv on inv.stock_sn = p.drug_sn 
	where p.drug_status='1' and p.paystatus='1' and p.dept_id='$dept_id' $staff and transact_date between '$start' AND '$to'");
					} else {
						$query_rstSelect2 = $db->query("SELECT distinct p.drug_sn 
	FROM patient_ap_services as p 
	inner join stock_table_inven as inv on inv.stock_sn = p.drug_sn 
	where p.transact_date='$setdate' and p.drug_status='1' and p.paystatus='1' and p.dept_id='$dept_id' $staff");
					}

					if ($query_rstSelect2->rowCount() > 0) {  ?>
						<?php  // $pos=0;  $nhis=0;      
						$t_pos = 0;
						$t_nhis = 0;
						$t_pvt = 0;
						$t_claim = 0;
						$t_nhis_90 = 0;
						$pos = 0;
						$nhis = 0;
						$pvt = 0;
						$claim = 0;
						$nhis_90 = 0;
						$qty = 0;
						while ($row = $query_rstSelect2->fetch(PDO::FETCH_ASSOC)) { ?>

							<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
								<thead>
									<tr bgcolor="#CCCCCC">
										<th width="7%">Invoice # </th>
										<th width="7%">Hospital # </th>
										<th width="7%">Qty </th>
										<th width="7%">Claim </th>
										<th width="7%">Amt Paid </th>
										<th width="7%">Date </th>
										<th width="7%">Entered/Disp. by </th>
									</tr>
								</thead>
								<tbody>

									<?php

									$drug_sn = $row['drug_sn'];    ///// target 
									if ($ddset == 1) {

										$rstSelect = $db->query("SELECT 
			p.item_services,
			p.date_entry,
			p.prepared_by,
			p.invoice_no,
			p.hospital_no,
			p.qty as p_qty,
			p.serv_group,
			p.claim_amt,
			p.pay 
			FROM patient_ap_services as p 
			INNER JOIN stock_table_inven as inv ON p.sn=inv.sale_sn 
			where p.drug_status='1' and p.paystatus='1' and p.drug_sn='$drug_sn' and p.dept_id='$dept_id' $staff and 
			transact_date between '$start' AND '$to' order by hospital_no");
									} else {

										$rstSelect = $db->query("SELECT 
			p.item_services,
			p.date_entry,
			p.prepared_by,
			p.invoice_no,
			p.hospital_no,
			p.qty as p_qty,
			p.serv_group,
			p.claim_amt,
			p.pay 
			FROM patient_ap_services as p 
			INNER JOIN stock_table_inven as inv ON p.sn=inv.sale_sn 
			where p.transact_date='$setdate' and p.drug_status='1' and p.paystatus='1' and p.drug_sn='$drug_sn' and p.dept_id='$dept_id' $staff");
									}

									if ($rstSelect->rowCount() > 0) {
										while ($roww = $query_rstSelect2->fetch(PDO::FETCH_ASSOC)) {

											$qty = $qty + $roww['p_qty'];
											if ($roww['serv_group'] == 'EX') {
												$pos = $pos + $roww['pay'];
											}
											if ($roww['claim_amt'] > 0 and $roww['pay'] > 0) {
												$nhis = $nhis + $roww['pay'];
												$nhis_90 = $nhis_90 + $roww['claim_amt'];
											}
											if ($roww['claim_amt'] == 0 and $roww['pay'] > 0 and $roww['serv_group'] != 'EX') {
												$pvt = $pvt + $roww['pay'];
											}
											if ($roww['claim_amt'] > 0 and $roww['pay'] == 0) {
												$claim = $claim + $roww['claim_amt'];
											}
											$drug_name = $roww['item_services'];
											//$d_qty=$roww['d_qty'];
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

									<?php	 }
									} ?>

									<tr class="record">
										<td style="border-bottom: 1px solid #ddd;"></td>
										<td colspan="2" style="border-bottom: 1px solid #ddd;"><?php
																								echo '<table width="100%"><tr><td colspan="2"><b>' . $drug_name . '</b></td></tr>';
																								echo '<tr><td colspan="2">' . $qty . ' - Qty Dispensed' . '</td></tr></table>';
																								?>
										</td>
										<td colspan="2" style="border-bottom: 1px solid #ddd;"><?php
																								echo '<table width="100%"><tr><td> <div align="right"><b>' . 'Sales: ' . '</b></div></td><td>' . ' N' . number_format($pos, 2, '.', ',') . '</td></tr>';
																								echo '<tr><td> <div align="right"><b>' . 'Claim: ' . '</b></div></td><td>' . ' N' . number_format($claim, 2, '.', ',') . '</td></tr></table>';
																								?>
										</td>
										<td colspan="2" style="border-bottom: 1px solid #ddd;"><?php
																								echo '<table width="100%"><tr><td> <div align="right"><b>' . 'NHIS(10%) Paid: ' . '</b></div></td><td>' . ' N' . number_format($nhis, 2, '.', ',') . '</td></tr>';
																								echo '<tr><td> <div align="right"><b>' . 'NHIS(90%) Billed: ' . '</b></div></td><td>' . ' N' . number_format($nhis_90, 2, '.', ',') . '</td></tr>';
																								echo '<tr><td> <div align="right"><b>' . 'Private Paid: ' . '</b></div></td><td>' . ' N' . number_format($pvt, 2, '.', ',') . '</td></tr></table>';
																								?>
										</td>

									</tr>

									<?php
									$t_pos = $t_pos + $pos;
									$t_nhis = $t_nhis + $nhis;
									$t_nhis_90 = $t_nhis_90 + $nhis_90;
									$t_pvt = $t_pvt + $pvt;
									$t_claim = $t_claim + $claim;
									$t_qty = $t_qty + $qty;
									$pos = 0;
									$nhis = 0;
									$pvt = 0;
									$claim = 0;
									$nhis_90 = 0;
									$qty = 0;
									//} 
									?>

								</tbody>
							</table>
						<?php } ?>
						<br><br>
						<div style="font-size:14px"><strong><i>GENERATED BY</i>:</strong></div>
						<strong style="font-size:14px;"><?php echo $staff_name; ?></strong>

					<?php
					} else {
						echo '<strong style="font-size:14px;">No Data Found</strong>';
					}
				} elseif ($rpt_type == 'ps') {


					/// PATIENT SEEN
					$setdate = date('Y-m-d');
					if ($ddset == 1) {
						$rstSelect = $db->query("SELECT 
			distinct p.hospital_no 
			FROM patient_ap_services as p 
			where p.paystatus='1' and p.dept_id='$dept_id' $staff and date(p.date_entry) between '$start' AND '$to'");
					} else {
						$rstSelect = $db->query("SELECT 
			distinct p.hospital_no 
			FROM patient_ap_services as p 
			where p.paystatus='1' and p.dept_id='$dept_id' and date(p.date_entry)='$setdate' $staff");
					}

					if ($rstSelect->rowCount() > 0) { ?>

						<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
							<thead>
								<tr bgcolor="#CCCCCC">
									<th>A/P#</th>
									<th>Hospital #</th>
									<th>Dept</th>
									<th>Item/Services</th>
									<th>Qty</th>
									<th>Claim</th>
									<th>Amount</th>
									<th>Coverage</th>
									<th>Entered/Disp.By</th>
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
								$pos = 0;
								$nhis = 0;
								$pvt = 0;
								$claim = 0;
								$nhis_90 = 0;
								while ($row = $rstSelect->fetch(PDO::FETCH_ASSOC)) {
									///while($row=mysql_fetch_array()) {
									$hospital_no = $row['hospital_no'];   ///// target 

									if ($ddset == 1) {
										$query_rstSelect2 = $db->query("SELECT * FROM patient_ap_services 
							where paystatus='1' and hospital_no='$hospital_no' and dept_id='$dept_id' $staff and date(date_entry) between '$start' AND '$to' ");
									} else {
										$query_rstSelect2 = $db->query("SELECT * FROM patient_ap_services 
							where paystatus='1' and hospital_no='$hospital_no' and dept_id='$dept_id' and date(date_entry)='$setdate' $staff");
									}
									while ($roww = $query_rstSelect2->fetch(PDO::FETCH_ASSOC)) {
										$qty = $qty + $roww['p_qty'];

										if ($roww['serv_group'] == 'EX' and $roww['paystatus'] == 1) {
											$pos = $pos + $roww['pay'];
										}
										if ($roww['claim_amt'] > 0 and $roww['pay'] > 0 and $roww['paystatus'] == 1) {
											$nhis = $nhis + $roww['pay'];
											$nhis_90 = $nhis_90 + $roww['claim_amt'];
										}
										if ($roww['claim_amt'] == 0 and $roww['pay'] > 0 and $roww['serv_group'] != 'EX' and $roww['paystatus'] == 1) {
											$pvt = $pvt + $roww['pay'];
										}
										if ($roww['claim_amt'] > 0 and $roww['pay'] == 0 and $roww['paystatus'] == 1) {
											$claim = $claim + $roww['claim_amt'];
										}
										if ($roww['drug_status'] == 1 and $roww['paystatus'] == 0) {
											$pay_credit = $pay_credit + $roww['pay'];
										}
								?>
										<tr class="record">
											<td style="border-bottom: 1px solid #ddd;"><?php echo $roww['app_no']; ?></td>
											<td style="border-bottom: 1px solid #ddd;"><?php echo $hospital_no; ?></td>
											<td style="border-bottom: 1px solid #ddd;"></td>
											<td style="border-bottom: 1px solid #ddd;"><?php echo $roww['item_services']; ?></td>
											<td style="border-bottom: 1px solid #ddd;"><?php echo $roww['qty']; ?></td>
											<td style="border-bottom: 1px solid #ddd;"><?php echo $roww['claim_amt']; ?></td>
											<td style="border-bottom: 1px solid #ddd;"><?php echo $roww['pay']; ?></td>
											<td style="border-bottom: 1px solid #ddd;"><?php if ($roww['access'] == 1) {
																							echo 'NHIS';
																						} else {
																							echo 'Private';
																						} ?></td>
											<td style="border-bottom: 1px solid #ddd;"><?php echo $roww['prepared_by'] . ' / ' . $roww['dsp_by']; ?></td>
											<td style="border-bottom: 1px solid #ddd;"><?php echo date('d,M Y h:i:s a', strtotime($roww['date_entry'])); ?></td>
										</tr>
									<?php }
									// end of looping create summary
									?>
									<tr class="record">
										<td style="border-bottom: 1px solid #ddd;"></td>
										<td style="border-bottom: 1px solid #ddd;"></td>
										<td style="border-bottom: 1px solid #ddd;"></td>
										<td style="border-bottom: 1px solid #ddd;"><strong>Patient Sub Total:</strong></td>
										<td style="border-bottom: 1px solid #ddd;"></td>
										<td style="border-bottom: 1px solid #ddd;"><?php if ($pay_credit > 0) {
																						echo '<b>Dispense on Credit</b><br>' . 'N ' . number_format($pay_credit, 2, '.', ',');
																					} ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?php if ($pos > 0) {
																						echo '<b>POS (External Sales)</b><br>' . 'N ' . number_format($pos, 2, '.', ',');
																					} ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?php if ($nhis > 0) {
																						echo '<b>NHIS (10% Payable)</b><br>' . 'N ' . number_format($nhis, 2, '.', ',');
																					} ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?php if ($pvt > 0) {
																						echo '<b>Private Sales</b><br>' . 'N ' . number_format($pvt, 2, '.', ',');
																					} ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?php if ($claim > 0) {
																						echo '<b>Claim(100% Insurance Bills)</b><br>' . 'N ' . number_format($claim, 2, '.', ',');
																					} ?></td>
									</tr>
								<?php } ?>

								<?php
								$t_pos = $t_pos + $pos;
								$t_nhis = $t_nhis + $nhis;
								$t_nhis_90 = $t_nhis_90 + $nhis_90;
								$t_pvt = $t_pvt + $pvt;
								$t_claim = $t_claim + $claim;
								$t_pay_credit = $t_pay_credit + $pay_credit;
								$pos = 0;
								$nhis = 0;
								$pvt = 0;
								$pvt = 0;
								$claim = 0;
								$pay_credit = 0;

								?>

							</tbody>
						</table>
						<div align="right">
							<div style="font-size:18px"><b>Summary:</b></div>
							<table width="" cellpadding="3">
								<tr>
									<td width="">Dispensed On Credit</td>
									<td width="">
										<div align="right"><strong><?php echo number_format($t_pay_credit, 2, '.', ','); ?></strong></div>
									</td>
								</tr>
								<td width="">External Sale (POS)</td>
								<td width="">
									<div align="right"><strong><?php echo number_format($t_pos, 2, '.', ','); ?></strong></div>
								</td>
								</tr>
								<tr>
									<td width="">Private Patient Sales:</td>
									<td width="">
										<div align="right"><strong><?php echo number_format($t_pvt, 2, '.', ','); ?></strong></div>
									</td>
								</tr>
								<tr>
									<td width="">NHIS (10% Payable/Recieved)</td>
									<td width="25%">
										<div align="right"><strong><?php echo number_format($t_nhis, 2, '.', ','); ?></strong></div>
									</td>
								</tr>
								<tr>
									<td width="">NHIS (90% Claim)</td>
									<td width="25%">
										<div align="right"><strong><?php echo number_format($t_nhis, 2, '.', ','); ?></strong></div>
									</td>
								</tr>

								<tr>
									<td width="">Total Claim/Insurance Bills</td>
									<td width="25%">
										<div align="right"><strong><?php echo number_format($t_claim, 2, '.', ','); ?></strong></div>
									</td>
								</tr>
							</table>
						</div>

						<br><br>
						<div style="font-size:14px"><strong><i>GENERATED BY</i>:</strong></div>
						<strong style="font-size:14px;"><?php echo $staff_name; ?></strong>

					<?php } else {
						echo '<strong style="font-size:14px;">No Data Found</strong>';
					}
				} elseif ($rpt_type == 'dcr') {

					/// PATIENT SEEN
					$setdate = date('Y-m-d');

					if ($ddset == 1) {

						$query_rstSelect2 = $db->query("SELECT distinct hospital_no 
			FROM patient_ap_services 
			where cr='1' and dept_id='$dept_id' $staff and paystatus='0' and date(date_entry) between '$start' AND '$to'");
					} else {

						$query_rstSelect2 = $db->query("SELECT distinct hospital_no 
			FROM patient_ap_services 
			where cr='1' and dept_id='$dept_id' $staff and paystatus='0' and date(date_entry)='$setdate'");
					}


					if ($query_rstSelect2->rowCount() > 0) {  ?>

						<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
							<thead>
								<tr bgcolor="#CCCCCC">
									<th>A/P#</th>
									<th>Hospital #</th>
									<th>Dept</th>
									<th>Service/Consumable</th>
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
								$pos = 0;
								$nhis = 0;
								$pvt = 0;
								$claim = 0;
								$nhis_90 = 0;
								///while($row=mysql_fetch_array($rstSelect)) {
								while ($row = $query_rstSelect2->fetch(PDO::FETCH_ASSOC)) {
									$hospital_no = $row['hospital_no'];

									if ($ddset == 1) {

										$rstSelect2 = $db->query("SELECT * FROM patient_ap_services 
			where cr='1' and paystatus='0' and hospital_no='$hospital_no' and dept_id='$dept_id' $staff 
				and date(date_entry) between '$start' AND '$to'");
									} else {

										$rstSelect2 = $db->query("SELECT * FROM patient_ap_services 
			where cr='1' and paystatus='0' and hospital_no='$hospital_no' and dept_id='$dept_id' $staff 
				and date(date_entry)= '$setdate'");
									}

									if ($rstSelect2->rowCount() > 0) { ?>
										<?php  // $pos=0;  $nhis=0;      
										while ($roww = $rstSelect2->fetch(PDO::FETCH_ASSOC)) {
											$qty = $qty + $roww['qty'];
											if ($roww['serv_group'] == 'EX') {
												$pos = $pos + $roww['pay'];
											}
											if ($roww['claim_amt'] > 0 and $roww['pay'] > 0) {
												$nhis = $nhis + $roww['pay'];
												$nhis_90 = $nhis_90 + $roww['claim_amt'];
											}
											if ($roww['claim_amt'] == 0 and $roww['pay'] > 0 and $roww['serv_group'] != 'EX') {
												$pvt = $pvt + $roww['pay'];
											}
											if ($roww['claim_amt'] > 0 and $roww['pay'] == 0) {
												$claim = $claim + $roww['claim_amt'];
											}
										?>
											<tr class="record">
												<td style="border-bottom: 1px solid #ddd;"><?php echo $roww['app_no']; ?></td>
												<td style="border-bottom: 1px solid #ddd;"><?php echo $hospital_no; ?></td>
												<td style="border-bottom: 1px solid #ddd;"><?php echo $roww['serv_group']; ?></td>
												<td style="border-bottom: 1px solid #ddd;"><?php echo $roww['item_services']; ?></td>
												<td style="border-bottom: 1px solid #ddd;"><?php echo $roww['qty']; ?></td>
												<td style="border-bottom: 1px solid #ddd;"><?php echo $roww['claim_amt']; ?></td>
												<td style="border-bottom: 1px solid #ddd;"><?php echo $roww['pay']; ?></td>
												<td style="border-bottom: 1px solid #ddd;"><?php echo $roww['prepared_by'] . ' / ' . $roww['dsp_by']; ?></td>
												<td style="border-bottom: 1px solid #ddd;"><?php echo date('d,M Y h:i:s a', strtotime($roww['date_entry'])); ?></td>
											</tr>
										<?php }
										// end of looping create summary
										?>
										<tr class="record">
											<td style="border-bottom: 1px solid #ddd;"></td>
											<td style="border-bottom: 1px solid #ddd;"></td>
											<td style="border-bottom: 1px solid #ddd;"></td>
											<td style="border-bottom: 1px solid #ddd;"><strong>Patient Sub Total:</strong></td>
											<td style="border-bottom: 1px solid #ddd;"></td>
											<td style="border-bottom: 1px solid #ddd;"><?php if ($pay_credit > 0) {
																							echo '<b>Dispense on Credit</b><br>' . 'N ' . number_format($pay_credit, 2, '.', ',');
																						} ?></td>
											<td style="border-bottom: 1px solid #ddd;"><?php if ($pos > 0) {
																							echo '<b>POS (External Credit)</b><br>' . 'N ' . number_format($pos, 2, '.', ',');
																						} ?></td>
											<td style="border-bottom: 1px solid #ddd;"><?php if ($nhis > 0) {
																							echo '<b>NHIS (10% Payable)</b><br>' . 'N ' . number_format($nhis, 2, '.', ',');
																						} ?></td>
											<td style="border-bottom: 1px solid #ddd;"><?php if ($pvt > 0) {
																							echo '<b>Private Credit </b><br>' . 'N ' . number_format($pvt, 2, '.', ',');
																						} ?></td>
											<td style="border-bottom: 1px solid #ddd;"><?php if ($claim > 0) {
																							echo '<b>Claim(100% Insurance Bills)</b><br>' . 'N ' . number_format($claim, 2, '.', ',');
																						} ?></td>
										</tr>
									<?php } ?>

								<?php
									$t_pos = $t_pos + $pos;
									$t_nhis = $t_nhis + $nhis;
									$t_nhis_90 = $t_nhis_90 + $nhis_90;
									$t_pvt = $t_pvt + $pvt;
									$t_claim = $t_claim + $claim;
									$t_pay_credit = $t_pay_credit + $pay_credit;
									$pos = 0;
									$nhis = 0;
									$pvt = 0;
									$pvt = 0;
									$claim = 0;
									$pay_credit = 0;
								} ?>

							</tbody>
						</table>
						<div align="right">
							<div style="font-size:18px"><b>Summary:</b></div>
							<table width="" cellpadding="3">
								<tr>
									<td width="">Dispensed On Credit</td>
									<td width="">
										<div align="right"><strong><?php echo number_format($t_pay_credit, 2, '.', ','); ?></strong></div>
									</td>
								</tr>
								<td width="">External Sale (POS)</td>
								<td width="">
									<div align="right"><strong><?php echo number_format($t_pos, 2, '.', ','); ?></strong></div>
								</td>
								</tr>
								<tr>
									<td width="">Private Patients :</td>
									<td width="">
										<div align="right"><strong><?php echo number_format($t_pvt, 2, '.', ','); ?></strong></div>
									</td>
								</tr>
								<tr>
									<td width="">NHIS (10% Payable/Recieved)</td>
									<td width="25%">
										<div align="right"><strong><?php echo number_format($t_nhis, 2, '.', ','); ?></strong></div>
									</td>
								</tr>
								<tr>
									<td width="">NHIS (90% Claim)</td>
									<td width="25%">
										<div align="right"><strong><?php echo number_format($t_nhis, 2, '.', ','); ?></strong></div>
									</td>
								</tr>

								<tr>
									<td width="">Total Claim/Insurance Bills</td>
									<td width="25%">
										<div align="right"><strong><?php echo number_format($t_claim, 2, '.', ','); ?></strong></div>
									</td>
								</tr>
							</table>
						</div>

						<br><br>
						<div style="font-size:14px"><strong><i>GENERATED BY</i>:</strong></div>
						<strong style="font-size:14px;"><?php echo $staff_name; ?></strong>


						<form method="post" action="download_credit_report_csv.php" target="_blank" style="margin-bottom:10px;">
							<input type="hidden" name="ddset" value="<?php echo $ddset; ?>">
							<input type="hidden" name="dept_id" value="<?php echo $dept_id; ?>">
							<input type="hidden" name="start" value="<?php echo $start; ?>">
							<input type="hidden" name="to" value="<?php echo $to; ?>">
							<input type="hidden" name="staff" value="<?php echo htmlspecialchars($staff); ?>">

							<button type="submit" style="padding:6px 12px; cursor:pointer;">
								⬇ Download CSV
							</button>
						</form>




					<?php } else {
						echo '<strong style="font-size:14px;">No Data Found</strong>';
					}
				} elseif ($rpt_type == 'inv') {
				} elseif ($rpt_type == 'd_r') {

					/// PATIENT SEEN
					$setdate = date('Y-m-d');

					if ($ddset == 1) {

						$query_rstSelect = $db->query("SELECT * FROM patient_ap_services 
			where paystatus='1' and dept_id='$dept_id' $staff and date(date_entry) between '$start' AND '$to' order by item_services ");
					} else {

						$query_rstSelect = $db->query("SELECT * FROM patient_ap_services 
			where paystatus='1' and dept_id='$dept_id' $staff and date(date_entry)= '$setdate' order by item_services ");
					}


					if ($query_rstSelect->rowCount() > 0) { ?>
						<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
							<thead>
								<tr bgcolor="#CCCCCC">
									<th>A/P#</th>
									<th>Hospital #</th>
									<th>Dept</th>
									<th>Item</th>
									<th>Qty</th>
									<th>Claim</th>
									<th>Amount</th>
									<th>Entered/Disp by</th>
									<th>Date</th>
								</tr>
							</thead>
							<tbody>
								<?php

								while ($roww = $query_rstSelect->fetch(PDO::FETCH_ASSOC)) {

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


						<form method="post" action="dept_services_csv.php" target="_blank" style="margin-top:10px;">
							<input type="hidden" name="dept_id" value="<?php echo $dept_id; ?>">
							<input type="hidden" name="ddset" value="<?php echo $ddset; ?>">
							<input type="hidden" name="start" value="<?php echo $start; ?>">
							<input type="hidden" name="to" value="<?php echo $to; ?>">
							<input type="hidden" name="setdate" value="<?php echo $setdate; ?>">

							<button type="submit" class="btn btn-success btn-sm">
								Export to CSV
							</button>
						</form>

					<?php
					} else {
						echo '<strong style="font-size:14px;">No Data Found</strong>';
					}
				} elseif ($rpt_type == 'inv_drug') {

					$searchdrug_inv = $_POST['searchdrug_inv'];
					$setdate = date('Y-m-d');

					if ($ddset == 1) {



						$rstSelect2 = $db->query("SELECT * FROM patient_ap_services 
			where paystatus='1' and drug_sn='$searchdrug_inv' and dept_id='$dept_id' $staff and 
			date(date_entry) between '$start' AND '$to' order by item_services ");
					} else {

						$rstSelect2 = $db->query("SELECT * FROM patient_ap_services 
			where paystatus='1' and drug_sn='$searchdrug_inv' and dept_id='$dept_id' $staff and 
			date(date_entry)= '$setdate' order by item_services ");
					}


					if ($rstSelect2->rowCount() > 0) { ?>
						<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
							<thead>
								<tr bgcolor="#CCCCCC">
									<th>A/P#</th>
									<th>Hospital #</th>
									<th>Dept</th>
									<th>Service Name</th>
									<th>Qty</th>
									<th>Claim</th>
									<th>Amount</th>
									<th>Entered/Disp by</th>
									<th>Date</th>
								</tr>
							</thead>
							<tbody>
								<?php

								//while($roww=mysql_fetch_array($rstSelect)) {
								while ($roww = $rstSelect2->fetch(PDO::FETCH_ASSOC)) {

									if ($roww['claim_amt'] == 0 and $roww['pay'] > 0) {
										$pvt = $pvt + $roww['pay'];
									}
									if ($roww['claim_amt'] > 0 and $roww['pay'] == 0) {
										$claim = $claim + $roww['claim_amt'];
									}


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
						<hr>
						<h2>Total (Cash): =N= <?php echo number_format($pvt); ?></h2>
						<h2>Total (Claim): =N= <?php echo number_format($claim); ?></h2>

						<br><br>
						<div style="font-size:14px"><strong><i>GENERATED BY</i>:</strong></div>
						<strong style="font-size:14px;"><?php echo $staff_name; ?></strong>


						<form method="post" action="drug_usage_csv.php" target="_blank" style="margin-top:10px;">
							<input type="hidden" name="searchdrug_inv" value="<?php echo $searchdrug_inv; ?>">
							<input type="hidden" name="dept_id" value="<?php echo $dept_id; ?>">
							<input type="hidden" name="ddset" value="<?php echo $ddset; ?>">
							<input type="hidden" name="start" value="<?php echo $start; ?>">
							<input type="hidden" name="to" value="<?php echo $to; ?>">
							<input type="hidden" name="setdate" value="<?php echo $setdate; ?>">

							<button type="submit" class="btn btn-success btn-sm">
								Export to CSV
							</button>
						</form>

						<?php
					} else {
						echo '<strong style="font-size:14px;">No Data Found</strong>';
					}
				} elseif ($rpt_type == 'rhmo') {


					if (isset($_POST["hmo_nhis"]) and $_POST["hmo_nhis"] != '') {
						$Tclaim_hmo = 0;
						$Tpay_hmo = 0;
						$hmo_type = $_POST["hmo_type"];

						foreach ($_POST["hmo_nhis"] as $hmo_nhis_no) {


							$parts = explode("__", $hmo_nhis_no);
							$hmo_nhis_no = $parts[0];
							$hmo_name = $parts[1];

							if ($ddset == 1) {
								$search_date = " and date(ap.date_entry) between '$start' AND '$to'";
							} else {
								$search_date = " and date(ap.date_entry)='$setdate'";
							}

							$stt = $db->prepare("SELECT 
enl.hospital_no, ap.* 
FROM enrollee AS enl 
INNER JOIN patient_ap_services AS ap ON ap.hospital_no=enl.hospital_no 
WHERE enl.hmo_no=:hmo_no and enl.insurance=:insur_type and ap.dept_id=:dept_id and ap.paystatus='1' $search_date");
							$stt->bindValue(':hmo_no', $hmo_nhis_no, PDO::PARAM_STR);
							$stt->bindValue(':insur_type', $hmo_type, PDO::PARAM_STR);
							$stt->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
							$stt->execute();
							if ($stt->rowCount() > 0) {

						?>

								<h3><?php echo $hmo_name; ?></h3>
								<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
									<thead>
										<tr bgcolor="#CCCCCC">
											<th>A/P#</th>
											<th>Hospital #</th>
											<th>Item Name</th>
											<th>Qty</th>
											<th>Claim</th>
											<th>Amount</th>
											<th>Entered/Disp by</th>
											<th>Date</th>
										</tr>
									</thead>
									<tbody>
										<?php
										$claim_hmo = 0;
										$pay_hmo = 0;
										while ($roww = $stt->fetch(PDO::FETCH_ASSOC)) {

											$claim_hmo = $claim_hmo + $roww['claim_amt'];
											$Tclaim_hmo = $Tclaim_hmo + $roww['claim_amt'];

											$pay_hmo = $pay_hmo + $roww['pay'];
											$Tpay_hmo = $Tpay_hmo + $roww['pay'];

										?>
											<tr class="record">
												<td style="border-bottom: 1px solid #ddd;"><?php echo $roww['app_no']; ?></td>
												<td style="border-bottom: 1px solid #ddd;"><?php echo $roww['hospital_no']; ?></td>
												<td style="border-bottom: 1px solid #ddd;"><?php echo $roww['item_services']; ?></td>
												<td style="border-bottom: 1px solid #ddd;"><?php echo $roww['qty']; ?></td>
												<td style="border-bottom: 1px solid #ddd;"><?php echo $roww['claim_amt']; ?></td>
												<td style="border-bottom: 1px solid #ddd;"><?php echo $roww['pay']; ?></td>
												<td style="border-bottom: 1px solid #ddd;"><?php echo $roww['prepared_by'] . ' / ' . $roww['dsp_by']; ?></td>
												<td style="border-bottom: 1px solid #ddd;"><?php echo date('d,M Y h:i:s a', strtotime($roww['date_entry'])); ?></td>
											</tr>
										<?php

										}  ?>
									</tbody>
								</table>

								<h3><strong>Total Claim:</strong> <?php echo number_format($claim_hmo, 2); ?> &nbsp;|&nbsp; <strong>Cash Recieved: </strong><?php echo number_format($pay_hmo, 2); ?> </h3>
								<br>

								<form method="post" action="hmo_dept_csv.php" target="_blank" style="margin-bottom:15px;">
									<?php foreach ($_POST['hmo_nhis'] as $hmo): ?>
										<input type="hidden" name="hmo_nhis[]" value="<?php echo htmlspecialchars($hmo); ?>">
									<?php endforeach; ?>

									<input type="hidden" name="hmo_type" value="<?php echo $hmo_type; ?>">
									<input type="hidden" name="dept_id" value="<?php echo $dept_id; ?>">
									<input type="hidden" name="start" value="<?php echo $start; ?>">
									<input type="hidden" name="to" value="<?php echo $to; ?>">
									<input type="hidden" name="ddset" value="<?php echo $ddset; ?>">
									<input type="hidden" name="setdate" value="<?php echo $setdate; ?>">

									<button type="submit" class="btn btn-success btn-sm">
										Export to CSV
									</button>
								</form>

							<?php

							}
							$claim_hmo = 0;
							$pay_hmo = 0;
						}

						if ($Tclaim_hmo > 0) { ?>
							<hr>
							<h2>Grand Total (Claim): <?php echo number_format($Tclaim_hmo, 2); ?></h2>
							<h2>Grand Total (Paid Amount): <?php echo number_format($Tpay_hmo, 2); ?></h2>
						<?php } ?>

					<?php } else {
						echo 'Invalid selection';
					}
				} elseif ($rpt_type == 'visit') {

					$setdate = date('Y-m-d');

					if ($ddset == 1) {
					} else {
						$start = $setdate;
						$to = $setdate;
					}
					$paystatus = '1';
					$TotalP_claim = 0;
					$Tp_pay = 0;
					$Tvisit = 0;
					///	$dept_id='Pharmacy';
					//	echo '------'. $dept_id;

					$stt = $db->prepare("SELECT DISTINCT hospital_no FROM patient_ap_services WHERE paystatus=:paystatus and dept_id=:dept_id $staff and date(date_entry) between '$start' AND '$to' order by item_services");
					$stt->bindValue(':paystatus', $paystatus, PDO::PARAM_STR);
					$stt->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
					$stt->execute();
					if ($stt->rowCount() > 0) {	?>



						<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
							<thead>
								<tr bgcolor="#CCCCCC">
									<th>Hospital #</th>
									<th>Total Visit</th>
									<th>Claim</th>
									<th>Amount</th>
								</tr>
							</thead>
							<tbody>
								<?php

								while ($roww = $stt->fetch(PDO::FETCH_ASSOC)) {
									$hospital_no = $roww['hospital_no'];
									$stt2 = $db->prepare("SELECT 
sum(claim_amt) as P_claim, 
sum(pay) as p_pay 
FROM patient_ap_services 
WHERE paystatus=:paystatus and dept_id=:dept_id and hospital_no=:hospital_no $staff and date(date_entry) between '$start' AND '$to' order by item_services");
									$stt2->bindValue(':paystatus', $paystatus, PDO::PARAM_STR);
									$stt2->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
									$stt2->bindValue(':hospital_no', $hospital_no, PDO::PARAM_STR);
									$stt2->execute();
									$rowwx = $stt2->fetch(PDO::FETCH_ASSOC);


									////// total visit

									$stt_visit = $db->prepare("SELECT DISTINCT app_no FROM patient_ap_services 
WHERE hospital_no=:hospital_no and paystatus=:paystatus and dept_id=:dept_id $staff and date(date_entry) between '$start' AND '$to' order by item_services");

									$stt_visit->bindValue(':hospital_no', $hospital_no, PDO::PARAM_STR);
									$stt_visit->bindValue(':paystatus', $paystatus, PDO::PARAM_STR);
									$stt_visit->bindValue(':dept_id', $dept_id, PDO::PARAM_STR);
									$stt_visit->execute();


									$TotalP_claim = $TotalP_claim + $rowwx['P_claim'];
									$Tp_pay = $Tp_pay + $rowwx['p_pay'];
									$Tvisit = $Tvisit + $stt_visit->rowCount();

								?>

									<tr class="record">
										<td style="border-bottom: 1px solid #ddd;"><?php echo $hospital_no; ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?php echo $stt_visit->rowCount(); ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?php echo number_format($rowwx['P_claim'], 2); ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?php echo  number_format($rowwx['p_pay'], 2); ?></td>
									</tr>
								<?php }  ?>

								<tr class="record">
									<td style="border-bottom: 1px solid #ddd;">&nbsp;</td>
									<td style="border-bottom: 1px solid #ddd;">&nbsp;</td>
									<td style="border-bottom: 1px solid #ddd;">&nbsp; </td>
								</tr>
								<tr class="record">
									<td style="border-bottom: 1px solid #ddd;"><strong>Total: <?php echo $stt->rowCount(); ?></strong></td>
									<td style="border-bottom: 1px solid #ddd;"><strong><?php echo $Tvisit; ?></strong></td>
									<td style="border-bottom: 1px solid #ddd;"><strong><?php echo number_format($TotalP_claim, 2); ?></strong></td>
									<td style="border-bottom: 1px solid #ddd;"><strong><?php echo number_format($Tp_pay, 2); ?></strong></td>
								</tr>
							</tbody>
						</table>

						<br><br>
						<div style="font-size:14px"><strong><i>GENERATED BY</i>:</strong></div>
						<strong style="font-size:14px;"><?php echo $staff_name; ?></strong>



						<form method="post" action="dept_visit_csv.php" target="_blank" style="margin-top:10px;">
							<input type="hidden" name="dept_id" value="<?php echo $dept_id; ?>">
							<input type="hidden" name="start" value="<?php echo $start; ?>">
							<input type="hidden" name="to" value="<?php echo $to; ?>">
							<input type="hidden" name="paystatus" value="<?php echo $paystatus; ?>">

							<button type="submit" class="btn btn-primary btn-sm">
								Export to CSV
							</button>
						</form>

					<?php
					} else {
						echo '<strong style="font-size:14px;">No Data Found</strong>';
					}
				} elseif ($rpt_type == 'labour') {

					?>



					<table cellpadding="5" cellspacing="2" class="table table-bordered" style="font-size:12px; font-family:Arial, Helvetica, sans-serif;" width="100%">
						<thead>
							<tr>
								<th>Hospital No</th>
								<th>Induction</th>
								<th>Delivery</th>
								<th>Perineum</th>
								<th>Placenta</th>
								<th>Cord</th>
								<th>Blood Loss (ml)</th>
								<th>Infant Status</th>
								<th>Mother BP</th>
								<th>Mother Pulse</th>
								<th>Mother Uterus</th>
								<th>Delivery/Discharge Date</th>
								<th>.</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$start = $setdate;
							$to = $setdate;
							$results = $db->query("SELECT * FROM labour_summary where date(delivery_date) between '$start' AND '$to'");

							// Loop through the results and display them in the table
							while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
								echo "<tr>";
								echo "<td>" . htmlspecialchars($row['hos_no']) . "</td>";
								echo "<td>" . htmlspecialchars($row['induction']) . "</td>";
								echo "<td>" . htmlspecialchars($row['delivery']) . "</td>";
								echo "<td>" . htmlspecialchars($row['perineum']) . "</td>";
								echo "<td>" . htmlspecialchars($row['placenta']) . "</td>";
								echo "<td>" . htmlspecialchars($row['cord']) . "</td>";
								echo "<td>" . htmlspecialchars($row['blood_loss']) . "</td>";
								echo "<td>" . htmlspecialchars($row['infant_status']) . "</td>";
								echo "<td>" . htmlspecialchars($row['mother_bp']) . "</td>";
								echo "<td>" . htmlspecialchars($row['mother_pulse']) . "</td>";
								echo "<td>" . htmlspecialchars($row['mother_uterus']) . "</td>";
								echo "<td>" . date('d-m-Y', strtotime($row['delivery_date'])) . '<br>' . date('d-m-Y', strtotime($row['discharge_date'])) . '<br><b>Delivered By</b><br>' .  $row['delivered_by'] . "</td>";
							}
							?>
						</tbody>
					</table>

					<form method="post" action="labour_csv.php" target="_blank" style="margin-top:10px;">
						<input type="hidden" name="start" value="<?php echo $setdate; ?>">
						<input type="hidden" name="to" value="<?php echo $setdate; ?>">

						<button type="submit" class="btn btn-success btn-sm">
							Export to CSV
						</button>
					</form>


					<?php

				} elseif ($rpt_type == 'adm') {

					$admission_type = $_POST["admission_type"];
					$room = $_POST["room"];

					if ($room != '') {
						$room_phase = " and room_bed_sn='$room'";
					} else {
						$room_phase = '';
					}
					if ($ddset == 1) {
					} else {
						$start = $setdate;
						$to = $setdate;
					}

					//echo '=' . $start . $to;

					$stt = $db->prepare("SELECT * FROM admission WHERE adm_status=:adm_status $room_phase and date(date_admit) between '$start' AND '$to' order by date_admit");
					$stt->bindValue(':adm_status', $admission_type, PDO::PARAM_STR);
					$stt->execute();
					if ($stt->rowCount() > 0) {	?>



						<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">
							<thead>
								<tr bgcolor="#CCCCCC">
									<th width="10%">Hosp. #</th>
									<th width="17%">Room/bed</th>
									<th width="30%">Admission Details</th>
									<th width="18%">Amount (Details)</th>
									<th width="8%">Claim</th>
									<th width="8%">Paid</th>
									<th width="8%">Credits</th>
									<th width="20%">Dates</th>

								</tr>
							</thead>
							<tbody>
								<?php
								// discharge_status	// discharge_note

								while ($row = $stt->fetch(PDO::FETCH_ASSOC)) {
									$hospital_no = $row['hospital_no'];
									$date_admit = $row['date_admit'];

									if ($admission_type == '3') {
										$date_discharge = date("Y-m-d H:i:s");
										$dates_ = "-" . date('d M,Y', strtotime($row['date_admit'])) . '<br>-Pending';
									} else {
										$date_discharge = $row['date_discharge'];
										$dates_ = "-" . date('d M,Y', strtotime($row['date_admit'])) . '<br>-' . date('d M,Y', strtotime($date_discharge));
									}

									$stt2 = $db->prepare("SELECT * 
									FROM patient_ap_services 
									WHERE hospital_no=:hospital_no and date(date_entry) between '$date_admit' AND '$date_discharge'");
									$stt2->bindValue(':hospital_no', $hospital_no, PDO::PARAM_STR);
									$stt2->execute();

									$other_hosp = 0;
									$medical_hosp = 0;
									$invest_hosp = 0;
									$nursing_hosp = 0;
									$nur_com_hosp = 0;
									$invest_pharm = 0;
									$pvt = 0;
									$claim = 0;
									$pay_credit = 0;
									$Accommodation = 0;
									while ($roww = $stt2->fetch(PDO::FETCH_ASSOC)) {
										$cat_type = $roww['cat_type'];
										$serv_group = $roww['serv_group'];
										if ($roww['claim_amt'] > 0) {
											$pay = $roww['claim_amt'];
										} else {
											$pay = $roww['pay'];
										}



										$serv_group = $roww['serv_group'];

										if ($roww['paystatus'] == 1) {	/// 
											if ($serv_group == 'Other Services' or $serv_group == 'Consultation' or $serv_group == 'Registration') {
												$other_hosp = $other_hosp + $pay;
											}
											if ($serv_group == 'Medical Services') {
												$medical_hosp = $medical_hosp + $pay;
											}
											if ($serv_group == 'Laboratory' or $serv_group == 'Radiology') {
												$invest_hosp = $invest_hosp + $pay;
											}
											if ($serv_group == 'Nursing Services') {
												$nursing_hosp = $nursing_hosp + $pay;
											}
											if ($serv_group == 'Nursing Consumable') {
												$nur_com_hosp = $nur_com_hosp + $pay;
											}
											if ($serv_group == 'Pharmacy') {
												$invest_pharm = $invest_pharm + $pay;
											}
											if ($serv_group == 'Bed Space/Accommodation') {
												$Accommodation = $Accommodation + $pay;
											}
										}


										if ($roww['claim_amt'] == 0 and $roww['pay'] > 0 and $roww['serv_group'] != 'EX' and $roww['paystatus'] == 1) {
											$pvt = $pvt + $roww['pay'];
										}
										if ($roww['claim_amt'] > 0 and $roww['pay'] == 0 and $roww['paystatus'] == 1) {
											$claim = $claim + $roww['claim_amt'];
										}
										if (($roww['drug_status'] == 1 or $roww['cr'] == 1) and $roww['paystatus'] == 0) {
											$pay_credit = $pay_credit + $roww['pay'];
										}


										if (($roww['drug_status'] == 1 or $roww['cr'] == 1) and $roww['paystatus'] == 0) {
											$pay_credit = $pay_credit + $roww['pay'];
										}
									}

									$Tother_hosp = $Tother_hosp + $other_hosp;
									$Tmedical_hosp = $Tmedical_hosp + $medical_hosp;
									$Tinvest_hosp = $Tinvest_hosp + $invest_hosp;
									$Tnursing_hosp = $Tnursing_hosp + $nursing_hosp;
									$Tnur_com_hosp = $Tnur_com_hosp + $nur_com_hosp;
									$Tinvest_pharm = $Tinvest_pharm + $invest_pharm;
									$TAccommodation = $TAccommodation + $Accommodation;								?>

									<tr class="record">
										<td style="border-bottom: 1px solid #ddd;"><?php echo $hospital_no; ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?php echo $row['room_bed'];
																					echo '<br><strong>Room Charge: </strong><br>' . number_format($Accommodation, 2);
																					?></td>
										<td style="border-bottom: 1px solid #ddd;">
											<?php
											echo '<strong>Doctor:</strong>' . $row['doc_incharge'] . '<br>';
											echo '<strong>Reason:</strong><br>' . $row['reason_adm'] . '<br>';
											echo '<strong>Condition:</strong><br>' . $row['cond'] . '<br>';
											echo '<strong>Discharge Note:</strong><br>' . $row['discharge_note'] . '<br>';
											echo '<strong>Discharge Status:</strong><br>' . $row['discharge_status'];
											?>
										</td>

										<td style="border-bottom: 1px solid #ddd;"><?php
																					echo '<strong>Nur. Con: </strong>' . number_format($nur_com_hosp, 2) . '<br>';
																					echo '<strong>Nur. Serv.: </strong>' . number_format($nursing_hosp, 2) . '<br>';
																					echo '<strong>Pharm: </strong>' . number_format($invest_pharm, 2) . '<br>';
																					echo '<strong>Invest: </strong>' . number_format($invest_hosp, 2) . '<br>';
																					echo '<strong>Med.Serv: </strong>' . number_format($medical_hosp, 2) . '<br>';
																					echo '<strong>App/Other(s): </strong>' . number_format($other_hosp, 2);

																					?></td>
										<td style="border-bottom: 1px solid #ddd;"><?php echo  number_format($claim, 2); ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?php echo  number_format($pvt, 2); ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?php echo  number_format($pay_credit, 2); ?></td>
										<td style="border-bottom: 1px solid #ddd;"><?php echo  $dates_; ?></td>
									</tr>
								<?php }  ?>

								<tr class="record">
									<td style="border-bottom: 1px solid #ddd;">&nbsp;</td>
									<td style="border-bottom: 1px solid #ddd;">&nbsp;</td>
									<td style="border-bottom: 1px solid #ddd;"><strong>Total: <?php //echo $stt->rowCount(); 
																								?></strong></td>
									<td colspan="5" style="border-bottom: 1px solid #ddd;"><strong><?php

																									echo '<strong>Nursing Consumable(s): </strong>' . number_format($Tnur_com_hosp, 2) . '<br>';
																									echo '<strong>Nursing Services(s): </strong>' . number_format($Tnursing_hosp, 2) . '<br>';
																									echo '<strong>Pharmacy: </strong>' . number_format($Tinvest_pharm, 2) . '<br>';
																									echo '<strong>Investigation(s): </strong>' . number_format($Tinvest_hosp, 2) . '<br>';
																									echo '<strong>Medical Services(s): </strong>' . number_format($Tmedical_hosp, 2) . '<br>';
																									echo '<strong>Consultation/Other(s): </strong>' . number_format($Tother_hosp, 2);
																									?></strong></td>
								</tr>
							</tbody>
						</table>

						<br><br>
						<div style="font-size:14px"><strong><i>GENERATED BY</i>:</strong></div>
						<strong style="font-size:14px;"><?php echo $staff_name; ?></strong>
				<?php
					} else {
						echo '<strong style="font-size:14px;">No Data Found</strong>';
					}


					$Tother_hosp = $Tother_hosp + $other_hosp;
					$Tmedical_hosp = $Tmedical_hosp + $medical_hosp;
					$Tinvest_hosp = $Tinvest_hosp + $invest_hosp;
					$Tnursing_hosp = $Tnursing_hosp + $nursing_hosp;
					$Tnur_com_hosp = $Tnur_com_hosp + $nur_com_hosp;
					$Tinvest_pharm = $Tinvest_pharm + $invest_pharm;
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


				<form method="post" action="adm_csv.php" target="_blank" style="margin-top:10px;">
					<input type="hidden" name="admission_type" value="<?php echo $admission_type; ?>">
					<input type="hidden" name="room" value="<?php echo $room; ?>">
					<input type="hidden" name="start" value="<?php echo $start; ?>">
					<input type="hidden" name="to" value="<?php echo $to; ?>">

					<button type="submit" style="padding:6px 12px; cursor:pointer;">
						Export to CSV
					</button>
				</form>



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