<?php include("../Connections/Conn.php"); ?>

<?php
session_start();
$dept_id = $_SESSION['dept_id'];
$setdate = date('Y-m-d H:i:s');


if (isset($_POST["delete_consumable"])) {
    $delete_consumable = $_POST["delete_consumable"];
    $stmt = $db->prepare("DELETE FROM patient_ap_services WHERE sn = :sn AND paystatus=0");
    $stmt->execute([':sn' => $delete_consumable]);

    if ($stmt->rowCount() > 0) {
        echo "Record deleted successfully!";
    } else {
        echo "No record found or deletion failed.";
    }
}



if (isset($_POST["view_Edit_stock"])) {

    $view_Edit_stock = $_POST["view_Edit_stock"];
    $part = explode("__", $view_Edit_stock);
    $sn = $part[0];
    $stmt = $db->prepare("SELECT * FROM patient_ap_services WHERE sn = :sn");
    $stmt->execute([':sn' => $sn]);
    $row_d = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row_d) {
        $emr = $row_d['hospital_no'];
        $item_services = $row_d['item_services'];
        $pay = $row_d['pay'];
        $drug_sn = $row_d['drug_sn'];
        $pay_mode = $row_d['pay_mode'];
    }


    $dept_id = $_SESSION['dept_id'];

    $stmt = $db->prepare("SELECT bal FROM stock_table_inven WHERE stock_sn = :stock_sn AND cust_patient_id = :cust_patient_id ORDER BY sn DESC LIMIT 1");
    $stmt->execute([':stock_sn' => $drug_sn, ':cust_patient_id' => $dept_id]);

    if ($stmt->rowCount() > 0) {
        $rw = $stmt->fetch(PDO::FETCH_ASSOC);
        $current_qty = $rw['bal'];
    } else {
        $stmt = $db->prepare("SELECT qty FROM stock_table WHERE sn = :sn");
        $stmt->execute([':sn' => $drug_sn]);

        if ($stmt->rowCount() > 0) {
            $rw = $stmt->fetch(PDO::FETCH_ASSOC);
            $current_qty = $rw['qty'];
        } else {
            $current_qty = 0;
        }
    }
?>

    <table class="table table-bordered">
        <tbody>

            <tr>
                <td>Item Services: </td>
                <td><?php echo $row_d['item_services']; ?></td>
            </tr>
            <tr>
                <td>Hospital Price: &nbsp; <?php echo $row_d['hosp_price']; ?></td>
                <td>Claim Price: &nbsp; <?php echo $row_d['claim_amt']; ?></td>
            </tr>
            <tr>
                <td><strong style="color: red;">Current Quantity / Request Quantity::</strong> </td>
                <td><?php echo $current_qty; ?> / <?php echo $row_d['qty']; ?></td>
            </tr>
            <tr>
                <td>Invoice Date / Status: </td>
                <td><?php if ($row_d['invoice_date'] == '' or $row_d['invoice_date'] == '0000-00-00') {
                        echo '';
                    } else {
                        echo date("d M Y H:i:s a ", strtotime($row_d['invoice_date']));
                    } ?> / <?php if ($row_d['invoice_status'] == 0) {
                                echo 'Invoice Pending';
                            } else {
                                echo 'Invoice Ready';
                            } ?></td>
            </tr>
            <tr>
                <td>Invoice By: </td>
                <td><?php echo $row_d['invoice_by']; ?></td>
            </tr>
            <tr>
                <td>Amount: </td>
                <td><?php echo $pay; ?>&nbsp; / Credit Status:<?php if ($row_d['cr'] == 1) {
                                                                    echo 'Credit';
                                                                } else {
                                                                    echo 'No';
                                                                } ?></td>
            </tr>
            <tr>
                <td>Paid Status: </td>
                <td><?php if ($row_d['paystatus'] == 1) {
                        echo 'Amount Paid';
                    } else {
                        echo 'Pending';
                    } ?></td>
            </tr>
            </tr>

        </tbody>
    </table>

    <div class="form_sep">
        <label for="reg_input_no" class="req">Edit Price</label>
        <input type="number" name="edit_price" id="edit_price" class="form-control" value="<?= $pay; ?>" required>
    </div>
    <?php if ($pay_mode == 'cash') { ?>
        <button type="button" class="btn btn-sm btn-success" id="save_btn" name="" onclick="save_price()">Save Price</button>
    <?php } ?>

    <input type="hidden" name="" id="ap_sn" value="<?= $sn; ?>">
    <input type="hidden" name="" id="stock_sn" value="<?= $drug_sn; ?>">


    <?php
    exit;
}



