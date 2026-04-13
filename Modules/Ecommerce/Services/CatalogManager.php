<?php

namespace Modules\Ecommerce\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\User;
use Modules\Ecommerce\Models\Category;
use Modules\Ecommerce\Models\Product;
use Modules\Ecommerce\Models\ProductImage;
use Modules\Ecommerce\Models\ProductVariant;
use Modules\Ecommerce\Models\Vendor;

class CatalogManager
{
    public function adminProducts(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Product::query()
            ->with(['category', 'vendor', 'variants'])
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
        return Product::query()
            ->with(['category', 'vendor', 'variants', 'images'])
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
            ->when($filters['vendor'] ?? null, function ($query, string $vendorSlug) {
                $query->whereHas('vendor', fn ($vendorQuery) => $vendorQuery->where('slug', $vendorSlug));
            })
            ->when($filters['featured'] ?? false, fn ($query) => $query->featured())
            ->latest()
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
            ->with(['category', 'vendor', 'images', 'variants' => fn ($query) => $query->active()])
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
            $vendor = Vendor::create([
                'user_id' => $user->id,
                'name' => $data['store_name'],
                'slug' => $this->uniqueSlug(Vendor::class, $data['store_name']),
                'email' => $data['email'] ?? $user->email,
                'phone' => $data['phone'] ?? null,
                'description' => $data['description'] ?? null,
                'commission_rate' => $data['commission_rate'] ?? 0,
                'status' => 'pending',
            ]);

            $user->assignRole('vendor');

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

            $this->syncVariants($product, $data['variants'] ?? []);
            $this->appendGalleryImages($product, $galleryImages);

            return $product->fresh(['category', 'vendor', 'variants', 'images']);
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
        return [
            'user_id' => $data['user_id'] ?? null,
            'name' => $data['name'],
            'slug' => $this->uniqueSlug(Vendor::class, $data['slug'] ?? $data['name'], $vendor?->id),
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'description' => $data['description'] ?? null,
            'commission_rate' => $data['commission_rate'] ?? 0,
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
            'name' => $data['name'],
            'slug' => $this->uniqueSlug(Product::class, $data['slug'] ?? $data['name'], $product?->id),
            'sku' => $data['sku'],
            'short_description' => $data['short_description'] ?? null,
            'description' => $data['description'] ?? null,
            'base_price' => $data['base_price'],
            'sale_price' => $data['sale_price'] ?? null,
            'stock' => (int) ($data['stock'] ?? 0),
            'is_featured' => (bool) ($data['is_featured'] ?? false),
            'status' => (bool) ($data['status'] ?? false),
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
                ]
            );

            $existingIds[] = $variant->id;
        }

        $product->variants()->whereNotIn('id', $existingIds ?: [0])->delete();
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
