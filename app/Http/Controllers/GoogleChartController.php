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
