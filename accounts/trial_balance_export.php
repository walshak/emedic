<?php
session_start();
include("../Connections/Conn.php");
include('inc/functions.php');

// Check if the form data was posted
if ($_POST && isset($_POST['trial_content'])) {
    $trial_content = $_POST['trial_content'];
    $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
    $end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';
    $hospital_name = isset($_POST['hospital_name']) ? $_POST['hospital_name'] : 'Hospital';
    
    // Format dates for display
    $period_text = '';
    if ($start_date && $end_date) {
        $period_text = 'Period: ' . date('d M Y', strtotime($start_date)) . ' to ' . date('d M Y', strtotime($end_date));
    }
    
    // Set headers for Excel download
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="Trial_Balance_' . date('Y-m-d_H-i-s') . '.xls"');
    header('Cache-Control: max-age=0');
    header('Pragma: public');
    
    // Output Excel-compatible HTML
    echo '<?xml version="1.0" encoding="UTF-8"?>
    <html xmlns:o="urn:schemas-microsoft-com:office:office"
          xmlns:x="urn:schemas-microsoft-com:office:excel"
          xmlns="http://www.w3.org/TR/REC-html40">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="ProgId" content="Excel.Sheet" />
        <meta name="Generator" content="Microsoft Excel 11" />
        <!--[if gte mso 9]>
        <xml>
            <x:ExcelWorkbook>
                <x:ExcelWorksheets>
                    <x:ExcelWorksheet>
                        <x:Name>Trial Balance</x:Name>
                        <x:WorksheetOptions>
                            <x:Print>
                                <x:ValidPrinterInfo />
                            </x:Print>
                        </x:WorksheetOptions>
                    </x:ExcelWorksheet>
                </x:ExcelWorksheets>
            </x:ExcelWorkbook>
        </xml>
        <![endif]-->
        <style>
            body {
                font-family: Arial, sans-serif;
                font-size: 12px;
            }
            .header {
                font-size: 16px;
                font-weight: bold;
                text-align: center;
                margin-bottom: 10px;
            }
            .period-info {
                font-weight: bold;
                text-align: center;
                margin-bottom: 20px;
            }
            .trial-table {
                border-collapse: collapse;
                width: 100%;
                margin-top: 20px;
            }
            .trial-table th, .trial-table td {
                border: 1px solid #000;
                padding: 8px;
                text-align: left;
            }
            .trial-table th {
                background-color: #f0f0f0;
                font-weight: bold;
            }
            .trial-class-row {
                font-weight: bold;
                background-color: #e0e0e0;
                font-size: 14px;
            }
            .trial-group-row {
                font-weight: bold;
                background-color: #f0f0f0;
            }
            .trial-account-row {
                padding-left: 20px;
            }
            .amount-cell, .balance-cell {
                text-align: right;
                mso-number-format: "#,##0.00";
            }
            .summary-section {
                margin-top: 20px;
                font-weight: bold;
            }
            .toggle-link {
                display: none !important;
            }
            button, .btn {
                display: none !important;
            }
        </style>
    </head>
    <body>';
    
    echo '<div class="header">' . htmlspecialchars($hospital_name) . '</div>';
    echo '<div class="header">Trial Balance</div>';
    
    if ($period_text) {
        echo '<div class="period-info">' . htmlspecialchars($period_text) . '</div>';
    }
    
    // Process the trial content to clean it up for Excel
    // Remove toggle links and buttons
    $trial_content = preg_replace('/<span[^>]*class="toggle-link"[^>]*>.*?<\/span>/s', '', $trial_content);
    $trial_content = preg_replace('/<button[^>]*>.*?<\/button>/s', '', $trial_content);
    $trial_content = preg_replace('/<a[^>]*onclick[^>]*>([^<]*)<\/a>/', '$1', $trial_content);
    
    // Clean up any remaining onclick attributes and styling that might interfere
    $trial_content = preg_replace('/onclick="[^"]*"/i', '', $trial_content);
    $trial_content = preg_replace('/style="[^"]*display:\s*none[^"]*"/i', '', $trial_content);
    
    // Remove any remaining JavaScript or style attributes that could cause issues
    $trial_content = preg_replace('/\s*style="[^"]*"/i', '', $trial_content);
    $trial_content = preg_replace('/\s*class="toggle-link[^"]*"/i', '', $trial_content);
    
    echo $trial_content;
    
    echo '</body></html>';
    exit;
    
} else {
    // Redirect back to trial balance if accessed directly
    header('Location: trial_balance.php');
    exit;
}
?>
