<?php

namespace Modules\Page\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Page\Models\Page;

class PageController extends Controller
{
    public function index()
    {
        return view('page::index', [
            'pages' => Page::query()
                ->latest()
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function create()
    {
        return view('page::form', [
            'page' => new Page([
                'is_active' => true,
            ]),
            'isEdit' => false,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);
        $page = Page::create($validated);

        $this->ensureBladeViewExists($page);

        return redirect()
            ->route('admin.pages.index')
            ->with('success', 'Page created successfully.');
    }

    public function edit(Page $page)
    {
        return view('page::form', [
            'page' => $page,
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, Page $page)
    {
        $previousViewPath = $page->view_path;
        $validated = $this->validated($request, $page);
        $page->update($validated);

        $this->ensureBladeViewExists($page);
        $this->deleteBladeViewIfExists($previousViewPath, $page->view_path);

        return redirect()
            ->route('admin.pages.index')
            ->with('success', 'Page updated successfully.');
    }

    public function destroy(Page $page)
    {
        $this->deleteBladeViewIfExists($page->view_path);
        $page->delete();

        return redirect()
            ->route('admin.pages.index')
            ->with('success', 'Page deleted successfully.');
    }

    public function show(Page $page)
    {
        abort_unless($page->is_active, 404);
        abort_unless(view()->exists($page->view_path), 404);

        return response()->view($page->view_path, [
            'page' => $page,
        ]);
    }

    protected function validated(Request $request, ?Page $page = null): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('pages', 'slug')->ignore($page?->id),
            ],
            'view_path' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'slug.regex' => 'The slug may only contain lowercase letters, numbers, and hyphens.',
        ]);

        $validated['slug'] = Str::slug($validated['slug']);
        $validated['view_path'] = $this->normalizeViewPath($validated['view_path'], $validated['slug']);
        $validated['is_active'] = $request->boolean('is_active');
        $this->ensureBladeViewPathIsAvailable($validated['view_path'], $page);

        return $validated;
    }

    protected function normalizeViewPath(?string $viewPath, string $slug): string
    {
        $path = trim((string) $viewPath);

        if ($path === '') {
            $path = 'pages.' . $slug;
        }

        $path = str_replace(['\\', '/'], '.', $path);
        $path = preg_replace('/[^A-Za-z0-9._-]+/', '', $path) ?? '';
        $path = preg_replace('/\.{2,}/', '.', $path) ?? '';
        $path = trim($path, '.');

        return $path !== '' ? $path : 'pages.' . $slug;
    }

    protected function ensureBladeViewExists(Page $page): void
    {
        $fullPath = $this->bladeViewFullPath($page->view_path);
        $directory = dirname($fullPath);

        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        if (File::exists($fullPath)) {
            return;
        }

        File::put($fullPath, $this->defaultBladeTemplate($page));
    }

    protected function ensureBladeViewPathIsAvailable(string $viewPath, ?Page $page = null): void
    {
        $fullPath = $this->bladeViewFullPath($viewPath);
        $currentPath = $page?->view_path
            ? $this->bladeViewFullPath($page->view_path)
            : null;

        if ($currentPath && realpath($currentPath) === realpath($fullPath)) {
            return;
        }

        if (File::exists($fullPath)) {
            throw ValidationException::withMessages([
                'view_path' => 'This Blade path already exists. Please choose a different path or edit the existing Blade file directly.',
            ]);
        }
    }

    protected function deleteBladeViewIfExists(string $viewPath, ?string $exceptViewPath = null): void
    {
        if ($exceptViewPath && $viewPath === $exceptViewPath) {
            return;
        }

        $fullPath = $this->bladeViewFullPath($viewPath);

        if (! File::exists($fullPath)) {
            return;
        }

        File::delete($fullPath);
        $this->cleanupEmptyViewDirectories(dirname($fullPath));
    }

    protected function cleanupEmptyViewDirectories(string $directory): void
    {
        $viewsRoot = resource_path('views');
        $normalizedViewsRoot = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $viewsRoot);
        $normalizedDirectory = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $directory);

        while (
            $normalizedDirectory !== $normalizedViewsRoot
            && str_starts_with($normalizedDirectory, $normalizedViewsRoot . DIRECTORY_SEPARATOR)
            && File::isDirectory($normalizedDirectory)
            && count(File::files($normalizedDirectory)) === 0
            && count(File::directories($normalizedDirectory)) === 0
        ) {
            File::deleteDirectory($normalizedDirectory);
            $normalizedDirectory = dirname($normalizedDirectory);
        }
    }

    protected function bladeViewFullPath(string $viewPath): string
    {
        return resource_path('views' . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $viewPath) . '.blade.php');
    }

    protected function defaultBladeTemplate(Page $page): string
    {
        return <<<'BLADE'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('seo::meta')
    <title>{{ $page->title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 40px 20px;
            background: #f8fafc;
            color: #111827;
        }

        .page-shell {
            max-width: 960px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 32px;
        }
    </style>
</head>
<body>
    <main class="page-shell">
        <h1>{{ $page->title }}</h1>
        <p>Start editing this Blade file:</p>
        <p><code>{{ $page->view_path }}</code></p>
    </main>
</body>
</html>
BLADE;
    }
}
