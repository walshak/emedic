<?php
// DB setup
include("../../Connections/Conn.php");
session_start();

$username = $_SESSION['username'];
$stmt2 = $db->query("SELECT * FROM admin_users_rights WHERE username='$username'");
if ($stmt2->rowCount() > 0) {
    $row_invst = $stmt2->fetch(PDO::FETCH_ASSOC);
    $row_invst['report_mgr'];
    $row_invst['doctor'];
    $row_invst['see_med_rpt'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Search Lab Investigations</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7fafc;
            /* Light gray background */
            color: #2d3748;
            /* Dark gray text */
            margin: 0;
            padding: 20px;
        }

        h2 {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 20px;
            color: #2b6cb0;
            /* Blue color */
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            /* White background for content */
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .section {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #e2e8f0;
            /* Light border */
            border-radius: 8px;
            background-color: #f9f9f9;
            /* Light background for sections */
        }

        .section h3 {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2b6cb0;
            /* Blue color */
            margin-bottom: 10px;
        }

        a {
            color: #3182ce;
            /* Blue link color */
            text-decoration: none;
            transition: color 0.3s;
        }

        a:hover {
            color: #2b6cb0;
            /* Darker blue on hover */
            text-decoration: underline;
        }

        hr {
            border: 0;
            border-top: 1px solid #e2e8f0;
            /* Light border for horizontal rule */
            margin: 10px 0;
        }

        .button {
            display: inline-block;
            padding: 10px 15px;
            margin-right: 10px;
            border: none;
            border-radius: 5px;
            background-color: #2b6cb0;
            /* Blue background */
            color: white;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .button:hover {
            background-color: #2c5282;
            /* Darker blue on hover */
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>DATABASE REPORT</h2>

        <?php if ($stmt2->rowCount() > 0 && $row_invst['see_med_rpt'] == 1 && $_SESSION['rights'] == 'MD') { ?>
            <div class="section">
                <h3>Reports</h3>
                <hr>
                <a href="test.php" class="button">INVESTIGATION REPORT</a>
                <hr>
                <a href="notes.php" class="button">MEDICAL HISTORY REPORT</a>
                <hr>
                <a href="notes_by_doc.php" class="button">MEDICAL HISTORY REPORT (Doctors)</a>
            </div>
        <?php } ?>

        <div class="section">
            <h3>Pharmacy Services</h3>
            <hr>
            <a href="statistics.php" class="button">PHARMACY SERVICES</a>
        </div>

        <?php if ($stmt2->rowCount() > 0 && $row_invst['report_mgr'] == 1) { ?>
            <div class="section">
                <h3>Billing and Services</h3>
                <hr>
                <a href="notes_by_doc.php" class="button">BILLING AND SERVICES</a>
            </div>
        <?php } ?>
    </div>
</body>

</html>