if (isset($_POST["change_price"])) {


    try {
        $db->beginTransaction();

        $updateSQL = "UPDATE patient_ap_services SET pay=:edit_price WHERE paystatus=0 AND sn=:ap_sn";
        $sql = $db->prepare($updateSQL);
        $sql->bindParam(':edit_price', $_POST['edit_price'], PDO::PARAM_STR);
        $sql->bindParam(':ap_sn', $_POST['ap_sn'], PDO::PARAM_STR);
        $sql->execute();

        /*
        $updateSQL = "UPDATE stock_table SET cash_price=:cash_price, hosp_price=:hosp_price WHERE sn=:change_price";
        $sql = $db->prepare($updateSQL);
        $sql->bindParam(':cash_price', $_POST['edit_price'], PDO::PARAM_STR);
        $sql->bindParam(':hosp_price', $_POST['edit_price'], PDO::PARAM_STR);
        $sql->bindParam(':change_price', $_POST['change_price'], PDO::PARAM_STR);
        $sql->execute();

        */
        $db->commit();
        echo 'Successfully Updated!. Close and Re-open to see updates!';
    } catch (Exception $e) {
        $db->rollBack();
        echo 'Error: ' . $e->getMessage();
        // $response['status'] = '1';
        /// echo json_encode($response);
    }
}



