<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\UsageLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UsageLogController extends Controller
{
    private function buildQuery(Request $request, Project $project)
    {
        $query = $project->usageLogs()->with('apiKey')->latest('occurred_at');

        if ($request->filled('api_key_id')) {
            $query->where('api_key_id', $request->input('api_key_id'));
        }

        if ($request->filled('method')) {
            $query->where('method', strtoupper($request->input('method')));
        }

        if ($request->filled('status_code')) {
            $query->where('status_code', $request->input('status_code'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('endpoint', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('request_id', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    public function index(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $query = $this->buildQuery($request, $project);
        
        $logs = $query->paginate(20)->withQueryString();
        
        $apiKeys = $project->apiKeys()->orderBy('name')->get();

        return view('projects.usage-logs.index', compact('project', 'logs', 'apiKeys'));
    }

    public function export(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $query = $this->buildQuery($request, $project);
        $logs = $query->limit(5000)->get(); // Cap export to 5000 to prevent OOM
        $format = $request->input('format', 'csv');

        $filename = "usage_logs_{$project->slug}_" . now()->format('Y-m-d_His');

        if ($format === 'csv') {
            $headers = [
                "Content-type"        => "text/csv",
                "Content-Disposition" => "attachment; filename={$filename}.csv",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            ];

            $callback = function() use ($logs) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['ID', 'Request ID', 'API Key', 'Method', 'Endpoint', 'Status Code', 'Time (ms)', 'Size (bytes)', 'IP Address', 'Occurred At']);

                foreach ($logs as $log) {
                    fputcsv($file, [
                        $log->id,
                        $log->request_id,
                        $log->apiKey ? $log->apiKey->name : 'N/A',
                        $log->method,
                        $log->endpoint,
                        $log->status_code,
                        $log->response_time_ms,
                        $log->response_size_bytes,
                        $log->ip_address,
                        $log->occurred_at->format('Y-m-d H:i:s'),
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('projects.usage-logs.pdf', compact('project', 'logs'));
            return $pdf->download("{$filename}.pdf");
        }

        return back()->with('error', 'Invalid export format.');
    }
}
