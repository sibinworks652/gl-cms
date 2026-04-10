<?php

namespace App\Http\Middleware;

use App\Support\ModuleRegistry;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackActivityLog
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! ModuleRegistry::enabled('activity_logs') || ! function_exists('activity_log')) {
            return $next($request);
        }

        $admin = auth('admin')->user();
        $routeName = $request->route()?->getName();

        if (! $admin || ! $routeName || ! str_starts_with($routeName, 'admin.')) {
            return $next($request);
        }

        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $next($request);
        }

        if (in_array($routeName, ['login.submit', 'logout', 'admin.activity-logs.feed'], true)) {
            return $next($request);
        }

        $response = $next($request);

        if ($response->getStatusCode() >= 400) {
            return $response;
        }

        [$action, $module] = $this->resolveActionAndModule($routeName, $request);

        if (! $action || ! $module) {
            return $response;
        }

        $routeParameters = collect($request->route()?->parameters() ?? []);
        $model = $routeParameters->first(fn ($parameter) => $parameter instanceof Model);
        $recordId = $model?->getKey();
        $recordTitle = $this->recordTitle($model, $request);
        $relatedUrl = $this->relatedUrl($routeName, $model);
        $moduleLabel = str($module)->replace('-', ' ')->headline()->toString();
        $description = trim($admin->name . ' ' . $this->verb($action) . ' ' . str($moduleLabel)->singular()->lower() . ($recordTitle ? ' "' . $recordTitle . '"' : '.'));

        activity_log($action, $module, str_ends_with($description, '.') ? $description : $description . '.', [
            'admin_id' => $admin->id,
            'record_type' => $model ? get_class($model) : null,
            'record_id' => $recordId,
            'record_title' => $recordTitle,
            'route_name' => $routeName,
            'related_url' => $relatedUrl,
            'properties' => [
                'method' => $request->method(),
                'path' => $request->path(),
            ],
        ]);

        return $response;
    }

    protected function resolveActionAndModule(string $routeName, Request $request): array
    {
        $name = str_replace('admin.', '', $routeName);
        $segments = explode('.', $name);
        $module = $segments[0] ?? null;
        $tail = end($segments) ?: null;

        $action = match ($tail) {
            'store' => 'create',
            'update', 'reorder', 'status' => 'update',
            'destroy', 'delete' => 'delete',
            default => match ($request->method()) {
                'POST' => 'create',
                'PUT', 'PATCH' => 'update',
                'DELETE' => 'delete',
                default => null,
            },
        };

        return [$action, $module];
    }

    protected function recordTitle(?Model $model, Request $request): ?string
    {
        foreach (['title', 'name', 'question', 'slug', 'email'] as $field) {
            $value = $model?->{$field} ?? $request->input($field);
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return $model ? class_basename($model) . ' #' . $model->getKey() : null;
    }

    protected function relatedUrl(string $routeName, ?Model $model): ?string
    {
        if (! $model) {
            return null;
        }

        $editRoute = preg_replace('/\.(store|update|destroy|reorder|status)$/', '.edit', $routeName);

        if (! $editRoute || ! \Route::has($editRoute)) {
            return null;
        }

        try {
            return route($editRoute, $model);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function verb(string $action): string
    {
        return match ($action) {
            'create' => 'created',
            'update' => 'updated',
            'delete' => 'deleted',
            default => $action,
        };
    }
}
