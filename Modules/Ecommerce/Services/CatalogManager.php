<?php

namespace Modules\Ecommerce\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\User;
use Modules\Ecommerce\Models\AttributeOption;
use Modules\Ecommerce\Models\Category;
use Modules\Ecommerce\Models\Inventory;
use Modules\Ecommerce\Models\Product;
use Modules\Ecommerce\Models\ProductImage;
use Modules\Ecommerce\Models\ProductVariant;
use Modules\Ecommerce\Models\Vendor;
use Modules\Ecommerce\Support\EcommerceSettings;
use Spatie\Permission\Models\Role;

class CatalogManager
{
    public function adminProducts(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Product::query()
            ->with(['category.parent', 'vendor', 'brand', 'variants', 'tags'])
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('sku', 'like', '%' . $search . '%');
                });
            })
            ->when($filters['category_id'] ?? null, fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->when(isset($filters['status']) && $filters['status'] !== '', fn ($query) => $query->where('status', (bool) $filters['status']))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function storefrontProducts(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $sort = in_array($filters['sort'] ?? 'created_at', ['created_at', 'base_price', 'name'], true)
            ? ($filters['sort'] ?? 'created_at')
            : 'created_at';
        $sortDir = ($filters['sort_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return Product::query()
            ->with(['category.parent', 'vendor', 'variants.attributeOptions.attribute', 'images', 'tags', 'brand'])
            ->active()
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            })
            ->when($filters['category'] ?? null, function ($query, string $categorySlug) {
                $query->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('slug', $categorySlug));
            })
            ->when($filters['category_id'] ?? null, fn ($query, $catId) => $query->where('category_id', $catId))
            ->when($filters['vendor'] ?? null, function ($query, string $vendorSlug) {
                $query->whereHas('vendor', fn ($vendorQuery) => $vendorQuery->where('slug', $vendorSlug));
            })
            ->when($filters['brand'] ?? null, function ($query, string $brandSlug) {
                $query->whereHas('brand', fn ($brandQuery) => $brandQuery->where('slug', $brandSlug));
            })
            ->when($filters['brand_id'] ?? null, fn ($query, $brandId) => $query->where('brand_id', $brandId))
            ->when($filters['tag'] ?? null, function ($query, string $tagSlug) {
                $query->whereHas('tags', fn ($tagQuery) => $tagQuery->where('slug', $tagSlug));
            })
            ->when($filters['min_price'] ?? null, fn ($query, $min) => $query->where('base_price', '>=', $min))
            ->when($filters['max_price'] ?? null, fn ($query, $max) => $query->where('base_price', '<=', $max))
            ->when($filters['featured'] ?? false, fn ($query) => $query->featured())
            ->when($filters['in_stock'] ?? false, function ($query) {
                $query->where(function ($stockQuery) {
                    $stockQuery
                        ->where('stock', '>', 0)
                        ->orWhereHas('variants', fn ($variantQuery) => $variantQuery->where('stock', '>', 0))
                        ->orWhere('allow_backorder', true)
                        ->orWhereHas('variants', fn ($variantQuery) => $variantQuery->where('allow_backorder', true));
                });
            })
            ->when($filters['on_sale'] ?? false, fn ($query) => $query->whereNotNull('sale_price')->where('sale_price', '>', 0))
            ->orderBy($sort, $sortDir)
            ->paginate($perPage)
            ->withQueryString();
    }

    public function activeCategories(): Collection
    {
        return Category::query()->active()->ordered()->with('children')->get();
    }

    public function activeVendors(): Collection
    {
        return Vendor::query()->active()->orderBy('name')->get();
    }

    public function findProductBySlug(string $slug): Product
    {
        return Product::query()
            ->with([
                'category.parent',
                'vendor',
                'brand',
                'images',
                'tags',
                'attributes.options',
                'attributeOptions.attribute',
                'variants' => fn ($query) => $query->active()->with(['attributeOptions.attribute', 'inventory']),
            ])
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function createCategory(array $data, ?UploadedFile $image = null): Category
    {
        return DB::transaction(function () use ($data, $image) {
            $category = Category::create($this->categoryPayload($data));

            if ($image) {
                $category->update(['image' => $image->store('ecommerce/categories', 'public')]);
            }

            return $category;
        }, 3);
    }

    public function updateCategory(Category $category, array $data, ?UploadedFile $image = null): Category
    {
        return DB::transaction(function () use ($category, $data, $image) {
            $category->update($this->categoryPayload($data, $category));

            if ($image) {
                if ($category->image) {
                    Storage::disk('public')->delete($category->image);
                }

                $category->update(['image' => $image->store('ecommerce/categories', 'public')]);
            }

            return $category->fresh();
        }, 3);
    }

    public function createVendor(array $data, ?UploadedFile $logo = null): Vendor
    {
        return DB::transaction(function () use ($data, $logo) {
            $vendor = Vendor::create($this->vendorPayload($data));

            if ($logo) {
                $vendor->update(['logo' => $logo->store('ecommerce/vendors', 'public')]);
            }

            return $vendor;
        }, 3);
    }

    public function updateVendor(Vendor $vendor, array $data, ?UploadedFile $logo = null): Vendor
    {
        return DB::transaction(function () use ($vendor, $data, $logo) {
            $vendor->update($this->vendorPayload($data, $vendor));

            if ($logo) {
                if ($vendor->logo) {
                    Storage::disk('public')->delete($vendor->logo);
                }

                $vendor->update(['logo' => $logo->store('ecommerce/vendors', 'public')]);
            }

            return $vendor->fresh();
        }, 3);
    }

    public function registerVendor(User $user, array $data): Vendor
    {
        return DB::transaction(function () use ($user, $data) {
            $approved = EcommerceSettings::vendorAutoApprove();

            $vendor = Vendor::create([
                'user_id' => $user->id,
                'name' => $data['store_name'],
                'slug' => $this->uniqueSlug(Vendor::class, $data['store_name']),
                'email' => $data['email'] ?? $user->email,
                'phone' => $data['phone'] ?? null,
                'description' => $data['description'] ?? null,
                'commission_rate' => array_key_exists('commission_rate', $data) && $data['commission_rate'] !== null
                    ? (float) $data['commission_rate']
                    : EcommerceSettings::vendorDefaultCommissionRate(),
                'status' => $approved ? 'approved' : 'pending',
                'approved_at' => $approved ? now() : null,
            ]);

            $vendorRole = Role::findOrCreate('vendor', 'web');
            $user->assignRole($vendorRole);

            return $vendor;
        }, 3);
    }

    public function approveVendor(Vendor $vendor): Vendor
    {
        return DB::transaction(function () use ($vendor) {
            $vendor->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            return $vendor->fresh();
        }, 3);
    }

    public function rejectVendor(Vendor $vendor, string $reason): Vendor
    {
        return DB::transaction(function () use ($vendor, $reason) {
            $vendor->update([
                'status' => 'rejected',
                'rejection_reason' => $reason,
            ]);

            return $vendor->fresh();
        }, 3);
    }

    public function saveProduct(array $data, ?Product $product = null, ?UploadedFile $featuredImage = null, array $galleryImages = []): Product
    {
        return DB::transaction(function () use ($data, $product, $featuredImage, $galleryImages) {
            $payload = $this->productPayload($data, $product);
            $product ??= new Product();
            $product->fill($payload)->save();

            if ($featuredImage) {
                if ($product->featured_image) {
                    Storage::disk('public')->delete($product->featured_image);
                }

                $product->update(['featured_image' => $featuredImage->store('ecommerce/products', 'public')]);
            }

            $selectedAttributeIds = collect($data['attribute_ids'] ?? [])->filter()->map(fn ($id) => (int) $id)->values();
            $variants = $this->prepareVariants($data['variants'] ?? []);
            $variantOptionIds = collect($variants)
                ->pluck('attribute_option_ids')
                ->flatten()
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();

            if ($variantOptionIds->isNotEmpty()) {
                $selectedAttributeIds = $selectedAttributeIds
                    ->merge(
                        AttributeOption::query()
                            ->whereIn('id', $variantOptionIds)
                            ->pluck('attribute_id')
                    )
                    ->unique()
                    ->values();
            }

            $this->syncVariants($product, $variants);
            $this->appendGalleryImages($product, $galleryImages);
            $this->syncTags($product, $data['tag_ids'] ?? []);
            $this->syncAttributes($product, $selectedAttributeIds->all());
            $this->syncAttributeOptions($product, $variantOptionIds->all());

            return $product->fresh([
                'category.parent',
                'vendor',
                'brand',
                'variants.attributeOptions.attribute',
                'images',
                'tags',
                'attributes.options',
                'attributeOptions.attribute',
            ]);
        }, 3);
    }

    public function deleteProduct(Product $product): void
    {
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
        }

        if ($product->featured_image) {
            Storage::disk('public')->delete($product->featured_image);
        }

        $product->delete();
    }

    protected function categoryPayload(array $data, ?Category $category = null): array
    {
        return [
            'parent_id' => $data['parent_id'] ?? null,
            'name' => $data['name'],
            'slug' => $this->uniqueSlug(Category::class, $data['slug'] ?? $data['name'], $category?->id),
            'description' => $data['description'] ?? null,
            'status' => (bool) ($data['status'] ?? false),
            'order' => (int) ($data['order'] ?? $category?->order ?? ((int) Category::max('order') + 1)),
        ];
    }

    protected function vendorPayload(array $data, ?Vendor $vendor = null): array
    {
        $commissionRate = array_key_exists('commission_rate', $data) && $data['commission_rate'] !== null
            ? (float) $data['commission_rate']
            : ((float) ($vendor?->commission_rate ?? EcommerceSettings::vendorDefaultCommissionRate()));

        return [
            'user_id' => $data['user_id'] ?? null,
            'name' => $data['name'],
            'slug' => $this->uniqueSlug(Vendor::class, $data['slug'] ?? $data['name'], $vendor?->id),
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'description' => $data['description'] ?? null,
            'commission_rate' => $commissionRate,
            'status' => $data['status'] === 'pending' || $data['status'] === 'approved' || $data['status'] === 'rejected' 
                ? $data['status'] 
                : ($vendor?->status ?? 'pending'),
        ];
    }

    protected function productPayload(array $data, ?Product $product = null): array
    {
        return [
            'vendor_id' => $data['vendor_id'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'brand_id' => $data['brand_id'] ?? null,
            'name' => $data['name'],
            'slug' => $this->uniqueSlug(Product::class, $data['slug'] ?? $data['name'], $product?->id),
            'sku' => $data['sku'],
            'short_description' => $data['short_description'] ?? null,
            'description' => $data['description'] ?? null,
            'base_price' => $data['base_price'],
            'sale_price' => $data['sale_price'] ?? null,
            'tax_percentage' => $data['tax_percentage'] ?? null,
            'shipping_weight' => $data['shipping_weight'] ?? null,
            'shipping_cost' => $data['shipping_cost'] ?? null,
            'delivery_rules' => $this->normalizeDeliveryRules($data['delivery_rules'] ?? null),
            'stock' => (int) ($data['stock'] ?? 0),
            'is_featured' => (bool) ($data['is_featured'] ?? false),
            'status' => (bool) ($data['status'] ?? false),
            'track_inventory' => (bool) ($data['track_inventory'] ?? true),
            'low_stock_threshold' => (int) ($data['low_stock_threshold'] ?? 10),
            'allow_backorder' => (bool) ($data['allow_backorder'] ?? false),
        ];
    }

    protected function syncVariants(Product $product, array $variants): void
    {
        $existingIds = [];

        foreach ($variants as $variantData) {
            if (! is_array($variantData) || empty($variantData['sku'])) {
                continue;
            }

            $variant = ProductVariant::query()->updateOrCreate(
                [
                    'id' => $variantData['id'] ?? null,
                    'product_id' => $product->id,
                ],
                [
                    'sku' => $variantData['sku'],
                    'size' => $variantData['size'] ?? null,
                    'color' => $variantData['color'] ?? null,
                    'price' => $variantData['price'] ?? $product->sale_price ?? $product->base_price,
                    'stock' => (int) ($variantData['stock'] ?? 0),
                    'status' => (bool) ($variantData['status'] ?? true),
                    'track_inventory' => (bool) ($variantData['track_inventory'] ?? true),
                    'low_stock_threshold' => (int) ($variantData['low_stock_threshold'] ?? 10),
                    'allow_backorder' => (bool) ($variantData['allow_backorder'] ?? false),
                    'options' => $variantData['options'] ?? $variantData['label'] ?? null,
                ]
            );

            $existingIds[] = $variant->id;

            $optionIds = collect($variantData['attribute_option_ids'] ?? [])
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->values();

            if ($optionIds->isNotEmpty()) {
                $pivotData = AttributeOption::query()
                    ->whereIn('id', $optionIds)
                    ->get(['id', 'attribute_id'])
                    ->mapWithKeys(fn ($option) => [
                        $option->id => ['attribute_id' => $option->attribute_id],
                    ])
                    ->all();

                $variant->attributeOptions()->sync($pivotData);
            } else {
                $variant->attributeOptions()->sync([]);
            }

            Inventory::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'product_variant_id' => $variant->id,
                ],
                [
                    'sku' => $variantData['sku'],
                    'quantity' => (int) ($variantData['stock'] ?? 0),
                    'track_inventory' => (bool) ($variantData['track_inventory'] ?? true),
                    'low_stock_threshold' => (int) ($variantData['low_stock_threshold'] ?? 10),
                    'allow_backorder' => (bool) ($variantData['allow_backorder'] ?? false),
                ]
            );
        }

        $product->variants()->whereNotIn('id', $existingIds ?: [0])->delete();

        Inventory::where('product_id', $product->id)
            ->whereNotNull('product_variant_id')
            ->whereNotIn('product_variant_id', $existingIds ?: [0])
            ->delete();

        if ($product->variants()->doesntExist()) {
            Inventory::updateOrCreate(
                [
                    'product_id' => $product->id,
                ],
                [
                    'product_variant_id' => null,
                    'sku' => $product->sku,
                    'quantity' => $product->stock,
                    'track_inventory' => $product->track_inventory ?? true,
                    'low_stock_threshold' => $product->low_stock_threshold ?? 10,
                    'allow_backorder' => $product->allow_backorder ?? false,
                ]
            );
        } else {
            Inventory::where('product_id', $product->id)
                ->whereNull('product_variant_id')
                ->delete();
        }
    }

    protected function syncTags(Product $product, array $tagIds): void
    {
        $product->tags()->sync(array_filter($tagIds));
    }

    protected function syncAttributes(Product $product, array $attributeIds): void
    {
        $product->attributes()->sync(array_filter($attributeIds));
    }

    protected function syncAttributeOptions(Product $product, array $optionIds): void
    {
        $product->attributeOptions()->sync(array_filter($optionIds));
    }

    protected function appendGalleryImages(Product $product, array $galleryImages): void
    {
        $order = (int) $product->images()->max('order');

        foreach ($galleryImages as $image) {
            if (! $image instanceof UploadedFile) {
                continue;
            }

            ProductImage::create([
                'product_id' => $product->id,
                'path' => $image->store('ecommerce/products/gallery', 'public'),
                'order' => ++$order,
            ]);
        }
    }

    protected function prepareVariants(array $variants): array
    {
        return collect($variants)
            ->filter(fn ($variant) => is_array($variant) && ! empty($variant['sku']))
            ->map(function (array $variant) {
                $optionIds = collect($variant['attribute_option_ids'] ?? [])
                    ->filter()
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values()
                    ->all();

                $labels = collect($variant['label'] ?? [])
                    ->filter()
                    ->values()
                    ->all();

                if (empty($labels) && ! empty($variant['options']) && is_array($variant['options'])) {
                    $labels = array_values(array_filter($variant['options']));
                }

                return [
                    'id' => $variant['id'] ?? null,
                    'sku' => trim((string) $variant['sku']),
                    'size' => $variant['size'] ?? null,
                    'color' => $variant['color'] ?? null,
                    'price' => $variant['price'] ?? null,
                    'stock' => (int) ($variant['stock'] ?? 0),
                    'status' => (bool) ($variant['status'] ?? true),
                    'track_inventory' => (bool) ($variant['track_inventory'] ?? true),
                    'low_stock_threshold' => (int) ($variant['low_stock_threshold'] ?? 10),
                    'allow_backorder' => (bool) ($variant['allow_backorder'] ?? false),
                    'attribute_option_ids' => $optionIds,
                    'label' => $labels,
                    'options' => $labels,
                ];
            })
            ->values()
            ->all();
    }

    protected function normalizeDeliveryRules(?string $rules): ?array
    {
        if ($rules === null) {
            return null;
        }

        $normalized = collect(preg_split('/\r\n|\r|\n/', $rules))
            ->map(fn ($rule) => trim((string) $rule))
            ->filter()
            ->values()
            ->all();

        return $normalized ?: null;
    }

    protected function uniqueSlug(string $modelClass, string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source) ?: Str::random(8);
        $slug = $base;
        $counter = 2;

        while ($modelClass::query()->where('slug', $slug)->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))->exists()) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }
}
