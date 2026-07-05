<?php
include("../Connections/Conn.php");
if (isset($_POST['delete_'])) {
    // Prepare data
    $id = $_POST['id']; // Assuming you have a hidden input field for ID

    // Delete data
    $sql = "DELETE FROM labour_summary WHERE id='$id'";

    if ($conn->query($sql) === TRUE) {
        echo "Record deleted successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}



if (isset($_POST['labour_save'])) {

    try {
        // Prepare data
        $induction = isset($_POST['induction']) ? implode(", ", $_POST['induction']) : '';
        $delivery = isset($_POST['delivery']) ? implode(", ", $_POST['delivery']) : '';
        $perineum = isset($_POST['perineum']) ? implode(", ", $_POST['perineum']) : '';
        $placenta = isset($_POST['placenta']) ? implode(", ", $_POST['placenta']) : '';
        $cord = isset($_POST['cord']) ? implode(", ", $_POST['cord']) : '';
        $blood_loss = isset($_POST['blood_loss']) ? $_POST['blood_loss'] : '';
        $infant_status = isset($_POST['infant']) ? implode(", ", $_POST['infant']) : '';
        $mother_bp = isset($_POST['bp']) ? $_POST['bp'] : '';
        $mother_pulse = isset($_POST['pulse']) ? $_POST['pulse'] : '';
        $mother_uterus = isset($_POST['uterus']) ? $_POST['uterus'] : '';
        $delivered_by = isset($_POST['delivered_by']) ? $_POST['delivered_by'] : '';
        $delivery_date = isset($_POST['delivery_date']) ? $_POST['delivery_date'] : '';
        $discharge_date = isset($_POST['discharge_date']) ? $_POST['discharge_date'] : '';
        $hos_no = isset($_POST['hos_no']) ? $_POST['hos_no'] : '';



        if ($delivery_date == '' && $mother_pulse == '') {
            // throw new Exception("Hospital number and delivery date are required.");
            $mes = "delivery date / mother_bp are required";
            header("location:patient.php?hosp_no=$hos_no&mess=$mes");
            exit;
        }

        // Check if the record already exists
        $checkSql = "SELECT COUNT(*) FROM labour_summary WHERE hos_no = :hos_no AND delivery_date = :delivery_date";
        $checkStmt = $db->prepare($checkSql);
        $checkStmt->bindParam(':hos_no', $hos_no);
        $checkStmt->bindParam(':delivery_date', $delivery_date);
        $checkStmt->execute();

        $exists = $checkStmt->fetchColumn();

        if ($exists > 0) {
            // Record exists, prepare the SQL statement for update
            $sql = "UPDATE labour_summary SET 
                        induction = :induction, 
                        delivery = :delivery, 
                        perineum = :perineum, 
                        placenta = :placenta, 
                        cord = :cord, 
                        blood_loss = :blood_loss, 
                        infant_status = :infant_status, 
                        mother_bp = :mother_bp, 
                        mother_pulse = :mother_pulse, 
                        mother_uterus = :mother_uterus, 
                        delivered_by = :delivered_by, 
                        discharge_date = :discharge_date 
                    WHERE hos_no = :hos_no AND delivery_date = :delivery_date";

            // Prepare the statement
            $stmt = $db->prepare($sql);

            // Bind parameters
            $stmt->bindParam(':hos_no', $hos_no);
            $stmt->bindParam(':induction', $induction);
            $stmt->bindParam(':delivery', $delivery);
            $stmt->bindParam(':perineum', $perineum);
            $stmt->bindParam(':placenta', $placenta);
            $stmt->bindParam(':cord', $cord);
            $stmt->bindParam(':blood_loss', $blood_loss);
            $stmt->bindParam(':infant_status', $infant_status);
            $stmt->bindParam(':mother_bp', $mother_bp);
            $stmt->bindParam(':mother_pulse', $mother_pulse);
            $stmt->bindParam(':mother_uterus', $mother_uterus);
            $stmt->bindParam(':delivered_by', $delivered_by);
            $stmt->bindParam(':delivery_date', $delivery_date);
            $stmt->bindParam(':discharge_date', $discharge_date);

            // Execute the statement
            $stmt->execute();

            $mes = "Record updated successfully";
        } else {
            // Record does not exist, prepare the SQL statement for insertion
            $sql = "INSERT INTO labour_summary (hos_no, induction, delivery, perineum, placenta, cord, blood_loss, infant_status, mother_bp, mother_pulse, mother_uterus, delivered_by, delivery_date, discharge_date) 
                    VALUES (:hos_no, :induction, :delivery, :perineum, :placenta, :cord, :blood_loss, :infant_status, :mother_bp, :mother_pulse, :mother_uterus, :delivered_by, :delivery_date, :discharge_date)";

            // Prepare the statement for insertion
            $stmt = $db->prepare($sql);

            // Bind parameters for insertion
            $stmt->bindParam(':hos_no', $hos_no);
            $stmt->bindParam(':induction', $induction);
            $stmt->bindParam(':delivery', $delivery);
            $stmt->bindParam(':perineum', $perineum);
            $stmt->bindParam(':placenta', $placenta);
            $stmt->bindParam(':cord', $cord);
            $stmt->bindParam(':blood_loss', $blood_loss);
            $stmt->bindParam(':infant_status', $infant_status);
            $stmt->bindParam(':mother_bp', $mother_bp);
            $stmt->bindParam(':mother_pulse', $mother_pulse);
            $stmt->bindParam(':mother_uterus', $mother_uterus);
            $stmt->bindParam(':delivered_by', $delivered_by);
            $stmt->bindParam(':delivery_date', $delivery_date);
            $stmt->bindParam(':discharge_date', $discharge_date);

            // Execute the statement for insertion
            $stmt->execute();

            $mes = "New record created successfully";
        }
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
        exit;
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
        exit;
    }

    // Close the connection
    header("location:patient.php?hosp_no=$hos_no&mess=$mes");
    $db = null;
    exit;
}



?>


<div class="form_sep">
    <label for="search_hos_no">Search Hospital Number:</label>
    <input type="text" id="search_hos_no" name="search_hos_no" value="<?= $hos_no; ?>" class="form-control">
    <button type="button" id="search_button" class="btn btn-primary">Search</button>
</div>

<hr>


<form action="patient.php?hosp_no=<?= $hos_no; ?>" method="POST">

    <div class="row">
        <div class="col-lg-4">

            <div class="form_sep">
                <label for="induction" class="">Induction of Labour:</label>
                <div class="i-checks">
                    <label>
                        <input type="checkbox" id="induction_surgical" name="induction[]" value="Surgical">
                        <i></i> Surgical
                    </label>
                </div>
                <div class="i-checks">
                    <label>
                        <input type="checkbox" id="induction_oxytocin" name="induction[]" value="Oxytocin">
                        <i></i> Oxytocin
                    </label>
                </div>
                <div class="i-checks">
                    <label>
                        <input type="checkbox" id="induction_misoprostol" name="induction[]" value="Misoprostol">
                        <i></i> Misoprostol
                    </label>
                </div>
            </div>


            <div class="form_sep">
                <label for="delivery" class="">Method of Delivery:</label>
                <div class="i-checks">
                    <label>
                        <input type="checkbox" id="delivery_spontaneous" name="delivery[]" value="Spontaneous Vaginal Delivery">
                        <i></i> Spontaneous Vaginal Delivery
                    </label>
                </div>
                <div class="i-checks">
                    <label>
                        <input type="checkbox" id="delivery_breech" name="delivery[]" value="Breech Assisted">
                        <i></i> Breech Assisted
                    </label>
                </div>
                <div class="i-checks">
                    <label>
                        <input type="checkbox" id="delivery_forceps" name="delivery[]" value="Forceps">
                        <i></i> Forceps
                    </label>
                </div>
                <div class="i-checks">
                    <label>
                        <input type="checkbox" id="delivery_vacuum" name="delivery[]" value="Vacuum">
                        <i></i> Vacuum
                    </label>
                </div>
                <div class="i-checks">
                    <label>
                        <input type="checkbox" id="delivery_caesarean" name="delivery[]" value="Caesarean Section">
                        <i></i> Caesarean Section
                    </label>
                </div>
                <div class="i-checks">
                    <label>
                        <input type="checkbox" id="delivery_laparotomy" name="delivery[]" value="Laparotomy">
                        <i></i> Laparotomy
                    </label>
                </div>
            </div>

            <div class="form_sep">
                <label for="perineum" class="">Perineum:</label>
                <div class="i-checks">
                    <label>
                        <input type="checkbox" id="perineum_intact" name="perineum[]" value="Intact">
                        <i></i> Intact
                    </label>
                </div>
                <div class="i-checks">
                    <label>
                        <input type="checkbox" id="perineum_episiotomy" name="perineum[]" value="Episiotomy">
                        <i></i> Episiotomy
                    </label>
                </div>
                <div class="i-checks">
                    <label>
                        <input type="checkbox" id="perineum_1st_degree" name="perineum[]" value="1st Degree Laceration">
                        <i></i> 1st Degree Laceration
                    </label>
                </div>
                <div class="i-checks">
                    <label>
                        <input type="checkbox" id="perineum_2nd_degree" name="perineum[]" value="2nd Degree Laceration">
                        <i></i> 2nd Degree Laceration
                    </label>
                </div>
                <div class="i-checks">
                    <label>
                        <input type="checkbox" id="perineum_3rd_degree" name="perineum[]" value="3rd Degree Laceration">
                        <i></i> 3rd Degree Laceration
                    </label>
                </div>
            </div>

        </div>
        <div class="col-lg-4">

            <div class="form_sep">
                <label for="placenta_membranes" class="">Placenta and Membranes:</label>
                <div class="i-checks">
                    <label>
                        <input type="checkbox" id="placenta_controlled" name="placenta[]" value="Controlled Cord Traction">
                        <i></i> Controlled Cord Traction
                    </label>
                </div>
                <div class="i-checks">
                    <label>
                        <input type="checkbox" id="placenta_spontaneous" name="placenta[]" value="Spontaneous">
                        <i></i> Spontaneous
                    </label>
                </div>
                <div class="i-checks">
                    <label>
                        <input type="checkbox" id="placenta_manual" name="placenta[]" value="Manual Removal">
                        <i></i> Manual Removal
                    </label>
                </div>
                <div class="i-checks">
                    <label>
                        <input type="checkbox" id="placenta_fundal" name="placenta[]" value="Fundal Pressure">
                        <i></i> Fundal Pressure
                    </label>
                </div>
                <div class="i-checks">
                    <label>
                        <input type="checkbox" id="placenta_laparotomy" name="placenta[]" value="Laparotomy">
                        <i></i> Laparotomy
                    </label>
                </div>
            </div>

            <div class="form_sep">
                <label for="cord_placenta_membranes" class="">Cord, Placenta, and Membranes:</label>
                <div class="i-checks">
                    <label>
                        <input type="checkbox" id="cord_complete" name="cord[]" value="Complete">
                        <i></i> Complete
                    </label>
                </div>
                <div class="i-checks">
                    <label>
                        <input type="checkbox" id="cord_incomplete" name="cord[]" value="Incomplete">
                        <i></i> Incomplete
                    </label>
                </div>
                <div class="i-checks">
                    <label>
                        <input type="checkbox" id="cord_abnormalities" name="cord[]" value="Abnormalities">
                        <i></i> Abnormalities
                    </label>
                </div>
            </div>

            <div class="form_sep">
                <label for="blood_loss" class="">Estimated Blood Loss (ml):</label>
                <div class="i-checks">
                    <input type="text" id="blood_loss" name="blood_loss" class="form-control">
                </div>
            </div>

            <div class="form_sep">
                <label for="infant_status" class="">Infant Status:</label>
                <div class="i-checks">
                    <label>
                        <input type="checkbox" id="infant_alive" name="infant[]" value="Alive">
                        <i></i> Alive
                    </label>
                </div>
                <div class="i-checks">
                    <label>
                        <input type="checkbox" id="infant_immediate_neonatal_death" name="infant[]" value="Immediate Neonatal Death">
                        <i></i> Immediate Neonatal Death
                    </label>
                </div>
                <div class="i-checks">
                    <label>
                        <input type="checkbox" id="infant_fresh_still_birth" name="infant[]" value="Fresh Still-birth">
                        <i></i> Fresh Still-birth
                    </label>
                </div>
                <div class="i-checks">
                    <label>
                        <input type="checkbox" id="infant_macerated_still_birth" name="infant[]" value="Macerated Still-birth">
                        <i></i> Macerated Still-birth
                    </label>
                </div>
                <div class="i-checks">
                    <label>
                        <input type="checkbox" id="infant_apgar_score" name="infant[]" value="APGAR Score">
                        <i></i> APGAR Score
                    </label>
                </div>
                <div class="i-checks">
                    <label>
                        <input type="checkbox" id="infant_sex" name="infant[]" value="Sex">
                        <i></i> Sex
                    </label>
                </div>
                <div class="i-checks">
                    <label>
                        <input type="checkbox" id="infant_weight" name="infant[]" value="Weight">
                        <i></i> Weight
                    </label>
                </div>
            </div>
        </div>

        <div class="col-lg-4">

            <div class="form_sep">
                <label for="mother_condition" class="req">Condition of Mother:</label>
                <div class="i-checks">
                    Blood Pressure: <input type="text" id="mother_bp" name="bp" class="form-control" required>
                </div>
                <div class="i-checks">
                    Pulse: <input type="text" id="mother_pulse" name="pulse" class="form-control">
                </div>
                <div class="i-checks">
                    Uterus: <input type="text" id="mother_uterus" name="uterus" class="form-control">
                </div>
            </div>

            <div class="form_sep">
                <label for="delivered_by" class="">Delivered by:</label>
                <div class="i-checks">
                    <input type="text" id="delivered_by" name="delivered_by" class="form-control" value="<?= $_SESSION['fullname']  ?>" required>
                </div>
            </div>

            <div class="form_sep">
                <label for="delivery_date" class="req">Delivery Date:</label>
                <div class="i-checks">
                    <input type="date" id="delivery_date" name="delivery_date" class="form-control" required>
                </div>
            </div>

            <div class="form_sep">
                <label for="discharge_date" class="req">Discharge Date:</label>
                <div class="i-checks">
                    <input type="date" id="discharge_date" name="discharge_date" value="<?= date('Y-m-d'); ?>" class="form-control" required>
                </div>
            </div>
            <div class="form_sep">
                <input type="submit" value="Submit" name="labour_save" class="btn btn-primary">
            </div>
        </div>
    </div>


    <input type="hidden" value="<?= $hos_no; ?>" name="hos_no" id="hos_no">
</form>



<table cellpadding="5" cellspacing="2" class="table table-bordered" style="font-size:12px; font-family:Arial, Helvetica, sans-serif;" width="100%">
    <thead>
        <tr>
            <th>Induction</th>
            <th>Delivery</th>
            <th>Perineum</th>
            <th>Placenta</th>
            <th>Cord</th>
            <th>Blood Loss (ml)</th>
            <th>Infant Status</th>
            <th>Mother BP</th>
            <th>Mother Pulse</th>
            <th>Mother Uterus</th>
            <th>Delivery/Discharge Date</th>
            <th>.</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // Assuming you have a database connection and a query to fetch the records
        $results = $db->query("SELECT * FROM labour_summary where hos_no ='$hos_no'");

        // Loop through the results and display them in the table
        while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['induction']) . "</td>";
            echo "<td>" . htmlspecialchars($row['delivery']) . "</td>";
            echo "<td>" . htmlspecialchars($row['perineum']) . "</td>";
            echo "<td>" . htmlspecialchars($row['placenta']) . "</td>";
            echo "<td>" . htmlspecialchars($row['cord']) . "</td>";
            echo "<td>" . htmlspecialchars($row['blood_loss']) . "</td>";
            echo "<td>" . htmlspecialchars($row['infant_status']) . "</td>";
            echo "<td>" . htmlspecialchars($row['mother_bp']) . "</td>";
            echo "<td>" . htmlspecialchars($row['mother_pulse']) . "</td>";
            echo "<td>" . htmlspecialchars($row['mother_uterus']) . "</td>";
            echo "<td>" . date('d-m-Y', strtotime($row['delivery_date'])) . '<br>' . date('d-m-Y', strtotime($row['discharge_date'])) . '<br><b>Delivered By</b><br>' .  $row['delivered_by'] . "</td>";
            $dateTimeStamp = new DateTime($row['date_time_stamp']);
            $currentDateTime = new DateTime();
            $interval = $currentDateTime->diff($dateTimeStamp);
            $id = $row['id'];
            $hos_no = $row['hos_no'];

            // If the difference is less than 1 day, show the delete button
            if ($interval->d < 1 && $interval->h < 24) {
                echo "<td><button class='btn btn-xs btn-danger' onclick='deleteRecord(" . htmlspecialchars($id) . ", \"" . htmlspecialchars($hos_no) . "\")'>Del</button></td>";
            } else {
                echo "<td></td>"; // Empty cell if the delete button is not shown
            }
        }
        ?>
    </tbody>
