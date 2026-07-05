<?php session_start();
require_once('../Connections/Conn.php');

$currentMonthYear = date('m_Y'); // Format: MM_YYYY
$tableName = "drug_charts_inven_" . $currentMonthYear;

if (isset($_POST["hospital_no"])):
	$hospital_no = $_POST["hospital_no"];
	$ppointment_number = $_POST["appointment_number"];
	$month_year = $_POST["month_year"];
	$heading = null;
	$my_current_month_year = date('Y-m');

	$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
	$adm_id = isset($_POST['adm_id']) ? $_POST['adm_id'] : null;
	$drug_name = isset($_POST['drug_name']) ? $_POST['drug_name'] : null;
	$drug_name_sql = !empty($drug_name) ? " AND drug_name = '$drug_name' " : " ";
	$limit = 1;
	$offset = ($page - 1) * $limit;
	$limit_sql = " LIMIT $limit OFFSET $offset ";
	$serial_number = $page;

?>

	<div class="row">
		<div class="col-lg-12">
			<div class="ibox-content">
				<div style="overflow-x:scroll">
					<table class="table table-responsive" border="2" width="100%" style="overflow-x:scroll">
						<tr>
							<td>
								<h4>
									<?php if ($_SESSION['rights'] == 'NS'): ?>
										<button class="btn btn-primary" id="newDrugChartting" hosp="<?= $hospital_no; ?>">
											<i class="fa fa-plus-square"></i> Chart New Drug
										</button>
										|
									<?php endif; ?>
									<span><b> Previous/Current Admission List:</b></span>
									<select name="drug_chart_adm" id="drug_chart_adm" style="padding: 8px;" onchange="chart_by_adm(this.value)">
										<?php
										$stmt = $db->prepare("SELECT sn, date_admit FROM admission WHERE hospital_no=:hospital_no AND adm_status >=3 ORDER BY sn DESC LIMIT 3 ");
										$stmt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
										$stmt->execute();
										if ($stmt->rowCount() > 0) {

											$adm_count = 1;
											while ($adm = $stmt->fetch(PDO::FETCH_ASSOC)) {
												$isSelected = '';
												$adm_date = date('d/m/Y', strtotime('' . $adm['date_admit']));
												if ($adm_id == $adm['sn']) {
													$isSelected = 'selected';
												}

												$admission_id = $adm['sn'];

												if ($adm_count == 1) {
													echo '<option value="' . $admission_id . '" ' . $isSelected . '>Current Admission </option>';
												} else {
													echo '<option value="' . $admission_id . '" ' . $isSelected . '> Admission charts for [' . $adm_date . ']</option>';
												}

												$adm_count++;
											}
										}

										?>
									</select>
									<!-- Filter by month/Year: <input type="month" name="filterByMonth" id="filterByMonth" hosp="<?= $hospital_no; ?>"> -->
									&nbsp; : &nbsp; <span>Choose Previous Month Drug Charts: </span>
									<input type="month" id="month_picker" value="<?php echo $month_year; ?>" onchange="chart_type_selected_month('<?= $adm_id; ?>')">

								</h4>



							</td>
						</tr>


						<tr>
							<td>

								<?php

								$month_year = null;
								$drug_lists = null;
								if (isset($_POST['month_year'])):
									if (!empty($_POST['month_year'])):
										echo $month_year = $_POST['month_year'];
									endif;
								endif;

								$stmt11 = $db->prepare("SELECT DISTINCT drug_name, status FROM drug_charts WHERE hospital_no = :hospital_no AND adm_id=:adm_id ORDER BY CASE WHEN status='on-going' THEN 0 ELSE 1 END, sn DESC");
								$stmt11->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
								$stmt11->bindParam(':adm_id', $adm_id, PDO::PARAM_STR);
								$stmt11->execute();

								while ($drug_chart = $stmt11->fetch(PDO::FETCH_ASSOC)) {
									$isSelected = '';
									if ($drug_name == $drug_chart['drug_name']) {
										$isSelected = 'selected';
									}
									$drug_lists .= '<option value="' . $drug_chart['drug_name'] . '" ' . $isSelected . '>' . $drug_chart['drug_name'] . '</option>';
								}

								if (!empty($month_year)) {

									$date_arr = explode('-', $month_year);
									$currentMonthYear = $date_arr[0] . '_' . $date_arr[1];

									$tableName = "drug_charts_inven_" . $currentMonthYear;

									$ids = [];

									$stmt_inv = $db->prepare("SELECT DISTINCT drug_chart FROM drug_charts_inven WHERE hospital_no = :hospital_no AND date_given LIKE :date_given  ORDER BY sn DESC $limit_sql ");
									$stmt_inv->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
									$stmt_inv->bindValue(':date_given', $month_year . '%', PDO::PARAM_STR);
									$stmt_inv->execute();
									$count_for_inven = $stmt_inv->rowCount();
									$drug_chart_ids = $stmt_inv->fetchAll(PDO::FETCH_COLUMN);
									///print_r($drug_chart_ids);
									if ($stmt_inv->rowCount() > 0) {
										$placeholders_str = '';
										foreach ($drug_chart_ids as $i => $id) {
											$placeholders_str .= "$id,";
										}
										$placeholders_str = rtrim($placeholders_str, ',');

										$stmt1 = $db->prepare("SELECT * FROM drug_charts 
										WHERE hospital_no = :hospital_no 
										AND (date_time LIKE :month_year OR sn IN ($placeholders_str)) $drug_name_sql
										ORDER BY CASE WHEN status='on-going' THEN 0 ELSE 1 END, sn DESC $limit_sql ");
										$stmt1->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
										$stmt1->bindValue(':month_year', $month_year . '%', PDO::PARAM_STR);
										$stmt1->execute();
									} else {

										$stmt1 = $db->prepare("SELECT * FROM drug_charts WHERE hospital_no = :hospital_no  AND adm_id=:adm_id $drug_name_sql                         ORDER BY CASE WHEN status='on-going' THEN 0 ELSE 1 END, sn DESC $limit_sql ");
										$stmt1->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
										$stmt1->bindParam(':adm_id', $adm_id, PDO::PARAM_STR);
										$stmt1->execute();

										echo '<div class="alert alert-info">No charts have been entered for the selected month</div>';
									}

									$current_month_ = date('M', strtotime('' . $month_year));
									$heading = date('M, Y', strtotime('' . $month_year)); ?>
									<h4 class="text-center">Drug Charts <?= $heading; ?></h4>
									<div class="well sm-well">
										<?php if ($page > 1) { ?>
											<a href="#" class="btn btn-sm btn-primary" onclick="chart_type('<?php echo $month_year; ?>', <?php echo ($page - 1) ?>)">Previous <i class="fa fa-backward"></i></a> |
										<?php } ?>
										<select name="drug_name" id="drug_name" style="padding:8px" onchange="chart_type('<?php echo $month_year; ?>', 1, this.value)">
											<option value=""> Select Drug Name</option>
											<?php echo $drug_lists; ?>
										</select>
										<a href="#" class="btn btn-sm btn-primary" onclick="chart_type('', <?php echo ($page + 1) ?>)">Next <i class="fa fa-forward"></i></a>
									</div>

								<?php

								} else {
									$count_for_inven = 1;
									$stmt1 = $db->prepare("SELECT * FROM drug_charts WHERE hospital_no = :hospital_no  AND adm_id=:adm_id $drug_name_sql                        ORDER BY CASE WHEN status='on-going' THEN 0 ELSE 1 END, sn DESC $limit_sql ");
									$stmt1->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
									$stmt1->bindParam(':adm_id', $adm_id, PDO::PARAM_STR);
									$stmt1->execute();
									$heading = date('M, Y');
									$current_month_ = date('M');
									$month_year = date('Y-m'); ?>
									<h4 class="text-center">Drug Charts <?= $heading; ?></h4>
									<div class="well sm-well">
										<?php if ($page > 1) { ?>
											<a href="#" class="btn btn-sm btn-primary" onclick="chart_type('', <?php echo ($page - 1) ?>)">Previous <i class="fa fa-backward"></i></a> |
										<?php } ?>
										<select name="drug_name" id="drug_name" style="padding:8px" onchange="chart_type('', 1, this.value)">
											<option value=""> Selectr Drug Name</option>
											<?php echo $drug_lists; ?>
										</select>

										<a href="#" class="btn btn-sm btn-primary" onclick="chart_type('', <?php echo ($page + 1) ?>)">Next <i class="fa fa-forward"></i></a>
									</div>
								<?php
								}
								?>
							</td>
						</tr>
						<tr>
							<td>
								<?php


								if ($stmt1->rowCount() > 0 && $count_for_inven > 0):
									// $drug_charts = $stmt->fetchAll(PDO::FETCH_ASSOC);
									while ($drug_chart = $stmt1->fetch(PDO::FETCH_ASSOC)) {

										// foreach ($drug_charts as $key => $drug_chart):
										$drug_chart_status = $drug_chart['status'];
										$isDiscontinued = $drug_chart_status == 'discontinued' ? true : false;
										$date_discontinued = $drug_chart['date_discontinued'];
										$discontinueDay = null;
										$discontinueMonth = null;
										$discontinueYear = null;
										if ($isDiscontinued):
											$part = explode("-", $date_discontinued);
											$discontinueYear = intval($part[0]);
											$discontinueMonth = intval($part[1]);
											$discontinueDay = intval($part[2]);
										endif;


										$today_num = date('d');
										$current_month_num = date('m');
										$selected_month_num = $current_month_num;
										$current_year = date('Y');
										$selected_year_num = $current_year;
										$previous_month_num = $current_month_num - 1;
										$previous_year = $current_year;
										if ($current_month_num == 1):
											$previous_month_num = 12;
											$previous_year = $current_year - 1;
										endif;

										if (!empty($month_year)):
											$month_year_ar = explode("-", $month_year);
											$selected_year_num = intval($month_year_ar[0]);
											$selected_month_num = intval($month_year_ar[1]);

											if ($current_month_num == 1) {
												if ($selected_year_num < $current_year) {
													$today_num = 31;
												}
											} else {
												if ($selected_month_num < $current_month_num) {
													$today_num = 31;
												}
											}
										endif;

										/// Previous month charts inven.
										$pdate = $previous_year . '-' . $previous_month_num;
										// $stmt = $db->prepare("SELECT date_given FROM $tableName WHERE hospital_no = :hospital_no AND date_given LIKE :date_given ORDER BY sn DESC");
										// $stmt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
										// $stmt->bindParam(':date_given', $pdate.'%', PDO::PARAM_STR);
										// $stmt->execute();


										/// this month chart								
								?>

										<?= $serial_number++ . '.  <b>' . $drug_chart['drug_name'] . '</b>';
										$dosage = $drug_chart['dosage'];
										echo ($dosage != '') ? ' : <b>Dosage:</b>' . $dosage . ' | ' : '';
										echo ($drug_chart['frequent'] != '') ? '<b>Frequent:</b>' . $drug_chart['frequent'] . ' | ' : '';
										echo ($drug_chart['route'] != '') ? '<b>Duration:</b>' . $drug_chart['route']  : ''
										?> |

										<?php if ($_SESSION['rights'] == 'NS' and $my_current_month_year == $month_year): ?>
											<button data-target="#modal"
												hosp="<?= $hospital_no; ?>" drug_chart="<?= $drug_chart['sn']; ?>"
												class="btn btn-xs btn-success administer_drug">
												<i class="fa fa-plus-square"></i>
												New Timing
											</button>
										<?php endif; ?>

										<?php


										if ($drug_chart['status'] == 'discontinued') { ?>
											<h5> <span class="text-danger">Discontinued:</span> <?= date('d M, Y', strtotime("" . $date_discontinued)); ?>
												By: <?= $drug_chart['discontinued_by']; ?>
											</h5>
										<?php } ?>

										<?php

										$drug_chart_sn = $drug_chart['sn'];
										$remarks = $drug_chart['remarks'];
										$cleaned_remarks = preg_replace('/[^A-Za-z0-9\s]/', '', $remarks);

										?>

										<?php if ($_SESSION['rights'] == 'NS' and $my_current_month_year == $month_year): ?>


											<?php if ($drug_chart['status'] == 'discontinued') { ?>
												<a href="patient.php?hosp_no=<?= $hospital_no ?>&resume_drug=<?= $drug_chart['sn']; ?>" class="btn btn-xs btn-warning" onclick="return confirm('Are you sure you want to Resume?')">Resume Chart</a>
											<?php } else { ?> |
												<button data-target="#modal"
													hosp="<?= $hospital_no; ?>" drug_chart="<?= $drug_chart['sn']; ?>"
													drug_name="<?= $drug_chart['drug_name']; ?>"
													class="btn btn-xs btn-danger discontinueDrugBtn">
													<i class="fa fa-stop"></i>
													Discontinue
												</button>
											<?php } ?>

										<?php endif; ?>
										<?php ?>

										<?php if ($_SESSION['rights'] == 'NS' and $my_current_month_year == $month_year): ?>
											<button data-target="#modal"
												hosp="<?= $hospital_no; ?>" drug_chart="<?= $drug_chart['sn']; ?>"
												drug_name="<?= $drug_chart['drug_name']; ?>"
												remarks="<?= $cleaned_remarks; ?>"
												class="btn btn-xs btn-primary enter_remark_btn">
												Add Notes
											</button>
											<button class="btn btn-danger btn-xs" onclick="delete_drug_chart('<?php echo $drug_chart['sn']; ?>')">Delete</button>
										<?php endif; ?>
										<?php if ($drug_chart['remarks'] != '') {
											echo '<br><strong>Remarks:</strong> ' . '<i><b style="color:red;">' . $drug_chart['remarks'] . '</b></i>'; ?> By: <?= $drug_chart['discontinued_by'];
																																							} ?>

										<table class="table" border="2" width="100%">
											<tr>
												<td colspan="2">

												</td>

											</tr>
											<tr>
												<td><b>Timing/Date</b></td>

												<?php
												for ($i = 1; $i <= $today_num; $i++):
													$day_txt = $i;
													if (substr($day_txt, -1) == 1 && $day_txt != 11):
														// $day_txt = $day_txt.'st';
														$day_txt = $day_txt;
													elseif (substr($day_txt, -1) == 2):
														// $day_txt = $day_txt.'nd';
														$day_txt = $day_txt;
													elseif (substr($day_txt, -1) == 3):
														// $day_txt = $day_txt.'rd';
														$day_txt = $day_txt;
													else:
														$day_txt = $day_txt;
													// $day_txt = $day_txt.'th';
													endif;

													if ($i == $today_num):
														if ($selected_year_num == $current_year && $selected_month_num == $current_month_num):
															$day_txt = 'Today';
														endif;
													endif;


												?>
													<td class="text-center"><?= $day_txt; ?></td>
													<!-- <td class="text-center"><?= $day_txt . ' ' . $current_month_; ?></td> -->
												<?php
													if ($i == $today_num): break;
													endif;
												endfor;
												?>
											</tr>
											<?php
											$stmt2 = $db->prepare("SELECT * FROM drug_chart_timing WHERE drug_chart_id = :drug_chart_id ORDER BY id");
											$stmt2->bindParam(':drug_chart_id', $drug_chart['sn'], PDO::PARAM_STR);
											$stmt2->execute();
											if ($stmt2->rowCount() > 0):
												// $drug_charts_timings = $stmt->fetchAll(PDO::FETCH_ASSOC);
												while ($drug_charts_timing = $stmt2->fetch(PDO::FETCH_ASSOC)) {
											?>
													<tr>
														<td class="text-left">
															<?= $drug_charts_timing['timing']; ?>
															<?php $timing_sn = $drug_charts_timing['id'];

															$stmt2C = $db->prepare("SELECT 1 FROM drug_charts_inven WHERE drug_chart_timing = :drug_chart_timing");
															$stmt2C->bindParam(':drug_chart_timing', $timing_sn, PDO::PARAM_STR);
															$stmt2C->execute();
															if ($stmt2C->rowCount() == 0) { ?>
																<a href="patient.php?hosp_no=<?= $hospital_no; ?>&del_drug_time=<?= $timing_sn; ?>" onclick="return confirm('Are you sure you want to Delete?')"><strong style="color: red;">[ Delete ]</strong></a>
															<?php } ?>
														</td>
														<?php
														$drug_charts_timing_id = $drug_charts_timing['id'];
														for ($i = 1; $i <= $today_num; $i++):
															$day_txt = '';
															$flag_discontinued = false;

															if ($isDiscontinued):
																if ($discontinueMonth < $current_month_num):
																	$flag_discontinued = true;
																elseif ($discontinueMonth == $current_month_num):
																	if ($discontinueDay <= $i):
																		$flag_discontinued = true;
																	endif;
																endif;
															endif;


															$isTaken = false;
															/// Check inven
															$day_str = strlen($i) == 2 ? $i : '0' . $i;
															$selected_month_num_str = strlen($selected_month_num) == 2 ? $selected_month_num : '0' . $selected_month_num;
															$cdate = $selected_year_num . '-' . $selected_month_num_str . '-' . $day_str;
															$stmt = $db->prepare("SELECT sn, time_given,captured_by,date_given,dosage_given FROM drug_charts_inven WHERE drug_chart_timing = :drug_chart_timing AND date_given LIKE :date_given LIMIT 1");
															$stmt->bindParam(':drug_chart_timing', $drug_charts_timing['id'], PDO::PARAM_STR);
															$stmt->bindParam(':date_given', $cdate, PDO::PARAM_STR);
															$stmt->execute();
															if ($stmt->rowCount() > 0):
																$inven = $stmt->fetch();
																$isTaken = true;
																$captured_by_arr = explode(' ', $inven['captured_by']);
																$day_txt = $inven['time_given'] . '_By_' . $captured_by_arr[0];

																$current = date('Y-m-d'); // Get the current date
																$previousDate = date('Y-m-d', strtotime($current . ' -1 day')); // Subtract one day

																$sn_ = $inven['sn'];
																///$sn_ = $inven['sn'];
																if (($inven['date_given'] == $current or $inven['date_given'] == $previousDate) and $inven['captured_by'] = $_SESSION['fullname']) {
																	$del = '<a href="patient.php?hosp_no=' . $hospital_no . '&del_drug_time_taken=' . $sn_ . '" onclick="return confirm(\'Are you sure you want to Delete?\')"><strong style="color:red;">Delete</strong></a>';
																} else {
																	$del = '';
																}

															endif;

															if ($isTaken && $flag_discontinued): ?>
																<td class="text-center" style="background:red; color:white"><?= $day_txt; ?></td>
															<?php elseif ($isTaken && !$flag_discontinued): ?>
																<td class="text-center" style="background:<?= ($isTaken ? '#23c6c8' : 'white'); ?>;color:white"><?= $day_txt;
																																								echo '<br>' . $inven['dosage_given'] . '/ ' . $del;

																																								?></td>
															<?php elseif (!$isTaken && $flag_discontinued): ?>
																<td class="text-center" style="background: red; color:white"><?= $day_txt; ?></td>
															<?php else:
																$day_txt = '';
																if ($_SESSION['rights'] == 'NS'):
																	if ($selected_year_num == $current_year && $selected_month_num == $current_month_num):
																		if ($i == $today_num || $i == ($today_num - 1) || $i == ($today_num - 0)):
																			$day_txt = '<button class="btn btn-xs btn-danger administer_drug_btn" 
																					drug_chart="' . $drug_chart['sn'] . '" chart_timing="' . $drug_charts_timing_id . '"
																					drug_sn="' . $drug_chart['drug_sn'] . '" 
																					dosage="' . $dosage . '" 
																					day="' . $i . '"
																					hosp="' . $hospital_no . '" sale_sn="' . $drug_chart['sale_sn'] . '" >
																					<i class="fa fa-plus-square"></i></button>';

																		endif;
																	endif;
																endif;
															?>
																<td class="text-center"><?= $day_txt; ?></td>
															<?php

															endif; ?>
														<?php
														endfor;
														?>

													</tr>
											<?php
												}
											// endforeach;
											endif;
											?>
										</table>
								<?php
									}
								// endforeach;
								endif;
								?>
							</td>
						</tr>
					</table>

					<?php ///} 
					?>
				</div>

			</div>
		</div>
	</div>
<?php endif; ?>