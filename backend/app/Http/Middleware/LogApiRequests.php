<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        // Log the incoming request
        $this->logRequest($request);

        // Process the request
        $response = $next($request);

        // Log the response
        $this->logResponse($request, $response, $startTime);

        return $response;
    }

    /**
     * Log the incoming request details
     */
    private function logRequest(Request $request): void
    {
        $logData = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => $request->user()?->id,
            'headers' => $this->filterHeaders($request->headers->all()),
            'query_params' => $request->query(),
            'timestamp' => now()->toISOString(),
        ];

        // Only log request body for non-GET requests and exclude sensitive data
        if (!$request->isMethod('GET')) {
            $logData['request_body'] = $this->filterSensitiveData($request->all());
        }

        Log::channel('api')->info('API Request', $logData);
    }

    /**
     * Log the response details
     */
    private function logResponse(Request $request, Response $response, float $startTime): void
    {
        $duration = round((microtime(true) - $startTime) * 1000, 2); // Duration in milliseconds

        $logData = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'status_code' => $response->getStatusCode(),
            'duration_ms' => $duration,
            'user_id' => $request->user()?->id,
            'response_size' => strlen($response->getContent()),
            'timestamp' => now()->toISOString(),
        ];

        // Log response body only for errors or if specifically configured
        if ($response->getStatusCode() >= 400) {
            $logData['response_body'] = $this->truncateContent($response->getContent(), 1000);
        }

        $logLevel = $this->getLogLevel($response->getStatusCode());
        Log::channel('api')->{$logLevel}('API Response', $logData);
    }

    /**
     * Filter sensitive headers (remove authorization tokens, etc.)
     */
    private function filterHeaders(array $headers): array
    {
        $filteredHeaders = [];
        $sensitiveHeaders = ['authorization', 'cookie', 'x-api-key'];

        foreach ($headers as $key => $value) {
            if (in_array(strtolower($key), $sensitiveHeaders)) {
                $filteredHeaders[$key] = '[FILTERED]';
            } else {
                $filteredHeaders[$key] = $value;
            }
        }

        return $filteredHeaders;
    }

    /**
     * Filter sensitive data from request body
     */
    private function filterSensitiveData(array $data): array
    {
        $filteredData = [];
        $sensitiveFields = [
            'password', 'password_confirmation', 'token', 'api_key',
            'secret', 'private_key', 'credit_card', 'ssn'
        ];

        foreach ($data as $key => $value) {
            if (in_array(strtolower($key), $sensitiveFields)) {
                $filteredData[$key] = '[FILTERED]';
            } elseif (is_array($value)) {
                $filteredData[$key] = $this->filterSensitiveData($value);
            } else {
                $filteredData[$key] = $value;
            }
        }

        return $filteredData;
    }

    /**
     * Truncate content if it's too long
     */
    private function truncateContent(string $content, int $maxLength = 500): string
    {
        if (strlen($content) > $maxLength) {
            return substr($content, 0, $maxLength) . '... [TRUNCATED]';
        }

        return $content;
    }

    /**
     * Get appropriate log level based on status code
     */
    private function getLogLevel(int $statusCode): string
    {
        return match (true) {
            $statusCode >= 500 => 'error',
            $statusCode >= 400 => 'warning',
            $statusCode >= 300 => 'info',
            default => 'info'
        };
    }
}