</table>

<script>
    function deleteRecord(id, hos_no) {
        if (confirm("Are you sure you want to delete this record?")) {
            // Implement the AJAX call or form submission to delete the record
            ///$.post('delete_record.php', { hos_no: hosNo }, function(response) { /* handle response */ });
            window.location.href = "patient.php?hosp_no=" + hos_no + '&idxs4444444444dsddsds=' + id;

        }
    }


    $(document).ready(function() {
        $('#search_button').on('click', function() {
            var hosNo = $('#search_hos_no').val(); // Get the hospital number from the inpu

            if (hosNo) {
                $.ajax({
                    type: 'POST',
                    url: '../inc/save_labour_summary.php', // Your PHP file to handle the search
                    data: {
                        hos_no: hosNo
                    },
                    success: function(response) {
                        // Parse the JSON response
                        var data = JSON.parse(response);

                        // Check if data is found
                        if (data) {


                            // Populate the form fields with the retrieved data
                            $('#hos_no').val(data.hos_no);
                            $('#mother_bp').val(data.mother_bp);
                            $('#mother_pulse').val(data.mother_pulse);
                            $('#mother_uterus').val(data.mother_uterus);
                            $('#delivered_by').val(data.delivered_by);
                            $('#delivery_date').val(data.delivery_date);
                            $('#discharge_date').val(data.discharge_date);

                            // Check the checkboxes based on the retrieved data
                            if (data.induction) {
                                var inductionArray = data.induction.split(", ");
                                inductionArray.forEach(function(value) {
                                    $('input[name="induction[]"][value="' + value + '"]').prop('checked', true);
                                });
                            }

                            if (data.delivery) {
                                var deliveryArray = data.delivery.split(", ");
                                deliveryArray.forEach(function(value) {
                                    $('input[name="delivery[]"][value="' + value + '"]').prop('checked', true);
                                });
                            }

                            if (data.perineum) {
                                var perineumArray = data.perineum.split(", ");
                                perineumArray.forEach(function(value) {
                                    $('input[name="perineum[]"][value="' + value + '"]').prop('checked', true);
                                });
                            }

                            if (data.placenta) {
                                var placentaArray = data.placenta.split(", ");
                                placentaArray.forEach(function(value) {
                                    $('input[name="placenta[]"][value="' + value + '"]').prop('checked', true);
                                });
                            }

                            if (data.cord) {
                                var cordArray = data.cord.split(", ");
                                cordArray.forEach(function(value) {
                                    $('input[name="cord[]"][value="' + value + '"]').prop('checked', true);
                                });
                            }

                            if (data.infant_status) {
                                var infantArray = data.infant_status.split(", ");
                                infantArray.forEach(function(value) {
                                    $('input[name="infant[]"][value="' + value + '"]').prop('checked', true);
                                });
                            }
                        } else {
                            alert('No record found for this hospital number.');
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('An error occurred: ' + error);
                    }
                });
            } else {
                alert('Please enter a hospital number to search.');
            }
        });
    });
</script>