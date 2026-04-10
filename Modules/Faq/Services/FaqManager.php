<?php

namespace Modules\Faq\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Faq\Models\Faq;
use Modules\Faq\Models\FaqCategory;
use Illuminate\Support\Str;

class FaqManager
{
    public function categories(bool $activeOnly = true): Collection
    {
        return FaqCategory::query()
            ->when($activeOnly, fn ($query) => $query->active())
            ->withCount(['faqs' => fn ($query) => $query->active()])
            ->ordered()
            ->get();
    }

    public function frontendFaqs(array $filters = [], int $perPage = 50): LengthAwarePaginator
    {
        return Faq::query()
            ->with('category')
            ->active()
            ->when($filters['category'] ?? null, function ($query, string $categorySlug) {
                $query->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('slug', $categorySlug));
            })
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('question', 'like', '%' . $search . '%')
                        ->orWhere('answer', 'like', '%' . $search . '%');
                });
            })
            ->ordered()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function createFaq(array $data): Faq
    {
        return Faq::create($this->faqPayload($data));
    }

    public function updateFaq(Faq $faq, array $data): Faq
    {
        $faq->update($this->faqPayload($data, $faq));

        return $faq->fresh('category');
    }

    public function createCategory(array $data): FaqCategory
    {
        return FaqCategory::create($this->categoryPayload($data));
    }

    public function updateCategory(FaqCategory $category, array $data): FaqCategory
    {
        $category->update($this->categoryPayload($data, $category));

        return $category->fresh();
    }

    protected function faqPayload(array $data, ?Faq $faq = null): array
    {
        return [
            'faq_category_id' => $data['faq_category_id'] ?? null,
            'question' => $data['question'],
            'answer' => $data['answer'],
            'order' => $data['order'] ?? $faq?->order ?? ((int) Faq::max('order') + 1),
            'status' => (bool) ($data['status'] ?? false),
        ];
    }

    protected function categoryPayload(array $data, ?FaqCategory $category = null): array
    {
        return [
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['slug'] ?? $data['name'], $category?->id),
            'order' => $data['order'] ?? $category?->order ?? ((int) FaqCategory::max('order') + 1),
            'status' => (bool) ($data['status'] ?? false),
        ];
    }

    protected function uniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source) ?: Str::random(8);
        $slug = $base;
        $counter = 2;

        while (FaqCategory::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }
}
