                                    <tr>
                                    	<td>

                                    		<?php
											if (isset($_GET['special_package']) and $special_pkg_count_status > 0) {

												$block_status = 1;
												if ($row['serv_group'] == 'Medical Services' or $row['serv_group'] == 'Registration' or $row['serv_group'] == 'Consultation' or $row['serv_group'] == 'Other Services') {
													if (in_array($row['drug_sn'], $medical_services)) {
														$block_status = 0;
													}
												} elseif ($row['serv_group'] == 'Pharmacy') {
													if (in_array($row['drug_sn'], $pharmacy)) {
														$block_status = 0;
													}
												} elseif ($row['serv_group'] == 'Nursing Services') {
													$block_status = 0;
												} elseif ($row['serv_group'] == 'Laboratory' or $row['serv_group'] == 'Radiology') {
													$drug_sn = $row['drug_sn'];
													$stmt_chk_adm = $db->query("SELECT test_id FROM lab_manage WHERE labrequest_no='$drug_sn'");
													$rwxV = $stmt_chk_adm->fetch(PDO::FETCH_ASSOC);

													if (in_array($rwxV['test_id'], $investigations)) {
														$block_status = 0;
													}
												} else {
													$block_status = 1;
												}
											} else {

												//// CLAIM MANAGERS ////
												$block_status = 0;
												$err = '';
												if ($row['serv_group'] === 'Laboratory' || $row['serv_group'] === 'Radiology') {
													$labrequest_no = $row['drug_sn'];

													$stmt2e = $db->prepare("SELECT sn,data_capture_status FROM lab_manage WHERE labrequest_no = :labrequest_no");
													$stmt2e->execute([':labrequest_no' => $labrequest_no]);

													if ($stmt2e->rowCount() === 0) {
														$block_status = 1;
														$err = "<br><strong style='color: red;'>[Request Error]</strong>";
													} else {
														$row_ = $stmt2e->fetch(PDO::FETCH_ASSOC);
														$data_capture_status = $row_['data_capture_status'];
													}
												} elseif (strtoupper($row['cat_type']) == 'DIALYSIS') {
													$created_by = $row['created_by'];
													$hosp_price = $row['hosp_price'];
													$date_entry = date("Y-m-d", strtotime($row["date_entry"]));
													/* $stmt2e=$db->query("SELECT id FROM dialysis 
										WHERE amount='$hosp_price' and created_by='$created_by' and date(request_date)='$date_entry'");
								if($stmt2e->rowCount()==0){
										$block_status =1;
										$err="<br><strong style='color: red;'>[Request Error]</strong>";
								}
								*/
												} elseif (strtoupper($row['serv_group']) == 'MEDICAL SERVICES') {
													$drug_sn = $row['drug_sn'];
													$created_by = $row['created_by'];
													$date_entry = date("Y-m-d", strtotime($row["date_entry"]));

													/*							$stmt2e=$db->query("SELECT sn FROM procedures 
								WHERE service_id='$drug_sn' and created_by='$created_by' and date(date_entry)='$date_entry'");
								if($stmt2e->rowCount()==0){
										$block_status =1;
										$err="<br><strong style='color: red;'>[Request Error]</strong>";
								}*/
												} else {
													$err = '';
												}
											}

											?>


                                    		<input type="checkbox" value="<?php echo $row['sn'] . '__' . $row['item_services'] . '__' . $row['pay'] . '__' . $row['claim_amt'] . '__' . $row['serv_group'] . '__' . $row['qty'] . '__' . $row['paystatus'] . '__' . $row['invoice_status'] . '__' . $row['transact_date'] . '__' . $row['hosp_price']; ?>" name="inv[]" <?php
																																																																																										if (($row['process_claim'] == '0' and $row['claim_amt'] > 0) or $row['paystatus'] == 1) { ?>checked <?php } ?>
                                    			<?php if ($block_status == 1) { ?>disabled<?php } ?>
                                    			class="checkbox i-checks" />

                                    	</td>

                                    	<td>


                                    		<?php


											echo $row['item_services'];
											if ($row['serv_group'] == 'Pharmacy' and $row['remarks'] != '') {
												echo '<br><strong style="color: blue"><i>PRESCRIPTION & QTY:</i></strong><br>' . $row['remarks'] .  ' / Qty: ' . '<strong>' . $row['qty'] . '</strong>';
												$phamarcy_desc .= '- ' . $row['item_services'] . '<br> <strong>' . $row['remarks'] . '</strong><br>';
											}


											if ($row['serv_group'] == 'Radiology' or $row['serv_group'] == 'Laboratory') {
												$investigation_desc .= '- ' . $row['item_services'] . '<br>';
											}
											echo '<br>';

											if ($row['serv_group'] === 'Pharmacy') {
												if ($row['invoice_status'] == 0 && $row['paystatus'] == 0) {
													echo '<strong style="color:#F00">Invoice Pending</strong>';
												} elseif ($row['invoice_status'] == 1 && $row['paystatus'] == 0) {
													echo '<strong style="color:green"><i>DRUG INVOICED BY </i></strong>' . ($row['invoice_by']);
												} elseif ($row['drug_status'] == 1 && $row['paystatus'] == 1) {
													echo '<strong style="color:#00F">Drug Dispensed to Patient.</strong><br>' . ($row['dsp_by']);
												} else {
													echo '<strong style="color:#F00">Dispense Pending</strong>';
												}
											} elseif ($row['serv_group'] === 'Laboratory' || $row['serv_group'] === 'Radiology') {

												if ($data_capture_status == 'specimen') {
													echo '<strong style="color:#00F">Specimen collected</strong>';
												} elseif ($data_capture_status == 'capture') {
													echo '<strong style="color:#00F">Image Captured/Scan</strong>';
												} elseif ($data_capture_status == 'result') {
													echo '<strong style="color:#00F">Investigation Done & Result Entered.</strong>';
												} elseif ($data_capture_status == 'approve') {
													echo '<strong style="color:green">Investigation Done & Approved.</strong>';
												} else {
													echo '<strong style="color:brown">On-Queue</strong>';
												}
											} elseif ($row['drug_status'] == 1) {
												// For all other service groups (not Pharmacy)
												echo '<strong style="color:#00F">Service Delivered/Documented</strong><br><strong>' . ($row['dsp_by']) . '</strong>';
											} else {
												echo '<strong style="color:#F00">Confirm Status</strong>';
											}


											///echo $err;

											if ($err != '' and ($row['serv_group'] == 'Radiology' or $row['serv_group'] == 'Laboratory')) { ?>
                                    			<a href="index.php?claims=<?= $hos_no; ?>&clear=<?= $labrequest_no; ?>">Clear Error</a>
                                    		<?php }  ?>
                                    	</td>
                                    	<?php $claim_set = 1;
										$amt_hmo = $amt_hmo + $row['claim_amt'];


										if ($row['pay'] > 0 && $row['claim_amt'] == 0) {

											// Direct pay without claim amount
											$unit_price = $row['pay'] / max(1, $row['qty']); // avoid division by zero
											echo '<td>' . number_format($unit_price, 2, '.', ',') . '</td>' .
												'<td>' . intval($row['qty']) . '</td>' .
												'<td>' . number_format($row['pay'], 2, '.', ',') . '</td>' .
												'<td style="color:blue">Self Pay</td>';
										} elseif ($row['pay'] > 0 && $row['claim_amt'] > 0) {
											// Calculate unit price based on pay amount (self pay)
											$unit_price = $row['pay'] / max(1, $row['qty']); // avoid division by zero
											echo '<td>' . number_format($unit_price, 2, '.', ',') . '</td>' .
												'<td>' . intval($row['qty']) . '</td>' .
												'<td>' . number_format($row['pay'], 2, '.', ',') . '</td>' .
												'<td style="color:red">Claim/Self Pay</td>';
										} elseif ($row['pay'] == 0 && $row['claim_amt'] == 0) {
											$unit_price = $row['pay'] / max(1, $row['qty']); // avoid division by zero
											echo '<td>' . number_format($unit_price, 2, '.', ',') . '</td>' .
												'<td>' . intval($row['qty']) . '</td>' .
												'<td>' . number_format($row['pay'], 2, '.', ',') . '</td>' .
												'<td style="color:blue">Free</td>';
										} else {
											// Calculate unit price based on claim amount (insurance)
											$unit_price = $row['claim_amt'] / max(1, $row['qty']); // avoid division by zero
											echo '<td>' . number_format($unit_price, 2, '.', ',') . '</td>' .
												'<td>' . intval($row['qty']) . '</td>' .
												'<td>' . number_format($row['claim_amt'], 2, '.', ',') . '</td>' .
												'<td style="color:red">Claim</td>';
										}

										//number_format($pay_amt,2,'.',','); 	
										?>

                                    	<td>
                                    		<?php
											$convert_status = 0;

											if ($row['pay'] > 0) {

												$convert_status = 1;

												if ($row['paystatus'] == 1) {
													$pay_amt += $row['pay'];
													echo '<strong style="color:#00F">Paid</strong>';
												} elseif (($row['cr'] == 1 || $row['drug_status'] == 1) && $row['paystatus'] == 0) {
													$total_cr_pay += $row['pay'];
													echo '<strong style="color:#F00">Delivered On-Credit/<br>Convert to Claim</strong>';
												} elseif ($row['invoice_status'] == 1 && $row['paystatus'] == 0) {
													$inv_processed_pay += $row['pay'];
													echo '<strong style="color:#F00">Invoiced</strong>';
												} elseif ($row['invoice_status'] == 0 && $row['paystatus'] == 0) {
													$inv_pending_pay += $row['pay'];
													echo '<strong style="">[ Pay Invoice Pending ]</strong>';
												}

												// Show validation or payment details
												if ($row['paystatus'] == 1 && $row['pay_mode'] === 'spkage') {
													echo '<strong style="color:#00F">/Validated By</strong><br><strong>' . ($row['claim_valid_by']) . '</strong>';
												} else {
													echo '<br><strong>Amt.: ' . number_format($row['pay']) . '</strong>';
													if (!empty($row['transact_date']) && $row['transact_date'] != '0000-00-00 00:00:00') {
														echo '<br>' . date("d-m-y h:i:s a", strtotime($row['transact_date'])) . '<br>';
													}

													echo '<br>' . $row['invoice_by'];
												}
											} else {
												if ($row['process_claim'] == 1) {
													$valid_amt += $row['claim_amt'];
													$pay_amt += $row['pay'];
													echo '<strong style="color:#00F">Validated By</strong><br><strong>' . ($row['claim_valid_by']) . '</strong>';
													$reverse_status = 1;

													if ($row['paystatus'] == 1 && $row['serv_group'] == 'Pharmacy') {
														echo '<br><strong style="color:#00F">Posted by Pharm</strong>';
													} elseif ($row['paystatus'] == 1 && ($row['serv_group'] == 'Laboratory' || $row['serv_group'] == 'Radiology')) {
														echo '<br><strong style="color:#00F">Posted by ' . $row['invoice_by'];

														if (!empty($row['invoice_date'])) {
															echo ' ' . date('d/m/Y', strtotime($row['invoice_date']));
														}

														echo '</strong>';
													} elseif ($row['paystatus'] == 1 && $row['serv_group'] != 'Pharmacy') {
														echo '<br><strong style="color:#00F">Posted by Validator</strong>';
													} else {
														echo '<br><strong style="color:#F00">Post Pending</strong>';
													}
												} elseif ($row['process_claim'] == 0) {
													if ($row['paystatus'] == 1) {
														echo '<strong style="color:red">Valid Pending</strong><br><strong style="color:#00F">Posted by ' . $row['invoice_by'];

														if (!empty($row['invoice_date'])) {
															echo ' ' . date('d/m/Y', strtotime($row['invoice_date']));
														}

														echo '</strong>';
													} else {
														echo '<strong style="color:#F00">Not Posted<br><strong style="color:#00F">Valid Pending</strong></strong>';
														$valid_pending = 1;
													}
													$total_cr_claim += $row['claim_amt'];
												}
											} ?>
                                    	</td>
                                    	<td><?php echo $row['prepared_by'] . '<br>' .
												date('d/m/y H:i:s a', strtotime($row['date_entry'])); ?></td>


                                    	<td>
                                    		<div class="btn-group">

                                    			<?php if (isset($_GET['special_package']) && $special_pkg_count_status > 0) : ?>

                                    				<?php if ($block_status == 0) : ?>
                                    					<button data-toggle="dropdown" class="btn btn-success btn-xs dropdown-toggle">
                                    						Action <span class="caret"></span>
                                    					</button>
                                    				<?php endif; ?>

                                    				<ul class="dropdown-menu">
                                    					<?php if ($row['paystatus'] == 1 && $row['drug_status'] == 0 && $row['pay'] > 0) : ?>
                                    						<li>
                                    							<a href="index.php?claims=<?= $hos_no ?>&A=<?= $row['sn'] ?>/<?= $from ?>/<?= $to ?>/dvl/<?= $from_where ?>&filter=<?= $filter . $pk ?>"
                                    								onclick="return confirm('Are you sure you want to delete?');">
                                    								[ DELETE VALIDATION ]
                                    							</a>
                                    						</li>
                                    					<?php endif; ?>

                                    					<?php if ($row['paystatus'] == 0 && $row['pay'] > 0 && $row['remarks'] != 'auto_deduct') : ?>
                                    						<li>
                                    							<a href="index.php?claims=<?= $hos_no ?>&A=<?= $row['sn'] ?>/<?= $from ?>/<?= $to ?>/vld/<?= $from_where ?>&filter=<?= $filter . $pk ?>">
                                    								<strong style="color: blue;">[ VALIDATE ]</strong>
                                    							</a>
                                    						</li>
                                    					<?php endif; ?>
                                    				</ul>

                                    			<?php elseif ($_SESSION['convert_patient_insur'] == 1) :
													$delete_valid_ph_status = 0;
													if (strtoupper($row['serv_group']) == 'PHARMACY' && $row['invoice_status'] == 1) {
														$delete_valid_ph_status = 1;
													}

												?>

                                    				<button data-toggle="dropdown" class="btn btn-success btn-xs dropdown-toggle">
                                    					Action <span class="caret"></span>
                                    				</button>
                                    				<ul class="dropdown-menu">
                                    					<?php if ($row['paystatus'] == 1 && $row['pay'] == 0 && $row['claim_amt'] > 0 && $insurance_no == '1000') : ?>
                                    						<li>
                                    							<a href="index.php?claims=<?= $hos_no ?>&A=<?= $row['sn'] ?>/<?= $from ?>/<?= $to ?>/cvtPP/<?= $from_where ?>&filter=<?= $filter ?>&tab=<?= $tab ?>"
                                    								onclick="return confirm('Are you sure you want to Convert to Payable After Posted At Cashier?');">
                                    								[ CONVERT TO PAYABLE AFTER POSTED ]
                                    							</a>
                                    						</li>
                                    					<?php endif; ?>




                                    					<?php if ($row['paystatus'] == 0 && $row['pay'] == 0 && $row['claim_amt'] > 0) : ?>
                                    						<li>
                                    							<a href="index.php?claims=<?= $hos_no ?>&A=<?= $row['sn'] ?>/<?= $from ?>/<?= $to ?>/cvtP/<?= $from_where ?>&filter=<?= $filter ?>&tab=<?= $tab ?>"
                                    								onclick="return confirm('Are you sure you want to Change to Payable At Cashier?');">
                                    								[ CONVERT TO PAYABLE ]
                                    							</a>
                                    						</li>
                                    					<?php endif; ?>

                                    					<?php if ($delete_valid_ph_status == 0) : ?>
                                    						<?php if ($row['paystatus'] == 0 && $row['pay'] > 0 && $row['claim_amt'] == 0 && $insurance_no != '1000') : ?>
                                    							<li>
                                    								<a href="index.php?claims=<?= $hos_no ?>&A=<?= $row['sn'] ?>/<?= $from ?>/<?= $to ?>/cvtC/<?= $from_where ?>&filter=<?= $filter ?>&tab=<?= $tab ?>"
                                    									onclick="return confirm('Are you sure you want to Convert to Claim?');">
                                    									<strong style="color: firebrick;">[ CONVERT TO CLAIM ]</strong>
                                    								</a>
                                    							</li>
                                    						<?php endif; ?>

                                    						<?php if ($reverse_status == 1 && $row['drug_status'] == 0 && $row['ledger_TX'] == '' && $row['pay'] == 0 && $row['claim_amt'] > 0 && $insurance_no != '1000') : ?>
                                    							<li>
                                    								<a href="index.php?claims=<?= $hos_no ?>&A=<?= $row['sn'] ?>/<?= $from ?>/<?= $to ?>/dvl/<?= $from_where ?>&filter=<?= $filter ?>&tab=<?= $tab ?>"
                                    									onclick="return confirm('Are you sure you want to delete?');">
                                    									[ DELETE VALIDATION ]
                                    								</a>
                                    							</li>

                                    						<?php endif; ?>
                                    					<?php endif; ?>

                                    					<?php if ($reverse_status == 0 && $row['pay_mode'] == 'claim' && $insurance_no != '1000') : $valid_pending = 1; ?>
                                    						<li>
                                    							<a href="index.php?claims=<?= $hos_no ?>&A=<?= $row['sn'] ?>/<?= $from ?>/<?= $to ?>/vld/<?= $from_where ?>&filter=<?= $filter ?>&tab=<?= $tab ?>"
                                    								<strong style="color: blue;">[ VALIDATE ]</strong>
                                    							</a>
                                    						</li>
                                    					<?php endif; ?>

                                    					<li class="dropdown-divider"></li>

                                    					<li class="text-center">
                                    						<?php
															if (($insurance == 'NHIS' || $insurance == 'PHIS' || $insurance == 'Corporate')) { ?>
                                    							<input type="button" name="edit_price" value="Edit Amount"
                                    								data-target="#modal" id="<?= $row['sn'] . '__' . $from . '/' . $to ?>"
                                    								class="btn btn-danger btn-xs edit_price_entry" />
                                    						<?php } ?>
                                    						<input type="button" name="edit_price" value="Change Date"
                                    							data-target="#modal" id="<?= $row['sn'] . '__' . $from . '/' . $to ?>"
                                    							class="btn btn-danger btn-xs edit_claim_date" />
                                    					</li>
                                    				</ul>

                                    			<?php endif; ?>

                                    		</div>

                                    	</td>

                                    </tr>
                                    <?php $n += 1;
									$convert_status = 0;
									$reverse_status = 0; ?>


                                    <div class="modal inmodal fade" id="edit_price_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
                                    	<div class="modal-dialog modal-sm">
                                    		<div class="modal-content">
                                    			<div class="modal-header">
                                    				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                    				<h4 class="modal-title" id="">Edit Price</h4>
                                    			</div>
                                    			<div class="modal-body" id="edit_price_body">
                                    			</div>
                                    		</div>
                                    	</div>
                                    </div>


                                    <div class="modal inmodal fade" id="edit_claim_date_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
                                    	<div class="modal-dialog modal-sm">
                                    		<div class="modal-content">
                                    			<div class="modal-header">
                                    				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                    				<h4 class="modal-title" id="">Edit Price</h4>
                                    			</div>
                                    			<div class="modal-body" id="edit_claim_date_body">
                                    			</div>
                                    		</div>
                                    	</div>
                                    </div>