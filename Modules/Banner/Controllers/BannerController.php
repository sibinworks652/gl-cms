<?php

namespace Modules\Banner\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Modules\Banner\Models\BannerSlide;

class BannerController extends Controller
{
    public function preview()
    {
        $slides = BannerSlide::query()
            ->live()
            ->ordered()
            ->get();

        return view('banner::preview', [
            'slides' => $slides,
        ]);
    }

    public function apiPublicIndex(): JsonResponse
    {
        $slides = BannerSlide::query()
            ->live()
            ->ordered()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $slides->map(fn (BannerSlide $slide) => $this->transformSlide($slide))->values(),
        ]);
    }

    public function apiPublicShow(BannerSlide $banner): JsonResponse
    {
        abort_unless($banner->is_active, 404);

        return response()->json([
            'success' => true,
            'data' => $this->transformSlide($banner),
        ]);
    }

    public function index(Request $request)
    {
        $slides = BannerSlide::query()
            ->ordered()
            ->paginate(12)
            ->withQueryString();

        if ($request->expectsJson()) {
            return response()->json($slides);
        }

        return view('banner::index', [
            'slides' => $slides,
        ]);
    }

    public function create(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'media_types' => BannerSlide::mediaTypes(),
                'link_types' => BannerSlide::linkTypes(),
                'page_suggestions' => $this->pageSuggestions(),
            ]);
        }

        return view('banner::form', [
            'slide' => new BannerSlide([
                'media_type' => 'image',
                'button_link_type' => 'custom',
                'is_active' => true,
            ]),
            'isEdit' => false,
            'mediaTypes' => BannerSlide::mediaTypes(),
            'linkTypes' => BannerSlide::linkTypes(),
            'pageSuggestions' => $this->pageSuggestions(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateSlide($request);

        $slide = null;

        DB::transaction(function () use ($request, $validated, &$slide) {
            $slide = BannerSlide::create($this->payload($validated));

            if ($request->hasFile('image')) {
                $slide->update([
                    'image_path' => $request->file('image')->store('banners', 'public'),
                ]);
            }
        }, 3);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Banner slide created successfully.',
                'data' => $slide ? $this->transformSlide($slide->fresh()) : null,
            ], 201);
        }

        return redirect()
            ->route('admin.banners.index')
            ->with('success', 'Banner slide created successfully.');
    }

    public function edit(Request $request, BannerSlide $banner)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'slide' => $this->transformSlide($banner),
                'media_types' => BannerSlide::mediaTypes(),
                'link_types' => BannerSlide::linkTypes(),
                'page_suggestions' => $this->pageSuggestions(),
            ]);
        }

        return view('banner::form', [
            'slide' => $banner,
            'isEdit' => true,
            'mediaTypes' => BannerSlide::mediaTypes(),
            'linkTypes' => BannerSlide::linkTypes(),
            'pageSuggestions' => $this->pageSuggestions(),
        ]);
    }

    public function show(BannerSlide $banner)
    {
        return view('banner::view', [
            'slide' => $banner,
        ]);
    }

    public function update(Request $request, BannerSlide $banner)
    {
        $validated = $this->validateSlide($request, $banner);

        DB::transaction(function () use ($request, $validated, $banner) {
            $payload = $this->payload($validated, $banner);

            if ($validated['media_type'] === 'video' && $banner->image_path) {
                Storage::disk('public')->delete($banner->image_path);
                $payload['image_path'] = null;
            }

            $banner->update($payload);

            if ($request->hasFile('image')) {
                if ($banner->image_path) {
                    Storage::disk('public')->delete($banner->image_path);
                }

                $banner->update([
                    'image_path' => $request->file('image')->store('banners', 'public'),
                ]);
            }
        });

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Banner slide updated successfully.',
                'data' => $this->transformSlide($banner->fresh()),
            ]);
        }

        return redirect()
            ->route('admin.banners.index')
            ->with('success', 'Banner slide updated successfully.');
    }

    public function destroy(BannerSlide $banner)
    {
        if ($banner->image_path) {
            Storage::disk('public')->delete($banner->image_path);
        }

        $banner->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Banner slide deleted successfully.',
            ]);
        }

        return redirect()
            ->route('admin.banners.index')
            ->with('success', 'Banner slide deleted successfully.');
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', Rule::exists('banner_slides', 'id')],
        ]);

        DB::transaction(function () use ($validated) {
            foreach (array_values($validated['order']) as $index => $id) {
                BannerSlide::query()->whereKey($id)->update(['sort_order' => $index]);
            }
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'message' => 'Banner order updated successfully.',
            ]);
        }

        return redirect()
            ->route('admin.banners.index')
            ->with('success', 'Banner order updated successfully.');
    }

    protected function validateSlide(Request $request, ?BannerSlide $slide = null): array
    {
        $mediaType = $request->input('media_type', $slide?->media_type ?? 'image');

        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'media_type' => ['required', Rule::in(array_keys(BannerSlide::mediaTypes()))],
            'image' => [
                Rule::requiredIf($mediaType === 'image' && ! $slide?->image_path),
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp,gif',
                'max:4096',
            ],
            'video_url' => [Rule::requiredIf($mediaType === 'video'), 'nullable', 'url', 'max:2048'],
            'button_label' => ['nullable', 'string', 'max:255'],
            'button_link_type' => ['required', Rule::in(array_keys(BannerSlide::linkTypes()))],
            'button_link' => ['nullable', 'string', 'max:2048'],
            'open_in_new_tab' => ['nullable', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    protected function payload(array $validated, ?BannerSlide $slide = null): array
    {
        return [
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'] ?? null,
            'description' => $validated['description'] ?? null,
            'media_type' => $validated['media_type'],
            'image_path' => $validated['media_type'] === 'image' ? ($slide?->image_path) : null,
            'video_url' => $validated['media_type'] === 'video' ? ($validated['video_url'] ?? null) : null,
            'button_label' => $validated['button_label'] ?? null,
            'button_link_type' => $validated['button_link_type'],
            'button_link' => $validated['button_link'] ?? null,
            'open_in_new_tab' => (bool) ($validated['open_in_new_tab'] ?? false),
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? false),
            'sort_order' => $slide?->sort_order ?? ((int) BannerSlide::max('sort_order') + 1),
        ];
    }

    protected function pageSuggestions(): array
    {
        return collect(Route::getRoutes())
            ->filter(function ($route) {
                $uri = trim($route->uri(), '/');

                return in_array('GET', $route->methods(), true)
                    && ! str_starts_with($route->uri(), 'admin')
                    && ! str_starts_with($route->uri(), 'api')
                    && ! str_contains($route->uri(), '{')
                    && ! in_array($uri, ['login', 'logout'], true);
            })
            ->map(function ($route) {
                $uri = trim($route->uri(), '/');

                return $uri === '' ? '/' : $uri;
            })
            ->unique()
            ->values()
            ->all();
    }

    protected function transformSlide(BannerSlide $slide): array
    {
        return [
            'id' => $slide->id,
            'title' => $slide->title,
            'subtitle' => $slide->subtitle,
            'description' => $slide->description,
            'media_type' => $slide->media_type,
            'image_path' => $slide->image_path,
            'image_url' => $slide->image_path ? asset('storage/' . $slide->image_path) : null,
            'video_url' => $slide->video_url,
            'button_label' => $slide->button_label,
            'button_link_type' => $slide->button_link_type,
            'button_link' => $slide->button_link,
            'resolved_button_link' => $slide->resolved_button_link,
            'open_in_new_tab' => (bool) $slide->open_in_new_tab,
            'starts_at' => optional($slide->starts_at)?->toISOString(),
            'ends_at' => optional($slide->ends_at)?->toISOString(),
            'is_active' => (bool) $slide->is_active,
            'sort_order' => $slide->sort_order,
            'created_at' => optional($slide->created_at)?->toISOString(),
            'updated_at' => optional($slide->updated_at)?->toISOString(),
        ];
    }
}
