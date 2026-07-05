<!DOCTYPE html>
<html>

<head>
    <title>Pharmacy Dashboard</title>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            font-family: "Segoe UI", Arial;
            background: #f4f6f9;
            margin: 0;
            padding: 20px;
        }

        h2 {
            color: #333;
        }

        .card {
            background: #fff;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }

        form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        input,
        select {
            padding: 6px 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            padding: 8px 14px;
            border: none;
            background: #007bff;
            color: #fff;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background: #0056b3;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th {
            background: #007bff;
            color: #fff;
            padding: 8px;
        }

        td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }

        canvas {
            max-height: 350px;
        }

        /* PRINT CLEAN VIEW */
        @media print {
            button {
                display: none;
            }

            body {
                background: #fff;
            }

            .card {
                box-shadow: none;
                border: 1px solid #ddd;
            }
        }
    </style>
</head>

<body>

    <h2>Pharmacy Analytics Dashboard</h2>

    <div class="card">
        <form id="filterForm">

            From: <input type="date" name="from" required>
            To: <input type="date" name="to" required>

            Hospital No: <input type="text" name="hospital_no">

            Chart Type:
            <select name="chart_reporting_type">
                <option value="insurance_type">Insurance Type</option>
                <option value="insurance_name">Insurance Names</option>
                <option value="prepared_by">Prepared By</option>
                <option value="dsp_by">Dispensed By</option>
                <option value="item_services">Medication/Drugs</option>
                <option value="pay_mode">Claim And Cash</option>
                <option value="category">Drug Category</option>
                <option value="generic_name">Generic Name</option>
            </select>

            Metric:
            <select name="metric_type">
                <option value="claim">Claim Only</option>
                <option value="pay">Cash(Paid) Only</option>
                <option value="combined">Claim + Cash(Paid)</option>
            </select>

            <button type="submit">Load Report</button>

        </form>
    </div>

    <div class="card">
        <canvas id="barChart"></canvas>
    </div>

    <div class="card">
        <canvas id="pieChart"></canvas>
    </div>

    <div class="card">

        <button onclick="downloadBar()">Download Bar</button>
        <button onclick="downloadPie()">Download Pie</button>
        <button onclick="downloadCSV()">Download CSV</button>
        <button onclick="printReport()">Print</button>
        <button onclick="goBack()">← Back to Pharmacy</button>

    </div>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Label</th>
                    <th>Count</th>
                    <th>Claim</th>
                    <th>Cash(Paid)</th>
                </tr>
            </thead>
            <tbody id="reportTable"></tbody>
        </table>
    </div>

    <script>
        let barChart, pieChart;

        /* LOAD REPORT */
        $("#filterForm").submit(function(e) {
            e.preventDefault();

            let formData = $(this).serialize();
            let metric = $("select[name='metric_type']").val();

            let labelName = 'Amount';
            if (metric === 'claim') labelName = 'Claim Amount';
            if (metric === 'pay') labelName = 'Cash(Paid) Amount';
            if (metric === 'combined') labelName = 'Total Amount';

            $.post("ajax_pharmacy_report.php", formData, function(res) {

                let data = JSON.parse(res);

                let html = "";
                data.table.forEach(r => {
                    html += `<tr>
                <td>${r.label}</td>
                <td>${r.total_count}</td>
                <td>${r.total_claim}</td>
                <td>${r.total_pay}</td>
            </tr>`;
                });

                $("#reportTable").html(html);

                if (barChart) barChart.destroy();
                if (pieChart) pieChart.destroy();

                barChart = new Chart(document.getElementById("barChart"), {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: labelName,
                            data: data.values
                        }]
                    }
                });

                pieChart = new Chart(document.getElementById("pieChart"), {
                    type: 'pie',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            data: data.values
                        }]
                    }
                });

            });
        });


        /* DOWNLOAD CHARTS */
        function downloadBar() {
            let a = document.createElement('a');
            a.href = document.getElementById('barChart').toDataURL();
            a.download = 'bar_chart.png';
            a.click();
        }

        function downloadPie() {
            let a = document.createElement('a');
            a.href = document.getElementById('pieChart').toDataURL();
            a.download = 'pie_chart.png';
            a.click();
        }

        /* CSV */
        function downloadCSV() {

            if (!$("input[name='from']").val() || !$("input[name='to']").val()) {
                alert("Please select date range first");
                return;
            }

            let params = $("#filterForm").serialize();
            window.location.href = "export_csv.php?" + params;
        }

        /* PRINT */
        function printReport() {
            window.print();
        }

        /* BACK */
        function goBack() {
            window.location.href = "../../pharmacy/index.php";
        }
    </script>

</body>

</html>