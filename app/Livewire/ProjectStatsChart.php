<?php

namespace App\Livewire;

use App\Models\Project;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ProjectStatsChart extends Component
{
    public Project $project;

    public function render()
    {
        // Generate last 7 days including today
        $period = CarbonPeriod::create(Carbon::now()->subDays(6)->startOfDay(), '1 day', Carbon::now()->endOfDay());
        $labels = [];
        $data = [];
        $errors = [];

        // Initialize array with 0s
        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');
            $labels[] = $date->format('M d');
            $data[$dateString] = 0;
            $errors[$dateString] = 0;
        }

        // Fetch logs
        $logs = $this->project->usageLogs()
            ->where('occurred_at', '>=', Carbon::now()->subDays(6)->startOfDay())
            ->select(
                DB::raw('DATE(occurred_at) as date'),
                DB::raw('COUNT(id) as total'),
                DB::raw('SUM(CASE WHEN status_code >= 400 THEN 1 ELSE 0 END) as error_count')
            )
            ->groupBy('date')
            ->get();

        foreach ($logs as $log) {
            $date = $log->date;
            if (isset($data[$date])) {
                $data[$date] = $log->total;
                $errors[$date] = $log->error_count;
            }
        }

        return view('livewire.project-stats-chart', [
            'labels' => $labels,
            'data' => array_values($data),
            'errors' => array_values($errors),
        ]);
    }
}
