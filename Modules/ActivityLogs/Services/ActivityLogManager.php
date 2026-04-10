<?php

namespace Modules\ActivityLogs\Services;

use App\Models\Admin;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Modules\ActivityLogs\Events\ActivityLogged;
use Modules\ActivityLogs\Models\ActivityLog;
use Modules\ActivityLogs\Models\LoginHistory;

class ActivityLogManager
{
    public function log(array $data): ActivityLog
    {
        $log = ActivityLog::create([
            'admin_id' => $data['admin_id'] ?? auth('admin')->id(),
            'action' => $data['action'],
            'module' => $data['module'] ?? null,
            'record_type' => $data['record_type'] ?? null,
            'record_id' => $data['record_id'] ?? null,
            'record_title' => $data['record_title'] ?? null,
            'description' => $data['description'],
            'route_name' => $data['route_name'] ?? request()->route()?->getName(),
            'related_url' => $data['related_url'] ?? null,
            'ip_address' => $data['ip_address'] ?? request()->ip(),
            'user_agent' => $data['user_agent'] ?? request()->userAgent(),
            'properties' => $data['properties'] ?? null,
        ]);

        event(new ActivityLogged($log));

        return $log;
    }

    public function recent(int $limit = 8, ?Admin $admin = null): Collection
    {
        return $this->activityQuery([], $admin)
            ->limit($limit)
            ->get();
    }

    public function paginateActivities(array $filters = [], ?Admin $viewer = null): LengthAwarePaginator
    {
        return $this->activityQuery($filters, $viewer)
            ->paginate(20)
            ->withQueryString();
    }

    public function recentFeed(array $filters = [], ?Admin $viewer = null, int $limit = 20): Collection
    {
        return $this->activityQuery($filters, $viewer)
            ->limit($limit)
            ->get();
    }

    public function paginateLoginHistories(?Admin $viewer = null): LengthAwarePaginator
    {
        return LoginHistory::query()
            ->with('admin')
            ->when($viewer && ! $viewer->can('login-histories.view'), fn ($query) => $query->where('admin_id', $viewer->id))
            ->recent()
            ->paginate(20)
            ->withQueryString();
    }

    public function recordLogin(Admin $admin, Request $request): LoginHistory
    {
        $history = LoginHistory::create([
            'admin_id' => $admin->id,
            'session_id' => $request->session()->getId(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'login_at' => now(),
            'is_active' => true,
        ]);

        $this->log([
            'admin_id' => $admin->id,
            'action' => 'login',
            'module' => 'auth',
            'record_type' => Admin::class,
            'record_id' => $admin->id,
            'record_title' => $admin->name,
            'description' => $admin->name . ' logged in.',
            'related_url' => route('admin.dashboard'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return $history;
    }

    public function recordLogout(Admin $admin, Request $request): void
    {
        LoginHistory::query()
            ->where('admin_id', $admin->id)
            ->where('session_id', $request->session()->getId())
            ->where('is_active', true)
            ->latest('login_at')
            ->first()
            ?->update([
                'logout_at' => now(),
                'is_active' => false,
            ]);

        $this->log([
            'admin_id' => $admin->id,
            'action' => 'logout',
            'module' => 'auth',
            'record_type' => Admin::class,
            'record_id' => $admin->id,
            'record_title' => $admin->name,
            'description' => $admin->name . ' logged out.',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    protected function activityQuery(array $filters = [], ?Admin $viewer = null): Builder
    {
        return ActivityLog::query()
            ->with('admin')
            ->when($viewer && ! $viewer->can('activity-logs.view'), fn ($query) => $query->where('admin_id', $viewer->id))
            ->when($filters['action'] ?? null, fn ($query, $action) => $query->where('action', $action))
            ->when($filters['module'] ?? null, fn ($query, $module) => $query->where('module', $module))
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('description', 'like', '%' . $search . '%')
                        ->orWhere('record_title', 'like', '%' . $search . '%')
                        ->orWhere('module', 'like', '%' . $search . '%');
                });
            })
            ->recent();
    }
}
