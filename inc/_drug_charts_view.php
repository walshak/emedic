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

    //echo '<br>';

    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $adm_id = isset($_POST['adm_id']) ? $_POST['adm_id'] : null;
    $drug_name = isset($_POST['drug_name']) ? $_POST['drug_name'] : null;
    $drug_name_sql = !empty($drug_name) ? " AND drug_name = '$drug_name' " : " ";
    $limit = 4;
    $offset = ($page - 1) * $limit;
    $limit_sql = " LIMIT $limit OFFSET $offset ";
    $serial_number = $page;

    if ($adm_id != '') {

        $stmt = $db->prepare("SELECT date_admit FROM admission WHERE sn=:sn");
        $stmt->bindParam(':sn', $adm_id, PDO::PARAM_STR);
        $stmt->execute();
        $adm = $stmt->fetch(PDO::FETCH_ASSOC);
        $date_admit = $adm['date_admit'];
        $month_year = substr($date_admit, 0, 7); // Outputs: 2024-07


    }
    ///echo '==========================' . $adm_id;

?>

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox-content">
                <div style="overflow-x:scroll">
                    <h4>

                        <span><b> Patient Admission List:</b></span>
                        <select name="drug_chart_adm" id="drug_chart_adm" style="padding: 8px;" onchange="chart_by_adm(this.value)">
                            <?php
                            $stmt = $db->prepare("SELECT sn, date_admit,adm_status FROM admission WHERE hospital_no=:hospital_no AND adm_status >=3 ORDER BY date_admit desc");
                            $stmt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
                            $stmt->execute();
                            if ($adm_id == '') {
                                $isSelected = 'selected';
                                echo '<option value="' . $adm_id . '" ' . $isSelected . '> ---Select---</option>';
                            }
                            if ($stmt->rowCount() > 0) {

                                $adm_count = 1;
                                while ($adm = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    $isSelected = '';
                                    $adm_date = date('d/m/Y', strtotime('' . $adm['date_admit']));
                                    if ($adm_id == $adm['sn'] && $adm['adm_status'] == 4) {
                                        $isSelected = 'selected';
                                    } elseif ($adm_id == $adm['sn'] && $adm['adm_status'] == 3) {
                                        $isSelected = 'selected';
                                    } else {
                                        $isSelected = '';
                                    }

                                    $admission_id = $adm['sn'];

                                    if ($adm_count == 1 && $adm['adm_status'] == 3) {
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
                        &nbsp; : &nbsp; <span>Choose Charts Calendar Month</span>
                        <input type="month" id="month_picker" value="<?php echo $month_year; ?>" onchange="chart_type_selected_month('<?= $adm_id; ?>')">

                    </h4>


                    <hr>

                    <table class="table table-striped table-bordered table-hover dataTables-example" style="font-size: 14px;">

                        <tbody>
                            <?php

                            $stmt11 = $db->prepare("SELECT * FROM drug_charts WHERE hospital_no = :hospital_no  AND adm_id=:adm_id ORDER BY CASE WHEN status='on-going' THEN 0 ELSE 1 END, sn DESC $limit_sql ");
                            // $stmt11 = $db->prepare("SELECT * FROM drug_charts WHERE hospital_no = :hospital_no AND adm_id=:adm_id ORDER BY CASE WHEN status='on-going' THEN 0 ELSE 1 END, sn DESC");
                            $stmt11->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
                            $stmt11->bindParam(':adm_id', $adm_id, PDO::PARAM_STR);
                            $stmt11->execute();
                            $sn = 1;
                            if ($stmt11->rowCount() > 0) {

                                while ($drug_chart = $stmt11->fetch(PDO::FETCH_ASSOC)) {
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


                                    $today_num = 31; //date('d');
                                    $current_month_num = date('m');
                                    // $selected_month_num = $current_month_num;
                                    // $current_year = date('Y');
                                    //$selected_year_num = $current_year;
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
                                    $pdate = $previous_year . '-' . $previous_month_num; ?>
                                    <b><?= $serial_number++ . '. ' . $drug_chart['drug_name'];
                                        $dosage = $drug_chart['dosage']; ?></b>


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


                                    <?php if ($drug_chart['remarks'] != '') {
                                        echo '<br><strong>Remarks:</strong> ' . '<i><b style="color:red;">' . $drug_chart['remarks'] . '</b></i>'; ?> By: <?= $drug_chart['discontinued_by'];
                                                                                                                                                        } ?>

                                    <table class="table" border="2" width="100%">
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

                                                            $current = date('Y-m-d');
                                                            $sn_ = $inven['sn'];
                                                        endif;

                                                        if ($isTaken && $flag_discontinued): ?>
                                                            <td class="text-center" style="background:red; color:white"><?= $day_txt; ?></td>
                                                        <?php elseif ($isTaken && !$flag_discontinued): ?>
                                                            <td class="text-center" style="background:<?= ($isTaken ? '#23c6c8' : 'white'); ?>;color:white">
                                                                <?= $day_txt;
                                                                echo '<br>' . $inven['dosage_given'] . '/ ' . $del; ?></td>
                                                        <?php elseif (!$isTaken && $flag_discontinued): ?>
                                                            <td class="text-center" style="background: red; color:white"><?= $day_txt; ?></td>
                                                        <?php else:
                                                            $day_txt = '';
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


                                <?php } ?>
                        </tbody>
                    </table>

                    <hr>
                    <div class="well sm-well">
                        <?php if ($page > 1) { ?>
                            <a href="#" class="btn btn-sm btn-primary" onclick="chart_type('<?= $month_year; ?>', <?php echo ($page - 1) ?>)">Previous <i class="fa fa-backward"></i></a> |
                        <?php } ?>

                        <a href="#" class="btn btn-sm btn-primary" onclick="chart_type('<?= $month_year; ?>', <?php echo ($page + 1) ?>)">Next <i class="fa fa-forward"></i></a>
                    </div>
                <?php } else {
                                $page = 1; ?>
                    <a href="#" class="btn btn-sm btn-success" onclick="chart_type('<?= $month_year; ?>', <?php echo ($page) ?>)"><i class="fa fa-refresh"></i> Reset/Refresh </a>
                <?php } ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>