   <?php

	$total_chr = null;
	$total_dsc = null;
	$paymethod = null;

	if (!isset($_GET['chk'])) {
	}

	$from_date = '';
	$to_date = '';
	$checked_me = 'checked';
	$disbled = null;

	if (isset($_GET['cnldd'])) {
		$param = $_GET['cnldd'];
	} elseif (isset($_GET['chk'])) {
		$param = $_GET['chk'];
	} elseif (isset($_GET['uchk'])) {
		$param = $_GET['uchk'];
		$checked_me = '';
	}

	if (isset($param)) {
		list($from_date, $to_date) = explode("/", $param);
	}


	if (isset($_POST['apply_pay'])) {
		$from_date = $_POST['start'];
		$to_date = $_POST['end'];
	} elseif ($from_date != '' and $to_date != '') {
	} else {

		$todate = date("Y-m-d");
		$from_date = $to_date = $todate;
		$adm_count = 0;

		$check_adm = $db->prepare("
			SELECT date_admit 
			FROM admission 
			WHERE hospital_no = :emr 
			  AND (adm_status = '3' OR DATE(date_discharge) = :todate)
			ORDER BY date_admit DESC 
			LIMIT 1
		");
		$check_adm->bindParam(':emr', $emr);
		$check_adm->bindParam(':todate', $todate);
		$check_adm->execute();

		if ($check_adm->rowCount() > 0) {
			$adm_count = $check_adm->rowCount();
			$rowx = $check_adm->fetch(PDO::FETCH_ASSOC);
			$from_date = date('Y-m-d', strtotime($rowx['date_admit']));
		}
	}
	?>

   <div class="ibox-title">
   	<h5>Welcome to <u><?php echo $patient_name; ?></u> Account!</h5>
   </div>
   <div class="ibox-content">
   	<div class="row">

   		<form action="pacct.php" method="POST" id="subjects" name="subjects" enctype="multipart/form-data">

   			<div class="col-md-4">
   				<div class="ibox float-e-margins">
   					<label for="reg_input_no" class="">Set Dates Range & Click Apply button </label><br>
   					<div class="form_sep" id="">
   						<div class="input-daterange input-group" id="">
   							<input type="date" class="input-sm form-control" name="start" value="<?php echo $from_date; ?>" />
   							<span class="input-group-addon">to</span>
   							<input type="date" class="input-sm form-control" name="end" value="<?php echo $to_date; ?>" />
   						</div>

   					</div>
   				</div>
   			</div>

   			<div class="col-md-4">
   				<div class="ibox float-e-margins">
   					<div class="form_sep">
   						<label class="" style="color: brown;"><i>FILTER PAYMENT OPTIONS</i></label>
   						<select name="paymethod" id="paymethod" class="input-sm form-control">
   							<option value='all' <?php echo (!isset($_POST['paymethod']) || $_POST['paymethod'] == 'all') ? 'selected' : ''; ?>>Select...</option>
   							<option value="all" <?php echo (isset($_POST['paymethod']) && $_POST['paymethod'] == 'all') ? 'selected' : ''; ?>>[Pending] All Payments</option>
   							<option value="invoice" <?php echo (isset($_POST['paymethod']) && $_POST['paymethod'] == 'invoice') ? 'selected' : ''; ?>>[Pending] Invoiced Only</option>
   							<option value="cancel" <?php echo (isset($_POST['paymethod']) && $_POST['paymethod'] == 'cancel') ? 'selected' : ''; ?>>[Pending] View All Cancelled Invoiced</option>
   							<option value="credit" <?php echo (isset($_POST['paymethod']) && $_POST['paymethod'] == 'credit') ? 'selected' : ''; ?>>[Pending] All Credit Payments</option>
   							<option value="debt" <?php echo (isset($_POST['paymethod']) && $_POST['paymethod'] == 'debt') ? 'selected' : ''; ?>>[Pending] Debts</option>
   							<option value="accomodation" <?php echo (isset($_POST['paymethod']) && $_POST['paymethod'] == 'accomodation') ? 'selected' : ''; ?>>[Pending] Accomodation</option>

   							<?php if ($_SESSION['lab'] == '1') { ?>
   								<option value="invest" <?php echo (isset($_POST['paymethod']) && $_POST['paymethod'] == 'invest') ? 'selected' : ''; ?>>[Pending] Investigation bills</option>
   							<?php } ?>
   							<?php if ($_SESSION['pharm'] == '1') { ?>
   								<option value="pharm" <?php echo (isset($_POST['paymethod']) && $_POST['paymethod'] == 'pharm') ? 'selected' : ''; ?>>[Pending] Pharmacy bills</option>
   							<?php } ?>
   							<?php if ($_SESSION['nursing'] == '1') { ?>
   								<option value="nursing" <?php echo (isset($_POST['paymethod']) && $_POST['paymethod'] == 'nursing') ? 'selected' : ''; ?>>[Pending] Nursing Station bills</option>
   							<?php } ?>
   							<?php if ($_SESSION['other_bill'] == '1') { ?>
   								<option value="other" <?php echo (isset($_POST['paymethod']) && $_POST['paymethod'] == 'other') ? 'selected' : ''; ?>>[Pending] Other Payments</option>
   							<?php } ?>

   							<?php if ($_SESSION['reciept'] == '1') { ?>
   								<option value="reprint" <?php echo (isset($_POST['paymethod']) && $_POST['paymethod'] == 'reprint') ? 'selected' : ''; ?>>Re-print and Old Payments</option>
   							<?php } ?>
   						</select>
   					</div>
   				</div>
   			</div>


   			<div class="col-md-1">
   				<div class="ibox float-e-margins">
   					<div class="form_sep">
   						<label for="reg_input_no" class="">.</label><br>
   						<button class="btn btn-primary btn btn-sm" type="submit" name="apply_pay"> <i class="fa fa-check"></i> Apply</button>
   					</div>
   				</div>
   			</div>


   			<div class="col-md-1">
   				<div class="ibox float-e-margins">
   					<div class="form_sep">
   						<label for="reg_input_no" class="">.</label><br>

   						<a href="pacct.php?emr=<?php echo $emr; ?>&pay&all" class="btn btn-info btn btn-sm"> <i class="fa fa-times"></i> &nbsp;All Inv.</a>
   					</div>
   				</div>
   			</div>


   			<div class="col-md-1">
   				<div class="ibox float-e-margins">
   					<div class="form_sep">
   						<label for="reg_input_no" class="">.</label><br>
   						<a href="pacct.php?emr=<?php echo $emr; ?>&pay" class="btn btn-success btn btn-sm"> <i class="fa fa-refresh"></i> &nbsp;Refresh</a>
   					</div>
   				</div>
   			</div>




   			<div class="col-md-1">
   				<div class="ibox float-e-margins">
   					<div class="form_sep">
   						<label for="reg_input_no" class="">.</label><br>

   						<a href="pacct.php?emr=<?php echo $emr; ?>" class="btn btn-danger btn btn-sm"> <i class="fa fa-times"></i> &nbsp;Close</a>
   					</div>
   				</div>
   			</div>





   			<div class="col-md-12">
   				<?php
					// date(date_entry) between '$from_date' and '$to_date'
					$n = 1;
					$s = 1;
					$claim_set = 0;
					$total_claim = 0;
					$invoice_set = 0;
					$pay_access = '';
					$and = '';

					if ($_SESSION['lab'] == '1') {
						$pay_access = "serv_group='Radiology' or serv_group='Laboratory' or ";
					}
					if ($_SESSION['pharm'] == '1') {
						// if($pay_access!=''){$and='and';}
						$pharm = "serv_group='Pharmacy' or ";
						$pay_access .= $pharm;
					}
					if ($_SESSION['nursing'] == '1') {
						//  if($pay_access!=''){$and='and';}
						$Nursing = "serv_group='Nursing Services' or cat_type='Nursing Consumable' or ";
						$pay_access .= $Nursing;
					}

					if ($_SESSION['other_bill'] == '1') {
						//if($pay_access!=''){$and='and';}
						//$other_bill="(cat_type!='Nursing' or cat_type!='medication' or cat_type!='Radiology' or cat_type!='Laboratory') and ";
						// $pay_access.=$other_bill;
					}

					$search_datee = "AND DATE(date_entry) BETWEEN '$from_date' AND '$to_date'";
					$search = "";

					if (isset($_POST['apply_pay']) || (isset($_GET['psel']))) {
						$print = 0;
						$paymethod = 'all';

						if (!empty($_POST['paymethod'])) {
							$paymethod = $_POST['paymethod'];
						} elseif (!empty($_GET['psel'])) {
							$paymethod = $_GET['psel'];
						}

						$base = "hospital_no='$emr' AND paystatus=0 AND pay>0 AND invoice_status=1";

						switch ($paymethod) {
							case 'all':
								$base   = "hospital_no='$emr' AND paystatus=0 AND pay>0 AND (med_dosage_unit=0 OR med_dosage_unit IS NULL OR med_dosage_unit='') ";
								$search = "$base $search_datee";
								break;

							case 'invoice':
								// Using IS NULL to handle null values properly
								$base   = "hospital_no='$emr' AND paystatus=0 AND pay>0 
					AND (med_dosage_unit=0 OR med_dosage_unit IS NULL OR med_dosage_unit='') 
					AND invoice_status=1";
								$search = "$base $search_datee";
								break;

							case 'cancel':
								$base   = "hospital_no='$emr' AND paystatus=0 AND pay>0 AND med_dosage_unit=1";
								$search = "$base $search_datee";
								break;

							case 'credit':
								$search = "$base AND cr=1";
								break;

							case 'debt':
								$search = "$base AND cr=2";
								break;

							case 'accomodation':
								$search = "$base AND cat_type='Bed Space/Accommodation' $search_datee";
								break;

							case 'invest':
								$search = "$base AND serv_group IN ('Radiology', 'Laboratory') $search_datee";
								break;

							case 'pharm':
								$search = "$base AND serv_group='Pharmacy' $search_datee";
								break;

							case 'nursing':
								$search = "$base AND (cat_type='Nursing Consumable' OR serv_group='Nursing Services') $search_datee";
								break;

							case 'other':
								$search = "$base AND (
						serv_group IN ('Other Services', 'Consultation') 
						OR cat_type='Medical Services'
					) $search_datee";
								break;

							case 'reprint':
								$search = "hospital_no='$emr' 
					AND paystatus=1 
					AND pay>0 
					AND transact_date BETWEEN '$from_date' AND '$to_date'";
								$print  = 1;
								break;
						}
					} elseif (isset($_GET['all'])) {
						$search = "hospital_no='$emr' AND paystatus=0 AND pay>0 AND (med_dosage_unit=0 OR med_dosage_unit IS NULL OR med_dosage_unit='')";
					} else {
						$search = "
						hospital_no='$emr' 
						AND paystatus=0 
						AND (med_dosage_unit=0 OR med_dosage_unit IS NULL OR med_dosage_unit='') 
						AND pay>0 
						AND cr IN (0,1,2) 
						AND invoice_status=1 
						$search_datee";
					}

					////echo '===========' . $search;

					$staff_discount = null;
					if ($discount_set == 1 && $patient_type == "Private(Self)" or $patient_type == "Family") {
						$stmtcv = $db->query("SELECT 1 FROM hremp WHERE EmployeeCode='$nhis_no' LIMIT 1");
						$staff_discount = $stmtcv->rowCount() > 0 ? 1 : null;
					}

					$stmt = $db->query("SELECT * FROM patient_ap_services WHERE $search order by invoice_status,paystatus,date_entry,cat_type");
					if ($stmt->rowCount() > 0) {

						include("../inc/utilities.php");
						patient_discount(
							$db,
							$staff_discount,
							$emr,
							$patient_type,
							$discount_set,
							$referral_sn,
							$insurance_no,
							$pf,
							$pf_value,
							$dsc_chr,
							$dura,
							$service_type,
							$dsc_chr_set,
							$post_type,
							$count_bal,
							$dsc_chr_set,
							$mySearch,
							$grp_idv,
							$grp_idv_no
						);

					?>
   					<input type="hidden" id="paymethod_selected" value="<?= $paymethod; ?>">
   					<table class="table table-striped" width="100%">
   						<tr>
   							<th width="3%"><input type="checkbox" id="unchecked_me" <?= $checked_me; ?> style="height: 15px; width: 15px; border: 2px solid #000;"></th>
   							<th width="28%">Item (Check/UnCheck)</th>
   							<th width="6%">Price</th>
   							<th width="2%">Qty</th>
   							<th width="6%">Amount</th>
   							<th width="12%">DSC/CHR</th>
   							<th width="10%">.</th>
   							<th width="12%">Date</th>
   							<th width="21%">.</th>
   						</tr>
   						<?php

							$total_inv = 0;
							$total_cr = 0;
							$total_dsc = 0;
							$total_chr = 0;
							$cnlc_no = 0;
							$Uncnlc_no = 0;
							while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

								$pay = $row['pay'];
								$snn = $row['sn'];
								$cat_type = $row['cat_type'];

								if ($row['cr'] == 1) {
									$total_cr += $pay;
								}


								$disbled = '';

								if ($row['remarks'] == 'auto_deduct' && $row['invoice_status'] == 0) {
									$disbled = "";
									$checked_me = 'checked';
								} elseif ($row['serv_group'] == 'Pharmacy' && $row['invoice_status'] == 0) {
									$disbled = "disabled";
									$checked_me = '';
								} else {
									if (isset($_GET['chk']) or !isset($_GET['uchk'])) {
										$checked_me = 'checked';
									}
								}

								// Auto-generate invoice
								if ($disbled == '' && $row['invoice_status'] == 0) {
									$invoice_no = ($row['invoice_no'] == '')
										? $row['app_no'] . mt_rand(100, 999)
										: $row['invoice_no'];

									$invoice_date = date('Y-m-d H:i:s');
									$invoice_by = $_SESSION['fullname'];

									$inv = "UPDATE patient_ap_services 
										SET invoice_status=1, 
											invoice_no='$invoice_no', 
											invoice_date='$invoice_date', 
											invoice_by='$invoice_by' 
										WHERE sn='$snn'";

									$db->exec($inv);
								}


								$err = '';


								$serv_group = strtoupper(trim($row['serv_group']));
								$cat_type = strtoupper(trim($row['cat_type']));
								$drug_sn = $row['drug_sn'];
								$date_entry = date("Y-m-d", strtotime($row["date_entry"]));

								if ($serv_group === 'RADIOLOGY' || $serv_group === 'LABORATORY') {
									$stmt2 = $db->prepare("SELECT sn FROM lab_manage WHERE labrequest_no = :labrequest_no LIMIT 1");
									$stmt2->bindParam(':labrequest_no', $drug_sn);
									$stmt2->execute();

									if (!$stmt2->fetchColumn()) {
										$disbled = "disabled";
										$err = "<br><strong style='color: red;'>[Request Error]</strong>";
									}
								} elseif ($cat_type === 'DIALYSIS') {
									//$hosp_price = $row['hosp_price'];
									//$stmt2 = $db->prepare("SELECT id FROM dialysis WHERE amount = :amount AND DATE(request_date) = :entry_date LIMIT 1");
									//$stmt2->bindParam(':amount', $hosp_price);
									//$stmt2->bindParam(':entry_date', $date_entry);
									//$stmt2->execute();

									// Uncomment this if you want to validate presence of dialysis record
									/*
									if ($stmt2->rowCount() === 0) {
										$disbled = "disabled";
										$err = "<br><strong style='color: red;'>[Request Error]</strong>";
									}
									*/
								} elseif ($serv_group === 'MEDICAL SERVICES') {
									// Uncomment if you need to check for medical service procedure request
									/*
									$stmt2 = $db->prepare("SELECT sn FROM procedures WHERE service_id = :service_id AND DATE(date_entry) = :entry_date LIMIT 1");
									$stmt2->bindParam(':service_id', $drug_sn);
									$stmt2->bindParam(':entry_date', $date_entry);
									$stmt2->execute();
								
									if ($stmt2->rowCount() === 0) {
										$disbled = "disabled";
										$err = "<br><strong style='color: red;'>[Request Error]</strong>";
									}
									*/
								} else {
									$err = '';
								}


								$discount = null;
								$charge = null;

								if ($discount_set == 1) {

									if (strtoupper($service_type) == 'SPECIFY') {

										$item_services = $row['item_services'];
										if ($patient_type === 'Family' || $patient_type === 'Corporate') {
											$mySearch = "individual_group_no='$insurance_no'";
										} else {
											$group_no = ($staff_discount == 1) ? 'staff' : $emr;
											$mySearch = "individual_group_no='$group_no'";
										}

										$serv_group = strtoupper($row['serv_group']);

										switch (true) {
											case ($serv_group === 'PHARMACY'):
												$item_services_2 = "all_Pharmacy";
												break;
											case ($serv_group === 'NURSING SERVICES'):
												$item_services_2 = "all_Nursing Services";
												break;
											case (in_array($row['serv_group'], ['Radiology', 'Laboratory'])):
												$item_services_2 = "all_investigation";
												break;
											case ($serv_group === 'MEDICAL SERVICES'):
												$item_services_2 = "all_Medical Services";
												break;
											case (!in_array($serv_group, ['PHARMACY', 'NURSING SERVICES', 'MEDICAL SERVICES']) &&
												!in_array($row['serv_group'], ['Radiology', 'Laboratory'])):
												$item_services_2 = "all_others";
												break;
											default:
												$item_services_2 = $item_services;
												break;
										}



										$query = "SELECT 1 FROM patient_discount_services WHERE $mySearch AND service_item = :service_item AND status = '0' LIMIT 1";
										$stmt_dsc2 = $db->prepare($query);
										$stmt_dsc2->bindParam(':service_item', $item_services);
										$stmt_dsc2->execute();

										if ($stmt_dsc2->fetchColumn()) {
											$item_services_2 = $item_services;
										}


										include_once("../inc/utilities.php");
										selected_items($db, $emr, $item_services_2, $mySearch, $pf, $pf_value, $dsc_chr, $dura, $post_type, $count_bal, $dsc_chr_set, $sn_service);
									}
									///echo $pay;
									include_once("../inc/utilities.php");    /// 
									//	if($cat_type == $service_type or $service_type == 'All Services'){
									dsc_chr_calc($pf, $dsc_chr_set, $dsc_chr, $cat_type, $service_type, $pay, $pf_value, $discount, $charge, $total_chr, $total_dsc, $post_type, $count_bal);
									///}

								}

							?>

   							<tr>
   								<td width="3%">
   									<?php
										$replacementCharacter = "-";
										$item_services = str_replace(",", $replacementCharacter, $row['item_services']);

										$checkboxValue = implode('__', [
											$row['sn'],
											$pay,
											$item_services,
											$row['qty'],
											$row['cr'],
											$discount,
											$charge,
											$total_chr,
											$total_dsc,
											$dura,
											$post_type,
											$count_bal,
											$dsc_chr_set,
											$grp_idv_no,
											$row['cat_type'],
											$row['serv_group'],
											$row['drug_sn'],
											$sn_service,
											strtoupper($service_type)
										]);
										?>
   									<input type="checkbox"
   										value="<?= htmlspecialchars($checkboxValue, ENT_QUOTES) ?>"
   										name="inv[]"
   										id="add_m_<?= $n ?>"
   										onclick="UpdateCost()"
   										<?= $checked_me ?>
   										<?= $disbled ?>
   										style="height: 15px; width: 15px;" />
   								</td>

   								<td width="28%"><?php echo $s . ' - ' . $row['item_services'] . $err; ?>

   									<?php if ($err != '' and $row['serv_group'] == 'Investigation') { ?>
   										<a href="pacct.php?emr=<?= $emr; ?>&pay&clear=<?= $labrequest_no; ?>">Clear Error</a>
   									<?php }  ?>

   								</td>
   								<td width="6%" align=""><?php echo number_format($row['hosp_price'], 2); ?></td>
   								<td width="2%"><?php echo $row['qty']; ?></td>
   								<td width="6%" align=""><?php echo number_format($pay, 2); ?>


   								</td>
   								<td width="12%"><?php


													$showCharge = ($print == 0) ? $charge : $row['add_charge'];
													$showDiscount = ($print == 0) ? $discount : $row['discount'];

													if ($showCharge > 0) {
														echo 'CHR: ' . number_format($showCharge, 2);
													}

													if ($showDiscount > 0) {
														echo 'DSC: ' . number_format($showDiscount, 2);
													}

													?></td>
   								<td width="10%">
   									<?php
										if ($row['cr'] == 2 and $row['paystatus'] == '0') { ?>
   										<strong style="color:#F00">Debt</strong>
   									<?php } elseif ($row['drug_status'] == 1 and $row['paystatus'] == '0') { ?>
   										<strong style="color:#F00">Delivered</strong>
   									<?php } elseif ($row['cr'] == 1 and $row['paystatus'] == '0') { ?>
   										<strong style="color:#F00">Credit</strong>
   									<?php } elseif ($row['invoice_status'] == '0' && $row['serv_group'] == 'Pharmacy') {
											echo '<b style="color:blue;">Pharm Inv.</b>';
										} elseif ($row['invoice_status'] == '1' && $row['med_dosage_unit'] == 1) {
											echo '<b style="color:red;">Cancel Inv.</b>';
										} elseif ($row['invoice_status'] == '1' && $row['paystatus'] == '0') {
											echo '<b>Invoiced</b>';
										} elseif ($row['invoice_status'] == '0') {
											echo 'Pending Inv.';
										} elseif ($row['paystatus'] == '1') {
											echo 'Paid';
										}
										?>
   								</td>
   								<td width="15%"><?php echo  date('d M,y', strtotime($row['date_entry'])) . ' ' .  date('h:ia', strtotime($row['date_entry'])); ?></td>
   								<td width="18%"><input type="button" name="edit" value="Notes" data-target="#modal" id="<?php echo $row["sn"] . '__' . $row['item_services'] . '__' . $patient_type . '__' . $referral_name . '__' . $insurance_no . '__' . $discount_set; ?>" class="btn btn-success btn-xs view_notes" />

   									<?php if ($_SESSION['edit_price_at_point'] == 1 && $print == 0) { ?>
   										<input type="button" name="edit_price" value="Edit" data-target="#modal" id="<?php echo $row["sn"] . '__pay'; ?>" class="btn btn-warning btn-xs edit_price_entry" />
   									<?php } ?>

   									<?php if ($row['serv_group'] == 'Medical Services' and $_SESSION['allow_part_pay_medical_service'] == 1 and $pay > 0) { ?>
   										<input type="button" name="part_pay" value="Part" data-target="#modal" id="<?php echo $row["sn"]; ?>" class="btn btn-info btn-xs part_payment_entry" />
   									<?php } ?>

   									<?php if (
											$row['serv_group'] != 'Pharmacy' && $row['cr'] == 0 && $row['drug_status'] == 0 && $row['med_dosage_unit'] != 1 && $print == 0
										) {
											$cnlc_no++; ?>
   										&nbsp;|&nbsp; <a href="pacct.php?emr=<?= $emr ?>&pay&cnl=<?= $row['sn'] ?>&cnldd=<?= "$from_date/$to_date" ?>"
   											class="btn btn-warning btn-xs" onclick="return confirm('Do you want to un-invoice?');"> Cancel</a>
   										<?php } elseif ($row['cr'] == 1 && $row['med_dosage_unit'] != 1 && $print == 0) {
											$cnlc_no++;
											$sn_q = base64_encode(base64_encode(base64_encode($row['sn'])));

											if ($row['serv_group'] == 'Nursing Services' && $insurance_type != 'EX') { ?>
   											&nbsp;|&nbsp; <a href="pacct.php?emr=<?= $emr ?>&pay&cnlcr=<?= $sn_q; ?>
											&cnldd=<?= "$from_date/$to_date" ?>
											&psel=<?= $paymethod; ?>"
   												class="btn btn-danger btn-xs" onclick="return confirm('Do you want to cancel credit invoice?');"> Cancel</a>
   										<?php }
										}
										if ($row['med_dosage_unit'] == 1 && $paymethod == 'cancel') {
											$Uncnlc_no++; ?>
   										&nbsp;|&nbsp; <a href="pacct.php?emr=<?= $emr ?>&pay&uncl=<?= $row['sn']; ?>&cnldd=<?= "$from_date/$to_date" ?>&psel=<?= $paymethod; ?>" class="btn btn-danger btn-xs"> Un-Cancel</a>
   									<?php } ?>
   								</td>
   							</tr>
   						<?php $n += 1;
								$s += 1;
							} ?>
   					</table>
   				<?php  } else { ?>
   					<div class="alert alert-warning">
   						No records found for the selected date range
   						(<?php echo date("d M, Y", strtotime($from_date)); ?> to <?php echo date("d M, Y", strtotime($to_date)); ?>).
   						Please adjust the date range and click Apply.
   					</div>
   				<?php }

					if ($stmt->rowCount() > 0 and $print == 0) { ?>

   					<div class="row">
   						<div class="col-md-2">
   							<h4 align="right" class="no-margins">Totals:</h4>
   							<small> </small>
   						</div>
   						<div class="col-md-2">
   							<h3 class="no-margins"><?php echo number_format($total_inv, 2); ?></h3>
   							<small>T/Invoice</small>
   						</div>
   						<div class="col-md-2">
   							<h3 class="no-margins"><?php echo number_format($total_claim, 2); ?></h3>
   							<small>T/Claim</small>
   						</div>

   						<div class="col-md-2">
   							<h3 class="no-margins"><?php echo number_format($total_dsc, 2); ?></h3>
   							<small>T/Discount(DSC)</small>
   						</div>

   						<div class="col-md-2">
   							<h3 class="no-margins"><?php echo number_format($total_chr, 2); ?></h3>
   							<small>T/Charges(CHR)</small>
   						</div>

   						<div class="col-md-2">
   							<h3 class="no-margins"><?php echo number_format($total_cr, 2); ?></h3>
   							<small>T/Credits</small>
   						</div>

   					</div>


   					<?php if (isset($_GET['sel'])) { ?>
   						<strong style="color:#F00">Select item(s) you wish to pay ... </strong>
   					<?php } ?>



   					<hr>
   					<div style="display: flex; align-items: center; justify-content: flex-end; gap: 10px;">
   						<span style="font-size: 20px; ;">Total Amount Paying:</span>
   						<input type="text" name="totalcost" id="totalcost" class="input-sm form-control"
   							readonly="readonly"
   							style="width:150px; font-size:20px; outline:none; background:none; background-color:transparent; border:0px solid;">
   					</div>
   					<div class="align-right">

   						<table width="100%">
   							<tr>
   								<td width="1%"><strong style="color:#C03">Auth.Code</strong></td>
   								<td width="13%">
   									<input type="text" id="discount_vourcher" name="discount_vourcher" class="input-sm form-control" maxlength="12" placeholder="Discount Voucher Only">
   									<?php if ($paymethod == 'debt') { ?>
   										<input type="hidden" id="debt_post" name="debt_post" value="debt">
   									<?php } else { ?>
   										<input type="hidden" id="debt_post" name="debt_post" value="">
   									<?php } ?>
   									<input type="hidden" id="discount_set" name="discount_set" value="<?= $discount_set; ?>">
   									<input type="hidden" id="discount_insurance_no" name="discount_insurance_no" value="<?= $insurance_no; ?>">
   									<input type="hidden" id="dsc_chr_type" name="dsc_chr_type" value="<?= $dsc_chr; ?>">
   								</td>
   								<td width="10%">
   									&nbsp;
   								</td>
   								<?php if ($cnlc_no > 1) { ?>
   									<td width="10%">
   										<button class="btn btn-danger btn-sm" type="submit" name="cancel_invoice_only" id="cancel_invoice_only">Cancel Invoice</button>
   									</td>
   								<?php }

									if ($Uncnlc_no > 1 && $paymethod == 'cancel') {  ?>
   									<td width="10%">
   										<button class="btn btn-danger btn-sm" type="submit" name="un_cancel_invoice_only" id="un_cancel_invoice_only">Un-Cancel Invoice</button>
   									</td>
   								<?php } ?>


   								<?php if ($stmt->rowCount() <= 300) { ?>
   									<td width="10%">
   										<button class="btn btn-info btn-sm" type="submit" name="print_invoice_only" id="print_invoice_only">Print Invoice</button>
   									</td>
   								<?php } ?>

   								<?php if ($adm_count > 0 && $stmt->rowCount() > 0) { ?>
   									<td width="10%">
   										<input type="button" name="" value="Admission Invoice" data-target="#modal" id="12x" class="btn btn-success btn-sm adm_invoice" />
   									</td>
   								<?php } ?>


   								<td width="30%">
   									<button class="btn btn-success btn-sm" type="submit" name="process_inv" id="process_inv">Pay Now</button>
   								</td>
   							</tr>
   						</table>
   					</div>

   				<?php }
					if ($print == 1) { ?>
   					<div class="pull-left">
   						<button class="btn btn-info btn-sm" type="submit" name="print_recep" id="print_recep"><i class="fa fa-print"></i> &nbsp; Print Reciept</button>
   					</div>
   					<input type="hidden" value="print" name="printing">
   				<?php } ?>


   				<input type="hidden" value="pay" name="i_come_from">
   				<input type="hidden" value="<?php echo $emr; ?>" name="emr" id="emr">
   				<input type="hidden" value="<?php echo $patient_name; ?>" name="patient_name2">
   				<input type="hidden" value="<?php echo $search_datee; ?>" name="search_datee">
   		</form>

   		<?php if ($print == 0 && $stmt->rowCount() > 300) { ?>
   			<div style="display: flex; align-items: center; justify-content: flex-end; gap: 10px;">
   				<form action="pacct.php" method="POST" id="subjects" name="subjects" enctype="multipart/form-data">
   					<button class="btn btn-info btn-sm" type="submit" name="print_invoice_adv" id="print_invoice_adv">Print Invoice (Advance)</button>
   					<input type="hidden" value="<?php echo $emr; ?>" name="emr" id="emr">
   					<input type="hidden" value="<?php echo $patient_name; ?>" name="patient_name2">
   					<input type="hidden" value="<?php echo $search_datee; ?>" name="search_datee">
   				</form>
   			</div>
   		<?php } ?>
   	</div>






   	<?php $inv_count = $n; ?>






   	<script>
   		const checkbox = document.getElementById('unchecked_me');
   		checkbox.addEventListener('change', function() {
   			var emr = document.getElementById("emr").value;
   			var paymethod_selected = document.getElementById("paymethod_selected").value;

   			if (this.checked) {
   				<?php if (isset($_GET['all'])) { ?>
   					if (paymethod_selected == '') {
   						window.location.href = 'pacct.php?emr=' + emr + '&pay&chk&all';
   					} else {
   						window.location.href = 'pacct.php?emr=' + emr + '&pay&chk&all&psel=' + paymethod_selected;
   					}
   				<?php } else { ?>
   					window.location.href = 'pacct.php?emr=' + emr + '&pay&chk=<?php echo $from_date . '/' . $to_date; ?>&psel=' + paymethod_selected;
   				<?php } ?>
   			} else {

   				<?php if (isset($_GET['all'])) { ?>

   					if (paymethod_selected == '') {
   						window.location.href = 'pacct.php?emr=' + emr + '&pay&uchk&all';
   					} else {
   						window.location.href = 'pacct.php?emr=' + emr + '&pay&uchk&all&psel=' + paymethod_selected;
   					}
   				<?php } else { ?>
   					window.location.href = 'pacct.php?emr=' + emr + '&pay&uchk=<?php echo $from_date . '/' . $to_date; ?>&psel=' + paymethod_selected;
   				<?php } ?>
   			}
   		});
   	</script>