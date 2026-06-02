<?php
// app/Http/Controllers/EnhancedChartController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EnhancedChartController extends Controller
{
    // Main dashboard with multiple charts
    public function dashboard(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->subYear()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        
        return view('enhanced-dashboard', [
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }
    
    // API endpoint for dynamic data
    public function getChartData(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->subYear());
        $endDate = $request->input('end_date', Carbon::now());
        $chartType = $request->input('chart_type', 'monthly');
        
        $data = [];
        
        switch($chartType) {
            case 'monthly':
                $data = $this->getMonthlyData($startDate, $endDate);
                break;
            case 'department':
                $data = $this->getDepartmentData($startDate, $endDate);
                break;
            case 'city':
                $data = $this->getCityData($startDate, $endDate);
                break;
            case 'age':
                $data = $this->getAgeGroupData($startDate, $endDate);
                break;
            case 'prediction':
                $data = $this->getPredictionData($startDate, $endDate);
                break;
        }
        
        return response()->json($data);
    }
    
    // Monthly registration data with trend
    private function getMonthlyData($startDate, $endDate)
    {
        $data = User::selectRaw('
            DATE_FORMAT(created_at, "%Y-%m") as month,
            COUNT(*) as count,
            MONTH(created_at) as month_num,
            YEAR(created_at) as year
        ')
        ->whereBetween('created_at', [$startDate, $endDate])
        ->groupBy('month', 'month_num', 'year')
        ->orderBy('year')
        ->orderBy('month_num')
        ->get();
        
        $months = [];
        $counts = [];
        
        foreach($data as $item) {
            $months[] = $item->month;
            $counts[] = $item->count;
        }
        
        // Calculate trend line (linear regression)
        $trend = $this->calculateTrend($counts);
        
        return [
            'months' => $months,
            'counts' => $counts,
            'trend' => $trend,
            'total' => array_sum($counts),
            'average' => count($counts) > 0 ? round(array_sum($counts) / count($counts), 2) : 0,
            'highest' => !empty($counts) ? max($counts) : 0,
            'lowest' => !empty($counts) ? min($counts) : 0
        ];
    }
    
    // Department distribution
    private function getDepartmentData($startDate, $endDate)
    {
        $data = User::select('department', DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('department')
            ->groupBy('department')
            ->get();
        
        $departments = [];
        $counts = [];
        
        foreach($data as $item) {
            $departments[] = $item->department;
            $counts[] = $item->count;
        }
        
        return [
            'departments' => $departments,
            'counts' => $counts,
            'total' => array_sum($counts)
        ];
    }
    
    // City wise distribution
    private function getCityData($startDate, $endDate)
    {
        $data = User::select('city', DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('city')
            ->groupBy('city')
            ->orderBy('count', 'DESC')
            ->limit(10)
            ->get();
        
        $cities = [];
        $counts = [];
        
        foreach($data as $item) {
            $cities[] = $item->city;
            $counts[] = $item->count;
        }
        
        return [
            'cities' => $cities,
            'counts' => $counts,
            'total' => array_sum($counts)
        ];
    }
    
    // Age group distribution
    private function getAgeGroupData($startDate, $endDate)
    {
        $ageGroups = [
            '18-25' => [18, 25],
            '26-35' => [26, 35],
            '36-45' => [36, 45],
            '46-55' => [46, 55],
            '55+' => [56, 150]
        ];
        
        $data = [];
        foreach($ageGroups as $group => $range) {
            $count = User::whereBetween('age', $range)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();
            $data[$group] = $count;
        }
        
        return [
            'groups' => array_keys($data),
            'counts' => array_values($data),
            'total' => array_sum($data)
        ];
    }
    
    // Predictive analysis for next 6 months
    private function getPredictionData($startDate, $endDate)
    {
        // Get last 12 months of data for prediction
        $historicalData = User::selectRaw('
            DATE_FORMAT(created_at, "%Y-%m") as month,
            COUNT(*) as count
        ')
        ->whereBetween('created_at', [Carbon::now()->subYear(), Carbon::now()])
        ->groupBy('month')
        ->orderBy('month')
        ->get();
        
        $historical = [];
        foreach($historicalData as $item) {
            $historical[] = $item->count;
        }
        
        // Simple moving average prediction
        $predictions = [];
        $windowSize = 3;
        
        for($i = 0; $i < 6; $i++) {
            if(count($historical) >= $windowSize) {
                $lastValues = array_slice($historical, -$windowSize);
                $prediction = round(array_sum($lastValues) / $windowSize);
            } else {
                $prediction = !empty($historical) ? round(end($historical)) : 10;
            }
            $predictions[] = max(0, $prediction);
            $historical[] = $prediction;
        }
        
        $futureMonths = [];
        for($i = 1; $i <= 6; $i++) {
            $futureMonths[] = Carbon::now()->addMonths($i)->format('M Y');
        }
        
        return [
            'historicalMonths' => $historicalData->pluck('month')->toArray(),
            'historicalCounts' => $historicalData->pluck('count')->toArray(),
            'futureMonths' => $futureMonths,
            'predictions' => $predictions
        ];
    }
    
    // Calculate trend line using linear regression
    private function calculateTrend($data)
    {
        $n = count($data);
        if($n < 2) return array_fill(0, $n, 0);
        
        $x = range(1, $n);
        $xSum = array_sum($x);
        $ySum = array_sum($data);
        $xySum = array_sum(array_map(function($xi, $yi) { return $xi * $yi; }, $x, $data));
        $xxSum = array_sum(array_map(function($xi) { return $xi * $xi; }, $x));
        
        $slope = ($n * $xySum - $xSum * $ySum) / ($n * $xxSum - $xSum * $xSum);
        $intercept = ($ySum - $slope * $xSum) / $n;
        
        $trend = [];
        for($i = 1; $i <= $n; $i++) {
            $trend[] = round($slope * $i + $intercept);
        }
        
        return $trend;
    }
    
    // Export chart data as CSV
    public function exportData(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->subYear());
        $endDate = $request->input('end_date', Carbon::now());
        
        $data = User::select('name', 'email', 'department', 'city', 'age', 'created_at')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
        
        $filename = "user_data_" . date('Y-m-d_His') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        
        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Name', 'Email', 'Department', 'City', 'Age', 'Registration Date']);
            
            foreach ($data as $row) {
                fputcsv($file, [
                    $row->name,
                    $row->email,
                    $row->department,
                    $row->city,
                    $row->age,
                    $row->created_at
                ]);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}