<?php
// Connection string
$host = 'localhost'; // Change as needed
$dbname = 'emedic'; // Change to your database name
$username = 'root'; // Change to your database username
$password = 'surepass098'; // Change to your database password

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

$hospital_no = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $hospital_no = $_POST['hospital_no'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Hospital Records</title>
    <link rel="stylesheet" href="path_to_your_css_file.css"> <!-- Add your CSS file path -->
</head>

<body>

    <form method="post" action="">
        <label for="hospital_no">Hospital No:</label>
        <input type="text" id="hospital_no" name="hospital_no" required>
        <button type="submit">Search</button>
    </form>

    <table id="datatable-buttons" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Hospital #</th>
                <th>Name</th>
                <th>Notes</th>
                <th>Doctor</th>
                <th>Date/Time</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($hospital_no) {
                // Prepare and execute the query
                $stmt = $db->prepare("SELECT med.*, e.surname, e.fname 
                                   FROM enrollee AS e 
                                   INNER JOIN notes AS med ON e.hospital_no = med.hospital_no 
                                   WHERE med.hospital_no = :hospital_no 
                                   ORDER BY med.sn");
                $stmt->bindParam(':hospital_no', $hospital_no);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $n = 1;
                    while ($rwx = $stmt->fetch(PDO::FETCH_ASSOC)) {
            ?>
                        <tr>
                            <td><?php echo $n; ?></td>
                            <td><?php echo htmlspecialchars($rwx['hospital_no']); ?></td>
                            <td><?php echo htmlspecialchars($rwx['surname'] . ', ' . $rwx['fname']); ?></td>
                            <td><?php echo htmlspecialchars($rwx['notes']); ?></td>
                            <td><?php echo htmlspecialchars($rwx['prepared_by']); ?></td>
                            <td><?php echo date("d,M y H:i:s a", strtotime($rwx['date_entry'])); ?></td>
                        </tr>
            <?php
                        $n++;
                    }
                } else {
                    echo '<tr><td colspan="6">No record found</td></tr>';
                }
            }
            ?>
        </tbody>
    </table>

</body>

</html>