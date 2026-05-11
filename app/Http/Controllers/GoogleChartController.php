<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;

class GoogleChartController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->input('year', date('Y'));

        $currentYearData = User::selectRaw('COUNT(*) as count, MONTH(created_at) as month')
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->pluck('count', 'month');

        $previousYearData = User::selectRaw('COUNT(*) as count, MONTH(created_at) as month')
            ->whereYear('created_at', $year - 1)
            ->groupBy('month')
            ->pluck('count', 'month');

        $chartData = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthName = Carbon::create()->month($i)->format('M');
            $chartData[] = [
                'month' => $monthName,
                'current' => $currentYearData[$i] ?? 0,
                'previous' => $previousYearData[$i] ?? 0
            ];
        }

        $currentMonth = (int)date('m');
        $currentMonthCount = $currentYearData[$currentMonth] ?? 0;

        if ($currentMonth == 1) {
            $previousMonthCount = User::whereYear('created_at', $year - 1)
                ->whereMonth('created_at', 12)
                ->count();
        } else {
            $previousMonthCount = $currentYearData[$currentMonth - 1] ?? 0;
        }

        $growth = 0;
        if ($previousMonthCount > 0) {
            $growth = (($currentMonthCount - $previousMonthCount) / $previousMonthCount) * 100;
        }

        $totalCount = array_sum($currentYearData->toArray());

        if ($request->ajax()) {
            return response()->json([
                'chartData' => $chartData,
                'total' => $totalCount,
                'current' => $currentMonthCount,
                'growth' => round($growth, 2)
            ]);
        }

        return view('chart', [
            'chartData' => $chartData,
            'selectedYear' => $year,
            'growth' => round($growth, 2),
            'total' => $totalCount,
            'current' => $currentMonthCount
        ]);
    }

    public function quarterChart(Request $request)
    {
        $year = $request->input('year', date('Y'));

        $userData = User::selectRaw('COUNT(*) as count, QUARTER(created_at) as quarter')
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