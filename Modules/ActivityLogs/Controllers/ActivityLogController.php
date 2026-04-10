<?php

namespace Modules\ActivityLogs\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Modules\ActivityLogs\Models\ActivityLog;
use Modules\ActivityLogs\Services\ActivityLogManager;

class ActivityLogController extends Controller
{
    public function __construct(protected ActivityLogManager $manager)
    {
    }

    public function index(Request $request)
    {
        $this->authorizeViewer($request);

        $logs = $this->manager->paginateActivities($request->only(['action', 'module', 'search']), $request->user('admin'));

        return view('activity-logs::index', [
            'logs' => $logs,
            'groupedLogs' => $logs->getCollection()->groupBy(fn ($log) => $log->created_at->format('Y-m-d')),
            'actions' => ['create', 'update', 'delete', 'login', 'logout'],
            'modules' => ActivityLog::query()->whereNotNull('module')->distinct()->orderBy('module')->pluck('module'),
        ]);
    }

    public function feed(Request $request)
    {
        $this->authorizeViewer($request);

        $logs = $this->manager->recentFeed(
            $request->only(['action', 'module', 'search']),
            $request->user('admin')
        );

        $groupedLogs = $logs->groupBy(fn ($log) => $log->created_at->format('Y-m-d'));

        return response()->json([
            'html' => View::make('activity-logs::partials.timeline-items', ['groupedLogs' => $groupedLogs])->render(),
            'count' => $logs->count(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    protected function authorizeViewer(Request $request): void
    {
        abort_unless(
            $request->user('admin')?->can('activity-logs.view') || $request->user('admin')?->can('activity-logs.view-own'),
            403
        );
    }
}
