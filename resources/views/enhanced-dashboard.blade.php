{{-- resources/views/enhanced-dashboard.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Advanced Analytics Dashboard | Laravel 12 + Google Charts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * { font-family: 'Poppins', sans-serif; }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
            font-weight: 600;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .chart-card {
            margin-bottom: 30px;
        }
        
        .chart-container {
            padding: 20px;
            min-height: 450px;
        }
        
        .btn-export {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-export:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .filter-section {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .chart-type-btn {
            margin: 5px;
            padding: 8px 15px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f0f0f0;
            border: none;
        }
        
        .chart-type-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .loading {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .growth-positive { color: #10b981; }
        .growth-negative { color: #ef4444; }
    </style>
</head>
<body>

<div class="dashboard-container">
    
    <!-- Loading Spinner -->
    <div class="loading">
        <div class="spinner-border text-light" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    
    <!-- Header -->
    <div class="text-center mb-4">
        <h1 style="color: white; font-weight: 700;">
          Advanced Analytics Dashboard
        </h1>
        <p style="color: rgba(255,255,255,0.9);">Dynamic Google Charts Integration | Laravel 12</p>
    </div>
    
    <!-- Filter Section -->
    <div class="filter-section">
        <div class="row align-items-center">
            <div class="col-md-4">
                <label class="fw-bold mb-2">
                   Date Range
                </label>
                <div class="row">
                    <div class="col">
                        <input type="date" id="startDate" class="form-control" value="{{ $startDate }}">
                    </div>
                    <div class="col">
                        <input type="date" id="endDate" class="form-control" value="{{ $endDate }}">
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <label class="fw-bold mb-2">
                    Chart Type
                </label>
                <select id="chartTypeSelect" class="form-control">
                    <option value="monthly"> Monthly Registrations</option>
                    <option value="department"> Department Distribution</option>
                    <option value="city"> City Distribution</option>
                    <option value="age"> Age Group Distribution</option>
                    <option value="prediction"> User Growth Prediction</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="fw-bold mb-2">&nbsp;</label>
                <div>
                    <button onclick="refreshChart()" class="btn btn-primary w-100">
                      Refresh Data
                    </button>
                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col text-end">
                <button onclick="exportChartAsImage()" class="btn btn-export me-2">
                   Export as Image
                </button>
                <button onclick="exportData()" class="btn btn-export">
                   Export CSV
                </button>
            </div>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row mb-4" id="statsCards">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon">
                  
                </div>
                <div class="stat-value" id="statTotal">-</div>
                <div class="stat-label">Total Users</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon">
                   
                </div>
                <div class="stat-value" id="statAverage">-</div>
                <div class="stat-label">Monthly Average</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon">
                 
                </div>
                <div class="stat-value" id="statHighest">-</div>
                <div class="stat-label">Highest Month</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon">
                   
                </div>
                <div class="stat-value" id="statLowest">-</div>
                <div class="stat-label">Lowest Month</div>
            </div>
        </div>
    </div>
    
    <!-- Main Chart -->
    <div class="card chart-card">
        <div class="card-header">
            <i class="fas fa-chart-area"></i> Interactive Data Visualization
            <span class="float-end">
                <button class="btn btn-sm btn-light" onclick="toggleFullscreen()">
                 Fullscreen
                </button>
            </span>
        </div>
        <div class="chart-container" id="chartContainer">
            <div id="mainChart" style="height: 500px; width: 100%;"></div>
        </div>
    </div>
    
    <!-- Additional Insights -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    Quick Insights
                </div>
                <div class="card-body" id="insightsContent">
                    <p class="text-muted">Select a chart type to see insights...</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    Export Options
                </div>
                <div class="card-body text-center">
                    <button onclick="exportChartAsPDF()" class="btn btn-danger m-2">
                       Export as PDF
                    </button>
                    <button onclick="printChart()" class="btn btn-info m-2">
                         Print Chart
                    </button>
                    <button onclick="shareChart()" class="btn btn-success m-2">
                        Share
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://www.gstatic.com/charts/loader.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>
    let currentChart = null;
    
    google.charts.load('current', {'packages':['corechart', 'line', 'bar', 'table']});
    
    function refreshChart() {
        $('.loading').fadeIn();
        
        let startDate = $('#startDate').val();
        let endDate = $('#endDate').val();
        let chartType = $('#chartTypeSelect').val();
        
        $.ajax({
            url: "{{ url('api/chart-data') }}",
            type: "GET",
            data: {
                start_date: startDate,
                end_date: endDate,
                chart_type: chartType
            },
            success: function(response) {
                updateStatistics(response);
                drawChart(response, chartType);
                updateInsights(response, chartType);
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                alert('Error loading chart data. Please try again.');
            },
            complete: function() {
                $('.loading').fadeOut();
            }
        });
    }
    
    function updateStatistics(data) {
        if(data.total !== undefined) {
            $('#statTotal').text(data.total.toLocaleString());
        }
        if(data.average !== undefined) {
            $('#statAverage').text(data.average.toLocaleString());
        }
        if(data.highest !== undefined) {
            $('#statHighest').text(data.highest.toLocaleString());
        }
        if(data.lowest !== undefined) {
            $('#statLowest').text(data.lowest.toLocaleString());
        }
    }
    
    function drawChart(data, chartType) {
        google.charts.setOnLoadCallback(function() {
            let dataTable = new google.visualization.DataTable();
            
            switch(chartType) {
                case 'monthly':
                    dataTable.addColumn('string', 'Month');
                    dataTable.addColumn('number', 'Registrations');
                    dataTable.addColumn('number', 'Trend Line');
                    
                    for(let i = 0; i < data.months.length; i++) {
                        dataTable.addRow([data.months[i], data.counts[i], data.trend[i]]);
                    }
                    
                    let options = {
                        title: 'Monthly User Registrations with Trend',
                        curveType: 'function',
                        legend: { position: 'bottom' },
                        colors: ['#667eea', '#10b981'],
                        vAxis: { title: 'Number of Users', minValue: 0 },
                        hAxis: { title: 'Month', slantedText: true },
                        animation: { duration: 1000, easing: 'out' },
                        pointSize: 8,
                        pointShape: 'circle'
                    };
                    
                    let chart = new google.visualization.LineChart(document.getElementById('mainChart'));
                    chart.draw(dataTable, options);
                    currentChart = chart;
                    break;
                    
                case 'department':
                    dataTable.addColumn('string', 'Department');
                    dataTable.addColumn('number', 'Users');
                    
                    for(let i = 0; i < data.departments.length; i++) {
                        dataTable.addRow([data.departments[i], data.counts[i]]);
                    }
                    
                    let pieOptions = {
                        title: 'User Distribution by Department',
                        pieHole: 0.4,
                        legend: { position: 'right' },
                        colors: ['#667eea', '#764ba2', '#f093fb', '#4facfe', '#00f2fe', '#f6d365'],
                        chartArea: { width: '85%' }
                    };
                    
                    let pieChart = new google.visualization.PieChart(document.getElementById('mainChart'));
                    pieChart.draw(dataTable, pieOptions);
                    currentChart = pieChart;
                    break;
                    
                case 'city':
                    dataTable.addColumn('string', 'City');
                    dataTable.addColumn('number', 'Users');
                    
                    for(let i = 0; i < data.cities.length; i++) {
                        dataTable.addRow([data.cities[i], data.counts[i]]);
                    }
                    
                    let barOptions = {
                        title: 'Top 10 Cities by User Count',
                        legend: { position: 'none' },
                        colors: ['#667eea'],
                        vAxis: { title: 'Number of Users' },
                        hAxis: { title: 'City' }
                    };
                    
                    let barChart = new google.visualization.BarChart(document.getElementById('mainChart'));
                    barChart.draw(dataTable, barOptions);
                    currentChart = barChart;
                    break;
                    
                case 'age':
                    dataTable.addColumn('string', 'Age Group');
                    dataTable.addColumn('number', 'Users');
                    
                    for(let i = 0; i < data.groups.length; i++) {
                        dataTable.addRow([data.groups[i], data.counts[i]]);
                    }
                    
                    let columnOptions = {
                        title: 'User Distribution by Age Group',
                        legend: { position: 'none' },
                        colors: ['#10b981'],
                        vAxis: { title: 'Number of Users' }
                    };
                    
                    let columnChart = new google.visualization.ColumnChart(document.getElementById('mainChart'));
                    columnChart.draw(dataTable, columnOptions);
                    currentChart = columnChart;
                    break;
                    
                case 'prediction':
                    let combinedData = [];
                    combinedData.push(['Month', 'Historical', 'Predicted']);
                    
                    for(let i = 0; i < data.historicalMonths.length; i++) {
                        combinedData.push([data.historicalMonths[i], data.historicalCounts[i], null]);
                    }
                    
                    for(let i = 0; i < data.futureMonths.length; i++) {
                        combinedData.push([data.futureMonths[i], null, data.predictions[i]]);
                    }
                    
                    let predictionTable = google.visualization.arrayToDataTable(combinedData);
                    let predOptions = {
                        title: 'User Growth Prediction (Next 6 Months)',
                        curveType: 'function',
                        legend: { position: 'bottom' },
                        colors: ['#667eea', '#f093fb'],
                        vAxis: { title: 'Predicted Users' },
                        pointSize: 8
                    };
                    
                    let predChart = new google.visualization.LineChart(document.getElementById('mainChart'));
                    predChart.draw(predictionTable, predOptions);
                    currentChart = predChart;
                    break;
            }
        });
    }
    
    function updateInsights(data, chartType) {
        let insights = '';
        
        switch(chartType) {
            case 'monthly':
                insights = `
                    <h6> Monthly Insights</h6>
                    <ul class="list-unstyled">
                        <li> Total Users: <strong>${data.total.toLocaleString()}</strong></li>
                        <li></i> Monthly Average: <strong>${data.average.toLocaleString()}</strong></li>
                        <li> Peak Month: <strong>${data.highest.toLocaleString()} users</strong></li>
                        <li> Lowest Month: <strong>${data.lowest.toLocaleString()} users</strong></li>
                    </ul>
                    <div class="alert alert-info mt-2">
                       
                        ${data.highest > data.average * 1.5 ? 'Strong growth detected! Consider scaling resources.' : 'Steady growth pattern observed.'}
                    </div>
                `;
                break;
                
            case 'department':
                let topDept = data.departments[data.counts.indexOf(Math.max(...data.counts))];
                insights = `
                    <h6><i class="fas fa-building"></i> Department Insights</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-trophy"></i> Largest Department: <strong>${topDept}</strong></li>
                        <li><i class="fas fa-users"></i> Total Users: <strong>${data.total.toLocaleString()}</strong></li>
                    </ul>
                    <div class="alert alert-success mt-2">
                        <i class="fas fa-chart-pie"></i> ${topDept} department has the highest user engagement.
                    </div>
                `;
                break;
                
            case 'prediction':
                let totalPredicted = data.predictions.reduce((a,b) => a + b, 0);
                insights = `
                    <h6><i class="fas fa-chart-line"></i> Growth Prediction Insights</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-chart-line"></i> Next 6 Months Prediction: <strong>${totalPredicted.toLocaleString()} users</strong></li>
                        <li><i class="fas fa-calendar"></i> Monthly Average Prediction: <strong>${(totalPredicted/6).toFixed(0)} users</strong></li>
                    </ul>
                    <div class="alert alert-warning mt-2">
                        <i class="fas fa-chart-line"></i> Expected growth rate: ${((totalPredicted/6) / data.average * 100).toFixed(1)}% of current average
                    </div>
                `;
                break;
                
            default:
                insights = `<p class="text-muted">Select a different chart type to see detailed insights...</p>`;
        }
        
        $('#insightsContent').html(insights);
    }
    
    function exportChartAsImage() {
        html2canvas(document.getElementById('mainChart')).then(canvas => {
            let link = document.createElement('a');
            link.download = 'chart-image.png';
            link.href = canvas.toDataURL();
            link.click();
            
            // Show success notification
            alert('Chart exported successfully as PNG!');
        });
    }
    
    function exportChartAsPDF() {
        const { jsPDF } = window.jspdf;
        html2canvas(document.getElementById('mainChart')).then(canvas => {
            let imgData = canvas.toDataURL('image/png');
            let pdf = new jsPDF('landscape');
            pdf.addImage(imgData, 'PNG', 10, 10, 280, 150);
            pdf.save('chart-export.pdf');
            alert('PDF exported successfully!');
        });
    }
    
    function exportData() {
        let startDate = $('#startDate').val();
        let endDate = $('#endDate').val();
        window.location.href = `{{ url('export-data') }}?start_date=${startDate}&end_date=${endDate}`;
    }
    
    function printChart() {
        let printWindow = window.open('', '_blank');
        printWindow.document.write('<html><head><title>Print Chart</title>');
        printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">');
        printWindow.document.write('</head><body>');
        printWindow.document.write('<div class="container mt-5">');
        printWindow.document.write('<h2 class="text-center">Analytics Dashboard Report</h2>');
        printWindow.document.write('<hr>');
        printWindow.document.write(document.getElementById('mainChart').innerHTML);
        printWindow.document.write('<p class="text-muted mt-3">Generated on: ' + new Date().toLocaleString() + '</p>');
        printWindow.document.write('</div></body></html>');
        printWindow.document.close();
        printWindow.print();
    }
    
    function shareChart() {
        if(navigator.share) {
            navigator.share({
                title: 'Analytics Dashboard',
                text: 'Check out this user analytics data',
                url: window.location.href
            });
        } else {
            alert('Share feature not supported in this browser. You can copy the URL to share.');
        }
    }
    
    function toggleFullscreen() {
        let elem = document.getElementById('chartContainer');
        if (!document.fullscreenElement) {
            elem.requestFullscreen().catch(err => {
                alert(`Error: ${err.message}`);
            });
        } else {
            document.exitFullscreen();
        }
    }
    
    // Auto-refresh every 30 seconds
    setInterval(refreshChart, 30000);
    
    // Initial load
    $(document).ready(function() {
        refreshChart();
    });
</script>

</body>
</html>