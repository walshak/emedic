<?php
if (isset($_POST['action'])) {
    if ($_POST['action'] == 'delete_patient_service') {
        include 'Connections/Conn.php';
        include 'doctor/objects.php';
        include 'doctor/helpers.php';
        header('Content-Type: application/json');
        $sn = cleanInput($_POST['sn']);
        $hospital_no = cleanInput($_POST['hospital_no']);

        $status = 401;
        $message = 'Error: Service is not removed...';
        $delete = $db->prepare("DELETE FROM patient_ap_services WHERE sn = ? AND paystatus = '0'");
        $deleted = $delete->execute([$sn]);
        if ($deleted) {
            $message = 'success: Service has been removed...';
            $status = 200;
        }

        echo json_encode(['status' => $status, 'message' => $message]);
    }

    if ($_POST['action'] == 'delete_patient_review_service') {
        include 'Connections/Conn.php';
        include 'doctor/objects.php';
        include 'doctor/helpers.php';
        header('Content-Type: application/json');
        $sn = cleanInput($_POST['sn']);
        // $hospital_no = $_POST['hospital_no'];
        $app_no = cleanInput($_POST['app_no']);

        $status = 401;
        $message = 'Error: Service is not removed...';
        $delete = $db->prepare("DELETE FROM patient_ap_services WHERE sn = ? AND paystatus = '0'");
        $deleted = $delete->execute([$sn]);
        if ($deleted) {
            $message = 'success: Service has been removed...';
            $status = 200;

            $delete = $db->prepare(
                "UPDATE notes SET status = '0' WHERE service_id = ? AND app_no = ? ",
            );
            $deleted = $delete->execute([$sn, $app_no]);
        }

        echo json_encode(['status' => $status, 'message' => $message]);
    }

    if ($_POST['action'] == 'edit_patient_review_service') {
        include 'Connections/Conn.php';
        include 'doctor/objects.php';
        include 'doctor/helpers.php';
        header('Content-Type: application/json');
        $sn = cleanInput($_POST['sn']); /// service_id | drug_sn
        $app_no = cleanInput($_POST['app_no']);

        $status = 401;
        $id = null;
        $note = '';
        $message = 'Error: Service is not removed...';
        $stmt = $db->prepare(
            "SELECT *  FROM notes WHERE  app_no = ? AND service_id = ? AND status = '1' ORDER by sn DESC ",
        );
        $stmt->execute([$app_no, $sn]);
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            $note = $row['notes'];
            $id = $row['sn'];
            $status = 200;
        }

        echo json_encode(['status' => $status, 'notes' => $note, 'id' => $id]);
    }

    if ($_POST['action'] == 'update_service_review') {
        include 'Connections/Conn.php';
        include 'doctor/objects.php';
        include 'doctor/helpers.php';
        header('Content-Type: application/json');
        $sn = cleanInput($_POST['sn']);
        $app_no = cleanInput($_POST['app_no']);
        $service_id = cleanInput($_POST['service_id']);
        $notes = cleanInput($_POST['notes']);

        $status = 400;
        $id = null;
        $note = '';
        $message = 'Error: Service is not saved...';
        if (!empty($notes)) {
            $update = editNotes($db, $sn, $notes);

            if ($update == true) {
                $status = 200;
                $message = 'Success : Service note saved';
            }
        }

        echo json_encode(['status' => $status, 'message' => $message]);
    }

    exit();
}

/// loop and add services selected
if (isset($_POST['add-nursing-services-btn'])) {
    $error_status = 1;
    $error_msg = 'Error : Something went wrong';

    $save = false;
    foreach ($_POST['services_name'] as $services_name) {
        $item_sn = $services_name;
        //[ 'nurse_care' ];
        $stmt = $db->prepare("SELECT * FROM prices_table where sn='$item_sn'");
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $rowCat = $stmt->fetch(PDO::FETCH_ASSOC);
            $service_name = $rowCat['item_service'];
            $coverage = $rowCat['coverage'];
            $part = explode('/', $coverage);
            $private = $part[0];
            $nhis = $part[1];
            $nhis = strtoupper($nhis);
            $insurance_type = $rowCat['insurance_type'];
            $hosp_price = $rowCat['hosp_price'];
            $ext_price = $rowCat['ext_price'];
            $nhis_price = $rowCat['nhis_price'];
            $cat_type = $rowCat['price_table'];
            $serv_group = $rowCat['category'];
            $dept_id = $rowCat['dept_id'];

            ///------------------------------------------------
            $setdate = date('Y-m-d H:i:s');
            $invoice_no = date('m') . sprintf('%006d', mt_rand(00000, 99999));
            $fullname = $_SESSION['fullname'];
            include '../inc/price_calc.php'; ///// ISSSSSUEESSSS DEY HERE

            /* $claim_amt=0;	 $amt_paying=$hosp_price; $pay_mode='cash';  $item_amt=$hosp_price	; */
            $save = build_query_save(
                $db,
                $app_no,
                $hosp_no,
                $service_access,
                $serv_group,
                $cat_type,
                $dept_id,
                $item_sn,
                $service_name,
                $claim_amt,
                $item_amt,
                $invoice_no,
                $fullname,
                $setdate,
                $amt_paying,
                $pay_mode,
            );
        }
    }

    if ($save) {
        $error_status = 2;
        $error_msg = 'Success : Service(s) added successfully...';
    }
}
?>

