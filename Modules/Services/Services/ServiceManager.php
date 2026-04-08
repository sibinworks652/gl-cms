<?php

namespace Modules\Services\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Services\Models\Service;
use Modules\Services\Models\ServiceCategory;

class ServiceManager
{
    public function categories(bool $activeOnly = true): Collection
    {
        return ServiceCategory::query()
            ->when($activeOnly, fn ($query) => $query->active())
            ->withCount(['services' => fn ($query) => $query->active()])
            ->ordered()
            ->get();
    }

    public function services(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        return Service::query()
            ->with('category')
            ->active()
            ->when($filters['category'] ?? null, function ($query, string $categorySlug) {
                $query->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('slug', $categorySlug));
            })
            ->ordered()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function featured(int $limit = 6): Collection
    {
        return Service::query()
            ->with('category')
            ->active()
            ->featured()
            ->ordered()
            ->limit($limit)
            ->get();
    }

    public function findActiveBySlug(string $slug): Service
    {
        return Service::query()
            ->with('category')
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function create(array $data, ?UploadedFile $image = null): Service
    {
        return DB::transaction(function () use ($data, $image) {
            $service = Service::create($this->payload($data));

            if ($image) {
                $service->update([
                    'image_path' => $image->storeAs('services', $this->datedOriginalFilename($image), 'public'),
                ]);
            }

            return $service;
        }, 3);
    }

    public function update(Service $service, array $data, ?UploadedFile $image = null): Service
    {
        return DB::transaction(function () use ($service, $data, $image) {
            $service->update($this->payload($data, $service));

            if ($image) {
                if ($service->image_path) {
                    Storage::disk('public')->delete($service->image_path);
                }

                $service->update([
                    'image_path' => $image->storeAs('services', $this->datedOriginalFilename($image), 'public'),
                ]);
            }

            return $service->fresh('category');
        }, 3);
    }

    public function delete(Service $service): void
    {
        if ($service->image_path) {
            Storage::disk('public')->delete($service->image_path);
        }

        $service->delete();
    }

    public function createCategory(array $data): ServiceCategory
    {
        return ServiceCategory::create($this->categoryPayload($data));
    }

    public function updateCategory(ServiceCategory $category, array $data): ServiceCategory
    {
        $category->update($this->categoryPayload($data, $category));

        return $category->fresh();
    }

    public function payload(array $data, ?Service $service = null): array
    {
        return [
            'service_category_id' => $data['service_category_id'] ?? null,
            'title' => $data['title'],
            'slug' => $this->uniqueSlug(Service::class, $data['slug'] ?? $data['title'], $service?->id),
            'short_description' => $data['short_description'] ?? null,
            'full_description' => $data['full_description'] ?? null,
            'icon' => $data['icon'] ?? null,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'cta_label' => $data['cta_label'] ?? null,
            'cta_url' => $data['cta_url'] ?? null,
            'is_featured' => (bool) ($data['is_featured'] ?? false),
            'is_active' => (bool) ($data['is_active'] ?? false),
            'sort_order' => $data['sort_order'] ?? $service?->sort_order ?? ((int) Service::max('sort_order') + 1),
        ];
    }

    public function categoryPayload(array $data, ?ServiceCategory $category = null): array
    {
        return [
            'name' => $data['name'],
            'slug' => $this->uniqueSlug(ServiceCategory::class, $data['slug'] ?? $data['name'], $category?->id),
            'description' => $data['description'] ?? null,
            'sort_order' => $data['sort_order'] ?? $category?->sort_order ?? ((int) ServiceCategory::max('sort_order') + 1),
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];
    }

    protected function uniqueSlug(string $modelClass, string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source) ?: Str::random(8);
        $slug = $base;
        $counter = 2;

        while ($modelClass::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }

    protected function datedOriginalFilename(UploadedFile $file): string
    {
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $safeName = Str::slug($name) ?: 'file';

        return $safeName . '-' . now()->format('Y-m-d-His') . ($extension ? '.' . strtolower($extension) : '');
    }
}