if (isset($_POST["patient_show_consumable"])) {

    $hospital_no = $_POST['patient_show_consumable'];
    $appointment_number = $_POST['appointment_number'];


    /*
    if (isset($_POST['service_apply_consumable'])) {
        //// past payment
      
        $from_date_cn = $_POST['from_date_cn'];
        $to_date_cn = $_POST['to_date_cn'];
        if ($_POST['select_rpt_type'] == 1) {
            $search_critera = "and paystatus='1' and date(date_entry) between '$from_date_cn' and '$to_date_cn'";
        } else {
            $search_critera = "and paystatus='0' and date(date_entry) between '$from_date_cn' and '$to_date_cn'";
        }
    } else {
*/
    $search_critera = '';
    $hos_no = $hospital_no;
    ///  $search_critera = "and paystatus='0'";
    ///  }

    $stmt = $db->query("SELECT p.serv_group,p.remarks,p.pay,p.claim_amt,p.date_entry,p.prepared_by,p.paystatus,
    p.qty,p.dsp_by,p.invoice_status,p.drug_status,p.item_services,stock_table.product_name,stock_table.dosage,stock_table.strength,p.sn,p.access 
        FROM patient_ap_services as p 
        INNER JOIN stock_table ON p.drug_sn=stock_table.sn 
        WHERE hospital_no='$hospital_no' and (invoice_status='0' or invoice_status='1' or drug_status='0') $search_critera 
        and serv_group='Consumables' and dept_id='$dept_id'");


    if ($stmt->rowCount() > 0) { ?>


        <hr>

        <form action="patient_bill.php" method="POST">

            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th data-toggle="true" width="1%"></th>
                        <th data-toggle="true">Consumable</th>
                        <th data-hide="phone,tablet" width="5%">Cash</th>
                        <th data-hide="phone,tablet" width="5%">Claim</th>
                        <th data-hide="phone,tablet" width="5%">Quantity</th>
                        <th data-hide="phone,tablet" width="10%">Date</th>
                        <th data-hide="phone,tablet" width="10%">Pay Status</th>
                        <th data-hide="phone,tablet" width="18%">Action</th>
                        <th data-hide="phone,tablet" width="15%">Entered By</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    $paying = 0;
                    $claiming = 0;
                    $invoice_set = 0;
                    $inv_print = 0;

                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $inv_print = 1;   ?>

                        <tr>
                            <td><?php echo '<input type="checkbox" value="' . $row['sn'] . '__' . $row['item_services'] . '__' . $row['pay'] . '__' . $row['claim_amt'] . '__' . $row['qty'] . '" name="inv[]" checked="checked" />' . ' ' . $row['invoice_no']; ?></td>
                            <td><?php if ($insurance_status == 1 and $row['access'] == 3) {
                                    echo '<strong style="color:#F00"> Private Drug(100% payable)</strong><br>';
                                }
                                echo $row['item_services']; ?>
                            </td>

                            <td><?php echo number_format($row['pay']); ?></td>
                            <td><?php echo number_format($row['claim_amt']); ?></td>
                            <td><?php echo $row['qty']; ?></td>
                            <td><?php echo date('d M', strtotime($row['date_entry'])) . ' ' . date('h:i a', strtotime($row['date_entry'])); ?></td>
                            <td>

                                <?php if ($row['paystatus'] == 1 and $row['pay'] > 0) { ?>
                                    <strong>PAID</strong>
                                <?php
                                } elseif ($row['paystatus'] == 0 and $row['cr'] == 1) { ?>
                                    <strong style="color:#F00;">CREDIT</strong>
                                <?php
                                } elseif ($row['paystatus'] == 0 and $row['invoice_status'] == 1) { ?>
                                    <strong style="color:#F00;">INVOICED</strong>
                                <?php } elseif ($row['paystatus'] == 0 and $row['invoice_status'] == 0) { ?>
                                    <strong style="color:#F00;">PENDING</strong>
                                <?php
                                } elseif ($row['paystatus'] == 1 and $row['pay'] == 0 and $row['claim'] > 0) { ?>
                                    <strong>POSTED</strong><br>
                                <?php } ?>

                            </td>
                            <td bgcolor="#CCFFFF">
                                <?php if ($_SESSION['unit_head'] == '1') { ?>
                                    <input type="button" name="edit" value="Edit" data-target="#modal" id="<?php echo $row["sn"] . '__' . $row['item_services']; ?>" class="btn btn-warning btn-xs view_Edit_stock" />
                                    &nbsp;|&nbsp;
                                <?php } ?>
                                <?php

                                $l = 0;
                                if ($_SESSION['unit_head'] == '1' or $row['prepared_by'] == $_SESSION['fullname']) {
                                    $l = 0;
                                } else {
                                    $l = 1;
                                }


                                if ($row['paystatus'] == 0 && $row['drug_status'] == 0) { ?>
                                    <button type="button" id="del_external" onclick="delete_xternal(<?php echo $row['sn']; ?>)" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure you want to delete this Item?')">Delete </button>

                                <?php } elseif ($row['drug_status'] == 0 and $row['paystatus'] == 1) { ?>
                                    <button type="button" id="dispense_external" onclick="dispense_xternal(<?php echo $row['sn']; ?>)" class="btn btn-warning btn-xs">Dispense Drug </button>

                                <?php } elseif ($row['drug_status'] == 1 and $row['paystatus'] == 1) {
                                    $grp = $row['sn'] . '__' . $hospital_no;
                                ?>
                                    <button type="button" id="dispense_external" onclick="reverse_pay_final(<?php echo $row['sn']; ?>)" class="btn btn-danger btn-xs"> - Reverse </button>
                                <?php } ?>




                            </td>
                            <td><?php echo $row['prepared_by']; ?> </td>
                        </tr>


                    <?php
                        if ($row['paystatus'] == 0) {
                            $claiming = $claiming + $row['claim_amt'];
                            $paying = $paying + $row['pay'];
                        }
                    }
                    //	}

                    ?>
                </tbody>
            </table>

        </form>
        <hr>




    <?php

    } else { ?>
        <br>
        <div style="font-size:13px;" align="center"><b>No Existing Consumables</b></div>
<?php }
    exit;
}


