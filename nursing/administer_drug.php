<?php session_start();
include("../Connections/Conn.php");

if (isset($_POST['saveDrugChart'])):
    header('Content-Type: application/json');

    try {
        // Get POST data
        $hospital_no = $_POST['hospital_no'];
        $Dosage = $_POST['dosage_value']; // Not saving? We'll check
        $frequency_value = $_POST['frequency_value'];
        $route_value = $_POST['dura_value'];
        $drugToChart = $_POST['drugToChart'];
        $toStartDate = $_POST['toStartDate'];

        if (empty($drugToChart)) {
            throw new Exception("No drug selected.");
        }

        // Parse drug info
        $part = explode("__", $drugToChart);
        if (count($part) < 3) {
            throw new Exception("Invalid drug format.");
        }
        $drug_name = $part[0];
        $drug_sn = $part[1];
        $sale_sn = $part[2];

        $adm_id = 0;
        $status = 'On-going';

        // Enable PDO error mode
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check admission record
        $stmt = $db->prepare("SELECT * FROM admission AS adm  
            WHERE adm_status = 3 AND hospital_no = ? ORDER BY sn DESC LIMIT 1");
        $stmt->execute([$hospital_no]);

        if ($stmt->rowCount() > 0) {
            $admission_info = $stmt->fetch(PDO::FETCH_ASSOC);
            $sale_sn = $admission_info['sn'];
            $adm_id = $admission_info['sn'];
        }

        // Check if already charted
        $stmt = $db->prepare("SELECT sn FROM drug_charts WHERE drug_sn = :drug_sn AND adm_id = :adm_id AND hospital_no = :hospital_no");
        $stmt->bindParam(':drug_sn', $drug_sn);
        $stmt->bindParam(':adm_id', $adm_id, PDO::PARAM_INT);
        $stmt->bindParam(':hospital_no', $hospital_no);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            // Insert new chart record
            $stmt = $db->prepare("INSERT INTO drug_charts (
                hospital_no, drug_sn, sale_sn, drug_name, status, frequent, route, sDate, date_time, captured_by, adm_id, dosage
            ) VALUES (
                :hospital_no, :drug_sn, :sale_sn, :drug_name, :status, :frequency_value, :route_value, :toStartDate, NOW(), :captured_by, :adm_id, :dosage
            )");

            $stmt->bindParam(':hospital_no', $hospital_no);
            $stmt->bindParam(':drug_sn', $drug_sn);
            $stmt->bindParam(':sale_sn', $sale_sn);
            $stmt->bindParam(':drug_name', $drug_name);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':frequency_value', $frequency_value);
            $stmt->bindParam(':route_value', $route_value);
            $stmt->bindParam(':toStartDate', $toStartDate);
            $stmt->bindParam(':captured_by', $_SESSION['fullname']);
            $stmt->bindParam(':adm_id', $adm_id, PDO::PARAM_INT);
            $stmt->bindParam(':dosage', $Dosage);

            if ($stmt->execute()) {
                $message = "Saved successfully.";
            } else {
                $errorInfo = $stmt->errorInfo();
                throw new Exception("Not Saved: " . $errorInfo[2]);
            }
        } else {
            $message = "Drug already charted.";
        }

        echo json_encode(["message" => $message]);
        exit;
    } catch (PDOException $e) {
        echo json_encode([
            "message" => "Database Error: " . $e->getMessage(),
            "line" => $e->getLine(),
            "file" => $e->getFile()
        ]);
        exit;
    } catch (Exception $e) {
        echo json_encode([
            "message" => "General Error: " . $e->getMessage(),
            "line" => $e->getLine(),
            "file" => $e->getFile()
        ]);
        exit;
    }
endif;




if (isset($_POST['drug_remarks'])):
    $remarks = $_POST['remarks'];
    $hospital_no = $_POST['hosp'];
    $drug_chart_id = $_POST['drug_chart'];

    header('Content-Type: application/json');
    $message = 'Something went wrong';

    $stmt = $db->prepare("UPDATE drug_charts SET remarks=:remarks,discontinued_by=:discontinued_by WHERE sn = :drug_chart_id AND hospital_no = :hospital_no ");
    $stmt->bindParam(':remarks', $remarks, PDO::PARAM_STR);
    $stmt->bindParam(':discontinued_by', $_SESSION['fullname'], PDO::PARAM_STR);
    $stmt->bindParam(':drug_chart_id', $drug_chart_id, PDO::PARAM_STR);
    $stmt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
    $saved = $stmt->execute();
    $message = $saved ? "Remarks Entered Successful" : "Not Successful";

    echo json_encode(["message" =>  $message]);
    exit;

endif;



