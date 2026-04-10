<?php

namespace Modules\Testimonials\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Testimonials\Models\Testimonial;

class TestimonialManager
{
    public function frontendTestimonials(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        return Testimonial::query()
            ->active()
            ->when(($filters['featured'] ?? null) === '1', fn ($query) => $query->featured())
            ->ordered()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function featured(int $limit = 6): Collection
    {
        return Testimonial::query()
            ->active()
            ->featured()
            ->ordered()
            ->limit($limit)
            ->get();
    }

    public function findActiveBySlug(string $slug): Testimonial
    {
        return Testimonial::query()
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function related(Testimonial $testimonial, int $limit = 3): Collection
    {
        return Testimonial::query()
            ->active()
            ->whereKeyNot($testimonial->id)
            ->ordered()
            ->limit($limit)
            ->get();
    }

    public function create(array $data, ?UploadedFile $image = null): Testimonial
    {
        return DB::transaction(function () use ($data, $image) {
            $testimonial = Testimonial::create($this->payload($data));

            if ($image) {
                $testimonial->update([
                    'image' => $image->storeAs('testimonials', $this->datedOriginalFilename($image), 'public'),
                ]);
            }

            return $testimonial;
        }, 3);
    }

    public function update(Testimonial $testimonial, array $data, ?UploadedFile $image = null): Testimonial
    {
        return DB::transaction(function () use ($testimonial, $data, $image) {
            $testimonial->update($this->payload($data, $testimonial));

            if ($image) {
                if ($testimonial->image) {
                    Storage::disk('public')->delete($testimonial->image);
                }

                $testimonial->update([
                    'image' => $image->storeAs('testimonials', $this->datedOriginalFilename($image), 'public'),
                ]);
            }

            return $testimonial->fresh();
        }, 3);
    }

    public function delete(Testimonial $testimonial): void
    {
        if ($testimonial->image) {
            Storage::disk('public')->delete($testimonial->image);
        }

        $testimonial->delete();
    }

    protected function payload(array $data, ?Testimonial $testimonial = null): array
    {
        return [
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['slug'] ?? $data['name'], $testimonial?->id),
            'company' => $data['company'] ?? null,
            'designation' => $data['designation'] ?? null,
            'content' => $data['content'],
            'rating' => $data['rating'],
            'location' => $data['location'] ?? null,
            'project_name' => $data['project_name'] ?? null,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'is_featured' => (bool) ($data['is_featured'] ?? false),
            'status' => (bool) ($data['status'] ?? false),
            'order' => $data['order'] ?? $testimonial?->order ?? ((int) Testimonial::max('order') + 1),
        ];
    }

    protected function uniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source) ?: Str::random(8);
        $slug = $base;
        $counter = 2;

        while (Testimonial::query()
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
        $safeName = Str::slug($name) ?: 'testimonial';

        return $safeName . '-' . now()->format('Y-m-d-His') . ($extension ? '.' . strtolower($extension) : '');
    }
}
