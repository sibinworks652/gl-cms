<?php

namespace Modules\Gallery\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\Gallery\Models\GalleryAlbum;
use Modules\Gallery\Models\GalleryImage;
use Modules\Menu\Models\Menu;
use Modules\Menu\Models\MenuItem;

class GalleryController extends Controller
{
    public function apiGalleries(): JsonResponse
    {
        $albums = GalleryAlbum::query()
            ->where('is_active', true)
            ->with(['images' => fn ($query) => $query->latest()])
            ->withCount('images')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $albums->map(fn (GalleryAlbum $album) => $this->transformAlbum($album))->values(),
        ]);
    }

    public function apiGallery(string $slug): JsonResponse
    {
        $album = GalleryAlbum::query()
            ->where('is_active', true)
            ->where('slug', $slug)
            ->with(['images' => fn ($query) => $query->latest()])
            ->withCount('images')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $this->transformAlbum($album),
        ]);
    }

    public function index()
    {
        $albums = GalleryAlbum::withCount('images')
            ->with(['images' => fn ($query) => $query->latest()->limit(4)])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('gallery::index', [
            'albums' => $albums,
        ]);
    }

    public function create()
    {
        return view('gallery::form', [
            'album' => new GalleryAlbum(),
            'isEdit' => false,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $album = GalleryAlbum::create([
                'title' => $validated['title'],
                'slug' => $this->uniqueSlug($validated['title']),
                'description' => $validated['description'] ?? null,
                'is_active' => (bool) ($validated['is_active'] ?? false),
            ]);

            $this->storeImages($album, $request->file('images', []));
        });

        return redirect()
            ->route('admin.gallery.index')
            ->with('success', 'Gallery album created successfully.');
    }

    public function edit(GalleryAlbum $gallery)
    {
        $gallery->load('images');

        return view('gallery::form', [
            'album' => $gallery,
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, GalleryAlbum $gallery)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'images' => ['nullable', 'array'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'delete_images' => ['nullable', 'array'],
            'delete_images.*' => ['integer', Rule::exists('gallery_images', 'id')->where('album_id', $gallery->id)],
        ]);

        DB::transaction(function () use ($validated, $request, $gallery) {
            $gallery->update([
                'title' => $validated['title'],
                'slug' => $gallery->title === $validated['title'] ? $gallery->slug : $this->uniqueSlug($validated['title'], $gallery->id),
                'description' => $validated['description'] ?? null,
                'is_active' => (bool) ($validated['is_active'] ?? false),
            ]);

            if (! empty($validated['delete_images'])) {
                $images = GalleryImage::where('album_id', $gallery->id)
                    ->whereIn('id', $validated['delete_images'])
                    ->get();

                foreach ($images as $image) {
                    Storage::disk('public')->delete($image->image_path);
                    $image->delete();
                }
            }

            $this->storeImages($gallery, $request->file('images', []));
        });

        return redirect()
            ->route('admin.gallery.index')
            ->with('success', 'Gallery album updated successfully.');
    }

    public function destroy(GalleryAlbum $gallery)
    {
        DB::transaction(function () use ($gallery) {
            foreach ($gallery->images as $image) {
                Storage::disk('public')->delete($image->image_path);
            }

            $gallery->images()->delete();
            $gallery->delete();
        });

        return redirect()
            ->route('admin.gallery.index')
            ->with('success', 'Gallery album deleted successfully.');
    }

    protected function storeImages(GalleryAlbum $album, array $images): void
    {
        foreach ($images as $image) {
            $path = $image->storeAs('gallery', $this->datedOriginalFilename($image), 'public');

            $album->images()->create([
                'image_path' => $path,
                'original_name' => $image->getClientOriginalName(),
                'mime_type' => $image->getClientMimeType(),
                'file_size' => $image->getSize(),
            ]);
        }
    }

    protected function datedOriginalFilename(mixed $file): string
    {
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $safeName = Str::slug($name) ?: 'file';

        return $safeName . '-' . now()->format('Y-m-d-His') . ($extension ? '.' . strtolower($extension) : '');
    }

    protected function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (
            GalleryAlbum::query()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
    protected function transformAlbum(GalleryAlbum $album): array
    {
        return [
            'id' => $album->id,
            'title' => $album->title,
            'slug' => $album->slug,
            'description' => $album->description,
            'is_active' => (bool) $album->is_active,
            'images_count' => $album->images_count ?? $album->images->count(),
            'images' => $album->images->map(fn (GalleryImage $image) => [
                'id' => $image->id,
                'image_path' => $image->image_path,
                'image_url' => asset('storage/' . $image->image_path),
                'original_name' => $image->original_name,
                'mime_type' => $image->mime_type,
                'file_size' => $image->file_size,
                'created_at' => optional($image->created_at)?->toISOString(),
                'updated_at' => optional($image->updated_at)?->toISOString(),
            ])->values(),
            'created_at' => optional($album->created_at)?->toISOString(),
            'updated_at' => optional($album->updated_at)?->toISOString(),
        ];
    }

    
}