if (isset($_POST['discontinueDrug'])):
    $reasonToDiscontinue = $_POST['reasonToDiscontinue'];
    $hospital_no = $_POST['hosp'];
    $drug_chart_id = $_POST['drug_chart'];
    $date_discontinued = date('Y-m-d');

    header('Content-Type: application/json');
    $message = 'Something went wrong';
    $status = "discontinued";

    $stmt = $db->prepare("SELECT sn FROM `drug_charts` WHERE sn = :drug_chart_id AND hospital_no = :hospital_no AND status = :status ");
    $stmt->bindParam(':drug_chart_id', $drug_chart_id, PDO::PARAM_STR);
    $stmt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $saved = $stmt->execute();
    if ($stmt->rowCount() == 0) {
        $stmt = $db->prepare("UPDATE drug_charts SET status =:status , remarks=:reasonToDiscontinue, 
                date_discontinued=:date_discontinued, discontinued_by=:discontinued_by WHERE sn = :drug_chart_id AND hospital_no = :hospital_no ");
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->bindParam(':reasonToDiscontinue', $reasonToDiscontinue, PDO::PARAM_STR);
        $stmt->bindParam(':date_discontinued', $date_discontinued, PDO::PARAM_STR);
        $stmt->bindParam(':discontinued_by', $_SESSION['fullname'], PDO::PARAM_STR);
        $stmt->bindParam(':drug_chart_id', $drug_chart_id, PDO::PARAM_STR);
        $stmt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
        $saved = $stmt->execute();
        $message = $saved ? "Discontinued" : "Not Discontinued";
    } else {
        $message = "Discontinued before";
    }

    echo json_encode(["message" =>  $message]);
    exit;

endif;

if (isset($_POST['administerDrug'])):
    $drug_chart_timing = $_POST['chart_timing'];
    $dosage_given = $_POST['dosage_given'];
    $medication_routes = $_POST['medication_routes'];
    $time_given = $_POST['timeGeven'];
    $drug_sn = $_POST['drug_sn'];
    $sale_sn = $_POST['sale_sn'];
    $hospital_no = $_POST['hosp'];
    $drug_chart = $_POST['drug_chart'];
    $day = $_POST['day'];
    $date_given = date('Y-m-') . '' . $day;

    if ($medication_routes != '' && $dosage_given != '') {
        $dosage_given = $dosage_given . ' (' . $medication_routes . ')';
    }
    header('Content-Type: application/json');
    $message = 'Something went wrong';

    $currentMonthYear = date('m_Y'); // Format: MM_YYYY
    $tableName = "drug_charts_inven_" . $currentMonthYear;

    $stmt = $db->prepare("SELECT sn FROM drug_charts_inven WHERE sale_sn = :sale_sn AND drug_chart_timing = :drug_chart_timing AND date_given = :date_given");
    $stmt->bindParam(':sale_sn', $sale_sn, PDO::PARAM_STR);
    $stmt->bindParam(':drug_chart_timing', $drug_chart_timing, PDO::PARAM_STR);
    $stmt->bindParam(':date_given', $date_given, PDO::PARAM_STR);
    $saved = $stmt->execute();
    if ($stmt->rowCount() == 0) {

        $captured_by = $_SESSION['fullname'];
        $stmt = $db->prepare("INSERT INTO drug_charts_inven ( `hospital_no`, `drug_chart_timing`, `time_given`, `drug_sn`, 
            `sale_sn`, `date_given`, `captured_by`, `drug_chart`, `dosage_given`) VALUES ('$hospital_no', $drug_chart_timing, '$time_given', $drug_sn, 
            $sale_sn, '$date_given', '$captured_by', $drug_chart, '$dosage_given') ");
        $stmt->execute();

        $stmt = $db->prepare("UPDATE drug_charts SET dosage = ? WHERE sn = ? ");
        $stmt->execute([$dosage_given, $drug_chart]);


        $message = $saved ? "Saved" : "Not Saved";
    } else {
        $message = 'Already given';
    }
    echo json_encode(["message" =>  $message]);
    exit;

endif;



if (isset($_POST['saveDrugChartTiming'])):

    $hospital_no = $_POST['hospital_no'];
    $drug_chart_id = $_POST['drug_chart'];
    $timing = $_POST['drugChatTiming'];
    header('Content-Type: application/json');
    $message = 'Something went wrong';


    $stmt = $db->prepare("SELECT id FROM `drug_chart_timing` WHERE drug_chart_id = :drug_chart_id AND timing = :timing ");
    $stmt->bindParam(':drug_chart_id', $drug_chart_id, PDO::PARAM_STR);
    $stmt->bindParam(':timing', $timing, PDO::PARAM_STR);
    $saved = $stmt->execute();
    if ($stmt->rowCount() == 0) {
        $stmt = $db->prepare("INSERT INTO `drug_chart_timing`( `drug_chart_id`, `timing`) VALUES ('$drug_chart_id', '$timing') ");
        $stmt->execute();
        $message = $saved ? "Saved" : "Not Saved";
    }
    echo json_encode(["message" =>  $message]);
    exit;

endif;


if (isset($_POST['loadDrugChartTiming'])):
    $hospital_no = $_POST['hospital_no'];
    $drug_chart = $_POST['drug_chart'];

?>

    <!-- <form action="patient.php?hosp_no=" method="POST">                     -->
    <div class="form_sep">
        <label for="reg_input_no" class="req">Timing </label>
        <select name="drugChatTiming" id="drugChatTiming" class="form-control" required>
            <option value="" selected>--Select--</option>
            <option value="STAT">STAT</option>
            <option value="NOCTE">NOCTE</option>
            <?php
            for ($i = 1; $i <= 24; $i++):
                $timing = ($i > 12 ? $i - 12 : $i);
                $ampm =  ($i > 12 ? 'PM' : 'AM');
            ?>
                <option value="<?= $timing . $ampm; ?>"> <?= $timing . ' ' . $ampm; ?></option>
            <?php endfor;
            ?>
            <option value="once-daily">Once Daily</option>
            <option value="twice-daily">Twice Daily</option>
            <option value="Morning">Morning</option>
            <option value="Afternoon">Afternoon</option>
            <option value="Evening">Evening</option>
            <option value="every-6-hours">Every 6 Hours</option>
            <option value="every-8-hours">Every 8 Hours</option>
            <option value="every-12-hours">Every 12 Hours</option>
            <option value="weekly">Once Weekly</option>
            <option value="bi-weekly">Twice Weekly</option>
            <option value="monthly">Once Monthly</option>
        </select>
    </div>



    <br />
    <button type="submit" class="btn btn-primary" name="saveDrugChartTiming" id="saveDrugChartTiming"
        drug_chart="<?= $drug_chart; ?>" hosp="<?= $hospital_no; ?>"
        style="display: block; width:100%;">Save</button>

    <!-- </form> -->

<?php
endif;

if (isset($_POST['loadNewDrugChart'])):
    $hospital_no = $_POST['hospital_no'];
?>

    <div class="form_sep">
        <label for="reg_input_no" class="req">Select Drug </label>
        <select data-placeholder="Choose a Drug..." class="chosen-select" name="drugToChart" id="drugToChart" tabindex="2" required>

            <!--  <select name="drugToChart" id="drugToChart" class="form-control"  required>-->
            <option value="" selected>--Select--</option>
            <?php
            $cat_type = 'Pharmacy';
            $drug_status = '1';

            $stmtt = $db->prepare("SELECT * FROM stock_table WHERE stock_table=:stock_table  order by  product_name");


            //$stmtt =$db->prepare("SELECT distinct app_no,drug_sn,item_services,remarks,sn,prepared_by,drug_status,paystatus,date_entry FROM patient_ap_services 
            //  WHERE hospital_no=:hospital_no and cat_type=:cat_type and drug_status=:drug_status order by sn desc limit 30");
            // $stmtt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
            $stmtt->bindParam(':stock_table', $cat_type, PDO::PARAM_STR);
            //$stmtt->bindParam(':drug_status', $drug_status, PDO::PARAM_STR);
            $stmtt->execute();
            if ($stmtt->rowCount() > 0):
                while ($roww = $stmtt->fetch(PDO::FETCH_ASSOC)):
                    $app_no = $roww['app_no'];
            ?>
                    <option value="<?= $roww['product_name'] . '__' . $roww['sn'] . '__' . $roww['sn']; ?>"> <?= $roww['product_name']; ?></option>
            <?php
                endwhile;
            endif;
            ?>
        </select>
    </div>
    <br />

    <div class="form_sep">
        <label for="reg_input_no" class="">Dosage </label>
        <input type="text" id="dosage_value" class="form-control" required>
    </div>


    <div class="form_sep">
        <label for="reg_input_no" class="">Frequency </label>
        <input type="text" id="frequency_value" class="form-control" required>
    </div>


    <div class="form_sep">
        <label for="reg_input_no" class="">Duration </label>
        <input type="text" id="dura_value" class="form-control" required>
    </div>




    <br />
    <div class="form_sep">
        <label for="reg_input_no" class="req">Start Date </label>
        <input type="date" name="toStartDate" id="toStartDate" class="form-control" value="<?= date('Y-m-d'); ?>" />
        <input type="hidden" name="drugToChartHospital_no" id="drugToChartHospital_no" class="form-control" value="<?= $hospital_no; ?>" />
    </div>


    <br />
    <button type="submit" class="btn btn-primary" name="saveDrugChart" id="saveDrugChart" style="display: block; width:100%;">Save</button>
<?php
endif;

?>

<script src="../js/plugins/chosen/chosen.jquery.js"></script>
<script>
    $('.chosen-select').chosen({
        width: "100%"
    });

    $('#data_1 .input-group.date').datepicker({
        todayBtn: "linked",
        keyboardNavigation: false,
        forceParse: false,
        calendarWeeks: true,
        autoclose: true,
        format: "yyyy-mm-dd"

    });

    $('.clockpicker').clockpicker();





    $(function() {
        $("#checkbox2").click(function() {
            if ($(this).is(":checked")) {
                $("#div1").show();
            } else {
                $("#div1").hide();
            }
        });
    });
</script>