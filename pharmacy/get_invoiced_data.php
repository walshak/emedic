<?php
session_start();
include("../Connections/Conn.php");

$dept_id = $_SESSION['dept_id'];

if ($_SESSION['dispensory'] == 0) {
    $patch_Dispens_query = "";
} else {
    $patch_Dispens_query = " AND dept_dispensory_id = '$dept_id'"; // Use a placeholder for binding
}




if (isset($_POST['get_ex'])) {

    $term = isset($_POST['term']) ? trim($_POST['term']) : '';
    if ($term != '') {
        if (is_numeric($term)) {
            $term = 'EX' . $term;
        }
        $term_like = "%$term%";

        try {
            $stmt = $db->prepare("SELECT transc_code 
                                  FROM pharm_ext 
                                  WHERE transc_code LIKE :term 
                                  ORDER BY transc_code ASC 
                                  LIMIT 1");
            $stmt->bindParam(':term', $term_like, PDO::PARAM_STR);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            echo $row ? $row['transc_code'] : '';
        } catch (PDOException $e) {
            // Return error message for debugging
            echo 'PDO Error: ' . $e->getMessage();
        }
    } else {
        echo ''; // empty input
    }

    exit;
}





if (isset($_POST['Reversed'])) {



    header('Content-Type: text/html; charset=utf-8');

    try {
        // Get POST dates
        $from_date = isset($_POST['from_date']) ? trim($_POST['from_date']) : '';
        $to_date   = isset($_POST['to_date']) ? trim($_POST['to_date']) : '';

        // Default to last 3 days if empty
        if (empty($from_date) || empty($to_date)) {
            $dateStart = date('Y-m-d', strtotime('-2 days'));
            $dateEnd   = date('Y-m-d');
        } else {
            $dateStart = date('Y-m-d', strtotime($from_date));
            $dateEnd   = date('Y-m-d', strtotime($to_date));
        }

        // Check difference
        $start = strtotime($dateStart);
        $end   = strtotime($dateEnd);
        $diffDays = ($end - $start) / 86400; // difference in days

        if ($diffDays > 30) {
            // ❌ Alert user and stop execution
            echo '<div class="alert alert-danger">
                Selected date range exceeds 30 days. Please select a shorter range.
              </div>';
            exit; // stop processing further
        }

        // Query
        $query = "
        SELECT 
            s.product_name, 
            i.inven_desc, 
            i.qtyIN, 
            i.enter_by, 
            i.insertion_date_time 
        FROM stock_table_inven AS i 
        INNER JOIN stock_table AS s ON i.stock_sn = s.sn 
        WHERE i.inven_desc LIKE '%Returned%'
        AND i.insertion_date_time >= :dateStart 
        AND i.insertion_date_time < DATE_ADD(:dateEnd, INTERVAL 1 DAY)
        ORDER BY i.insertion_date_time DESC
    ";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':dateStart', $dateStart);
        $stmt->bindParam(':dateEnd', $dateEnd);
        $stmt->execute();

        // Dynamic Title
        $title = 'Drug Reversal List (' . date("d-m-Y", strtotime($dateStart)) . ' to ' . date("d-m-Y", strtotime($dateEnd)) . ')';

        // Start Table
        $output = '
    <h3>' . $title . '</h3>
    <table class="table table-striped table-bordered table-hover dataTables-example">
        <thead>
            <tr>
                <th>Date</th>
                <th>Product Name</th>
                <th>Patient</th>
                <th>Qty</th>
                <th>Reversed By</th>
            </tr>
        </thead>
        <tbody>
    ';

        // No Record Handling
        if ($stmt->rowCount() == 0) {
            $output .= '
            <tr>
                <td colspan="5" class="text-center text-muted">
                    No record found for selected date range
                </td>
            </tr>
        ';
        } else {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $product = htmlspecialchars($row['product_name'], ENT_QUOTES, 'UTF-8');
                $desc    = htmlspecialchars($row['inven_desc'], ENT_QUOTES, 'UTF-8');
                $qty     = (int)$row['qtyIN'];
                $user    = htmlspecialchars($row['enter_by'], ENT_QUOTES, 'UTF-8');

                // Extract hospital number
                $parts = explode('/', $desc);
                $hospital_no = isset($parts[1]) ? $parts[1] : '';

                $date_raw = $row['insertion_date_time'] ?: date('Y-m-d H:i:s');
                $formatted_date = date("d-m-Y h:i a", strtotime($date_raw));

                $link = $hospital_no
                    ? '<a href="index.php?presc&hos_no=' . urlencode($hospital_no) .
                    '&dd=' . date('Y-m-d', strtotime($date_raw)) . '/' . date('Y-m-d') . '">
                    [ View Patient ]
                  </a>'
                    : '<span class="text-muted">N/A</span>';

                $output .= '
                <tr>
                    <td>' . $formatted_date . '</td>
                    <td><strong>' . $product . '</strong></td>
                    <td>' . $link . '</td>
                    <td>' . $qty . '</td>
                    <td>' . $user . '</td>
                </tr>
            ';
            }
        }

        // End Table
        $output .= '
        </tbody>
    </table>
    ';

        echo $output;
    } catch (PDOException $e) {
        echo '<div class="alert alert-danger">
        Database error: ' . htmlspecialchars($e->getMessage()) . '
    </div>';
    }
} elseif (isset($_POST['invoiced'])) {

    header('Content-Type: text/html; charset=utf-8');

    try {
        // ✅ Prepare all filter values once
        $dateStart = date('Y-m-d', strtotime('-2 days'));
        $dateEnd   = date('Y-m-d');

        // ✅ Base WHERE clause — no string concatenation of dynamic SQL mid-query
        $whereClause = "
            WHERE DATE(date_entry) BETWEEN :dateStart AND :dateEnd
              AND serv_group = 'Pharmacy'
              AND paystatus = '0'
              AND invoice_status = '1'
              AND (pay_mode = 'cash' OR pay_mode = 'claim')
              $patch_Dispens_query
        ";

        // ✅ 1. Get DISTINCT COUNT (uses index if available)
        $countQuery = "SELECT COUNT(DISTINCT hospital_no) AS cnt FROM patient_ap_services $whereClause";
        $countStmt = $db->prepare($countQuery);
        $countStmt->bindParam(':dateStart', $dateStart);
        $countStmt->bindParam(':dateEnd', $dateEnd);
        $countStmt->execute();
        $distinctCount = (int)$countStmt->fetchColumn();

        // ✅ 2. Get latest record per hospital_no only
        $baseQuery = "
            SELECT hospital_no, MAX(DATE(date_entry)) AS entry_date
            FROM patient_ap_services
            $whereClause
            GROUP BY hospital_no
            ORDER BY MAX(date_entry) DESC
            LIMIT 200
        ";
        // 🔹 LIMIT protects UI from too large dataset — adjust as needed

        $stmt = $db->prepare($baseQuery);
        $stmt->bindParam(':dateStart', $dateStart);
        $stmt->bindParam(':dateEnd', $dateEnd);
        $stmt->execute();

        // ✅ Build HTML table
        $output = '
        <table class="table table-striped table-bordered table-hover dataTables-example">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Hospital No</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
        ';

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $hospital_no = htmlspecialchars($row['hospital_no'], ENT_QUOTES, 'UTF-8');
            $entry_date = $row['entry_date'] ?: date('Y-m-d');
            $formatted_date = date("d-m-Y", strtotime($entry_date));

            $link = '<a href="index.php?presc&hos_no=' . urlencode($hospital_no) .
                '&dd=' . $entry_date . '/' . date('Y-m-d') . '">[View Drug]</a>';

            $output .= '
                <tr>
                    <td>' . $formatted_date . '</td>
                    <td><strong>' . $hospital_no . '</strong></td>
                    <td>' . $link . '</td>
                </tr>
            ';
        }

        $output .= '</tbody></table>';
        echo $output;
    } catch (PDOException $e) {
        echo '<div class="alert alert-danger">Database error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
} elseif (isset($_POST['room_ward'])) {

    $room_ward = $_POST['room_ward'];
    $part_ = " AND room_bed LIKE %$room_ward%";

    // Fetch recently admitted patients (adm_status = 3)
    $sql = "SELECT DISTINCT e.surname, e.fname, e.oname, adm.hospital_no, adm.floor 
    FROM admission AS adm INNER JOIN enrollee AS e ON e.hospital_no = adm.hospital_no 
    WHERE adm.adm_status = '3' $part_ ORDER BY adm.floor, e.fname";
    $stmt = $db->prepare($sql);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $patients_by_floor = [];

        // Group patients by floor
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $floor = htmlspecialchars($row['floor']);
            $hospital_no = htmlspecialchars($row['hospital_no']);
            $full_name = htmlspecialchars($row['fname'] . ' ' . $row['oname'] . ', ' . $row['surname']);

            $patients_by_floor[$floor][] = [
                'hospital_no' => $hospital_no,
                'full_name' => $full_name
            ];
        } ?>

        <hr>
        <table class="table table-hover no-margins">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Hospital</th>
                    <th>Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $n = 1;
                // Loop through each floor and its patients
                foreach ($patients_by_floor as $floor => $patients): ?>
                    <tr>
                        <td colspan="4">
                            <h3>FLOOR: <?php echo strtoupper($floor); ?></h3>
                        </td>
                    </tr>
                    <?php foreach ($patients as $patient): ?>
                        <tr>
                            <td><?php echo $n++; ?></td>
                            <td><?php echo $patient['hospital_no']; ?></td>
                            <td><?php echo $patient['full_name']; ?></td>
                            <td><a href="index.php?presc&hos_no=<?php echo urlencode($patient['hospital_no']); ?>">View Drugs</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php } else { ?>
        <br><strong>No Requests Available!</strong>
    <?php }
} elseif (isset($_POST['adm'])) {

    if (isset($_POST['floor']) && $_POST['floor'] != '') {
        $floor = $_POST['floor'];
        if ($floor  == 'all') {
            $part_ = "";
        } else {
            $part_ = " AND floor ='$floor'";
        }
    } else {
        $part_ = "";
    }

    // Fetch recently admitted patients (adm_status = 3)
    $sql = "SELECT DISTINCT e.surname, e.fname, e.oname, adm.hospital_no, adm.floor, adm.room_bed 
    FROM admission AS adm INNER JOIN enrollee AS e ON e.hospital_no = adm.hospital_no WHERE adm.adm_status = '3' $part_ ORDER BY adm.floor, adm.room_bed";
    $stmt = $db->prepare($sql);
    $stmt->execute();

    if ($stmt->rowCount() > 0):
        $patients_by_floor = [];

        // Group patients by floor
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $floor = htmlspecialchars($row['floor']);
            $room_bed = htmlspecialchars($row['room_bed']);
            $hospital_no = htmlspecialchars($row['hospital_no']);
            $full_name = htmlspecialchars($row['fname'] . ' ' . $row['oname'] . ', ' . $row['surname']);

            $patients_by_floor[$floor][] = [
                'hospital_no' => $hospital_no,
                'room_bed' => $room_bed,
                'full_name' => $full_name
            ];
        } ?>


        <strong>Admitted Patients</strong><br>
        <select name="floor" id="floor" class="form-control" onchange="handleFloorChange(this)">
            <option value="all" <?= (empty($_GET['floor']) || $_GET['floor'] === 'all') ? 'selected' : '' ?>>Filter by Floors</option>
            <?php
            // Get distinct floors from the database
            $floorQuery = "SELECT DISTINCT floor FROM admission WHERE adm_status = '3' ORDER BY floor";
            $floorStmt = $db->prepare($floorQuery);
            $floorStmt->execute();

            while ($floor = $floorStmt->fetch(PDO::FETCH_COLUMN)) {
                $selected = (isset($_GET['floor']) && $_GET['floor'] === $floor) ? 'selected' : '';
                echo "<option value='$floor' $selected>Floor $floor</option>";
            }
            ?>
        </select>
        <br>
        <select name="room_wards" id="room_wards" class="form-control" onchange="handleRoomWardChange()">
            <option value="all" <?= (empty($_GET['room_wards']) || $_GET['room_wards'] === 'all') ? 'selected' : '' ?>>Filter by Room/Wards</option>
            <?php
            $floorQuery = "SELECT DISTINCT room_name FROM bed_mgt ORDER BY room_name";
            $floorStmt = $db->prepare($floorQuery);
            $floorStmt->execute();

            while ($room_name = $floorStmt->fetch(PDO::FETCH_COLUMN)) {
                $selected = (isset($_GET['room_wards']) && $_GET['room_wards'] === $room_name) ? 'selected' : '';
                echo "<option value='$room_name' $selected>$room_name</option>";
            }
            ?>
        </select>



        <hr>
        <table class="table table-hover no-margins">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Hospital</th>
                    <th>Name</th>
                    <th>Bed</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $n = 1;
                // Loop through each floor and its patients
                foreach ($patients_by_floor as $floor => $patients): ?>
                    <tr>
                        <td colspan="4">
                            <h3>FLOOR: <?php echo strtoupper($floor); ?></h3>
                        </td>
                    </tr>
                    <?php foreach ($patients as $patient): ?>
                        <tr>
                            <td><?php echo $n++; ?></td>
                            <td><?php echo $patient['hospital_no']; ?></td>
                            <td><?php echo $patient['full_name']; ?></td>
                            <td><?php echo $patient['room_bed']; ?></td>
                            <td><a href="index.php?presc&hos_no=<?php echo urlencode($patient['hospital_no']); ?>">View Drugs</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <br><strong>No Requests Available!</strong>
    <?php endif;
} elseif (isset($_POST['request'])) {
    $setdate = date("Y-m-d");

    // Base query with optional patch filter
    $sql = "SELECT DISTINCT e.surname, e.fname, e.oname, e.insurance, ap.hospital_no 
            FROM patient_ap_services AS ap
            INNER JOIN enrollee AS e ON e.hospital_no = ap.hospital_no
            WHERE ap.serv_group = 'Pharmacy'
              AND ap.drug_status = '0'
              AND ap.invoice_status = '0'
              AND ap.paystatus = '0'
              AND DATE(ap.date_entry) = :setdate
              $patch_Dispens_query
            ORDER BY ap.sn DESC
            LIMIT 35";

    $stmt = $db->prepare($sql);
    $stmt->bindParam(':setdate', $setdate, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0): ?>
        <table class="table table-striped table-bordered table-hover dataTables-example">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Hospital No</th>
                    <th>Patient Name</th>
                    <th>Insurance</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $n = 1;
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                    $hospital_no = htmlspecialchars($row['hospital_no']);
                    $full_name = htmlspecialchars($row['fname'] . ' ' . $row['oname'] . ', ' . $row['surname']);
                    $insurance = htmlspecialchars($row['insurance']);
                ?>
                    <tr>
                        <td><?php echo $n++; ?></td>
                        <td><?php echo $hospital_no; ?></td>
                        <td><?php echo $full_name; ?></td>
                        <td><?php echo $insurance; ?></td>
                        <td><a href="index.php?presc&hos_no=<?php echo urlencode($hospital_no); ?>">View Drugs</a></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <br><strong>No Requests Available!</strong>
    <?php endif;
} elseif (isset($_POST['encounter'])) {

    ?>

    <h3>Search Previous Encounter by Date Range.</h3>
    <table>
        <tr>
            <td>
                <div class="input-daterange input-group">
                    <input type="date" class="form-control" name="from_date" id="from_date" value="<?php echo date('Y-m-d'); ?>" />
                    <span class="input-group-addon">to</span>
                    <input type="date" class="form-control" name="to_date" id="to_date" value="<?php echo date('Y-m-d'); ?>" />
                </div>
            </td>
            <td> &nbsp;
                <button
                    class="btn btn-success"
                    type="submit"
                    name="apply_task2"
                    id="apply_task2"
                    onclick="handleApplyTask2()">
                    Show
                </button>
            </td>

        </tr>
    </table>



    <?php

    $dept_filter = '';
    $params = [];

    // Department filter (if applicable)
    if (isset($_SESSION['dispensory']) && $_SESSION['dispensory'] == 1) {
        $dept_filter = "AND dept = :dept_id";
        $params[':dept_id'] = $dept_id;
    }

    // Date range handling
    $from_date = !empty($_POST['from_date']) ? $_POST['from_date'] : date('Y-m-d');
    $to_date = !empty($_POST['to_date']) ? $_POST['to_date'] : date('Y-m-d');

    // Validate dates (optional: ensure to_date >= from_date)
    if (strtotime($to_date) < strtotime($from_date)) {
        $to_date = $from_date; // Force to_date = from_date if invalid
    }

    // Add dates to query parameters
    $params[':from_date'] = $from_date;
    $params[':to_date'] = $to_date;

    // Prepare and execute query (modified for date range)
    $sql = "SELECT DISTINCT hospital_no, patient_name,date_ap 
          FROM apptm 
          WHERE date_ap BETWEEN :from_date AND :to_date 
          $dept_filter 
          ORDER BY patient_name ASC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    // Display results
    if ($stmt->rowCount() > 0) { ?>
        <h3>Encounter(s) from <?php echo date('d-m-Y', strtotime($from_date)); ?> to <?php echo date('d-m-Y', strtotime($to_date)); ?></h3>
        <br>
        <table class="table table-striped table-bordered table-hover dataTables-example">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Hospital No</th>
                    <th>Name</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $n = 1;
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $hospital_no = htmlspecialchars($row['hospital_no']);
                    $patient_name = htmlspecialchars($row['patient_name']);
                    $date_ap = htmlspecialchars($row['date_ap']);
                ?>
                    <tr>
                        <td><?php echo $n++; ?></td>
                        <td><?php echo $hospital_no; ?></td>
                        <td><?php echo $patient_name; ?></td>
                        <td><?php echo date('d-m-Y', strtotime($date_ap)); ?></td>
                        <td><a href="index.php?presc&hos_no=<?php echo urlencode($hospital_no); ?>">View patient</a></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else {
        echo '<br><strong>No Encounters Found for ' . date('d-m-Y', strtotime($from_date)) . ' to ' . date('d-m-Y', strtotime($to_date)) . '!</strong>';
    }
} elseif (isset($_POST['pend'])) {

    // Main optimized query without JOIN (assuming patient_ap_services already has patient info if needed)
    $stmt = $db->query("
    SELECT DISTINCT hospital_no, serv_group, DATE(date_entry) AS date_entry
    FROM patient_ap_services
    WHERE serv_group = 'Pharmacy' 
      AND paystatus = '1' 
      AND drug_status = '0' 
      $patch_Dispens_query 
    ORDER BY date_entry DESC
");

    if ($stmt->rowCount() > 0) { ?>
        <strong>Pending Dispense</strong><br>
        <table class="table table-hover no-margins">
            <tbody>
                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $hospital_no = htmlspecialchars($row['hospital_no']);
                    $date_entry  = htmlspecialchars(date('d-m-Y', strtotime($row['date_entry'])));

                    // If patient names are stored elsewhere, fetch them dynamically here (optional)
                    $nameStmt = $db->prepare("SELECT fname, oname, surname FROM enrollee WHERE hospital_no = ?");
                    $nameStmt->execute([$hospital_no]);
                    $name = $nameStmt->fetch(PDO::FETCH_ASSOC);

                    if ($name) {
                        // Safely trim and combine available name parts
                        $parts = array_filter([$name['fname'], $name['oname'], $name['surname']]);
                        $full_name = htmlspecialchars(trim(implode(' ', $parts)));
                    } else {
                        // If not found in enrollee, try fetching from pharm_ext
                        $altStmt = $db->prepare("SELECT cust_name FROM pharm_ext WHERE transc_code = ? LIMIT 1");
                        $altStmt->execute([$hospital_no]);
                        $altName = $altStmt->fetch(PDO::FETCH_ASSOC);

                        if ($altName) {
                            $full_name = htmlspecialchars(trim($altName['cust_name']));
                        } else {
                            $full_name = 'Unknown';
                        }
                    }

                    $link = "index.php?presc&hos_no={$hospital_no}";
                ?>
                    <tr>
                        <td width="5%"><?php echo $hospital_no; ?></td>
                        <td width="20%"><?php echo $full_name; ?></td>
                        <td width="10%"><?php echo $date_entry; ?></td>
                        <td width="15%"><a href="<?php echo $link; ?>&dispense">[View Drug]</a></td>
                        <td width="10%">&nbsp;</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
<?php } else {
        echo '<strong>No Pending Dispense.</strong>';
    }
} ?>


<script>
    function handleRoomWardChange() {
        var selectElement = document.getElementById('room_wards');
        var selectedValue = selectElement.value;

        $.ajax({
            url: "get_invoiced_data_room.php",
            method: "POST",
            data: {
                room_ward: selectedValue
            },
            success: function(data) {
                toastr.success('Loading ...', '', {
                    timeOut: 500
                });
                $('#adm-table-container').html(data);
            }
        });
    }
</script>