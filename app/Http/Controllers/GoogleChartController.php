<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;  

class GoogleChartController extends Controller
{
    // Month-wise Line Chart
    public function index(Request $request)
    {
        $year = $request->input('year', date('Y'));

        $userData = User::selectRaw(
            'COUNT(*) as count, MONTH(created_at) as month, MONTHNAME(created_at) as month_name'
        )
            ->whereYear('created_at', $year)
            ->groupByRaw('MONTH(created_at), MONTHNAME(created_at)')
            ->pluck('count', 'month');

        $allMonths = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthName = Carbon::create()->month($i)->format('F');
            $allMonths[$monthName] = $userData[$i] ?? 0;
        }

        // ===== NEW: Growth Calculation =====
        $currentMonth = date('m');

        $currentMonthCount = User::whereYear('created_at', $year)
            ->whereMonth('created_at', $currentMonth)
            ->count();

        // Handle previous month
        if ($currentMonth == 1) {
            $previousMonthCount = User::whereYear('created_at', $year - 1)
                ->whereMonth('created_at', 12)
                ->count();
        } else {
            $previousMonthCount = User::whereYear('created_at', $year)
                ->whereMonth('created_at', $currentMonth - 1)
                ->count();
        }

        $growth = 0;
        if ($previousMonthCount > 0) {
            $growth = (($currentMonthCount - $previousMonthCount) / $previousMonthCount) * 100;
        }

        return view('chart', [
            'users' => $allMonths,
            'selectedYear' => $year,
            'growth' => round($growth, 2),
            'currentMonthCount' => $currentMonthCount
        ]);
    }

    // Quarterly Pie Chart
    public function quarterChart(Request $request)
    {
        $year = $request->input('year', date('Y')); // default current year

        $userData = User::selectRaw(
            'COUNT(*) as count, QUARTER(created_at) as quarter'
        )
            ->whereYear('created_at', $year)
            ->groupByRaw('QUARTER(created_at)')
            ->pluck('count', 'quarter');

        $allQuarters = [];
        for ($i = 1; $i <= 4; $i++) {
            $allQuarters["Q$i"] = $userData[$i] ?? 0;
        }

        return view('quarter_chart', [
            'users' => $allQuarters,
            'selectedYear' => $year
        ]);
    }
}
