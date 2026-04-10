<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ModuleRegistry;

class DashboardController extends Controller
{
    public function index()
    {
        $galleryCount = 0;
        $galleryImageCount = 0;
        $recentActivities = collect();
        $groupedLogs = collect();

        if (ModuleRegistry::enabled('gallery') && class_exists($galleryAlbumClass = \Modules\Gallery\Models\GalleryAlbum::class)) {
            $galleryCount = $galleryAlbumClass::query()
                ->where('is_active', true)
                ->count();

            $galleryImageCount = $galleryAlbumClass::query()
                ->where('is_active', true)
                ->withCount('images')
                ->get()
                ->sum('images_count');
        }

        if (ModuleRegistry::enabled('activity_logs') && class_exists($activityLogManagerClass = \Modules\ActivityLogs\Services\ActivityLogManager::class)) {
            $recentActivities = app($activityLogManagerClass)->recent(8, auth('admin')->user());
            $groupedLogs = $recentActivities->groupBy(fn ($log) => $log->created_at->format('Y-m-d'));
        }

        return view('admin.dashboard', compact('galleryCount', 'galleryImageCount', 'recentActivities', 'groupedLogs'));
    }
}
