<?php

namespace Modules\Seo\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ModuleRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Seo\Models\SeoSetting;

class SeoController extends Controller
{
    public function index()
    {
        return view('seo::index', [
            'seoSettings' => SeoSetting::query()
                ->latest()
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function create()
    {
        $pageTypes = $this->pageTypes();

        return view('seo::form', [
            'seoSetting' => new SeoSetting([
                'page_type' => array_key_first($pageTypes) ?: 'custom',
                'seo_twitter_card' => 'summary_large_image',
                'seo_indexing' => 'index',
                'is_active' => true,
            ]),
            'isEdit' => false,
            'pages' => $this->pages(),
            'pageTypes' => $pageTypes,
            'pageModuleAvailable' => $this->pageModuleAvailable(),
            'twitterCards' => $this->twitterCards(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(), $this->messages());
        $validated['page_key'] = $this->normalizePageKey($validated['page_key']);
        $validated['is_active'] = $request->boolean('is_active');
        $this->assertPageExistsForPageType($validated['page_type'], $validated['page_key']);

        if ($request->hasFile('seo_og_image')) {
            $validated['seo_og_image'] = $request->file('seo_og_image')->store('seo/og', 'public');
        }

        SeoSetting::create($validated);

        return redirect()
            ->route('admin.seo.index')
            ->with('success', 'SEO settings created successfully.');
    }

    public function edit(SeoSetting $seo)
    {
        return view('seo::form', [
            'seoSetting' => $seo,
            'isEdit' => true,
            'pages' => $this->pages(),
            'pageTypes' => $this->pageTypes(),
            'pageModuleAvailable' => $this->pageModuleAvailable(),
            'twitterCards' => $this->twitterCards(),
        ]);
    }

    public function update(Request $request, SeoSetting $seo)
    {
        $validated = $request->validate($this->rules($seo), $this->messages());
        $validated['page_key'] = $this->normalizePageKey($validated['page_key']);
        $validated['is_active'] = $request->boolean('is_active');
        $this->assertPageExistsForPageType($validated['page_type'], $validated['page_key']);

        if ($request->hasFile('seo_og_image')) {
            if ($seo->seo_og_image) {
                Storage::disk('public')->delete($seo->seo_og_image);
            }

            $validated['seo_og_image'] = $request->file('seo_og_image')->store('seo/og', 'public');
        }

        $seo->update($validated);

        return redirect()
            ->route('admin.seo.index')
            ->with('success', 'SEO settings updated successfully.');
    }

    public function destroy(SeoSetting $seo)
    {
        if ($seo->seo_og_image) {
            Storage::disk('public')->delete($seo->seo_og_image);
        }

        $seo->delete();

        return redirect()
            ->route('admin.seo.index')
            ->with('success', 'SEO settings deleted successfully.');
    }

    protected function rules(?SeoSetting $seo = null): array
    {
        return [
            'page_type' => ['required', 'string', 'max:50', Rule::in(array_keys($this->pageTypes()))],
            'page_key' => [
                'required',
                'string',
                'max:255',
                Rule::unique('seo_settings', 'page_key')
                    ->where(fn ($query) => $query->where('page_type', request('page_type')))
                    ->ignore($seo?->id),
            ],
            'page_label' => ['nullable', 'string', 'max:255'],
            'seo_meta_title' => ['nullable', 'string', 'max:255'],
            'seo_meta_description' => ['nullable', 'string', 'max:500'],
            'seo_meta_keywords' => ['nullable', 'string', 'max:1000'],
            'seo_og_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'seo_twitter_card' => ['required', Rule::in(array_keys($this->twitterCards()))],
            'seo_canonical_url' => ['nullable', 'url', 'max:255'],
            'seo_indexing' => ['required', Rule::in(['index', 'noindex'])],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function messages(): array
    {
        return [
            'page_type.required' => 'Please choose where this SEO setting should apply.',
            'page_type.in' => 'Please choose a valid page type from the list.',
            'page_key.required' => 'Please enter the page key. Use the helper text below the field for the selected page type.',
            'page_key.unique' => 'SEO settings already exist for this page type and page key.',
            'seo_canonical_url.url' => 'The canonical URL must be a full URL, for example https://example.com/about-us.',
            'seo_og_image.image' => 'The OG image must be a valid image file.',
            'seo_og_image.mimes' => 'The OG image must be a JPG, PNG, or WEBP file.',
            'seo_indexing.in' => 'Please choose index or noindex.',
        ];
    }

    protected function normalizePageKey(string $pageKey): string
    {
        $pageKey = trim($pageKey);

        if (str_starts_with($pageKey, 'http://') || str_starts_with($pageKey, 'https://')) {
            return $pageKey;
        }

        return trim($pageKey, '/');
    }

    protected function pageTypes(): array
    {
        $types = [
            'route' => 'Route Name',
            // 'form' => 'Form',
            // 'gallery' => 'Gallery',
            // 'menu' => 'Menu',
            'custom' => 'Custom',
        ];

        if ($this->pageModuleAvailable()) {
            $types = ['page' => 'Created Page'] + $types;
        }

        return $types;
    }

    protected function twitterCards(): array
    {
        return [
            'summary' => 'Summary',
            'summary_large_image' => 'Summary Large Image',
            'app' => 'App',
            'player' => 'Player',
        ];
    }

    protected function pages()
    {
        if (! $this->pageModuleAvailable()) {
            return collect();
        }

        return DB::table('pages')
            ->orderBy('title')
            ->get(['id', 'title', 'slug']);
    }

    protected function assertPageExistsForPageType(string $pageType, string $pageKey): void
    {
        if ($pageType !== 'page') {
            return;
        }

        if (! $this->pageModuleAvailable()) {
            throw ValidationException::withMessages([
                'page_key' => 'The Page module is not available right now, so page-based SEO entries cannot be used.',
            ]);
        }

        if (DB::table('pages')->where('slug', $pageKey)->exists()) {
            return;
        }

        throw ValidationException::withMessages([
            'page_key' => 'Please choose a valid page from the list.',
        ]);
    }

    protected function hasPagesTable(): bool
    {
        return Schema::hasTable('pages');
    }

    protected function pageModuleAvailable(): bool
    {
        return ModuleRegistry::enabled('page')
            && class_exists(\Modules\Page\Models\Page::class)
            && $this->hasPagesTable();
    }
}
