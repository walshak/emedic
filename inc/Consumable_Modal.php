<?php
session_start();

include("../Connections/Conn.php");
include('../doctor/objects.php');
$hosp_no = $hospital_num = $_POST['patient_show_consumable'];
$dept_id = $_SESSION['dept_id'];

$patient_info = $Patient->getByHospitalNo($hosp_no);

$ap_type = 0;
if (!empty($patient_info)) {
    $dob = $patient_info->dob;
    $visit_status = $patient_info->visit_status;
    $patient_name =  $patient_info->surname . ' ' . $patient_info->fname;
    $sex = $patient_info->gender;
    $insurance_type = $patient_info->insurance_type;
    $insurancen_no = $patient_info->hmo_no;
    $interest = $patient_info->interest;
    $token = $patient_info->token;
    $add_minus = $patient_info->add_minus;
    $payment_mode = $patient_info->payment_mode;
    $services_access = $patient_info->services_access;
    $manage_hx = $patient_info->patient_manage_hx;
    $patient_manage_by = $patient_info->patient_manage_by;
    $vip = $patient_info->vip;
}
?>





<div align='center'>
    <h3>Sales of Departmental/Unit Consumables, Drugs, and Other Items Under Your Requisition. </h3>
    <hr>
</div>
<?php
$stmt = $db->query("SELECT distinct s.product_name, s.sn FROM stock_table s 
inner join stock_table_inven c on s.sn=c.stock_sn 
where c.cust_patient_id='$dept_id' and c.bal>0 and s.cash_price>0 order by s.product_name");
if ($stmt->rowCount() > 0) {
?>
    <table width="100%">
        <tr>
            <td style="padding-right:10px; ">





                <div class="form_sep">
                    <label>Select Consumables</label><br>
                    <select name="stock_sn_sn" id="stock_sn_sn" class="input-sm chosen-select" style="width:100%;" required>

                        <option selected value="">-- Select Stock you wish to Deduct--</option>
                        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $stock_sn = $row['sn']; ?>
                            <?php
                            $stmtx = $db->query("SELECT bal FROM stock_table_inven 
where cust_patient_id='$dept_id' and stock_sn='$stock_sn' ORDER BY sn DESC LIMIT 1");
                            if ($stmtx->rowCount() > 0) {
                                $rocw = $stmtx->fetch(PDO::FETCH_ASSOC);
                                $bal = $rocw['bal'];
                            } else {
                                $bal = 0;
                            } ?>
                            <?php if ($bal > 0) { ?>
                                <option value="<?php echo $row["sn"]; ?>"><?php echo  $row['product_name']; ?></option>
                            <?php } else { ?>
                                <option value="">No Stock Available to Deduct!</option>

                        <?php }
                        } ?>

                    </select>
                </div>



            </td>
            <td style="padding-right:10px; ">
                <div class="form_sep">
                    <label>Qty</label>
                    <input type="number" id="new_qty" name="new_qty" class="form-control" value="1" data-required="true" min="1" required>
                </div>
            </td>
            <td>
                <label>.</label><br>
                <button type="button" id="remove_form_stock_final" onClick="remove_form_stock_final()" class="btn btn-warning">Add & Delivered</button>

            </td>
        </tr>
    </table>

    <input type="hidden" name="dept_id" id="dept_id" value="<?php echo $dept_id; ?>" />
    <input type="hidden" name="hospital_num" id="hospital_num" value="<?php echo $hospital_num; ?>" />
    <input type="hidden" name="fullname" id="fullname" value="<?php echo $_SESSION['fullname']; ?>" />
    <input type="hidden" name="insurance_no" id="insurance_no" value="<?php echo $insurancen_no; ?>" />
    <input type="hidden" name="insurance_type" id="insurance_type" value="<?php echo $insurance_type; ?>" />
    <input type="hidden" name="add_minus" id="add_minus" value="<?php echo $add_minus; ?>" />
    <input type="hidden" name="payment_mode" id="payment_mode" value="<?php echo $payment_mode; ?>" />
    <input type="hidden" name="appt_no" id="appt_no" value="<?php echo $appointment_number; ?>" />
    <input type="hidden" name="interest" id="interest" value="<?php echo $interest; ?>" />
    <input type="hidden" name="services_access" id="services_access" value="<?php echo $services_access; ?>" />


<?php } else { ?>
    <b>No Stock Available to Deduct!</b>
<?php } ?>



<?php

$stmt = $db->query("SELECT p.serv_group,p.remarks,p.pay,p.claim_amt,p.date_entry,p.prepared_by,p.paystatus,
    p.qty,p.dsp_by,p.invoice_status,p.drug_status,p.item_services,stock_table.product_name,stock_table.dosage,stock_table.strength,p.sn,p.access 
        FROM patient_ap_services as p 
        INNER JOIN stock_table ON p.drug_sn=stock_table.sn 
        WHERE hospital_no='$hospital_num' and (invoice_status='0' or invoice_status='1' or drug_status='0') $search_critera 
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
?>

<script>
    $("#remove_form_stock_final").click(function() {


        var stock_sn_sn = document.getElementById('stock_sn_sn').value;
        var new_qty = document.getElementById('new_qty').value;
        //var deduction_mode = document.getElementById('deduction_mode').value;
        //var remark = document.getElementById('remark').value;
        var hospital_num = document.getElementById('hospital_num').value;
        var dept_id = document.getElementById('dept_id').value;
        var fullname = document.getElementById('fullname').value;
        var insurance_no = document.getElementById('insurance_no').value;
        var insurance_type = document.getElementById('insurance_type').value;
        var payment_mode = document.getElementById('payment_mode').value;
        var appt_no = document.getElementById('appt_no').value;
        var services_access = document.getElementById('services_access').value;
        var add_minus = document.getElementById('add_minus').value;
        var interest = document.getElementById('interest').value;

        $.ajax({
            url: "../inc/fetch_consumable.php",
            data: {
                final_submission: stock_sn_sn,
                new_qty: new_qty,
                hospital_num: hospital_num,
                dept_id: dept_id,
                insurance_no: insurance_no,
                appt_no: appt_no,
                insurance_type: insurance_type,
                payment_mode: payment_mode,
                services_access: services_access,
                add_minus: add_minus,
                interest: interest,
                fullname: fullname
            },
            type: 'POST',
            success: function(response) {

                toastr.success(response, 'Attention', {
                    timeOut: 5000
                })
            }
        });


    });
</script>