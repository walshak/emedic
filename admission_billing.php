<?php include('Connections/Conn.php');
session_start(); ?>
<!DOCTYPE html>
<html>

<head>
    <title>Admission Billing Summary</title>
    <h2>Daily Patient Billing Summary for Current and Past Admissions</h2>

    <meta charset="utf-8">
    <script src="js/jquery-3.1.1.min.js"></script>
    <style>
        body {
            font-family: Arial;
            font-size: 14px;
        }

        select,
        button {
            padding: 6px;
            margin: 4px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 15px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 5px;
        }

        th {
            background: #f0f0f0;
        }

        .category {
            color: #004085;
            margin-top: 10px;
            font-weight: bold;
        }

        .day-header {
            color: #006699;
            margin-top: 15px;
            font-size: 16px;
        }

        #billingResult {
            margin-top: 20px;
        }

        #printBtn,
        #excelBtn {
            display: none;
            margin-right: 8px;
        }

        .filter-section {
            margin-top: 10px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>

    <h3>Patient Admission Billing Summary</h3>

    <div class="filter-section">
        <form id="billingForm">
            <label>Select Admission Period:</label>
            <select name="admission_sn" id="admission_sn" required>
                <option value="">-- Select Admission --</option>
                <?php

                if (isset($_GET['emr'])) {
                    $hospital_no = $_GET['emr']; // dynamically assign this in real use
                }

                $stmt = $db->prepare("SELECT sn, date_admit, date_discharge 
                                      FROM admission 
                                      WHERE hospital_no=? ORDER BY date_admit DESC");
                $stmt->execute(array($hospital_no));
                while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $from = date('Y-m-d', strtotime($r['date_admit']));
                    $to = ($r['date_discharge'] && $r['date_discharge'] != '0000-00-00 00:00:00')
                        ? date('Y-m-d', strtotime($r['date_discharge']))
                        : date('Y-m-d');
                    echo "<option value='{$r['sn']}'>$from → $to</option>";
                }
                ?>
            </select>

            <label>Filter by Category:</label>
            <select name="serv_group" id="serv_group">
                <?php if ($_SESSION['rights'] == 'PH') { ?>
                    <option value="Pharmacy" selected>Pharmacy</option>
                <?php } elseif ($_SESSION['rights'] == 'LB' or $_SESSION['rights'] == 'RD') { ?>
                    <option value="Laboratory">Laboratory</option>
                    <option value="Radiology">Radiology</option>
                <?php } else { ?>
                    <option value="">All</option>
                    <option value="Pharmacy">Pharmacy</option>
                    <option value="Laboratory">Laboratory</option>
                    <option value="Radiology">Radiology</option>
                    <option value="Nursing Services">Nursing</option>
                    <option value="Accommodation">Accommodation</option>
                    <option value="Accommodation_Nursing_care">Nursing Care</option>
                    <option value="Consultation">Consultation</option>
                    <option value="Medical Services">Medical Services</option>
                <?php } ?>

            </select>

            <button type="button" id="loadBilling">Load Billing</button>
        </form>
    </div>

    <div>
        <button id="printBtn" onclick="window.print()">🖨️ Print</button>
        <button id="excelBtn">📊 Export to Excel</button>

        <?php
        if ($_SESSION['rights'] == 'LB' or $_SESSION['rights'] == 'RD') { ?>
            <a href="investigations/mgt.php?hosp_no=<?= $hospital_no; ?>">Close</a>
        <?php } elseif ($_SESSION['rights'] == 'PH') { ?>
            <a href="pharmacy/index.php?presc&hos_no=<?= $hospital_no; ?>">Close</a>
        <?php } elseif ($_SESSION['rights'] == 'NS') { ?>
            <a href="nursing/patient.php?hosp_no=<?= $hospital_no; ?>">Close</a>
        <?php } else { ?>
            <a href="billing/pacct.php?emr=<?= $hospital_no; ?>&stt">Close</a>
        <?php  } ?>


    </div>

    <div id="billingResult"></div>

    <script>
        $(document).ready(function() {
            $('#loadBilling').on('click', function() {
                var admission_sn = $('#admission_sn').val();
                var serv_group = $('#serv_group').val();

                if (!admission_sn) {
                    alert('Select an admission first.');
                    return;
                }

                $('#billingResult').html('<em>Loading...</em>');
                $.ajax({
                    url: 'admission_billing_fetch.php',
                    method: 'POST',
                    data: {
                        admission_sn: admission_sn,
                        serv_group: serv_group
                    },
                    success: function(data) {
                        $('#billingResult').html(data);
                        $('#printBtn, #excelBtn').show();
                    },
                    error: function() {
                        $('#billingResult').html('<span style="color:red;">Error loading billing data.</span>');
                    }
                });
            });

            $('#excelBtn').on('click', function() {
                var sn = $('#admission_sn').val();
                var serv_group = $('#serv_group').val();
                window.location.href = 'admission_billing_export_excel.php?admission_sn=' + sn + '&serv_group=' + serv_group;
            });
        });
    </script>
</body>

</html>