<div class="modal inmodal" id="add_service_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="<?php echo $editFormAction; ?>" method="POST" id="subject" name="subject" enctype="multipart/form-data" onsubmit="return confirm('Please confirm your action by clicking on OK button to proceed or click on CANCEL button to abort')">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="">Add Service</h4>
                </div>
                <div class="modal-body" id="add_service_modal_body" style="min-height: 300px">

                    <div class="form_sep">
                        <label for="reg_select" class="req" style="font-size:14px">Select Services you wish to add</label>
                        <?php try {
                            if ($_SESSION['rights'] == 'NS') {
                                $query_rstSelect =
                                    "SELECT * FROM prices_table WHERE price_table='Nursing Services' ORDER BY item_service";
                            } else {
                                $query_rstSelect =
                                    'SELECT * FROM prices_table ORDER BY item_service';
                            }

                            $stmt = $db->prepare($query_rstSelect);
                            $stmt->execute();
                            ?>
                            <div class="form-group">
                                <label for="reg_input_no" class="">&nbsp;<small>selection of multiple items is enabled</small></label>
                                <select name="services_name[]" data-placeholder="Select.." class="chosen-select" multiple style="width:350px;" tabindex="4">
                                    <?php while ($row_emp = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                        <option value="<?php echo $row_emp[
                                            'sn'
                                        ]; ?>"><?php echo $row_emp['item_service']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        <?php
                        } catch (PDOException $e) {
                            echo 'Error: ' . $e->getMessage();
                        } ?>
                    </div>

                    <hr>

                    <input type="hidden" name="app_no" value="<?php echo $app_no; ?>" />
                    <input type="hidden" name="hosp_no" value="<?php echo $hosp_no; ?>" />
                    <input type="hidden" name="ap_type" value="<?php echo $ap_type; ?>" />
                    <input type="hidden" name="interest" value="<?php echo $interest; ?>" />
                    <input type="hidden" name="insurance_type" value="<?php echo $insurance_type; ?>" />

                    <?php
                    $sn = 1;
                    $total_srvices = 0;
                    $pending_services = $PatientApService->get(
                        [
                            'hospital_no' => $hosp_no,
                            'paystatus' => '0',
                            'created_by' => $_SESSION['id'],
                        ],
                        true,
                    );
                    if (count($pending_services) > 0) {
                        echo '
                                    <div id="patient_services_table_wrap">
                                        <table class="table table-bordered table-striped table-hover table-responsive">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Service</th>
                                                    <th>QTY</th>
                                                    <th>PRICE</th>
                                                    <th>STATUS</th>
                                                </tr>
                                            </thead>
                                        <tbody>
                                    ';
                        foreach ($pending_services as $key => $pending_service) {
                            $total_srvices += $pending_service->pay;
                            echo '
                                            <tr>
                                                <td>' .
                                $sn++ .
                                '</td>
                                                <td>' .
                                $pending_service->item_services .
                                '</td>
                                                <td>' .
                                $pending_service->qty .
                                '</td>
                                                <td>&#8358; ' .
                                number_format($pending_service->pay) .
                                '</td>
                                                <td>' .
                                ($pending_service->paystatus == '1' ? 'Paid' : 'Not Paid') .
                                '</td>
                                                <td>
                                                   <a href="#" class="btn btn-xs btn-danger 
                                                     delete-added-services-btn" arial-hosp-no="' .
                                $pending_service->hospital_no .
                                '" 
                                                     arial-sn="' .
                                $pending_service->sn .
                                '"><i class="fa fa-trash"></i> Cancel</a></td>
                                            </tr>
                                            
                                        ';
                        }
                        echo '
                                    <tr>
                                    <td></td>
                                    <td><b>Total</b></td>
                                    <td></td>
                                    <td><b>&#8358; ' .
                            number_format($total_srvices) .
                            '</b></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                        </tbody>
                                    </table>
                                    </div>
                                ';
                    }
                    ?>
                </div>
                <div class="modal-footer">
                    <div class="form_sep">
                        <div class="pull-right">
                            <button class="btn btn-primary" name="add-nursing-services-btn">Add Selected Services</button>
                            <button class="btn btn-danger" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>