<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Usage Logs Export</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        h1 { font-size: 18px; margin-bottom: 5px; }
        p { margin-top: 0; color: #666; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f4f4f4; font-weight: bold; }
        .success { color: green; }
        .danger { color: red; }
    </style>
</head>
<body>
    <h1>Usage Logs: {{ $project->name }}</h1>
    <p>Exported at: {{ now()->format('Y-m-d H:i:s') }} | Total Logs (in this export): {{ count($logs) }}</p>

    <table>
        <thead>
            <tr>
                <th>Req ID</th>
                <th>Method</th>
                <th>Endpoint</th>
                <th>Status</th>
                <th>Time (ms)</th>
                <th>Size (B)</th>
                <th>IP Address</th>
                <th>API Key</th>
                <th>Occurred At</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($logs as $log)
                <tr>
                    <td>{{ Str::limit($log->request_id, 8) }}</td>
                    <td>{{ $log->method }}</td>
                    <td>{{ Str::limit($log->endpoint, 30) }}</td>
                    <td class="{{ $log->status_code >= 400 ? 'danger' : 'success' }}">{{ $log->status_code }}</td>
                    <td>{{ $log->response_time_ms }}</td>
                    <td>{{ $log->response_size_bytes }}</td>
                    <td>{{ $log->ip_address }}</td>
                    <td>{{ $log->apiKey ? $log->apiKey->name : 'N/A' }}</td>
                    <td>{{ $log->occurred_at->format('Y-m-d H:i:s') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
