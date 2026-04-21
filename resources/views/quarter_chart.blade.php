<!DOCTYPE html>
<html>

<head>
    <title>Quarterly Analytics Dashboard</title>

    <!-- Bootstrap + Font -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        body {
            background: #f4f7fb;
            font-family: 'Poppins', sans-serif;
        }

        .dashboard-header {
            background: linear-gradient(135deg, #36b9cc, #4e73df);
            color: white;
            border-radius: 12px 12px 0 0;
        }

        .card {
            border: none;
            border-radius: 12px;
        }

        .custom-card {
            border-radius: 12px;
            transition: 0.3s;
        }

        .custom-card:hover {
            transform: translateY(-5px);
        }

        .stat-card {
            border-left: 5px solid #36b9cc;
        }

        select {
            border-radius: 8px;
            padding: 5px 10px;
            border: 1px solid #ddd;
        }

        .chart-container {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
        }
    </style>
</head>

<body>

<div class="container mt-5">

    <div class="card shadow-lg">

        <!-- Header -->
        <div class="dashboard-header p-4">
            <h3 class="mb-0">📈 Quarterly Analytics Dashboard</h3>
        </div>

        <div class="card-body">

            <!-- Year Filter -->
            <form method="GET" class="mb-4 text-end">
                <label class="me-2 fw-semibold">Select Year:</label>
                <select name="year" onchange="this.form.submit()">
                    @for($y = date('Y')-5; $y <= date('Y'); $y++)
                        <option value="{{ $y }}" {{ ($selectedYear ?? date('Y')) == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endfor
                </select>
            </form>

            <!-- Stats -->
            <div class="row mb-4">

                <!-- Total Users -->
                <div class="col-md-6">
                    <div class="card custom-card shadow-sm p-3 text-center stat-card">
                        <h6 class="text-muted">Total Users</h6>
                        <h3>{{ array_sum($users) }}</h3>
                    </div>
                </div>

                <!-- Active Quarters -->
                <div class="col-md-6">
                    <div class="card custom-card shadow-sm p-3 text-center stat-card">
                        <h6 class="text-muted">Active Quarters</h6>
                        <h3>{{ collect($users)->filter(fn($v) => $v > 0)->count() }}</h3>
                    </div>
                </div>

            </div>

            <!-- Chart -->
            <div class="chart-container shadow-sm">
                <div id="google-pie-chart" style="height: 500px;"></div>
            </div>

        </div>
    </div>

</div>

<!-- Google Charts -->
<script src="https://www.gstatic.com/charts/loader.js"></script>

<script>
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {
        var data = google.visualization.arrayToDataTable([
            ['Quarter', 'Users'],
            @php
                foreach($users as $quarter => $count) {
                    echo "['".$quarter."', ".$count."],";
                }
            @endphp
        ]);

        var options = {
            title: 'Quarterly User Distribution',
            pieHole: 0.4,
            legend: { position: 'right' },
            chartArea: { width: '85%', height: '75%' }
        };

        var chart = new google.visualization.PieChart(
            document.getElementById('google-pie-chart')
        );

        chart.draw(data, options);
    }
</script>

</body>
</html>