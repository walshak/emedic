
                    <?php
					$n = 1;
					$tclaim = 0;
					$tpay = 0;
					$tcredit = 0;
					$queue = 0;
					$specimen = 0;
					$result = 0;
					$approve = 0;
					$reject = 0;
					$cancel = 0;
					$tcredit = 0;
					while ($roww = $stmt->fetch(PDO::FETCH_ASSOC)) {

						if ($roww['paystatus'] == '1' and $roww['claim_amt'] > 0 and $roww['cr'] == "0") {
							echo '';
							$tclaim = $tclaim + $roww['claim_amt'];
						} elseif ($roww['paystatus'] == '1' and $roww['pay'] > 0 and $roww['cr'] == "0") {
							echo '';
							$tpay = $tpay + $roww['pay'];
						} elseif ($roww['paystatus'] == '0' and $roww['cr'] == "1") {
							$tcredit = $tcredit + $roww['pay'];
						} else {
							echo '';
						}

						if ($roww['data_capture_status'] == 'queue') {
							$queue = $queue + 1;
						}
						if ($roww['data_capture_status'] == 'specimen') {
							$specimen = $specimen + 1;
						}
						if ($roww['data_capture_status'] == 'result') {
							$result = $result + 1;
						}
						if ($roww['data_capture_status'] == 'approve') {
							$approve = $approve + 1;
						}
						if ($roww['data_capture_status'] == 'reject') {
							$reject = $reject + 1;
						}
						if ($roww['data_capture_status'] == 'cancel') {
							$cancel = $cancel + 1;
						}
					}



					$setdate = date("Y-m-d");
					if ($get_report == "") {

						if ($rpt_type == 'Department') {

							$stmt_dp = $db->query("SELECT department FROM department WHERE sn='$report_title'");
							if ($stmt_dp->rowCount() > 0) {
								$rwx = $stmt_dp->fetch(PDO::FETCH_ASSOC);
								$myreport_title = $rwx['department'];
								$report_title3 = $myreport_title . " Reports - Between " .  date("d M Y", strtotime("$start")) .
									" - " . date("d M Y", strtotime("$end"));
							} else {
								$report_title3 = $report_title;
							}
						} elseif ($post_selection != 'One') {

							$report_title3 = $report_title . " Reports - Between " .  date("d M Y", strtotime("$start")) .
								" - " . date("d M Y", strtotime("$end"));
						}


						$stmt_add = $db->prepare("INSERT IGNORE INTO invsti_transc_rpt 
		  (claim, paid, credit, Queue, Specimen, Results, Approved, Rejected, Cancelled, total_test, summary_title, operator, date_create, rpt_type) 
		  VALUES 
		  (:tclaim, :tpay, :tcredit, :queue, :specimen, :result, :approve, :reject, :cancel, :total_test_requested, :report_title3, :operator, :setdate, :rpt_type)");

						$stmt_add->bindParam(':tclaim', $tclaim, PDO::PARAM_STR);
						$stmt_add->bindParam(':tpay', $tpay, PDO::PARAM_STR);
						$stmt_add->bindParam(':tcredit', $tcredit, PDO::PARAM_STR);
						$stmt_add->bindParam(':queue', $queue, PDO::PARAM_STR);
						$stmt_add->bindParam(':specimen', $specimen, PDO::PARAM_STR);
						$stmt_add->bindParam(':result', $result, PDO::PARAM_STR);
						$stmt_add->bindParam(':approve', $approve, PDO::PARAM_STR);
						$stmt_add->bindParam(':reject', $reject, PDO::PARAM_STR);
						$stmt_add->bindParam(':cancel', $cancel, PDO::PARAM_STR);
						$stmt_add->bindParam(':total_test_requested', $total_test_requested, PDO::PARAM_STR);
						$stmt_add->bindParam(':report_title3', $report_title3, PDO::PARAM_STR);
						$stmt_add->bindParam(':operator', $operator, PDO::PARAM_STR);
						$stmt_add->bindParam(':setdate', $setdate, PDO::PARAM_STR);
						$stmt_add->bindParam(':rpt_type', $rpt_type, PDO::PARAM_STR);

						$stmt_add->execute();
					}
					?>
         
