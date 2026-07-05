<?php
if (!isset($appointment_number)) {
    $appointment_number = null;
}
if (isset($_POST['loadInvsHx'])) {

    session_start();
    include("../Connections/Conn.php");
    include('objects.php');
    include('helpers.php');

    if (isset($_POST['loadInvsHx'])) {
        header('Content-Type: application/json');

        $hospital_no = $_POST['hospital_no'];
        $section = "Radiology";
        // Pagination Setup
        $records_per_page = 10;

        $rad_page = !empty($_POST['rad_page']) ? intval($_POST['rad_page']) : 1;

        $rad_offset = ($rad_page - 1) * $records_per_page;

        // ------------------------------------------
        // 🔥 FILTERS
        // ------------------------------------------

        $where = "";
        $params = [];

        if (!empty($_POST['test_name_rd'])) {
            $where .= " AND test_name = :test_name ";
            $params[':test_name'] = $_POST['test_name_rd'];
        }
        if (!empty($_POST['app_no_rd'])) {
            $where .= " AND app_no = :app_no ";
            $params[':app_no'] = $_POST['app_no_rd'];
        }

        if (!empty($_POST['from_date_rd']) && !empty($_POST['to_date_rd'])) {
            $where .= " AND DATE(request_date) BETWEEN :from_date AND :to_date ";
            $params[':from_date'] = $_POST['from_date_rd'];
            $params[':to_date'] = $_POST['to_date_rd'];
        }


        // ------------------------------------------
        // 🔥 FUNCTION FOR QUERY + BINDING + PAGINATION
        // ------------------------------------------

        function loadInvestigations($db, $hospital_no, $section, $offset, $params, $where, $records_per_page)
        {
            // --- MAIN QUERY ---
            $sql = "
            SELECT *
            FROM lab_manage
            WHERE patient = :hospital_no
              AND section = :section
              AND data_capture_status = 'approve'
              $where
            ORDER BY result_date DESC
            LIMIT :limit OFFSET :offset";

            $stmt = $db->prepare($sql);

            // Bind the fixed parameters
            $stmt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
            $stmt->bindParam(':section', $section, PDO::PARAM_STR);
            $stmt->bindParam(':limit', $records_per_page, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

            // Bind dynamic parameters
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // --- COUNT QUERY ---
            $count_sql = "
            SELECT COUNT(*)
            FROM lab_manage
            WHERE patient = :hospital_no
              AND section = :section
              AND data_capture_status = 'approve'
              $where";

            $count_stmt = $db->prepare($count_sql);
            $count_stmt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
            $count_stmt->bindParam(':section', $section, PDO::PARAM_STR);

            foreach ($params as $key => $value) {
                $count_stmt->bindValue($key, $value);
            }

            $count_stmt->execute();
            $total_records = $count_stmt->fetchColumn();

            return [$rows, $total_records];
        }



        // ------------------------------------------
        // 🔥 LOAD RADIOLOGY
        // ------------------------------------------

        list($rad_rows, $rad_total) = loadInvestigations(
            $db,
            $hospital_no,
            "Radiology",
            $rad_offset,
            $params,
            $where,
            $records_per_page
        );

        $rads = '';
        $sn = 0;

        foreach ($rad_rows as $detail_row) {
            $sn++;
            $test_id = $detail_row['test_id'];
            $lab_sn = $detail_row['sn'];

            $request_date = date("d, M Y h:i:s A", strtotime($detail_row["request_date"]));
            $result_date = $detail_row['result_date'] == '' ? 'Nil' :
                date("d, M Y h:i:s A", strtotime($detail_row["result_date"]));

            $new_status = '';
            $new_status2 = '';

            if ($detail_row['result_date'] != '' && date('Y-m-d') == date("Y-m-d", strtotime($detail_row["result_date"]))) {
                $new_status = '<br><strong style="color:red;"><i>[ New Result ]</i></strong>';
            }

            if ($detail_row['result_date'] != '') {
                $since_days = date_diff_day($detail_row["result_date"]);
                $new_status2 = ' - <b style="color:brown;"><i>Done</i>:</b> ' . $since_days . ' days ago';
            }

            $rads .= '
            <tr>
                <td>' . $sn . '</td>
                <td>' . $detail_row['test_name'] . $new_status . '</td>
                <td class="text-left">' . $detail_row['request_by'] . '<br>' . $request_date . '</td>
                <td class="text-left">' . $detail_row['approved_by'] . '<br>' . $result_date . $new_status2 . '</td>
                <td class="text-center">
                    <a href="#" class="btn btn-success" onclick="openInvestigationResultModal2(\'' . $hospital_no . '\', \'' . $lab_sn . '\', \'Radiology\')">View Result</a>
                    <a href="#" class="btn btn-primary" onclick="openInvestigationResultModal(\'' . $hospital_no . '\', \'' . $test_id . '\', \'Radiology\')">Compare Results</a>
                </td>
            </tr>';
        }

        $rad_total_pages = ceil($rad_total / $records_per_page);

        $rad_page_html = '<div class="pagination">';
        if ($rad_page > 1) {
            $rad_page_html .= '<a href="#" onClick="load_more_rad(' . ($rad_page - 1) . ')">Previous</a>';
        }
        $rad_page_html .= ' <span>Page ' . $rad_page . ' of ' . $rad_total_pages . ' </span>';
        if ($rad_page < $rad_total_pages) {
            $rad_page_html .= '<a href="#" onClick="load_more_rad(' . ($rad_page + 1) . ')">Next</a>';
        }
        $rad_page_html .= '</div>';

        // ------------------------------------------
        // 🔥 FINAL JSON OUTPUT
        // ------------------------------------------

        echo json_encode([
            "status" => 200,
            "message" => "Data Loaded Successfully",
            "data" => [
                "labs" => $labs,
                "rads" => $rads,
                "page" => $lab_page_html,
                "page_rad" => $rad_page_html
            ]
        ]);
        exit;
    }
    exit;
}

// DISTINCT TEST NAME
$testNames = $db->query("
    SELECT DISTINCT test_name
    FROM lab_manage
    WHERE test_name <> '' 
      AND patient = $hospital_no
      AND data_capture_status = 'approve' AND section ='Radiology'
    ORDER BY test_name ASC
")->fetchAll(PDO::FETCH_COLUMN);

// DISTINCT REQUESTED BY
$app_no_visits = $db->query("
    SELECT DISTINCT app_no
    FROM lab_manage
    WHERE app_no <> '' 
      AND patient = $hospital_no
      AND data_capture_status = 'approve' AND section ='Radiology'
    ORDER BY app_no ASC
")->fetchAll(PDO::FETCH_COLUMN);

?>


<style>
    .btn.btn-app {

        width: fit-content !important;
        padding-bottom: 10px !important;
    }
</style>



<div>
    <hr>


    <table>
        <tr>
            <td>
                <b style="color:red;"><i>ADVANCE FILTER:</i></b>&nbsp;
            </td>
            <td>
                <select id="filter_visits_rd" class="form-control">
                    <option value="">-Visit No -</option>
                    <?php foreach ($app_no_visits as $t): ?>
                        <option value="<?= $t ?>"><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>
                <select id="filter_test_name_rd" class="form-control">
                    <option value="">-- Filter by Test Name --</option>
                    <?php foreach ($testNames as $t): ?>
                        <option value="<?= $t ?>"><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </td>

            <td>
                <input type="date" id="filter_from_date_rd" class="form-control">
            </td>
            <td>
                <input type="date" id="filter_to_date_rd" class="form-control">
            </td>
            <td>
                <button class="btn btn-info" onclick="applyLabFilters_rd()">Apply Filter</button>

            </td>
        </tr>
    </table>
    <hr>
    <table class="table table-striped  no-footer dtr-inline" id="lab_search_table" style="font-size:15px;">

        <thead>
            <tr>
                <td><strong>#</strong></td>
                <td><strong>Investigation(s)</strong></td>
                <td><strong>Requested By/Date</strong></td>
                <td><strong>Approved/Completed By/Date</strong></td>
                <td></td>
            </tr>
        </thead>
        <tbody id="rad_hx__wrap">
            <tr>
                <td colspan="4">
                    <h4 class="text-center text-danger">Loading, please wait...</h4>
                </td>
            </tr>
        </tbody>
    </table>

    <div id="page_rad_bottom"></div>

</div>

<div class='modal inmodal fade' id='labs-on-queue-modal' tabindex='-1' role='dialog' aria-hidden='true' data-keyboard='false'>
    <div class='modal-dialog modal-lg' style='width: 900px;'>
        <div class='modal-content'>
            <div class='modal-header'>
                <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>×</button>
                <h4 class='modal-title' id=''>Pending Imaging/Scans</h4>
            </div>
            <div class='modal-body' style='min-height: 300px'>
                <div id="labs-on-queue-body">

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>



<script>
    var investigations_image_hx_url = "<?php echo $investigations_image_hx_url; ?>";
    $(document).ready(function() {

        /// alert("Investigations History URL: " + investigations_hx_url);
        setTimeout(function() {
            $.ajax({
                url: investigations_image_hx_url,
                method: "POST",
                data: {
                    loadInvsHx: true,
                    hospital_no: "<?php echo $hospital_no; ?>"
                },
                success: function(response) {
                    //$('#lab_hx__wrap').html(response.data.labs)
                    $('#rad_hx__wrap').html(response.data.rads)
                    $('#page_rad_bottom').html(response.data.page_rad)
                },
                error: function(err) {
                    console.log(err)
                }
            });
        }, 1500)

    })
</script>