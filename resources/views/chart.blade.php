<!DOCTYPE html>
<html>

<head>
    <title>Laravel 12 Analytics Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        body {
            background: #f4f7fb;
            font-family: 'Poppins', sans-serif;
        }

        .dashboard-header {
            background: linear-gradient(135deg, #4e73df, #1cc88a);
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
            border-left: 5px solid #4e73df;
        }

        .growth-positive {
            color: #1cc88a;
            font-weight: 600;
        }

        .growth-negative {
            color: #e74a3b;
            font-weight: 600;
        }

        select {
            border-radius: 8px;
            padding: 5px 10px;
            border: 1px solid #ddd;
            outline: none;
        }

        .chart-container {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            min-height: 500px;
        }
    </style>
</head>

<body>

<div class="container mt-5 mb-5">

    <div class="card shadow-lg">
        
        <div class="dashboard-header p-4">
            <h3 class="mb-0">📈 Advanced Analytics Dashboard</h3>
        </div>

        <div class="card-body">

            <div class="row mb-4 align-items-center">
                <div class="col-md-6">
                    <label class="me-2 fw-semibold">Chart Type:</label>
                    <select id="chartType">
                        <option value="LineChart">Line Chart</option>
                        <option value="ColumnChart">Bar Chart</option>
                        <option value="AreaChart">Area Chart</option>
                    </select>
                </div>
                <div class="col-md-6 text-end">
                    <label class="me-2 fw-semibold">Select Year:</label>
                    <select id="yearFilter">
                        @for($y = date('Y')-5; $y <= date('Y'); $y++)
                            <option value="{{ $y }}" {{ ($selectedYear ?? date('Y')) == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card custom-card shadow-sm p-3 text-center stat-card">
                        <h6 class="text-muted">Total Users</h6>
                        <h3 id="stat-total">{{ $total }}</h3>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card custom-card shadow-sm p-3 text-center stat-card">
                        <h6 class="text-muted">Current Month</h6>
                        <h3 id="stat-current">{{ $current }}</h3>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card custom-card shadow-sm p-3 text-center stat-card">
                        <h6 class="text-muted">YoY Growth</h6>
                        <h3 id="stat-growth" class="{{ $growth >= 0 ? 'growth-positive' : 'growth-negative' }}">
                            {{ $growth }}%
                        </h3>
                    </div>
                </div>
            </div>

            <div class="chart-container shadow-sm position-relative">
                <div id="google-dynamic-chart" style="height: 500px; width: 100%;"></div>
            </div>

        </div>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://www.gstatic.com/charts/loader.js"></script>

<script>
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(refreshChart);

    $('#yearFilter, #chartType').on('change', function() {
        refreshChart();
    });

    function refreshChart() {
        let year = $('#yearFilter').val();
        
        $.ajax({
            url: "{{ url('chart') }}",
            type: "GET",
            data: { year: year },
            success: function(response) {
                $('#stat-total').text(response.total);
                $('#stat-current').text(response.current);
                
                let growthElement = $('#stat-growth');
                growthElement.text(response.growth + '%');
                growthElement.removeClass('growth-positive growth-negative');
                growthElement.addClass(response.growth >= 0 ? 'growth-positive' : 'growth-negative');

                drawChart(response.chartData);
            }
        });
    }

    function drawChart(chartData) {
        var dataTable = new google.visualization.DataTable();
        dataTable.addColumn('string', 'Month');
        dataTable.addColumn('number', 'Current Year');
        dataTable.addColumn('number', 'Previous Year');

        chartData.forEach(function(item) {
            dataTable.addRow([item.month, item.current, item.previous]);
        });

        var chartType = $('#chartType').val();

        var options = {
            title: 'User Registration Comparison (Current vs Previous Year)',
            curveType: 'function',
            legend: { position: 'bottom' },
            animation: { startup: true, duration: 1000, easing: 'out' },
            colors: ['#4e73df', '#1cc88a'],
            chartArea: { width: '85%', height: '70%' },
            pointSize: 5,
            vAxis: { minValue: 0 }
        };

        var chart = new google.visualization[chartType](document.getElementById('google-dynamic-chart'));
        chart.draw(dataTable, options);
    }
</script>

</body>
</html>