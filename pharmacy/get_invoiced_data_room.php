<?php
session_start();
include("../Connections/Conn.php");
?>

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


<?php
if (isset($_POST['room_ward'])) {

    // Assuming you have already validated and sanitized $_POST['room_ward']
    $room_ward = $_POST['room_ward'];

    // Prepare the SQL query with a placeholder for the room ward
    $sql = "SELECT DISTINCT e.surname, e.fname, e.oname, adm.hospital_no, adm.room_bed 
        FROM admission AS adm 
        INNER JOIN enrollee AS e ON e.hospital_no = adm.hospital_no 
        WHERE adm.adm_status = '3' AND adm.room_bed LIKE :room_ward 
        ORDER BY adm.floor, e.fname";

    // Prepare the statement
    $stmt = $db->prepare($sql);

    // Bind the parameter with wildcards
    $room_ward_param = "%$room_ward%";
    $stmt->bindParam(':room_ward', $room_ward_param, PDO::PARAM_STR);

    // Execute the statement
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $patients_by_floor = [];

        // Group patients by floor
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $floor = htmlspecialchars($row['floor']);
            $hospital_no = htmlspecialchars($row['hospital_no']);
            $room_bed = htmlspecialchars($row['room_bed']);
            $full_name = htmlspecialchars($row['fname'] . ' ' . $row['oname'] . ', ' . $row['surname']);

            $patients_by_floor[$floor][] = [
                'hospital_no' => $hospital_no,
                'full_name' => $full_name,
                'room_bed' => $room_bed
            ];
        } ?>

        <table class="table table-hover no-margins">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Hospital</th>
                    <th>Name</th>
                    <th>Location</th>
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
                            <h3>ROOM/WARD: <?php echo strtoupper($room_ward); ?></h3>
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

    <?php } else { ?>
        <br><strong>No Requests Available!</strong>
<?php }
}
