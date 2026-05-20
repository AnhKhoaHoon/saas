@extends('layouts.app', ['title' => 'Usage Logs - ' . $project->name])

@section('content')
    <div class="actions" style="margin-bottom: 2rem;">
        <a href="{{ route('projects.show', $project) }}" class="btn secondary">&larr; Back to Dashboard</a>
    </div>

    <section class="card stack">
        <div class="actions" style="justify-content: space-between;">
            <div>
                <h1>Usage Logs</h1>
                <p class="lead">Tra cứu và phân tích các request đi qua API keys của dự án <strong>{{ $project->name }}</strong>.</p>
            </div>
            
            <div class="actions">
                <a href="{{ route('projects.usage-logs.export', ['project' => $project->id, 'format' => 'csv'] + request()->all()) }}" class="btn secondary">
                    Export CSV
                </a>
                <a href="{{ route('projects.usage-logs.export', ['project' => $project->id, 'format' => 'pdf'] + request()->all()) }}" class="btn secondary">
                    Export PDF
                </a>
            </div>
        </div>

        <form class="grid cols-2" method="GET" action="{{ route('projects.usage-logs.index', $project) }}" style="background: rgba(0,0,0,0.2); padding: 1.5rem; border-radius: 16px; border: 1px solid var(--glass-border-light);">
            <div class="field">
                <label for="api_key_id">API Key</label>
                <select id="api_key_id" name="api_key_id">
                    <option value="">All Keys</option>
                    @foreach ($apiKeys as $key)
                        <option value="{{ $key->id }}" @selected(request('api_key_id') == $key->id)>
                            {{ $key->name }} ({{ $key->key_prefix }}...)
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="method">HTTP Method</label>
                <select id="method" name="method">
                    <option value="">All Methods</option>
                    @foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] as $method)
                        <option value="{{ $method }}" @selected(request('method') === $method)>{{ $method }}</option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="status_code">Status Code</label>
                <input id="status_code" type="number" name="status_code" value="{{ request('status_code') }}" placeholder="e.g. 200, 404, 500">
            </div>

            <div class="field">
                <label for="search">Global Search</label>
                <input id="search" type="text" name="search" value="{{ request('search') }}" placeholder="Search by Endpoint, IP, Request ID">
            </div>

            <div class="actions" style="grid-column: span 2; margin-top: 10px;">
                <button type="submit">Filter Logs</button>
                <a href="{{ route('projects.usage-logs.index', $project) }}" class="btn secondary">Reset</a>
            </div>
        </form>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9em;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--glass-border);">
                        <th style="padding: 1rem 0.5rem; color: var(--text-muted);">Method</th>
                        <th style="padding: 1rem 0.5rem; color: var(--text-muted);">Endpoint</th>
                        <th style="padding: 1rem 0.5rem; color: var(--text-muted);">Status</th>
                        <th style="padding: 1rem 0.5rem; color: var(--text-muted);">Time/Size</th>
                        <th style="padding: 1rem 0.5rem; color: var(--text-muted);">IP Address</th>
                        <th style="padding: 1rem 0.5rem; color: var(--text-muted);">API Key</th>
                        <th style="padding: 1rem 0.5rem; color: var(--text-muted);">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <td style="padding: 1rem 0.5rem;">
                                <span style="padding: 2px 8px; border-radius: 4px; background: rgba(255,255,255,0.1); font-weight: bold; font-size: 0.85em;">
                                    {{ $log->method }}
                                </span>
                            </td>
                            <td style="padding: 1rem 0.5rem;" class="mono">{{ Str::limit($log->endpoint, 40) }}</td>
                            <td style="padding: 1rem 0.5rem;">
                                <strong style="color: {{ $log->status_code >= 400 ? 'var(--danger)' : 'var(--success)' }}">
                                    {{ $log->status_code }}
                                </strong>
                            </td>
                            <td style="padding: 1rem 0.5rem;">
                                {{ $log->response_time_ms }}ms <br>
                                <span class="muted" style="font-size: 0.85em;">{{ $log->response_size_bytes }}B</span>
                            </td>
                            <td style="padding: 1rem 0.5rem;" class="mono">{{ $log->ip_address }}</td>
                            <td style="padding: 1rem 0.5rem;">{{ $log->apiKey ? $log->apiKey->name : 'N/A' }}</td>
                            <td style="padding: 1rem 0.5rem;" class="muted">{{ $log->occurred_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding: 2rem; text-align: center;" class="muted">Không có dữ liệu log nào khớp với bộ lọc.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1rem;">
            {{ $logs->links() }}
        </div>
    </section>
@endsection
