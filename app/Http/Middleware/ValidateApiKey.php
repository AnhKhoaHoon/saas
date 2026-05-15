<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use App\Models\UsageLog;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $plainTextKey = $this->extractApiKey($request);

        if (! $plainTextKey) {
            return $this->error('API key is required.', 401);
        }

        $apiKey = ApiKey::query()
            ->with('project')
            ->where('key_hash', hash('sha256', $plainTextKey))
            ->first();

        if (! $apiKey) {
            return $this->error('API key is invalid.', 401);
        }

        if ($apiKey->status !== 'active' || $apiKey->revoked_at !== null) {
            return $this->error('API key is revoked or inactive.', 403);
        }

        if ($apiKey->expires_at !== null && $apiKey->expires_at->isPast()) {
            return $this->error('API key has expired.', 403);
        }

        if ($apiKey->quota_limit !== null && $apiKey->requests_count >= $apiKey->quota_limit) {
            return $this->error('API key quota has been exhausted.', 429);
        }

        if ($this->ipIsNotAllowed($request, $apiKey)) {
            return $this->error('IP address is not allowed for this API key.', 403);
        }

        // Expose the resolved API key and project so downstream controllers do not need to re-query them.
        $request->attributes->set('apiKey', $apiKey);
        $request->attributes->set('project', $apiKey->project);

        $startedAt = microtime(true);
        $response = $next($request);

        $this->recordUsage($request, $response, $apiKey, $startedAt);

        return $response;
    }

    protected function extractApiKey(Request $request): ?string
    {
        $headerKey = $request->header('X-API-Key');

        if (is_string($headerKey) && $headerKey !== '') {
            return $headerKey;
        }

        $bearerToken = $request->bearerToken();

        return is_string($bearerToken) && $bearerToken !== '' ? $bearerToken : null;
    }

    protected function ipIsNotAllowed(Request $request, ApiKey $apiKey): bool
    {
        if (empty($apiKey->ip_whitelist)) {
            return false;
        }

        return ! in_array($request->ip(), $apiKey->ip_whitelist, true);
    }

    protected function recordUsage(Request $request, Response $response, ApiKey $apiKey, float $startedAt): void
    {
        // Persist usage after the response is built so validation stays on the hot path and bookkeeping stays consistent.
        $apiKey->forceFill([
            'requests_count' => $apiKey->requests_count + 1,
            'last_used_at' => now(),
        ])->save();

        UsageLog::create([
            'project_id' => $apiKey->project_id,
            'api_key_id' => $apiKey->id,
            'request_id' => $request->headers->get('X-Request-Id'),
            'endpoint' => '/'.ltrim($request->path(), '/'),
            'method' => $request->method(),
            'status_code' => $response->getStatusCode(),
            'response_time_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            'response_size_bytes' => strlen((string) $response->getContent()),
            'units' => 1,
            'ip_address' => $request->ip(),
            'meta' => [
                'user_agent' => $request->userAgent(),
            ],
            'occurred_at' => now(),
        ]);
    }

    protected function error(string $message, int $status): JsonResponse
    {
        return response()->json([
            'message' => $message,
        ], $status);
    }
}
