<?php include("../Connections/Conn.php");
session_start();

$fiscal_year_stmt = $db->query("
    SELECT begin, end 
    FROM chart_fiscal_year 
    WHERE closed = '0' 
    ORDER BY begin DESC 
    LIMIT 1");

$row = $fiscal_year_stmt->fetch(PDO::FETCH_ASSOC);
$begin = $row['begin'];
$end   = $row['end'];

$h = "25%";
$w = "25%";
$patient_name = $_GET['name'];
if (isset($_GET['recepinv'])) {
  $emr = $_GET['recepinv'];
} elseif (isset($_GET['ext_sale'])) {
  $emr = $_GET['ext_sale'];
  $patient_name = $_GET['name'];
} elseif (isset($_GET['refund'])) {
  $emr = $_GET['refund'];
} elseif (isset($_GET['deposit'])) {
  $emr = $_GET['deposit'];
} elseif (isset($_GET['invoice'])) {
  $emr = $_GET['invoice'];
} elseif (isset($_GET['daily'])) {
  $emr = $_GET['daily'];
}
?>

<?php
if (isset($_GET['dep'])) {
  $sn = $_GET['dep'];
  $stmtP = $db->prepare("SELECT item_services, insurance_no, cr_amt, prepared_by, post_stamp,ref_value,bank_name FROM chart_ledger WHERE sn = ?");
  $stmtP->execute([$sn]);
  $row = $stmtP->fetch(PDO::FETCH_ASSOC);

  if (!$row) {
    echo "<p>Error: Invalid deposit entry.</p>";
    return;
  }

  $insurance_no = $row['insurance_no'];
  $cr_amt = $row['cr_amt'];
  $bank_name = $row['bank_name'];
  $ref_value = $row['ref_value'];

  // Determine filter field/value safely
  $filterField = ($insurance_no === '1000') ? 'hospital_no' : 'insurance_no';
  $filterValue = ($insurance_no === '1000') ? $emr : $insurance_no;

  // (Optional but recommended) — Validate field name for safety
  $allowedFields = ['hospital_no', 'insurance_no'];
  if (!in_array($filterField, $allowedFields)) {
    die('Invalid filter field detected.');
  }

  $sql = "
    SELECT 
        SUM(dr_amt) AS TOTAL_DEBITS, 
        SUM(cr_amt) AS TOTAL_CREDITS
    FROM chart_ledger 
    WHERE $filterField = :filterValue 
    AND account_no = '2121' 
    AND patient_stt_status != '2' 
    AND DATE(date_entry2) BETWEEN :start AND :end";

  $stmt = $db->prepare($sql);
  $params = array(
    ':filterValue' => $filterValue,
    ':start'       => $begin,
    ':end'         => $end
  );

  // Execute
  $stmt->execute($params);
  $rowXX = $stmt->fetch(PDO::FETCH_ASSOC);
  $TOTAL_DEBITS  = !empty($rowXX['TOTAL_DEBITS'])  ? (float)$rowXX['TOTAL_DEBITS']  : 0;
  $TOTAL_CREDITS = !empty($rowXX['TOTAL_CREDITS']) ? (float)$rowXX['TOTAL_CREDITS'] : 0;
  $current_balance = $TOTAL_CREDITS - $TOTAL_DEBITS;

  // Define a function to render receipt
  function renderReceipt($copyType, $row, $emr, $patient_name, $cr_amt, $current_balance, $w, $h, $bank_name, $ref_value)
  { ?>
    <div class="deposit_reciept_<?= strtolower($copyType); ?>">
      <table width="350" border="0" cellpadding="2" cellspacing="2" bgcolor="#FFFFFF">
        <tr>
          <td colspan="2" height="34"></td>
        </tr>
        <tr>
          <td colspan="2" align="center">
            <h3>DEPOSIT RECEIPT</h3>
            <?php if ($copyType === 'Hospital') echo "<strong>[Hospital Copy]</strong>"; ?>
            <div><img src="../img/logo.png" width="<?= $w; ?>" height="<?= $h; ?>" /></div>
          </td>
        </tr>
        <tr>
          <td><strong>Hospital No:</strong></td>
          <td><strong><?= $emr; ?></strong></td>
        </tr>
        <tr>
          <td><strong>Name:</strong></td>
          <td><strong><?= $patient_name; ?></strong></td>
        </tr>
        <tr>
          <td colspan="2"><strong>Description</strong></td>
        </tr>
        <tr>
          <td colspan="2"><?= $row['item_services']; ?></td>
        </tr>
        <tr>
          <td colspan="2" align="right"><strong>Amount Received:</strong></td>
        </tr>
        <tr>
          <td colspan="2" align="right">=N= <?= number_format($cr_amt, 2); ?></td>
        </tr>
        <tr>
          <td colspan="2" align="right"><strong>Current Balance:</strong></td>
        </tr>
        <tr>
          <td colspan="2" align="right">=N= <?php /// number_format($current_balance, 2);
                                            ?></td>
        </tr>
        <tr>
          <td colspan="2">
            Payment Type: <?= strtoupper($ref_value); ?>
            <?php if (!empty($bank_name)) echo ' <b>Bank: </b> ' . $bank_name; ?>
          </td>
        </tr>

        <tr>
          <td colspan="2">---------------------------------------------</td>
        </tr>
        <tr>
          <td colspan="2"><b>Received By:</b>&nbsp;<?= $row['prepared_by']; ?></td>
        </tr>
        <tr>
          <td><b>Date Paid:</b></td>
          <td><?= date("d M Y H:i:s a", strtotime($row['post_stamp'])); ?></td>
        </tr>
      </table>
    </div>
  <?php
  }

  // Action buttons
  ?>
  <a href="../billing/pacct.php?emr=<?= $emr; ?>" class="btn btn-default btn-sm">Close</a>
  &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;
  <button onclick="printdeposit('deposit_reciept_customer')">Print (Patient)</button>
  &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;
  <button onclick="printdeposit('deposit_reciept_hospital')">Print (Hospital)</button>

<?php
  renderReceipt("Customer", $row, $emr, $patient_name, $cr_amt, $current_balance, $w, $h, $bank_name, $ref_value);
  renderReceipt("Hospital", $row, $emr, $patient_name, $cr_amt, $current_balance, $w, $h, $bank_name, $ref_value);
} elseif (isset($_GET['rfd'])) {
  $sn = $_GET['rfd'];
  $stmtP = $db->prepare("SELECT * FROM chart_ledger WHERE sn = ?");
  $stmtP->execute([$sn]);
  $row = $stmtP->fetch(PDO::FETCH_ASSOC);

  if (!$row) {
    echo "<p>Error: Refund not found.</p>";
    return;
  }

  // Buttons 
?>
  <a href="../billing/pacct.php?emr=<?= $emr; ?>" class="btn btn-default btn-sm">Close</a>
  &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;
  <button onclick="printdeposit('deposit_reciept_customer')">Print (Patient)</button>
  &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;
  <button onclick="printdeposit('deposit_reciept_hospital')">Print (Hospital)</button>

  <?php
  // Reusable function
  function renderRefundReceipt($copyType, $row, $emr, $patient_name, $w, $h)
  {
    $label = ($copyType === 'Hospital') ? "[Hospital Copy]" : "";
  ?>
    <div class="deposit_reciept_<?= strtolower($copyType); ?>">
      <table width="350" border="0" cellpadding="2" cellspacing="2" bgcolor="#FFFFFF">
        <tr>
          <td colspan="2" height="34"></td>
        </tr>
        <tr>
          <td colspan="2" align="center">
            <h3>REFUND RECEIPT</h3>
            <strong><?= $label; ?></strong>
            <div><img src="../img/logo.png" width="<?= $w; ?>" height="<?= $h; ?>" /></div>
          </td>
        </tr>
        <tr>
          <td><strong>Hospital No:</strong></td>
          <td><strong><?= $emr; ?></strong></td>
        </tr>
        <tr>
          <td><strong>Name:</strong></td>
          <td><strong><?= $patient_name; ?></strong></td>
        </tr>
        <tr>
          <td colspan="2"><strong>Description</strong></td>
        </tr>
        <tr>
          <td colspan="2"><?= $row['item_services']; ?></td>
        </tr>
        <tr>
          <td colspan="2" align="right"><strong>Amount Refunded:</strong></td>
        </tr>
        <tr>
          <td colspan="2" align="right">=N= <?= number_format($row['cr_amt'], 2); ?></td>
        </tr>
        <tr>
          <td colspan="2">---------------------------------------------</td>
        </tr>
        <tr>
          <td><strong>Received By:</strong></td>
          <td><?= $row['prepared_by']; ?></td>
        </tr>
        <tr>
          <td><strong>Date Paid:</strong></td>
          <td><?= date("d M Y H:i:s a", strtotime($row['post_stamp'])); ?></td>
        </tr>
      </table>
    </div>
    <?php
  }

  renderRefundReceipt("Customer", $row, $emr, $patient_name, $w, $h);
  renderRefundReceipt("Hospital", $row, $emr, $patient_name, $w, $h);
} elseif (isset($_GET['print']) and $voucher != "") {

  $stmt = $db->query("SELECT i.*,v.batch_type,v.date_expire,v.amount as amt,v.created_by 
    FROM vouchers_inventory as i 
    INNER JOIN vouchers as v on i.batch_code=v.batch_code 
    WHERE i.batch_code='$voucher' order by i.sn");
  if ($stmt->rowCount() > 0) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>

      <button class="btn btn-default btn-sm" type="submit" name="cancel_inv" onClick="window.location.href='index.php'">Cancel</button>
      &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;
      <button onclick="printdeposit('Voucher')">Print</button>

      <div class="Voucher">
        <table width="278" height="160" border="0" cellpadding="2" cellspacing="2" id="searchBorder" bgcolor="#FFFFFF">
          <tr>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <td height="55" colspan="2">
              <div align="center"><b>
                  <h3><?php echo $row['batch_type'] . ' Voucher' ?></h3>
                </b></div>
              <div align="center"><img src="../img/logo.png" width="<?= $w; ?>" height="<?= $h; ?>" /></div>
            </td>
          </tr>
          <tr>
            <td><strong>Voucher Code</strong>: </td>
            <td><strong style="font-size:18px"><?php echo $row['voucher_code']; ?></strong></td>
          </tr>
          <tr>
            <td><strong>Authorized Amount</strong>: </td>
            <td><strong style="font-size:12px"><?php echo number_format($row['amt'], 2); ?></strong></td>
          </tr>
          <tr>
            <td><strong>Expired</strong> : <?php echo date("d M,y", strtotime($row['date_expire'])); ?></td>
            <td><strong>Generated</strong>: <?php echo $row['created_by']; ?></td>
          </tr>
        </table>
        ----------------------------------------
    <?php }
  } ?>
      </div>

    <?php } elseif (isset($_GET['recepinv']) or isset($_GET['ext_sale'])) { ?>

      <?php if (isset($_GET['ext_sale'])) { ?>
        <a href="../admin/index.php?sale=<?php echo $emr; ?>" class="btn btn-default btn-sm">Close</a>
      <?php } else { ?>
        <a href="../billing/pacct.php?emr=<?php echo $emr; ?>" class="btn btn-default btn-sm">Close</a>
      <?php } ?>

      &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;
      <button onclick="printdeposit('payment_customer')">Print(Patient)</button>
      &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;
      <button onclick="printdeposit('payment_hospital')">Print(Hospital)</button>
      &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;
      <button onclick="printdeposit('deposit_reciept_hospital_A4')">Print Large Copy</button>

      <div class="payment_hospital">
        <table width="350" height="353" border="0" cellpadding="2" cellspacing="2" id="searchBorder" bgcolor="#FFFFFF">
          <tr>
            <td height="55" colspan="2" width="350">
              <div align="center"><b>
                  <h3>
                    <?php
                    $reciept_no = '';
                    $wallet_debt_bill_to_acct = '';
                    $cr = '';

                    $stmttx = $db->prepare("SELECT wallet_debt_bill_to_acct, cr, invoice_no,ledger_TX FROM patient_ap_services WHERE hospital_no = :emr AND wallet_debt_bill_to_acct != '' ORDER BY sn DESC LIMIT 1");
                    $stmttx->execute([':emr' => $emr]);
                    $rowwx = $stmttx->fetch(PDO::FETCH_ASSOC);

                    if ($rowwx) {
                      $reciept_no = $rowwx['invoice_no'];
                      $ledger_TX = $rowwx['ledger_TX'];
                      $wallet_debt_bill_to_acct = $rowwx['wallet_debt_bill_to_acct'];
                      $cr = $rowwx['cr'];


                      //===========================
                    }

                    if (isset($_GET['r']) && $cr != '2' && $wallet_debt_bill_to_acct != 'CREDIT') {
                      echo "PAYMENT RECEIPT <br> RC:" . htmlspecialchars($reciept_no);
                    } elseif ($wallet_debt_bill_to_acct === 'BILL') {
                      echo 'BILLED TO ACCOUNT';
                    } elseif ($wallet_debt_bill_to_acct === 'WRF') {
                      echo 'WRITE OFF';
                    } elseif ($wallet_debt_bill_to_acct === 'CREDIT') {
                      echo 'CLAIM';
                    } else {
                      echo "INVOICE";
                    }
                    ?>
                  </h3>
                </b></div>
              <div align="center">
                <img src="../img/logo.png" width="<?= $w; ?>" height="<?= $h; ?>" /><br>
                <?php echo htmlspecialchars($_SESSION['h_name']); ?>
              </div>
              <div align="center">(Hospital Copy)</div>
            </td>
          </tr>

          <tr>
            <td width="100" height="30">
              <div style="font-size: 18px;"><strong>Hospital No:</strong></div>
            </td>
            <td width="250" height="30">
              <div style="font-size: 18px;"><strong><?php echo htmlspecialchars($emr); ?></strong></div>
            </td>
          </tr>

          <tr>
            <td width="100" height="30">
              <div style="font-size: 18px;"><strong>Name:</strong></div>
            </td>
            <td width="250" height="30">
              <div style="font-size: 18px;"><strong><?php echo htmlspecialchars($patient_name); ?></strong></div>
            </td>
          </tr>

          <tr>
            <td colspan="2">
              <table width="350" border="0">
                <tr>
                  <td><b style="font-size: 18px;">S/N</b></td>
                  <td><b style="font-size: 18px;">Description</b></td>
                  <td><b style="font-size: 18px;">Qty</b></td>
                  <td><b style="font-size: 18px;">Amount</b></td>
                </tr>

                <?php
                $cnt = 0;
                $TotalTrans = 0;
                $payment_remarks = '';
                $fullname = 'Unknown';

                $stmtSales = $db->prepare("SELECT sale_no FROM saleprint WHERE hos_no = :emr ORDER BY sn");
                $stmtSales->execute([':emr' => $emr]);

                while ($roww = $stmtSales->fetch(PDO::FETCH_ASSOC)) {
                  $sale_no = $roww['sale_no'];

                  $stmtP = $db->prepare("SELECT item_services, claim_amt, qty, invoice_no, pay, discount, date_entry, cr, invoice_status, payment_remarks, who_process_paystatus, wallet_debt_bill_to_acct, transact_date, tag, remarks, serv_group, ledger_TX, drug_sn FROM patient_ap_services WHERE sn = :sn LIMIT 1");
                  $stmtP->execute([':sn' => $sale_no]);
                  $row = $stmtP->fetch(PDO::FETCH_ASSOC);

                  if (!$row) continue;

                  // Update part-payment tag
                  if ($row['tag'] === 'target' && $row['remarks'] === 'part') {
                    $updateSQL = "UPDATE patient_ap_services SET cr = 1 WHERE invoice_no = :invoice_no AND hospital_no = :emr AND remarks = 'part' AND paystatus = 0";
                    $stmtUpdate = $db->prepare($updateSQL);
                    $stmtUpdate->execute([':invoice_no' => $row['invoice_no'], ':emr' => $emr]);
                  }

                  if (!empty($row['payment_remarks'])) {
                    $payment_remarks = $row['payment_remarks'];
                  }

                  $EmployeeCode = $row['who_process_paystatus'];
                  if ($EmployeeCode) {
                    $stmtEmp = $db->prepare("SELECT FirstName, LastName FROM hremp WHERE EmployeeCode = :empcode LIMIT 1");
                    $stmtEmp->execute([':empcode' => $EmployeeCode]);
                    $emp = $stmtEmp->fetch(PDO::FETCH_ASSOC);
                    if ($emp) {
                      $fullname = $emp['FirstName'] . ' ' . $emp['LastName'];
                    }
                  }

                  $cnt++;
                  $amt = ($row['pay'] > 0) ? $row['pay'] : $row['claim_amt'];
                  $TotalTrans += $amt;
                ?>
                  <tr>
                    <td>
                      <div style="font-size: 18px;"><?php echo $cnt; ?></div>
                    </td>
                    <td>
                      <div style="font-size: 18px;"><?php echo htmlspecialchars($row['item_services']); ?></div>
                    </td>
                    <td>
                      <div style="font-size: 18px;"><?php echo htmlspecialchars($row['qty']); ?></div>
                    </td>
                    <td>
                      <div style="font-size: 18px;">
                        <?php echo number_format($amt, 2); ?>
                        <?php if ($row['discount'] > 0): ?>
                          <br><span style="font-size: 14px; color: gray;">Discount: <?php echo number_format($row['discount'], 2); ?></span>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                <?php } ?>

                <tr>
                  <td>&nbsp;</td>
                  <td><b style="font-size: 18px;">Total:</b></td>
                  <td>&nbsp;</td>
                  <td>
                    <hr style="border:1px dotted;" />
                    <strong style="font-size: 18px;"><?php echo number_format($TotalTrans, 2); ?></strong>
                    <hr style="border:1px dotted;" />
                  </td>
                </tr>
              </table>

              <?php
              $stmttxW = $db->prepare("SELECT DISTINCT ref_value, bank_name 
                      FROM chart_ledger 
                      WHERE lg_ref_no = :lg_ref_no 
                        AND transc_type = 'CREDIT' 
                        AND account_no = '2121'");
              $stmttxW->execute([
                ':lg_ref_no' => $ledger_TX
              ]);

              $methods = [];
              $bankName = '';
              while ($rowwxW = $stmttxW->fetch(PDO::FETCH_ASSOC)) {
                if (!in_array($rowwxW['ref_value'], $methods)) {
                  $methods[] = $rowwxW['ref_value']; // Avoid duplicates
                }
                if (!empty($rowwxW['bank_name'])) {
                  $bankName = $rowwxW['bank_name']; // You can adjust logic if multiple banks possible
                }
              }

              // Output
              if (!empty($methods)) {
                $paymentMethods = implode(' and ', $methods);
                echo $methed = "<b>Method: </b> " . strtoupper($paymentMethods);
                if ($bankName != '') {
                  echo $methed = " <b>Bank:</b> " . $bankName;
                }
              } else {
                if ($wallet_debt_bill_to_acct == "WALET") {
                  echo $methed = "<b><i> From Patient's Wallet</i></b>";
                }
              }
              ?>
              <?php if (!empty($payment_remarks)): ?>
                <strong>REMARK:<br><?= htmlspecialchars($payment_remarks) ?></strong>
              <?php endif; ?>

              <hr style="border:1px dotted;" />
            </td>
          </tr>

          <tr>
            <td width="100" height="30">
              <div style="font-size: 18px;"><strong>Date &amp; Time:</strong></div>
            </td>
            <td width="250" height="30">
              <div style="font-size: 18px;"><strong><?php echo isset($row['transact_date']) ? date('d-m-Y h:i a', strtotime($row['transact_date'])) : ''; ?></strong></div>
            </td>
          </tr>

          <tr>
            <td width="170" height="30">
              <div style="font-size: 18px;"><strong>Generated By:</strong></div>
            </td>
            <td width="170" height="30">
              <div style="font-size: 18px;"><strong><?php echo htmlspecialchars($fullname); ?></strong></div>
            </td>
          </tr>
        </table>
      </div>

      <div class="payment_customer">

        <table width="350" height="353" border="0" cellpadding="2" cellspacing="2" id="searchBorder" bgcolor="#FFFFFF">
          <tr>
            <td height="55" colspan="2" width="350">
              <div align="center">
                <b>
                  <h3>
                    <?php
                    // Fetch latest billing info for patient
                    $stmt = $db->prepare("SELECT wallet_debt_bill_to_acct, cr, invoice_no, acct_billed_staff 
                                    FROM patient_ap_services 
                                    WHERE hospital_no = :emr AND wallet_debt_bill_to_acct != '' 
                                    ORDER BY sn DESC LIMIT 1");
                    $stmt->execute([':emr' => $emr]);
                    $billingInfo = $stmt->fetch(PDO::FETCH_ASSOC);

                    $reciept_no = '';
                    $wallet_debt_bill_to_acct = '';
                    $cr = '';
                    $acct_billed_staff = '';
                    $fullname_billed_to = '';

                    if ($billingInfo) {
                      $reciept_no = $billingInfo['invoice_no'];
                      $wallet_debt_bill_to_acct = $billingInfo['wallet_debt_bill_to_acct'];
                      $cr = $billingInfo['cr'];
                      $acct_billed_staff = $billingInfo['acct_billed_staff'];
                    }

                    if (isset($_GET['r']) && $cr != '2' && $wallet_debt_bill_to_acct !== 'CREDIT') {
                      echo "PAYMENT RECEIPT <br> RC:" . htmlspecialchars($reciept_no);
                    } elseif ($wallet_debt_bill_to_acct === 'BILL') {
                      echo 'BILLED TO ACCOUNT';
                      if ($acct_billed_staff) {
                        $stmtStaff = $db->prepare("SELECT FirstName, LastName FROM hremp WHERE EmployeeCode = :empcode LIMIT 1");
                        $stmtStaff->execute([':empcode' => $acct_billed_staff]);
                        $staff = $stmtStaff->fetch(PDO::FETCH_ASSOC);
                        if ($staff) {
                          $fullname_billed_to = htmlspecialchars($staff['FirstName'] . ' ' . $staff['LastName']);
                        }
                      }
                    } elseif ($wallet_debt_bill_to_acct === 'WRF') {
                      echo 'WRITE OFF';
                    } elseif ($wallet_debt_bill_to_acct === 'CREDIT') {
                      echo 'CLAIM';
                    } else {
                      echo "INVOICE";
                    }
                    ?>
                  </h3>
                </b>
              </div>
              <div align="center">
                <img src="../img/logo.png" width="<?= htmlspecialchars($w); ?>" height="<?= htmlspecialchars($h); ?>" /><br>
                <?= htmlspecialchars($_SESSION['h_name']); ?>
              </div>
              <div align="center">(Customer Copy)</div>
            </td>
          </tr>

          <tr>
            <td colspan="2"></td>
          </tr>

          <tr>
            <td width="100" height="30">
              <div style="font-size: 18px;"><strong>Hospital No:</strong></div>
            </td>
            <td width="250" height="30">
              <div style="font-size: 18px;"><strong><?= htmlspecialchars($emr); ?></strong></div>
            </td>
          </tr>

          <tr>
            <td width="100" height="30">
              <div style="font-size: 18px;"><strong>Name:</strong></div>
            </td>
            <td width="250" height="30">
              <div style="font-size: 18px;"><strong><?= htmlspecialchars($patient_name); ?></strong></div>
            </td>
          </tr>

          <tr>
            <td height="136" colspan="2" width="350">
              <table width="350" border="0">
                <tr>
                  <td height="30"><b style="font-size: 18px;">S/N</b></td>
                  <td height="30"><b style="font-size: 18px;">Description</b></td>
                  <td height="30"><b style="font-size: 18px;">Qty</b></td>
                  <td height="30"><b style="font-size: 18px;">Amount</b></td>
                </tr>

                <?php
                $cnt = 0;
                $TotalTrans = 0;
                $payment_remarks = '';
                $fullname = '';

                // Get all sales for this hospital_no
                $stmtSales = $db->prepare("SELECT sale_no FROM saleprint WHERE hos_no = :emr ORDER BY sn");
                $stmtSales->execute([':emr' => $emr]);

                while ($sale = $stmtSales->fetch(PDO::FETCH_ASSOC)) {
                  $sale_no = $sale['sale_no'];

                  // Get service details for this sale_no
                  $stmtService = $db->prepare("SELECT item_services, claim_amt, qty, invoice_no, pay, discount, date_entry, cr, invoice_status, payment_remarks, who_process_paystatus, wallet_debt_bill_to_acct, transact_date 
                                         FROM patient_ap_services WHERE sn = :sale_no LIMIT 1");
                  $stmtService->execute([':sale_no' => $sale_no]);
                  $service = $stmtService->fetch(PDO::FETCH_ASSOC);

                  if (!$service) {
                    continue; // skip if no service found
                  }

                  // Capture payment remarks if any
                  if (!empty($service['payment_remarks'])) {
                    $payment_remarks = $service['payment_remarks'];
                  }

                  // Get fullname of staff who processed payment
                  $fullname = '';
                  if (!empty($service['who_process_paystatus'])) {
                    $stmtEmp = $db->prepare("SELECT FirstName, LastName FROM hremp WHERE EmployeeCode = :empcode LIMIT 1");
                    $stmtEmp->execute([':empcode' => $service['who_process_paystatus']]);
                    $emp = $stmtEmp->fetch(PDO::FETCH_ASSOC);
                    if ($emp) {
                      $fullname = htmlspecialchars($emp['FirstName'] . ' ' . $emp['LastName']);
                    }
                  }

                  $cnt++;
                  $amt = ($service['pay'] > 0) ? $service['pay'] : $service['claim_amt'];
                  $TotalTrans += $amt;
                ?>
                  <tr>
                    <td>
                      <div style="font-size: 18px;"><?= $cnt; ?></div>
                    </td>
                    <td>
                      <div style="font-size: 18px;"><?= htmlspecialchars($service['item_services']); ?></div>
                    </td>
                    <td>
                      <div style="font-size: 18px;"><?= htmlspecialchars($service['qty']); ?></div>
                    </td>
                    <td>
                      <div style="font-size: 18px;">
                        <?= number_format($amt, 2); ?>
                        <?php if ($service['discount'] > 0): ?>
                          <br><small style="color: gray;">DSC: <?= number_format($service['discount'], 2); ?></small>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                <?php } ?>

                <tr>
                  <td height="30">&nbsp;</td>
                  <td><b style="font-size: 18px;">Total:</b></td>
                  <td>&nbsp;</td>
                  <td>
                    <hr style="border:1px dotted;" />
                    <strong style="font-size: 18px;"><?= number_format($TotalTrans, 2); ?></strong>
                    <hr style="border:1px dotted;" />
                  </td>
                </tr>
              </table>
              <?php
              if (!empty($methods)) {
                $paymentMethods = implode(' and ', $methods);
                echo $methed = "<b>Method: </b> " . strtoupper($paymentMethods);
                if ($bankName != '') {
                  echo $methed = " <b>Bank:</b> " . $bankName;
                }
              } else {
                if ($wallet_debt_bill_to_acct == "WALET") {
                  echo $methed = "<b><i> From Patient's Wallet</i></b>";
                }
              } ?>
              <?php if (!empty($payment_remarks)): ?>
                <strong>REMARK:<br><?= htmlspecialchars($payment_remarks) ?></strong>
              <?php endif; ?>
              <hr style="border:1px dotted;" />
            </td>
          </tr>

          <tr>
            <td height="30" width="100">
              <div style="font-size: 18px;"><strong>Date &amp; Time:</strong></div>
            </td>
            <td height="30" width="250">
              <div style="font-size: 18px;">
                <strong><?= !empty($service['transact_date']) ? date('d-m-Y h:i a', strtotime($service['transact_date'])) : ''; ?></strong>
              </div>
            </td>
          </tr>

          <tr>
            <td height="30" width="170">
              <div style="font-size: 18px;"><strong>Generated By:</strong></div>
            </td>
            <td height="30" width="170">
              <div style="font-size: 18px;"><strong><?= $fullname ?: 'Unknown'; ?></strong></div>
            </td>
          </tr>
        </table>

      </div>

      <div class="deposit_reciept_hospital_A4">
        <div align="center">
          <img src="../img/logo.png" width="<?= $w; ?>" height="<?= $h; ?>" />
          <div style="font-size:18px; font-family:Verdana, Geneva, sans-serif;"><strong><?= $_SESSION['h_name']; ?></strong></div>
          <div style="font-size:14px"><?= $_SESSION['h_address']; ?><br><?= $_SESSION['h_phone']; ?></div>
        </div>

        <?php
        $stmt_en = $db->query("SELECT a.*, d.department     FROM apptm AS a     INNER JOIN department AS d ON d.sn = a.dept     WHERE hospital_no = '$emr'     ORDER BY a.sn DESC     LIMIT 1");

        if ($stmt_en->rowCount()) {
          $row = $stmt_en->fetch(PDO::FETCH_ASSOC);
          $patient_name = $row['patient_name'];
          $department   = $row['department'];
          $appt_no      = $row['appt_no'];
          $date_ap      = date("d-m-Y", strtotime($row['date_ap']));
        } else {
          $patient_name = $department = $appt_no = $date_ap = '';
        }
        ?>

        <table width="100%">
          <tr>
            <td>
              <table cellpadding="1" cellspacing="5" style="font-family: arial; font-size: 13px; width: 100%;">
                <tr>
                  <td style="border-bottom: 1px solid #ddd;">Patient Name:</td>
                  <td style="border-bottom: 1px solid #ddd;"><?= $patient_name; ?></td>
                </tr>
                <tr>
                  <td style="border-bottom: 1px solid #ddd;">Hospital Number:</td>
                  <td style="border-bottom: 1px solid #ddd;"><?= $emr; ?></td>
                </tr>
                <tr>
                  <td style="border-bottom: 1px solid #ddd;">Appointment Number:</td>
                  <td style="border-bottom: 1px solid #ddd;"><?= $appt_no; ?></td>
                </tr>
              </table>
            </td>
            <td>
              <table cellpadding="1" cellspacing="5" style="font-family: arial; font-size: 13px; width: 100%;">
                <tr>
                  <td style="border-bottom: 1px solid #ddd;">Contact Date:</td>
                  <td style="border-bottom: 1px solid #ddd;"><?= $date_ap; ?></td>
                </tr>
                <tr>
                  <td style="border-bottom: 1px solid #ddd;">Receipt Number:</td>
                  <td style="border-bottom: 1px solid #ddd;"></td>
                </tr>
                <tr>
                  <td style="border-bottom: 1px solid #ddd;">Department:</td>
                  <td style="border-bottom: 1px solid #ddd;"><?= $department; ?></td>
                </tr>
              </table>
            </td>
          </tr>
        </table>

        <hr>

        <table cellspacing="5" style="font-family: arial; font-size: 13px; width: 100%;">
          <thead>
            <tr bgcolor="#CCCCCC">
              <th width="5%">SN</th>
              <th width="70%">Description</th>
              <th width="10%">Qty</th>
              <th width="15%">Amount</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $TotalTrans = 0;
            $n = 1;

            $stmtt = $db->query("SELECT * FROM saleprint WHERE hos_no='$emr' ORDER BY sn");
            while ($roww = $stmtt->fetch(PDO::FETCH_ASSOC)) {
              $sale_no = $roww['sale_no'];
              $stmtP = $db->query("
          SELECT item_services, claim_amt, qty, invoice_no, pay, discount, date_entry, cr, 
                 invoice_status, payment_remarks, who_process_paystatus, wallet_debt_bill_to_acct, transact_date 
          FROM patient_ap_services 
          WHERE sn = '$sale_no'
        ");

              if ($stmtP->rowCount() === 0) continue;

              $row = $stmtP->fetch(PDO::FETCH_ASSOC);

              $amt = ($row['pay'] > 0) ? $row['pay'] : $row['claim_amt'];
              $TotalTrans += $amt;

              $discount = $row['discount'];
              $transact_date = $row['transact_date'];
              $wallet_debt_bill_to_acct = $row['wallet_debt_bill_to_acct'];
              $fullname_billed_to = '';

              $EmployeeCode = $row['who_process_paystatus'];
              $fullname = '';
              if ($EmployeeCode) {
                $stmt_get = $db->query("SELECT FirstName, LastName FROM hremp WHERE EmployeeCode='$EmployeeCode'");
                $rowX = $stmt_get->fetch(PDO::FETCH_ASSOC);
                $fullname = $rowX ? $rowX['FirstName'] . ' ' . $rowX['LastName'] : '';
              }
            ?>
              <tr class="record">
                <td style="border-bottom: 1px solid #ddd;"><?= $n++; ?></td>
                <td style="border-bottom: 1px solid #ddd;"><?= $row['item_services']; ?></td>
                <td style="border-bottom: 1px solid #ddd;"><?= $row['qty']; ?></td>
                <td style="border-bottom: 1px solid #ddd;">
                  <?= number_format($amt, 2); ?>
                  <?php if ($discount > 0) echo ' DSC: ' . number_format($discount); ?>
                </td>
              </tr>
            <?php } ?>

            <tr>
              <td></td>
              <td><br><b>WORD:</b> <?= ucwords(numberToWords($TotalTrans)) . ' Naira Only'; ?></td>
              <td><br>Total:</td>
              <td style="border-bottom: 1px solid #ddd;"><br><?= number_format($TotalTrans, 2); ?></td>
            </tr>
          </tbody>
        </table>

        <hr>
        <?php
        if (!empty($methods)) {
          $paymentMethods = implode(' and ', $methods);
          echo $methed = "<b>Method: </b> " . strtoupper($paymentMethods);
          if ($bankName != '') {
            echo $methed = " <b>Bank:</b> " . $bankName;
          }
        } else {
          if ($wallet_debt_bill_to_acct == "WALET") {
            echo $methed = "<b><i> From Patient's Wallet</i></b>";
          }
        }
        ?>
        <?php if (!empty($payment_remarks)): ?>
          <strong>REMARK:<br><?= htmlspecialchars($payment_remarks) ?></strong>
        <?php endif; ?>
        <div style="font-size: 12px;"><strong>Date & Time:</strong> <?= $transact_date ? date('d-m-Y h:i', strtotime($transact_date)) : date('d-m-Y h:i'); ?></div>
        <br>

        <?php
        if ($wallet_debt_bill_to_acct === 'BILL' && $fullname_billed_to) {
          echo '<b>BILLED TO ACCOUNT:</b> ' . $fullname_billed_to;
        }
        ?>

        <table width="100%">
          <tr>
            <td width="70%">
              <table width="100%" cellpadding="2" cellspacing="2">
                <tr>
                  <td height="30">&nbsp;</td>
                </tr>
              </table>
            </td>
            <td>
              <table width="100%" cellpadding="2" cellspacing="2">
                <tr>
                  <td width="170" height="30" align="right" style="font-size: 12px;">
                    <strong>Signature:</strong><br><br><br>
                    <b>(<?= strtoupper($fullname); ?>)</b>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </div>



    <?php } elseif (isset($_GET['daily'])) { ?>
      <a href="../billing/pacct.php?emr=<?= $emr; ?>" class="btn btn-default btn-sm">Close</a>
      &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;
      <button onclick="printdeposit('daily')">Print</button>

      <div class="daily">
        <table width="350" border="0" cellpadding="2" cellspacing="2" bgcolor="#FFFFFF" id="searchBorder">
          <tr>
            <td colspan="2" height="55" align="center">
              <h3>ADMISSION DAILY INVOICE</h3>
              <img src="../img/logo.png" width="<?= $w; ?>" height="<?= $h; ?>" />
              <div>(NOT A RECEIPT)</div>
            </td>
          </tr>
          <tr>
            <td width="100"><strong>Hospital No:</strong></td>
            <td width="250"><strong><?= $emr; ?></strong></td>
          </tr>
          <tr>
            <td><strong>Name:</strong></td>
            <td><strong><?= $patient_name; ?></strong></td>
          </tr>
          <tr>
            <td colspan="2">
              <table width="350" border="0">
                <tr>
                  <th>S/N</th>
                  <th>Description</th>
                  <th>Qty</th>
                  <th>Amount</th>
                </tr>

                <?php
                $cnt = 0;
                $TotalTrans = 0;

                // Admission date range
                $stmt_admission = $db->query("SELECT date_admit FROM admission WHERE adm_status='3' AND hospital_no='$emr'");
                if ($stmt_admission->rowCount()) {
                  $admission = $stmt_admission->fetch(PDO::FETCH_ASSOC);
                  $date_admit = date("Y-m-d", strtotime($admission['date_admit']));
                  $back_date = date("Y-m-d", strtotime("$date_admit -2 days"));
                  $todate = date("Y-m-d");
                }

                // Regular CR services
                $stmt_services = $db->query("
              SELECT * FROM patient_ap_services 
              WHERE hospital_no='$emr' 
              AND remarks!='auto_deduct' AND paystatus='0' AND cr='1' 
              AND DATE(date_entry) BETWEEN '$back_date' AND '$todate'
            ");
                foreach ($stmt_services as $roww) {
                  $cnt++;
                  $amt = ($roww['pay'] > 0) ? $roww['pay'] : $roww['claim_amt'];
                  $TotalTrans += $amt;
                  echo "
              <tr>
                <td>$cnt</td>
                <td>{$roww['item_services']}</td>
                <td>{$roww['qty']}</td>
                <td>CR: " . number_format($amt) . "</td>
              </tr>";
                }

                // Accommodation: auto_deduct
                $stmt_accom = $db->query("
              SELECT claim_amt, pay, sn, cat_type, date_entry, remarks, qty, invoice_status, item_services 
              FROM patient_ap_services 
              WHERE hospital_no='$emr' AND cr='1' AND paystatus='0' AND remarks='auto_deduct'
            ");
                foreach ($stmt_accom as $row_rs) {
                  $cnt++;
                  $now = new DateTime();
                  $entry_date = new DateTime($row_rs['date_entry']);
                  $diff = $entry_date->diff($now);

                  $duration = $diff->days;
                  if ($diff->h > 12 || $duration === 0) $duration++;

                  if ($row_rs['invoice_status'] == 1) {
                    $amt = ($row_rs['pay'] > 0) ? $row_rs['pay'] : $row_rs['claim_amt'];
                  } else {
                    $amt = ($row_rs['pay'] > 0 ? $row_rs['pay'] : $row_rs['claim_amt']) * $duration;
                  }
                  $TotalTrans += $amt;
                  echo "
              <tr>
                <td>$cnt</td>
                <td>{$row_rs['item_services']}</td>
                <td>$duration</td>
                <td>CR: " . number_format($amt) . "</td>
              </tr>";
                }

                // Temp Invoice Selections
                $stmt_temp = $db->query("SELECT * FROM invoice_temp2 WHERE hosp='$emr' ORDER BY sn");
                foreach ($stmt_temp as $roww) {
                  $cnt++;
                  $sale_no = $roww['sale_no'];
                  $stmtP = $db->query("SELECT item_services, claim_amt, qty, pay FROM patient_ap_services WHERE sn='$sale_no'");
                  $row = $stmtP->fetch(PDO::FETCH_ASSOC);

                  $amt = ($row['pay'] > 0) ? $row['pay'] : $row['claim_amt'];
                  $TotalTrans += $amt;
                  echo "
              <tr>
                <td>$cnt</td>
                <td>{$row['item_services']}</td>
                <td>{$row['qty']}</td>
                <td>" . number_format($amt, 2) . "</td>
              </tr>";
                }
                ?>

                <tr>
                  <td>&nbsp;</td>
                  <td><strong>Total:</strong></td>
                  <td>&nbsp;</td>
                  <td>
                    <hr style="border:1px dotted;" />
                    <strong><?= number_format($TotalTrans, 2); ?></strong>
                    <hr style="border:1px dotted;" />
                  </td>
                </tr>
              </table>

              <?php
              // Display Current Deposit
              $stmt = $db->query("
            SELECT SUM(dr_amt) AS TOTAL_DEBITS, SUM(cr_amt) AS TOTAL_CREDITS 
            FROM chart_ledger 
            WHERE hospital_no='$emr' AND account_no='2121' AND patient_stt_status != '2'
          ");
              if ($stmt->rowCount()) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $deposit = $row['TOTAL_CREDITS'] - $row['TOTAL_DEBITS'];
                echo "<strong>CURRENT DEPOSIT: " . number_format($deposit, 2) . "</strong>";
              } else {
                echo "<strong>CURRENT DEPOSIT: Zero Naira</strong>";
              }
              ?>
              <hr style="border:1px dotted;" />
            </td>
          </tr>

          <tr>
            <td><strong>Date &amp; Time:</strong></td>
            <td><strong><?= date("d-m-Y"); ?></strong></td>
          </tr>
          <tr>
            <td><strong>Generated By:</strong></td>
            <td><strong><?= $_SESSION['fullname']; ?></strong></td>
          </tr>
        </table>
      </div>
    <?php } elseif (isset($_GET['invoice'])) { ?>

      <a href="../billing/pacct.php?emr=<?= htmlspecialchars($emr); ?>&pay" class="btn btn-default btn-sm">Close</a>
      &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;
      <button onclick="printdeposit('daily')">Print</button>

      <div class="daily">
        <table width="350" border="0" cellpadding="2" cellspacing="2" id="searchBorder" bgcolor="#FFFFFF">
          <tr>
            <td colspan="2" height="55" align="center">
              <b style="font-size: 18px;">INVOICE</b>
              <div><img src="../img/logo.png" width="<?= $w; ?>" height="<?= $h; ?>" /></div>
              <b style="font-size: 18px;">(NOT A RECEIPT)</b>
            </td>
          </tr>
          <tr>
            <td width="100" height="30"><strong style="font-size: 18px;">Hospital No:</strong></td>
            <td width="250" height="30"><strong style="font-size: 18px;"><?= htmlspecialchars($emr); ?></strong></td>
          </tr>
          <tr>
            <td height="30"><strong style="font-size: 18px;">Name:</strong></td>
            <td height="30"><strong style="font-size: 18px;"><?= htmlspecialchars($patient_name); ?></strong></td>
          </tr>
          <tr>
            <td colspan="2">
              <table width="100%" border="0">
                <tr>
                  <td><b style="font-size: 18px;">S/N</b></td>
                  <td><b style="font-size: 18px;">Description</b></td>
                  <td><b style="font-size: 18px;">Qty</b></td>
                  <td><b style="font-size: 18px;">Amount</b></td>
                  <td><b style="font-size: 18px;">Discount</b></td>
                </tr>

                <?php
                $T_dsc_amt = $TotalTrans = $cnt = 0;
                $stmt = $db->prepare("SELECT * FROM invoice_temp2 WHERE hosp = ? ORDER BY sn");
                $stmt->execute([$emr]);

                while ($temp = $stmt->fetch(PDO::FETCH_ASSOC)) {
                  $sale_no = $temp['sale_no'];
                  $dsc_amt = $temp['amt'];

                  $stmtP = $db->prepare("SELECT item_services, claim_amt, qty, invoice_no, pay, date_entry, cr, invoice_status 
                                   FROM patient_ap_services WHERE sn = ?");
                  $stmtP->execute([$sale_no]);
                  $row = $stmtP->fetch(PDO::FETCH_ASSOC);

                  if (!$row) continue;

                  $cnt++;
                  $item_services = $row['item_services'];
                  $qty = $row['qty'];
                  $amt = $row['pay'] > 0 ? $row['pay'] - $dsc_amt : $row['claim_amt'];
                  $TotalTrans += $amt;
                  $T_dsc_amt += $dsc_amt;
                ?>

                  <tr>
                    <td style="font-size: 18px;"><?= $cnt; ?></td>
                    <td style="font-size: 18px;"><?= htmlspecialchars($item_services); ?></td>
                    <td style="font-size: 18px;"><?= $qty; ?></td>
                    <td style="font-size: 18px;"><?= number_format($amt, 2); ?></td>
                    <td style="font-size: 18px;"><?= $dsc_amt > 0 ? number_format($dsc_amt, 2) : ''; ?></td>
                  </tr>

                <?php } ?>

                <tr>
                  <td colspan="3"><b>Total:</b></td>
                  <td colspan="2">
                    <hr style="border:1px dotted;" />
                    <strong><?= number_format($TotalTrans, 2); ?></strong>
                    <hr style="border:1px dotted;" />
                  </td>
                </tr>

                <?php if ($T_dsc_amt > 0): ?>
                  <tr>
                    <td colspan="3"><b>Discount:</b></td>
                    <td colspan="2">
                      <hr style="border:1px dotted;" />
                      <strong><?= number_format($T_dsc_amt, 2); ?></strong>
                      <hr style="border:1px dotted;" />
                    </td>
                  </tr>
                <?php endif; ?>

              </table>

              <?php if (!empty($_SESSION['billing_remarks'])): ?>
                <div style="font-size: 16px;"><?= htmlspecialchars($_SESSION['billing_remarks']); ?></div>
              <?php endif; ?>
            </td>
          </tr>
        </table>
      </div>

    <?php } ?>



    <?php
    function numberToWords($num)
    {
      $ones = array(
        0 => "",
        1 => "one",
        2 => "two",
        3 => "three",
        4 => "four",
        5 => "five",
        6 => "six",
        7 => "seven",
        8 => "eight",
        9 => "nine",
        10 => "ten",
        11 => "eleven",
        12 => "twelve",
        13 => "thirteen",
        14 => "fourteen",
        15 => "fifteen",
        16 => "sixteen",
        17 => "seventeen",
        18 => "eighteen",
        19 => "nineteen"
      );

      $tens = array(
        0 => "",
        1 => "ten",
        2 => "twenty",
        3 => "thirty",
        4 => "forty",
        5 => "fifty",
        6 => "sixty",
        7 => "seventy",
        8 => "eighty",
        9 => "ninety"
      );

      $hundreds = array(
        "hundred",
        "thousand",
        "million",
        "billion",
        "trillion",
        "quadrillion",
        "quintillion"
      );

      $num = number_format($num, 2, ".", ",");
      $num_arr = explode(".", $num);
      $wholenum = $num_arr[0];
      $decnum = $num_arr[1];
      $whole_arr = array_reverse(explode(",", $wholenum));
      krsort($whole_arr, 1);
      $rettxt = "";

      foreach ($whole_arr as $key => $i) {
        if ($i < 20) {
          $rettxt .= $ones[$i];
        } elseif ($i < 100) {
          $rettxt .= $tens[substr($i, 0, 1)];
          $rettxt .= " " . $ones[substr($i, 1, 1)];
        } else {
          $rettxt .= $ones[substr($i, 0, 1)] . " " . $hundreds[0];
          $rettxt .= " " . $tens[substr($i, 1, 1)];
          $rettxt .= " " . $ones[substr($i, 2, 1)];
        }
        if ($key > 0) {
          $rettxt .= " " . $hundreds[$key] . " ";
        }
      }

      if ($decnum > 0) {
        $rettxt .= " and ";
        if ($decnum < 20) {
          $rettxt .= $ones[$decnum];
        } elseif ($decnum < 100) {
          $rettxt .= $tens[substr($decnum, 0, 1)];
          $rettxt .= " " . $ones[substr($decnum, 1, 1)];
        }
      }
      return $rettxt;
    }

    // Example usage:
    ?>


    <script>
      function printdeposit(deposit_reciept) {
        var printWindow = window.open('', 'PRINTOUT', 'height=400,width=600');
        printWindow.document.write('<html><head><title>PRINTOUT</title></head><body>');
        printWindow.document.write(document.getElementsByClassName(deposit_reciept)[0].innerHTML);
        printWindow.document.write('</body></html>');
        printWindow.print();
        printWindow.close();
      }
    </script>

    <script>
      /*
              function printdeposit(className) {
                var printContents = document.querySelector("." + className).innerHTML;
                var originalContents = document.body.innerHTML;
                document.body.innerHTML = printContents;
                window.print();
                document.body.innerHTML = originalContents;
              }
                */
    </script>