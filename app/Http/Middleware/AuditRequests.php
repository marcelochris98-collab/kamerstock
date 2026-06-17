<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuditRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        $lastLogId = $this->lastLogId();

        $response = $next($request);

        if ($this->shouldRecord($request, $response, $lastLogId)) {
            try {
                ActivityLog::record(
                    $this->actionName($request),
                    $this->description($request)
                );
            } catch (\Throwable $exception) {
                Log::warning('Audit log skipped: ' . $exception->getMessage());
            }
        }

        return $response;
    }

    private function lastLogId(): int
    {
        try {
            return ActivityLog::max('id') ?? 0;
        } catch (\Throwable) {
            return 0;
        }
    }

    private function shouldRecord(Request $request, Response $response, int $lastLogId): bool
    {
        if (!auth()->check() || $request->isMethod('GET') || $request->isMethod('HEAD')) {
            return false;
        }

        if ($response->getStatusCode() >= 400) {
            return false;
        }

        try {
            return ! ActivityLog::where('id', '>', $lastLogId)
                ->where('user_id', auth()->id())
                ->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    private function actionName(Request $request): string
    {
        $routeName = $request->route()?->getName();

        return 'request.' . strtolower($request->method()) . ($routeName ? ".{$routeName}" : '');
    }

    private function description(Request $request): string
    {
        $routeName = $request->route()?->getName() ?? $request->path();

        return sprintf('%s %s', strtoupper($request->method()), $routeName);
    }
}
