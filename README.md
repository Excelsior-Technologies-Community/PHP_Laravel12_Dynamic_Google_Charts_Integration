# PHP_Laravel12_Dynamic_Google_Charts_Integration

---

## Project Description:

This project is a Laravel 12 application that demonstrates dynamic integration of Google Charts to visualize user registration data month-wise.

It fetches data from the database, processes it in the controller, and renders a Line Chart using Google Charts on a Blade view. This allows you to see trends in user registrations over the year.



## Features

Dynamic Data Visualization:
Shows the number of users registered per month dynamically from the database.

Google Charts Integration:
Uses Google Charts to draw Line Charts, which are interactive and visually appealing.

Laravel 12 Project Structure:
Follows standard Laravel 12 MVC architecture with controllers, views, and models.

Dummy Data Generation:
You can use Laravel Factories to create dummy user data for testing the chart.

## How it Works

1. `GoogleChartController` fetches monthly user registration counts from the database.
2. Prepares data for all 12 months (shows 0 if no users for a month).
3. Passes the data to `chart.blade.php`.
4. Google Charts draws a dynamic Line Chart in the browser.



## Requirements
- PHP >= 8.1
- Laravel 12
- MySQL
- Composer
- Web browser



---



# Project SetUp

---

## STEP 1: Create New Laravel 12 Project

### Run Command :

```
composer create-project laravel/laravel:^12.0 PHP_Laravel12_Dynamic_Google_Charts_Integration

```

### Go inside project:

```
cd PHP_Laravel12_Dynamic_Google_Charts_Integration

```

Make sure Laravel 12 installed successfully.



## STEP 2: Database Configuration

### Open .env file and update database credentials:

```

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dynamic_google_integration
DB_USERNAME=root
DB_PASSWORD=

```

### Create database:

```
dynamic_google_integration

```



## Step 3: Create Controller

### Run Command :

```

php artisan make:controller GoogleChartController


```

### app/Http/Controllers/GoogleChartController.php

```

<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use App\Models\User;
  use Carbon\Carbon;

class GoogleChartController extends Controller
{
  

public function index()
{
    // Get user counts grouped by month
    $userData = User::selectRaw(
        'COUNT(*) as count, MONTH(created_at) as month, MONTHNAME(created_at) as month_name'
    )
    ->whereYear('created_at', date('Y'))
    ->groupByRaw('MONTH(created_at), MONTHNAME(created_at)')
    ->pluck('count', 'month');

    // Prepare data for all months
    $allMonths = [];
    for ($i = 1; $i <= 12; $i++) {
        $monthName = Carbon::create()->month($i)->format('F'); // e.g., January
        $allMonths[$monthName] = $userData[$i] ?? 0; // 0 if no users
    }

    return view('chart', ['users' => $allMonths]);
}

}

```

## Step 4: Create Blade View

### Now create the chart view file:

```
resources/views/chart.blade.php

```

### Paste: resources/views/chart.blade.php

```

<!DOCTYPE html>
<html>
<head>
    <title>Laravel 12 Google Chart Example</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <div class="card mt-5">
            <h3 class="card-header p-3">Laravel 12 Google Chart Example</h3>
            <div class="card-body"> 
                <div id="google-line-chart" style="height: 500px"></div>
            </div>
        </div>
    </div>

    <!-- Google Charts loader JS -->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        // Load Google Charts
        google.charts.load('current', {'packages':['corechart']});
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var data = google.visualization.arrayToDataTable([
                ['Month Name', 'Register Users Count'],
                @php
                    foreach($users as $monthName => $count) {
                        echo "['".$monthName."', ".$count."],";
                    }
                @endphp
            ]);

            var options = {
                title: 'Register Users Month Wise',
                curveType: 'function',
                legend: { position: 'bottom' }
            };

            var chart = new google.visualization.LineChart(
                document.getElementById('google-line-chart')
            );

            chart.draw(data, options);
        }
    </script>
</body>
</html>

```
This includes Google Charts loader and draws a LineChart using your database data.



## STEP 5: Routes

### File: routes/web.php

Defines routes :

```

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleChartController;


Route::get('/', function () {
    return view('welcome');
});


Route::get('chart', [GoogleChartController::class, 'index']);

```


## Step 6: Add Dummy User Records

To see dynamic results, you need some monthly users.

### In terminal:

```
php artisan tinker

```

### Then:

```
User::factory()->count(30)->create();

```

This will randomly fill users across dates — helpful for showing month-wise chart data.



## Step 7:Run Your Laravel App

### Start the development server:

```
php artisan serve

```

### Then open in browser:

```
 http://localhost:8000/chart

```
You should see a beautiful Google Line Chart of registered users month-wise this type:


<img width="1914" height="963" alt="Screenshot 2026-01-02 124505" src="https://github.com/user-attachments/assets/edcc5220-0935-47af-b743-3818cb2b41eb" />






---

# Project Folder Structure:

```

PHP_Laravel12_Dynamic_Google_Charts_Integration/
├── app/
│   ├── Console/
│   ├── Exceptions/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── GoogleChartController.php       <-- Your chart controller
│   │   ├── Middleware/
│   │   └── Kernel.php
│   ├── Models/
│   │   └── User.php                            <-- User model
│   └── Providers/
├── bootstrap/
├── config/
├── database/
│   ├── factories/
│   │   └── UserFactory.php                     <-- Optional for dummy users
│   ├── migrations/
│   │   └── 2014_10_12_000000_create_users_table.php
│   └── seeders/
├── public/
│   └── index.php
├── resources/
│   ├── js/
│   ├── lang/
│   ├── sass/
│   └── views/
│       ├── welcome.blade.php                  <-- Default welcome page
│       └── chart.blade.php                    <-- Your Google Chart view
├── routes/
│   └── web.php                                <-- Route definitions
├── storage/
├── tests/
├── .env                                       <-- DB config here
├── artisan
├── composer.json
└── package.json

```
