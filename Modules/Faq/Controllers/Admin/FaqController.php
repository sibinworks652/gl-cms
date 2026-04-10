<?php

namespace Modules\Faq\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\Faq\Models\Faq;
use Modules\Faq\Models\FaqCategory;
use Modules\Faq\Requests\FaqRequest;
use Modules\Faq\Services\FaqManager;

class FaqController extends Controller
{
    public function __construct(protected FaqManager $manager)
    {
    }

    public function index(Request $request)
    {
        $faqs = Faq::query()
            ->with('category')
            ->when($request->filled('category'), fn ($query) => $query->where('faq_category_id', $request->integer('category')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status') === 'active'))
            ->ordered()
            ->paginate(15)
            ->withQueryString();

        return view('faq::admin.index', [
            'faqs' => $faqs,
            'categories' => FaqCategory::query()->ordered()->get(),
        ]);
    }

    public function create()
    {
        return view('faq::admin.form', [
            'faq' => new Faq([
                'status' => true,
                'order' => (int) Faq::max('order') + 1,
            ]),
            'categories' => FaqCategory::query()->ordered()->get(),
            'isEdit' => false,
        ]);
    }

    public function store(FaqRequest $request)
    {
        $this->manager->createFaq($request->validated());

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ created successfully.');
    }

    public function edit(Faq $faq)
    {
        return view('faq::admin.form', [
            'faq' => $faq,
            'categories' => FaqCategory::query()->ordered()->get(),
            'isEdit' => true,
        ]);
    }

    public function update(FaqRequest $request, Faq $faq)
    {
        $this->manager->updateFaq($faq, $request->validated());

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ updated successfully.');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ deleted successfully.');
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', Rule::exists('faqs', 'id')],
        ]);

        DB::transaction(function () use ($validated) {
            foreach (array_values($validated['order']) as $index => $id) {
                Faq::query()->whereKey($id)->update(['order' => $index]);
            }
        });

        return response()->json(['message' => 'FAQ order saved.']);
    }
}
