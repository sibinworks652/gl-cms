<?php

namespace Modules\Menu\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Menu\Models\Menu;
use Modules\Menu\Models\MenuItem;
use Modules\Page\Models\Page;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::query()
            ->withCount('items')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('menu::index', [
            'menus' => $menus,
            'locations' => Menu::locations(),
        ]);
    }

    public function create()
    {
        return view('menu::form', [
            'menu' => new Menu(['is_active' => true]),
            'isEdit' => false,
            'locations' => Menu::locations(),
            'linkTypes' => MenuItem::linkTypes(),
            'pages' => $this->pages(),
            'itemsPayload' => '[]',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateMenu($request);
        $items = $this->validateItemsPayload($validated['items_payload'] ?? '[]');

        DB::transaction(function () use ($validated, $items) {
            $menu = Menu::create([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'location' => $validated['location'],
                'description' => $validated['description'] ?? null,
                'is_active' => (bool) ($validated['is_active'] ?? false),
            ]);

            $this->syncItems($menu, $items);
        });

        return redirect()
            ->route('admin.menus.index')
            ->with('success', 'Menu created successfully.');
    }

    public function edit(Menu $menu)
    {
        $menu->load(['rootItems.childrenRecursive']);

        return view('menu::form', [
            'menu' => $menu,
            'isEdit' => true,
            'locations' => Menu::locations(),
            'linkTypes' => MenuItem::linkTypes(),
            'pages' => $this->pages(),
            'itemsPayload' => json_encode($this->mapItemsForBuilder($menu->rootItems), JSON_PRETTY_PRINT),
        ]);
    }

    public function update(Request $request, Menu $menu)
    {
        $validated = $this->validateMenu($request, $menu);
        $items = $this->validateItemsPayload($validated['items_payload'] ?? '[]');

        DB::transaction(function () use ($validated, $menu, $items) {
            $menu->update([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'location' => $validated['location'],
                'description' => $validated['description'] ?? null,
                'is_active' => (bool) ($validated['is_active'] ?? false),
            ]);

            $this->syncItems($menu, $items);
        });

        return redirect()
            ->route('admin.menus.index')
            ->with('success', 'Menu updated successfully.');
    }
    public function view($menu){
        return view('menu::view',compact('menu'));
    }

    public function destroy(Menu $menu)
    {
        $menu->delete();

        return redirect()
            ->route('admin.menus.index')
            ->with('success', 'Menu deleted successfully.');
    }

    protected function validateMenu(Request $request, ?Menu $menu = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('menus', 'slug')->ignore($menu?->id),
            ],
            'location' => [
                'required',
                Rule::in(array_keys(Menu::locations())),
                Rule::unique('menus', 'location')->ignore($menu?->id),
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'items_payload' => ['nullable', 'string'],
        ]);
    }

    protected function validateItemsPayload(string $payload): array
    {
        if (trim($payload) === '') {
            return [];
        }

        $decoded = json_decode($payload, true);

        if (! is_array($decoded)) {
            throw ValidationException::withMessages([
                'items_payload' => 'The menu builder payload is invalid.',
            ]);
        }

        return $this->sanitizeItems($decoded);
    }

    protected function sanitizeItems(array $items, int $depth = 0): array
    {
        if ($depth > 10) {
            throw ValidationException::withMessages([
                'items_payload' => 'Menus can only be nested up to 10 levels.',
            ]);
        }

        $sanitized = [];

        foreach (array_values($items) as $index => $item) {
            if (! is_array($item)) {
                throw ValidationException::withMessages([
                    'items_payload' => 'Each menu item must be a valid object.',
                ]);
            }

            $title = trim((string) ($item['title'] ?? ''));
            $type = (string) ($item['type'] ?? 'custom');
            $target = trim((string) ($item['target'] ?? ''));
            $cssClass = trim((string) ($item['css_class'] ?? ''));

            if ($title === '') {
                throw ValidationException::withMessages([
                    'items_payload' => 'Every menu item needs a title.',
                ]);
            }

            if (! array_key_exists($type, MenuItem::linkTypes())) {
                throw ValidationException::withMessages([
                    'items_payload' => 'A menu item has an invalid link type.',
                ]);
            }

            if ($target === '') {
                throw ValidationException::withMessages([
                    'items_payload' => 'Every menu item needs a page slug, URL, or module route.',
                ]);
            }

            if ($type === 'page' && ! Page::query()->where('slug', $target)->exists()) {
                throw ValidationException::withMessages([
                    'items_payload' => 'A menu item points to a page that does not exist anymore.',
                ]);
            }

            $sanitized[] = [
                'id' => $item['id'] ?? null,
                'title' => Str::limit($title, 255, ''),
                'type' => $type,
                'target' => Str::limit($target, 2048, ''),
                'css_class' => $cssClass !== '' ? Str::limit($cssClass, 255, '') : null,
                'open_in_new_tab' => (bool) ($item['open_in_new_tab'] ?? false),
                'children' => $this->sanitizeItems($item['children'] ?? [], $depth + 1),
                'sort_order' => $index,
            ];
        }

        return $sanitized;
    }

    protected function syncItems(Menu $menu, array $items): void
    {
        $retainedIds = [];

        $persist = function (array $branch, ?int $parentId = null) use (&$persist, $menu, &$retainedIds) {
            foreach ($branch as $position => $item) {
                $model = null;
                $candidateId = $item['id'];

                if (is_numeric($candidateId)) {
                    $model = MenuItem::query()
                        ->where('menu_id', $menu->id)
                        ->find((int) $candidateId);
                }

                $model ??= new MenuItem();

                $model->fill([
                    'menu_id' => $menu->id,
                    'parent_id' => $parentId,
                    'title' => $item['title'],
                    'type' => $item['type'],
                    'target' => $item['target'],
                    'css_class' => $item['css_class'],
                    'open_in_new_tab' => $item['open_in_new_tab'],
                    'sort_order' => $position,
                ]);

                $model->save();
                $retainedIds[] = $model->id;

                $persist($item['children'], $model->id);
            }
        };

        $persist($items);

        $menu->items()
            ->whereNotIn('id', $retainedIds ?: [0])
            ->delete();
    }

    protected function mapItemsForBuilder($items): array
    {
        return $items->map(function (MenuItem $item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'type' => $item->type,
                'target' => $item->target,
                'css_class' => $item->css_class,
                'open_in_new_tab' => $item->open_in_new_tab,
                'children' => $this->mapItemsForBuilder($item->childrenRecursive),
            ];
        })->values()->all();
    }
     public function apiMenus(): JsonResponse
    {
        $menus = Menu::query()
            ->active()
            ->with(['rootItems.childrenRecursive'])
            ->orderBy('location')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $menus->map(fn (Menu $menu) => $this->transformMenu($menu))->values(),
        ]);
    }

    public function apiMenu(string $location): JsonResponse
    {
        $menu = Menu::query()
            ->active()
            ->where('location', $location)
            ->with(['rootItems.childrenRecursive'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $this->transformMenu($menu),
        ]);
    }
    

    protected function transformMenu(Menu $menu): array
    {
        return [
            'id' => $menu->id,
            'name' => $menu->name,
            'slug' => $menu->slug,
            'location' => $menu->location,
            'description' => $menu->description,
            'is_active' => (bool) $menu->is_active,
            'items' => $this->transformMenuItems($menu->rootItems),
            'created_at' => optional($menu->created_at)?->toISOString(),
            'updated_at' => optional($menu->updated_at)?->toISOString(),
        ];
    }

    protected function transformMenuItems($items): array
    {
        return $items->map(function (MenuItem $item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'type' => $item->type,
                'target' => $item->target,
                'url' => $item->resolved_url,
                'css_class' => $item->css_class,
                'open_in_new_tab' => (bool) $item->open_in_new_tab,
                'sort_order' => $item->sort_order,
                'children' => $this->transformMenuItems($item->childrenRecursive),
            ];
        })->values()->all();
    }

    protected function pages()
    {
        return Page::query()
            ->orderBy('title')
            ->get(['id', 'title', 'slug']);
    }
}