if (isset($_POST["final_submission"])) {

    $stock_sn = $_POST["final_submission"];
    $insurance_no = $_POST["insurance_no"];
    $insurance = $_POST["insurance_type"];
    $dept_id = $_POST["dept_id"];
    $new_qty = $_POST["new_qty"];
    $appt_no = $_POST["appt_no"];
    $qty = $_POST["new_qty"];
    $fullname = $_POST["fullname"];
    $remarks = 'Deducted: ' . $_POST["hospital_num"];
    $hospital_no = $_POST["hospital_num"];


    $sql = "SELECT bal FROM stock_table_inven WHERE stock_sn = :stock_sn AND cust_patient_id = :cust_patient_id ORDER BY sn DESC LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':stock_sn', $stock_sn, PDO::PARAM_STR);
    $stmt->bindParam(':cust_patient_id', $dept_id, PDO::PARAM_STR);
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        $rw = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($rw['bal'] >= $new_qty) {
            $c_qty = $rw['bal'] - $new_qty;
            $qtyIN = 0;
            $qtyOUT = $new_qty;
            $cust_patient_type = 'IN';
            $captured_date = date('Y-m-d H:i:00');
            $enter_by = $fullname; ///['fullname'];
            $cust_patient_id = $dept_id;
            $sale_sn = '';
            $return_status = 0;
            $batch = '';

            $selectSQL = "SELECT stock_sn FROM stock_table_inven 
									WHERE stock_sn = :stock_sn 
									AND inven_desc = :inven_desc 
									AND qtyIN = :qtyIN 
									AND qtyOUT = :qtyOUT 
									AND enter_by = :enter_by 
									AND captured_date = :captured_date 
									AND cust_patient_id = :cust_patient_id";
            $selectStmt = $db->prepare($selectSQL);

            // Bind parameters
            $selectStmt->bindParam(':stock_sn', $stock_sn, PDO::PARAM_STR);
            $selectStmt->bindParam(':inven_desc', $remarks, PDO::PARAM_STR);
            $selectStmt->bindParam(':qtyIN', $qtyIN, PDO::PARAM_STR);
            $selectStmt->bindParam(':qtyOUT', $qtyOUT, PDO::PARAM_STR);
            $selectStmt->bindParam(':enter_by', $enter_by, PDO::PARAM_STR);
            $selectStmt->bindParam(':captured_date', $captured_date, PDO::PARAM_STR);
            $selectStmt->bindParam(':cust_patient_id', $cust_patient_id, PDO::PARAM_STR);

            // Execute the SELECT statement
            $selectStmt->execute();

            // Check if any rows were returned
            if ($selectStmt->rowCount() == 0) {


                try {
                    // Begin a transaction
                    $db->beginTransaction();

                    $stmt = $db->query("SELECT * FROM stock_table WHERE sn='$stock_sn'");
                    if ($stmt->rowCount() > 0) {
                        $row_drug = $stmt->fetch(PDO::FETCH_ASSOC);

                        $item_service = $row_drug['product_name'];
                        $nhis_price = $row_drug['nhis_price'];
                        $hosp_price = $row_drug['hosp_price']; //* $new_qty;
                        $ext_price = $row_drug['cash_price']; ///* $new_qty;
                        $item_sn = $row_drug['sn'];
                        $price_table = $row_drug['stock_table'];
                        $category = $row_drug['category'];
                        if ($category == '') {
                            $category = 'Consumables';
                        }
                        $coverage = $row_drug['coverage'];
                        $part = explode("/", $coverage);
                        $private = $part[0];
                        $nhis = $part[1];
                        $nhis = strtoupper($nhis);
                        ///$insurance_type=$row_drug['insurance_type'];
                        $dept_id = $_SESSION['dept_id'];

                        include_once("price_calc.php");

                        $amt_paying = $amt_paying * $new_qty;
                        $claim_amt = $claim_amt * $new_qty;




                        if ($insurance != 'Private(Self)') {
                            $amt_paying = 0;
                            $stmt = $db->query("SELECT price FROM hmo_stocks_tariff WHERE stock_sn='$stock_sn'and price>0 and hmo='$insurance_no'");
                            if ($stmt->rowCount() > 0) {
                                $row_dx = $stmt->fetch(PDO::FETCH_ASSOC);
                                $claim_amt = $row_dx['price'] * $new_qty;
                                $ccop_int_charge = 0;
                            }
                        }
                        include("utilities.php");
                        $invoice_no = INV();
                        $qty = $new_qty;
                        $invoicedate = date('Y-m-d H:i:s');
                        $invoice_by = $_SESSION['fullname'];
                        $paystatus = '0';
                        $cr = '1';
                        $one = '1';
                        $zero = '0';
                        $batch = 'dsp';
                        $drug_status = '1';
                        $serv_group = 'Consumables';
                    }

                    $insertSQL2 = "INSERT INTO patient_ap_services(
												app_no, hospital_no, serv_group, cat_type, dept_id, drug_sn, 
												item_services, hosp_price, claim_amt, qty, remarks, drug_status, 
												invoice_status, invoice_no, invoice_date, invoice_by, prepared_by, 
												date_entry, transact_date, pay, pay_mode, paystatus, cr
											  ) VALUES (
												:app_no, :hospital_no, :serv_group, :cat_type, :dept_id, :drug_sn, 
												:item_services, :hosp_price, :claim_amt, :qty, :remarks, :drug_status, 
												:invoice_status, :invoice_no, :invoice_date, :invoice_by, :prepared_by, 
												:date_entry, :transact_date, :pay, :pay_mode, :paystatus, :cr
											  )";
                    $insertStmt2 = $db->prepare($insertSQL2);

                    // Bind parameters for the second INSERT
                    $insertStmt2->bindParam(':app_no', $appt_no, PDO::PARAM_STR);
                    $insertStmt2->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
                    $insertStmt2->bindParam(':serv_group', $serv_group, PDO::PARAM_STR);
                    $insertStmt2->bindParam(':cat_type', $category, PDO::PARAM_STR);
                    $insertStmt2->bindParam(':dept_id', $dept_id, PDO::PARAM_STR);
                    $insertStmt2->bindParam(':drug_sn', $item_sn, PDO::PARAM_STR);
                    $insertStmt2->bindParam(':item_services', $item_service, PDO::PARAM_STR);
                    $insertStmt2->bindParam(':hosp_price', $hosp_price, PDO::PARAM_STR);
                    $insertStmt2->bindParam(':claim_amt', $claim_amt, PDO::PARAM_STR);
                    $insertStmt2->bindParam(':qty', $qty, PDO::PARAM_STR);
                    $insertStmt2->bindParam(':remarks', $remarks, PDO::PARAM_STR);
                    $insertStmt2->bindParam(':drug_status', $zero, PDO::PARAM_STR);
                    $insertStmt2->bindParam(':invoice_status', $one, PDO::PARAM_STR);
                    $insertStmt2->bindParam(':invoice_no', $invoice_no, PDO::PARAM_STR);
                    $insertStmt2->bindParam(':invoice_date', $invoicedate, PDO::PARAM_STR);
                    $insertStmt2->bindParam(':invoice_by', $invoice_by, PDO::PARAM_STR);
                    $insertStmt2->bindParam(':prepared_by', $_SESSION['fullname'], PDO::PARAM_STR);
                    $insertStmt2->bindParam(':date_entry', $invoicedate, PDO::PARAM_STR);
                    $insertStmt2->bindParam(':transact_date', $blank, PDO::PARAM_STR);
                    $insertStmt2->bindParam(':pay', $amt_paying, PDO::PARAM_STR);
                    $insertStmt2->bindParam(':pay_mode', $pay_mode, PDO::PARAM_STR);
                    $insertStmt2->bindParam(':paystatus', $paystatus, PDO::PARAM_STR);
                    $insertStmt2->bindParam(':cr', $cr, PDO::PARAM_STR);

                    // Execute the second INSERT statement
                    $insertStmt2->execute();


                    // Commit the transaction
                    $db->commit();

                    echo 'Record Saved Successfully';
                } catch (Exception $e) {
                    // Roll back the transaction if something failed
                    $db->rollBack();
                    echo 'Failed to save record: ' . $e->getMessage();
                }
            } else {
                echo 'Already Exist!';
            }
        } else {
            echo 'Invalid Quantity Available';
        }
    } else {
        echo 'No Stock Available';
    }
}




?>