<?php
if (isset($_POST['gen_disease_notification'])) {
    error_reporting(E_ALL);
    $db = new PDO('mysql:host=localhost;dbname=emedic;charset=utf8mb4', 'root', 'surepass098');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    $report_type = $_POST["report_type"];
    $report_month = $_POST["report_month"];


    $disease_groups = [
        ['group' => 'IDSR', 'id' => 2, 'letter' => 'A.'],
        ['group' => 'GENDER-BASED VIOLENCE', 'id' => 3, 'letter' => 'B.'],
        ['group' => 'NON-COMMUNICABLE/OTHER DISEASES', 'id' => 4, 'letter' => 'C.']
    ];


    ob_start();

?>

    <html xmlns:o="urn:schemas-microsoft-com:office:office"
        xmlns:x="urn:schemas-microsoft-com:office:excel"
        xmlns="http://www.w3.org/TR/REC-html40">

    <head>
        <!--[if gte mso 9]><xml>
    <x:ExcelWorkbook>
        <x:ExcelWorksheets>
            <x:ExcelWorksheet>
                <x:Name>Sheet1</x:Name>
                <x:WorksheetOptions>
                    <x:Print>
                        <x:ValidPrinterInfo/>
                    </x:Print>
                </x:WorksheetOptions>
            </x:ExcelWorksheet>
        </x:ExcelWorksheets>
    </x:ExcelWorkbook>
    </xml><![endif]-->
        <style>
            table {
                width: 100%;
                border-collapse: collapse;
            }

            th,
            td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: center;
            }

            th {
                background-color: #f2f2f2;
            }

            tr:nth-child(even) {
                background-color: #f9f9f9;
            }

            tr:hover {
                background-color: #f1f1f1;
            }

            .header-row {
                background-color: #4CAF50;
                color: white;
            }

            .header-row td {
                text-align: center;
            }

            .age-group-row {
                background-color: #888;
                color: white;
            }

            .subtotal-row {
                background-color: #f2f2f2;
                font-weight: bold;
            }

            .police-cell {
                background-color: green;
            }

            .civilian-cell {
                background-color: skyblue;
            }

            .male-cell {
                background: orange;
                color: white;
            }

            .female-cell {
                background: blue;
                color: white;
            }
        </style>
    </head>

    <body>
        <table>
            <?php
            $sql_string = ''; // " AND dt.created_at LIKE '$report_month%' " ;
            // if($report_type == 'Out-patient'){
            //     $sql_string .= " AND dt.adm_id IS NULL ";
            // }elseif($report_type == 'In-patient'){
            //     $sql_string .= " AND dt.adm_id IS NOT NULL ";
            // }elseif ($report_type == 'Death'){
            //     $sql_string .= " AND dt.discharge_status = 'Death' ";
            // }

            $query = "
            SELECT 
                d.item, 
                COUNT(dt.diagnosis) AS total_cases, 
                SUM(CASE WHEN DATEDIFF(NOW(), dt.date_of_birth) <= 28 AND dt.gender = 'Male' AND (dt.rank IS NOT NULL) THEN 1 ELSE 0 END) AS pm_age_0_28_days,
                SUM(CASE WHEN DATEDIFF(NOW(), dt.date_of_birth) <= 28 AND dt.gender = 'Male' AND (dt.rank IS NULL) THEN 1 ELSE 0 END) AS cm_age_0_28_days,
                SUM(CASE WHEN DATEDIFF(NOW(), dt.date_of_birth) <= 28 AND dt.gender = 'Female' AND (dt.rank IS NOT NULL) THEN 1 ELSE 0 END) AS pf_age_0_28_days,
                SUM(CASE WHEN DATEDIFF(NOW(), dt.date_of_birth) <= 28 AND dt.gender = 'Female' AND (dt.rank IS NULL) THEN 1 ELSE 0 END) AS cf_age_0_28_days,
                SUM(CASE WHEN DATEDIFF(NOW(), dt.date_of_birth) BETWEEN 29 AND 365 AND dt.gender = 'Male' AND (dt.rank IS NOT NULL) THEN 1 ELSE 0 END) AS pm_age_1_11_months,
                SUM(CASE WHEN DATEDIFF(NOW(), dt.date_of_birth) BETWEEN 29 AND 365 AND dt.gender = 'Male' AND (dt.rank IS NULL) THEN 1 ELSE 0 END) AS cm_age_1_11_months,
                SUM(CASE WHEN DATEDIFF(NOW(), dt.date_of_birth) BETWEEN 29 AND 365 AND dt.gender = 'Female' AND (dt.rank IS NOT NULL) THEN 1 ELSE 0 END) AS pf_age_1_11_months,
                SUM(CASE WHEN DATEDIFF(NOW(), dt.date_of_birth) BETWEEN 29 AND 365 AND dt.gender = 'Female' AND (dt.rank IS NULL) THEN 1 ELSE 0 END) AS cf_age_1_11_months,
                SUM(CASE WHEN DATEDIFF(NOW(), dt.date_of_birth) BETWEEN 366 AND 1824 AND dt.gender = 'Male' AND (dt.rank IS NOT NULL) THEN 1 ELSE 0 END) AS pm_age_11_59_months,
                SUM(CASE WHEN DATEDIFF(NOW(), dt.date_of_birth) BETWEEN 366 AND 1824 AND dt.gender = 'Male' AND (dt.rank IS NULL) THEN 1 ELSE 0 END) AS cm_age_11_59_months,
                SUM(CASE WHEN DATEDIFF(NOW(), dt.date_of_birth) BETWEEN 366 AND 1824 AND dt.gender = 'Female' AND (dt.rank IS NOT NULL) THEN 1 ELSE 0 END) AS pf_age_11_59_months,
                SUM(CASE WHEN DATEDIFF(NOW(), dt.date_of_birth) BETWEEN 366 AND 1824 AND dt.gender = 'Female' AND (dt.rank IS NULL) THEN 1 ELSE 0 END) AS cf_age_11_59_months,
                SUM(CASE WHEN DATEDIFF(NOW(), dt.date_of_birth) BETWEEN 1825 AND 3659 AND dt.gender = 'Male' AND (dt.rank IS NOT NULL) THEN 1 ELSE 0 END) AS pm_age_5_9_years,
                SUM(CASE WHEN DATEDIFF(NOW(), dt.date_of_birth) BETWEEN 1825 AND 3659 AND dt.gender = 'Male' AND (dt.rank IS NULL) THEN 1 ELSE 0 END) AS cm_age_5_9_years,
                SUM(CASE WHEN DATEDIFF(NOW(), dt.date_of_birth) BETWEEN 1825 AND 3659 AND dt.gender = 'Female' AND (dt.rank IS NOT NULL) THEN 1 ELSE 0 END) AS pf_age_5_9_years,
                SUM(CASE WHEN DATEDIFF(NOW(), dt.date_of_birth) BETWEEN 1825 AND 3659 AND dt.gender = 'Female' AND (dt.rank IS NULL) THEN 1 ELSE 0 END) AS cf_age_5_9_years,
                SUM(CASE WHEN DATEDIFF(NOW(), dt.date_of_birth) BETWEEN 3660 AND 7304 AND dt.gender = 'Male' AND (dt.rank IS NOT NULL) THEN 1 ELSE 0 END) AS pm_age_10_19_years,
                SUM(CASE WHEN DATEDIFF(NOW(), dt.date_of_birth) BETWEEN 3660 AND 7304 AND dt.gender = 'Male' AND (dt.rank IS NULL) THEN 1 ELSE 0 END) AS cm_age_10_19_years,
                SUM(CASE WHEN DATEDIFF(NOW(), dt.date_of_birth) BETWEEN 3660 AND 7304 AND dt.gender = 'Female' AND (dt.rank IS NOT NULL) THEN 1 ELSE 0 END) AS pf_age_10_19_years,
                SUM(CASE WHEN DATEDIFF(NOW(), dt.date_of_birth) BETWEEN 3660 AND 7304 AND dt.gender = 'Female' AND (dt.rank IS NULL) THEN 1 ELSE 0 END) AS cf_age_10_19_years,
                SUM(CASE WHEN DATEDIFF(NOW(), dt.date_of_birth) BETWEEN 7305 AND 14600 AND dt.gender = 'Male' AND (dt.rank IS NOT NULL) THEN 1 ELSE 0 END) AS pm_age_20_40_years,
                SUM(CASE WHEN DATEDIFF(NOW(), dt.date_of_birth) BETWEEN 7305 AND 14600 AND dt.gender = 'Male' AND (dt.rank IS NULL) THEN 1 ELSE 0 END) AS cm_age_20_40_years,
                SUM(CASE WHEN DATEDIFF(NOW(), dt.date_of_birth) BETWEEN 7305 AND 14600 AND dt.gender = 'Female' AND (dt.rank IS NOT NULL) THEN 1 ELSE 0 END) AS pf_age_20_40_years,
                SUM(CASE WHEN DATEDIFF(NOW(), dt.date_of_birth) BETWEEN 7305 AND 14600 AND dt.gender = 'Female' AND (dt.rank IS NULL) THEN 1 ELSE 0 END) AS cf_age_20_40_years,
                SUM(CASE WHEN DATEDIFF(NOW(), dt.date_of_birth) >= 14601 AND dt.gender = 'Male' AND (dt.rank IS NOT NULL) THEN 1 ELSE 0 END) AS pm_age_41_above,
                SUM(CASE WHEN DATEDIFF(NOW(), dt.date_of_birth) >= 14601 AND dt.gender = 'Male' AND (dt.rank IS NULL) THEN 1 ELSE 0 END) AS cm_age_41_above,
                SUM(CASE WHEN DATEDIFF(NOW(), dt.date_of_birth) >= 14601 AND dt.gender = 'Female' AND (dt.rank IS NOT NULL) THEN 1 ELSE 0 END) AS pf_age_41_above,
                SUM(CASE WHEN DATEDIFF(NOW(), dt.date_of_birth) >= 14601 AND dt.gender = 'Female' AND (dt.rank IS NULL) THEN 1 ELSE 0 END) AS cf_age_41_above
                FROM diagnosis d
                LEFT JOIN view_diagnosis_tracking dt ON dt.diagnosis = d.item
                WHERE d.group_id = :group_id $sql_string GROUP BY d.item";

            foreach ($disease_groups as $disease_group) {
                $stmt = $db->prepare($query);
                $group_name = $disease_group['group'];
                $group_id = $disease_group['id'];
                $letter = $disease_group['letter'];

                $stmt->execute([':group_id' => $group_id]);
            ?>

                <tr class="header-row">
                    <td rowspan="3">S/No</td>
                    <td rowspan="3">Disease Condition</td>
                    <td colspan="4">0 - 28 days</td>
                    <td colspan="4">1 - 11 Months</td>
                    <td colspan="4">11 - 59 Months</td>
                    <td colspan="4">5 - 9 Years</td>
                    <td colspan="4">10 - 19 Years</td>
                    <td colspan="4">20 - 40 Years</td>
                    <td colspan="4">41+ Years</td>
                </tr>
                <tr class="header-row">
                    <td colspan="2" class="police-cell">Police</td>
                    <td colspan="2" class="civilian-cell">Civilian</td>
                    <td colspan="2" class="police-cell">Police</td>
                    <td colspan="2" class="civilian-cell">Civilian</td>
                    <td colspan="2" class="police-cell">Police</td>
                    <td colspan="2" class="civilian-cell">Civilian</td>
                    <td colspan="2" class="police-cell">Police</td>
                    <td colspan="2" class="civilian-cell">Civilian</td>
                    <td colspan="2" class="police-cell">Police</td>
                    <td colspan="2" class="civilian-cell">Civilian</td>
                    <td colspan="2" class="police-cell">Police</td>
                    <td colspan="2" class="civilian-cell">Civilian</td>
                    <td colspan="2" class="police-cell">Police</td>
                    <td colspan="2" class="civilian-cell">Civilian</td>
                </tr>
                <tr class="header-row">
                    <td class="male-cell">Male</td>
                    <td class="female-cell">Female</td>
                    <td class="male-cell">Male</td>
                    <td class="female-cell">Female</td>
                    <td class="male-cell">Male</td>
                    <td class="female-cell">Female</td>
                    <td class="male-cell">Male</td>
                    <td class="female-cell">Female</td>
                    <td class="male-cell">Male</td>
                    <td class="female-cell">Female</td>
                    <td class="male-cell">Male</td>
                    <td class="female-cell">Female</td>
                    <td class="male-cell">Male</td>
                    <td class="female-cell">Female</td>
                    <td class="male-cell">Male</td>
                    <td class="female-cell">Female</td>
                    <td class="male-cell">Male</td>
                    <td class="female-cell">Female</td>
                    <td class="male-cell">Male</td>
                    <td class="female-cell">Female</td>
                    <td class="male-cell">Male</td>
                    <td class="female-cell">Female</td>
                    <td class="male-cell">Male</td>
                    <td class="female-cell">Female</td>
                    <td class="male-cell">Male</td>
                    <td class="female-cell">Female</td>
                    <td class="male-cell">Male</td>
                    <td class="female-cell">Female</td>
                </tr>
                <tr class="age-group-row">
                    <td><?= $letter; ?></td>
                    <td><?= $group_name; ?></td>
                    <td colspan="28"></td>
                </tr>
                <?php
                $sn = 1;
                $total_pm_age_0_28_days = $total_pf_age_0_28_days = $total_cm_age_0_28_days = $total_cf_age_0_28_days = 0;
                $total_pm_age_1_11_months = $total_pf_age_1_11_months = $total_cm_age_1_11_months = $total_cf_age_1_11_months = 0;
                $total_pm_age_11_59_months = $total_pf_age_11_59_months = $total_cm_age_11_59_months = $total_cf_age_11_59_months = 0;
                $total_pm_age_5_9_years = $total_pf_age_5_9_years = $total_cm_age_5_9_years = $total_cf_age_5_9_years = 0;
                $total_pm_age_10_19_years = $total_pf_age_10_19_years = $total_cm_age_10_19_years = $total_cf_age_10_19_years = 0;
                $total_pm_age_20_40_years = $total_pf_age_20_40_years = $total_cm_age_20_40_years = $total_cf_age_20_40_years = 0;
                $total_pm_age_41_above = $total_pf_age_41_above = $total_cm_age_41_above = $total_cf_age_41_above = 0;

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $total_pm_age_0_28_days += $row['pm_age_0_28_days'];
                    $total_pf_age_0_28_days += $row['pf_age_0_28_days'];
                    $total_cm_age_0_28_days += $row['cm_age_0_28_days'];
                    $total_cf_age_0_28_days += $row['cf_age_0_28_days'];

                    $total_pm_age_1_11_months += $row['pm_age_1_11_months'];
                    $total_pf_age_1_11_months += $row['pf_age_1_11_months'];
                    $total_cm_age_1_11_months += $row['cm_age_1_11_months'];
                    $total_cf_age_1_11_months += $row['cf_age_1_11_months'];

                    $total_pm_age_11_59_months += $row['pm_age_11_59_months'];
                    $total_pf_age_11_59_months += $row['pf_age_11_59_months'];
                    $total_cm_age_11_59_months += $row['cm_age_11_59_months'];
                    $total_cf_age_11_59_months += $row['cf_age_11_59_months'];

                    $total_pm_age_5_9_years += $row['pm_age_5_9_years'];
                    $total_pf_age_5_9_years += $row['pf_age_5_9_years'];
                    $total_cm_age_5_9_years += $row['cm_age_5_9_years'];
                    $total_cf_age_5_9_years += $row['cf_age_5_9_years'];

                    $total_pm_age_10_19_years += $row['pm_age_10_19_years'];
                    $total_pf_age_10_19_years += $row['pf_age_10_19_years'];
                    $total_cm_age_10_19_years += $row['cm_age_10_19_years'];
                    $total_cf_age_10_19_years += $row['cf_age_10_19_years'];

                    $total_pm_age_20_40_years += $row['pm_age_20_40_years'];
                    $total_pf_age_20_40_years += $row['pf_age_20_40_years'];
                    $total_cm_age_20_40_years += $row['cm_age_20_40_years'];
                    $total_cf_age_20_40_years += $row['cf_age_20_40_years'];

                    $total_pm_age_41_above += $row['pm_age_41_above'];
                    $total_pf_age_41_above += $row['pf_age_41_above'];
                    $total_cm_age_41_above += $row['cm_age_41_above'];
                    $total_cf_age_41_above += $row['cf_age_41_above'];

                ?>
                    <tr>
                        <td><?= $sn++; ?></td>
                        <td><?= $row['item']; ?></td>
                        <td><?= $row['pm_age_0_28_days']; ?></td>
                        <td><?= $row['pf_age_0_28_days']; ?></td>
                        <td><?= $row['cm_age_0_28_days']; ?></td>
                        <td><?= $row['cf_age_0_28_days']; ?></td>
                        <td><?= $row['pm_age_1_11_months']; ?></td>
                        <td><?= $row['pf_age_1_11_months']; ?></td>
                        <td><?= $row['cm_age_1_11_months']; ?></td>
                        <td><?= $row['cf_age_1_11_months']; ?></td>
                        <td><?= $row['pm_age_11_59_months']; ?></td>
                        <td><?= $row['pf_age_11_59_months']; ?></td>
                        <td><?= $row['cm_age_11_59_months']; ?></td>
                        <td><?= $row['cf_age_11_59_months']; ?></td>
                        <td><?= $row['pm_age_5_9_years']; ?></td>
                        <td><?= $row['pf_age_5_9_years']; ?></td>
                        <td><?= $row['cm_age_5_9_years']; ?></td>
                        <td><?= $row['cf_age_5_9_years']; ?></td>
                        <td><?= $row['pm_age_10_19_years']; ?></td>
                        <td><?= $row['pf_age_10_19_years']; ?></td>
                        <td><?= $row['cm_age_10_19_years']; ?></td>
                        <td><?= $row['cf_age_10_19_years']; ?></td>
                        <td><?= $row['pm_age_20_40_years']; ?></td>
                        <td><?= $row['pf_age_20_40_years']; ?></td>
                        <td><?= $row['cm_age_20_40_years']; ?></td>
                        <td><?= $row['cf_age_20_40_years']; ?></td>
                        <td><?= $row['pm_age_41_above']; ?></td>
                        <td><?= $row['pf_age_41_above']; ?></td>
                        <td><?= $row['cm_age_41_above']; ?></td>
                        <td><?= $row['cf_age_41_above']; ?></td>
                    </tr>
                <?php
                    $sn++;
                }

                // Output subtotal rows
                ?>
                <tr class="age-group-row">
                    <td colspan="2">Subtotal</td>
                    <td><?= $total_pm_age_0_28_days; ?></td>
                    <td><?= $total_pf_age_0_28_days; ?></td>
                    <td><?= $total_cm_age_0_28_days; ?></td>
                    <td><?= $total_cf_age_0_28_days; ?></td>
                    <td><?= $total_pm_age_1_11_months; ?></td>
                    <td><?= $total_pf_age_1_11_months; ?></td>
                    <td><?= $total_cm_age_1_11_months; ?></td>
                    <td><?= $total_cf_age_1_11_months; ?></td>
                    <td><?= $total_pm_age_11_59_months; ?></td>
                    <td><?= $total_pf_age_11_59_months; ?></td>
                    <td><?= $total_cm_age_11_59_months; ?></td>
                    <td><?= $total_cf_age_11_59_months; ?></td>
                    <td><?= $total_pm_age_5_9_years; ?></td>
                    <td><?= $total_pf_age_5_9_years; ?></td>
                    <td><?= $total_cm_age_5_9_years; ?></td>
                    <td><?= $total_cf_age_5_9_years; ?></td>
                    <td><?= $total_pm_age_10_19_years; ?></td>
                    <td><?= $total_pf_age_10_19_years; ?></td>
                    <td><?= $total_cm_age_10_19_years; ?></td>
                    <td><?= $total_cf_age_10_19_years; ?></td>
                    <td><?= $total_pm_age_20_40_years; ?></td>
                    <td><?= $total_pf_age_20_40_years; ?></td>
                    <td><?= $total_cm_age_20_40_years; ?></td>
                    <td><?= $total_cf_age_20_40_years; ?></td>
                    <td><?= $total_pm_age_41_above; ?></td>
                    <td><?= $total_pf_age_41_above; ?></td>
                    <td><?= $total_cm_age_41_above; ?></td>
                    <td><?= $total_cf_age_41_above; ?></td>
                </tr>
            <?php
                // Reset totals for the next group
                $total_pm_age_0_28_days = $total_pf_age_0_28_days = $total_cm_age_0_28_days = $total_cf_age_0_28_days = 0;
                $total_pm_age_1_11_months = $total_pf_age_1_11_months = $total_cm_age_1_11_months = $total_cf_age_1_11_months = 0;
                $total_pm_age_11_59_months = $total_pf_age_11_59_months = $total_cm_age_11_59_months = $total_cf_age_11_59_months = 0;
                $total_pm_age_5_9_years = $total_pf_age_5_9_years = $total_cm_age_5_9_years = $total_cf_age_5_9_years = 0;
                $total_pm_age_10_19_years = $total_pf_age_10_19_years = $total_cm_age_10_19_years = $total_cf_age_10_19_years = 0;
                $total_pm_age_20_40_years = $total_pf_age_20_40_years = $total_cm_age_20_40_years = $total_cf_age_20_40_years = 0;
                $total_pm_age_41_above = $total_pf_age_41_above = $total_cm_age_41_above = $total_cf_age_41_above = 0;
            }
            ?>
        </table>
    </body>

    </html>

<?php

    $content = ob_get_clean();


    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="disease_data.xls"');
    header('Cache-Control: max-age=0');


    echo $content;
} else {
    header("location: ../index.php?datab");
    exit;
}
?>