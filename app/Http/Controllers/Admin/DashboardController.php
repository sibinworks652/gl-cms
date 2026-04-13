<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ModuleRegistry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class DashboardController extends Controller
{
    public function index()
    {
        $dashboardCards = collect();
        $galleryCount = 0;
        $galleryImageCount = 0;
        $recentActivities = collect();
        $groupedLogs = collect();

        if (
            ModuleRegistry::enabled('gallery')
            && class_exists($galleryAlbumClass = \Modules\Gallery\Models\GalleryAlbum::class)
            && Route::has('admin.gallery.index')
            && auth('admin')->user()?->can('gallery.view')
        ) {
            $galleryCount = $galleryAlbumClass::query()
                ->where('is_active', true)
                ->count();

            $galleryImageCount = $galleryAlbumClass::query()
                ->where('is_active', true)
                ->withCount('images')
                ->get()
                ->sum('images_count');

            $dashboardCards->push([
                'title' => 'Gallery Albums',
                'value' => number_format($galleryCount),
                'icon' => 'solar:gallery-wide-line-duotone',
                'iconClass' => 'text-primary',
                'iconWrapperClass' => 'bg-soft-primary',
                'metaValue' => number_format($galleryImageCount),
                'metaLabel' => 'Images',
                'metaClass' => 'text-success',
                'link' => route('admin.gallery.index'),
                'linkLabel' => 'Manage Gallery',
            ]);
        }

        $this->addDashboardCard(
            $dashboardCards,
            module: 'page',
            modelClass: \Modules\Page\Models\Page::class,
            routeName: 'admin.pages.index',
            permission: 'pages.view',
            title: 'Pages',
            icon: 'solar:document-text-line-duotone',
            totalQuery: fn (string $modelClass) => $modelClass::query()->count(),
            metaQuery: fn (string $modelClass) => [
                'value' => number_format($modelClass::query()->active()->count()),
                'label' => 'Active',
                'class' => 'text-success',
            ],
            linkLabel: 'Manage Pages'
        );

        $this->addDashboardCard(
            $dashboardCards,
            module: 'form_builder',
            modelClass: \Modules\FormBuilder\Models\Form::class,
            routeName: 'admin.forms.index',
            permission: 'forms.view',
            title: 'Forms',
            icon: 'solar:clipboard-text-line-duotone',
            totalQuery: fn (string $modelClass) => $modelClass::query()->count(),
            metaQuery: fn () => class_exists(\Modules\FormBuilder\Models\FormSubmission::class)
                ? [
                    'value' => number_format(\Modules\FormBuilder\Models\FormSubmission::query()->count()),
                    'label' => 'Submissions',
                    'class' => 'text-success',
                ]
                : null,
            linkLabel: 'View Forms'
        );

        $this->addDashboardCard(
            $dashboardCards,
            module: 'services',
            modelClass: \Modules\Services\Models\Service::class,
            routeName: 'admin.services.index',
            permission: 'services.view',
            title: 'Services',
            icon: 'solar:widget-2-line-duotone',
            totalQuery: fn (string $modelClass) => $modelClass::query()->count(),
            metaQuery: fn (string $modelClass) => [
                'value' => number_format($modelClass::query()->featured()->count()),
                'label' => 'Featured',
                'class' => 'text-success',
            ],
            linkLabel: 'Manage Services'
        );

        $this->addDashboardCard(
            $dashboardCards,
            module: 'team',
            modelClass: \Modules\Team\Models\TeamMember::class,
            routeName: 'admin.team-members.index',
            permission: 'team-members.view',
            title: 'Team Members',
            icon: 'solar:users-group-rounded-line-duotone',
            totalQuery: fn (string $modelClass) => $modelClass::query()->count(),
            metaQuery: fn (string $modelClass) => [
                'value' => number_format($modelClass::query()->active()->count()),
                'label' => 'Active',
                'class' => 'text-success',
            ],
            linkLabel: 'View Team'
        );

        $this->addDashboardCard(
            $dashboardCards,
            module: 'careers',
            modelClass: \Modules\Careers\Models\JobApplication::class,
            routeName: 'admin.applications.index',
            permission: 'careers.applications.view',
            title: 'Applications',
            icon: 'solar:case-round-line-duotone',
            totalQuery: fn (string $modelClass) => $modelClass::query()->count(),
            metaQuery: fn () => class_exists(\Modules\Careers\Models\Job::class)
                ? [
                    'value' => number_format(\Modules\Careers\Models\Job::query()->open()->count()),
                    'label' => 'Open Jobs',
                    'class' => 'text-success',
                ]
                : null,
            linkLabel: 'Review Applications'
        );

        $this->addDashboardCard(
            $dashboardCards,
            module: 'testimonials',
            modelClass: \Modules\Testimonials\Models\Testimonial::class,
            routeName: 'admin.testimonials.index',
            permission: 'testimonials.view',
            title: 'Testimonials',
            icon: 'solar:chat-round-like-line-duotone',
            totalQuery: fn (string $modelClass) => $modelClass::query()->count(),
            metaQuery: fn (string $modelClass) => [
                'value' => number_format($modelClass::query()->featured()->count()),
                'label' => 'Featured',
                'class' => 'text-success',
            ],
            linkLabel: 'Manage Testimonials'
        );

        $this->addDashboardCard(
            $dashboardCards,
            module: 'faq',
            modelClass: \Modules\Faq\Models\Faq::class,
            routeName: 'admin.faqs.index',
            permission: 'faqs.view',
            title: 'FAQs',
            icon: 'solar:question-circle-line-duotone',
            totalQuery: fn (string $modelClass) => $modelClass::query()->count(),
            metaQuery: fn (string $modelClass) => [
                'value' => number_format($modelClass::query()->active()->count()),
                'label' => 'Published',
                'class' => 'text-success',
            ],
            linkLabel: 'Manage FAQs'
        );
        $dashboardCards = $dashboardCards->take(8)->values();

        if (ModuleRegistry::enabled('activity_logs') && class_exists($activityLogManagerClass = \Modules\ActivityLogs\Services\ActivityLogManager::class)) {
            $recentActivities = app($activityLogManagerClass)->recent(8, auth('admin')->user());
            $groupedLogs = $recentActivities->groupBy(fn ($log) => $log->created_at->format('Y-m-d'));
        }

        return view('admin.dashboard', compact('dashboardCards', 'galleryCount', 'galleryImageCount', 'recentActivities', 'groupedLogs'));
    }

    protected function addDashboardCard(
        Collection $cards,
        string $module,
        string $modelClass,
        string $routeName,
        string $permission,
        string $title,
        string $icon,
        callable $totalQuery,
        ?callable $metaQuery = null,
        string $linkLabel = 'View More'
    ): void {
        if (! ModuleRegistry::enabled($module) || ! class_exists($modelClass) || ! Route::has($routeName)) {
            return;
        }

        $user = auth('admin')->user();

        if (! $user?->can($permission)) {
            return;
        }

        $meta = $metaQuery ? $metaQuery($modelClass) : null;

        $cards->push([
            'title' => $title,
            'value' => number_format((int) $totalQuery($modelClass)),
            'icon' => $icon,
            'iconClass' => 'text-primary',
            'iconWrapperClass' => 'bg-soft-primary',
            'metaValue' => data_get($meta, 'value', 'Ready'),
            'metaLabel' => data_get($meta, 'label', 'Overview'),
            'metaClass' => data_get($meta, 'class', 'text-success'),
            'link' => route($routeName),
            'linkLabel' => $linkLabel,
        ]);
    }
}
