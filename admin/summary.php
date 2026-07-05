
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

						if ($roww['paystatus'] == '1' and $roww['claim_amt'] > 0 and $roww['pay_mode'] != "writeoff") {
							echo '';
							$tclaim = $tclaim + $roww['claim_amt'];
						} elseif ($roww['paystatus'] == '1' and $roww['pay'] > 0 and $roww['pay_mode'] == "cash") {
							echo '';
							$tpay = $tpay + $roww['pay'];
						} elseif ($roww['paystatus'] == '0' and $roww['cr'] == "1") {
							$tcredit = $tcredit + $roww['pay'];
						} else {
							echo '';
						}
					}



					$setdate = date("Y-m-d");
					if ($get_report == "") {

						if ($rpt_type == 'Department') {
							//$stmt_dp = $db->query("SELECT department FROM department WHERE sn='$report_title'");
							//$rwx = $stmt_dp->fetch(PDO::FETCH_ASSOC);
							//$report_title = $rwx['department'];

							$report_title = $report_title . " Reports : <br>Between " .  date("d M Y", strtotime("$start")) .
								" - " . date("d M Y", strtotime("$end"));
						} elseif ($post_selection != 'One') {

							$report_title = $report_title . " Reports <br>Between " .  date("d M Y", strtotime("$start")) .
								" - " . date("d M Y", strtotime("$end"));
						}


						$stmt_add = $db->prepare('INSERT IGNORE INTO transc_rpt (claim, paid, credit, summary_title, operator, date_create, rpt_type) VALUES (:claim, :paid, :credit, :summary_title, :operator, :date_create, :rpt_type)');
						$stmt_add->bindParam(':claim', $tclaim);
						$stmt_add->bindParam(':paid', $tpay);
						$stmt_add->bindParam(':credit', $tcredit);
						$stmt_add->bindParam(':summary_title', $report_title);
						$stmt_add->bindParam(':operator', $operator);
						$stmt_add->bindParam(':date_create', $setdate);
						$stmt_add->bindParam(':rpt_type', $rpt_type);
						$stmt_add->execute();
					}
					?>
         
