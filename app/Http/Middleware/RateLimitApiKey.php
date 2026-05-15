<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var ApiKey|null $apiKey */
        $apiKey = $request->attributes->get('apiKey');

        if (! $apiKey instanceof ApiKey) {
            return $this->error('API key context is missing.', 500);
        }

        $limiterKey = $this->limiterKey($apiKey);
        $maxAttempts = max(1, (int) $apiKey->rate_limit_per_minute);

        if (RateLimiter::tooManyAttempts($limiterKey, $maxAttempts)) {
            return $this->error(
                'API key rate limit exceeded.',
                429,
                [
                    'Retry-After' => (string) RateLimiter::availableIn($limiterKey),
                    'X-RateLimit-Limit' => (string) $maxAttempts,
                    'X-RateLimit-Remaining' => '0',
                ]
            );
        }

        // Count the request before downstream execution so concurrent bursts still hit the limiter consistently.
        RateLimiter::hit($limiterKey, 60);

        $response = $next($request);
        $remaining = max(0, RateLimiter::remaining($limiterKey, $maxAttempts));

        $response->headers->set('X-RateLimit-Limit', (string) $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', (string) $remaining);

        return $response;
    }

    protected function limiterKey(ApiKey $apiKey): string
    {
        return "api-key-rate:{$apiKey->id}";
    }

    /**
     * @param  array<string, string>  $headers
     */
    protected function error(string $message, int $status, array $headers = []): JsonResponse
    {
        return response()->json([
            'message' => $message,
        ], $status, $headers);
    }
}
