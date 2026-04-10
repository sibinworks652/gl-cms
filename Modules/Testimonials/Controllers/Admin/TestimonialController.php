<?php

namespace Modules\Testimonials\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\Testimonials\Models\Testimonial;
use Modules\Testimonials\Requests\TestimonialRequest;
use Modules\Testimonials\Services\TestimonialManager;

class TestimonialController extends Controller
{
    public function __construct(protected TestimonialManager $manager)
    {
    }

    public function index(Request $request)
    {
        $testimonials = Testimonial::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('name', 'like', '%' . $search . '%')
                        ->orWhere('company', 'like', '%' . $search . '%')
                        ->orWhere('designation', 'like', '%' . $search . '%')
                        ->orWhere('project_name', 'like', '%' . $search . '%');
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status') === 'active'))
            ->when($request->filled('featured'), function ($query) use ($request) {
                if ($request->input('featured') === 'featured') {
                    $query->where('is_featured', true);
                }

                if ($request->input('featured') === 'regular') {
                    $query->where('is_featured', false);
                }
            })
            ->ordered()
            ->paginate(15)
            ->withQueryString();

        return view('testimonials::admin.index', [
            'testimonials' => $testimonials,
        ]);
    }

    public function create()
    {
        return view('testimonials::admin.form', [
            'testimonial' => new Testimonial([
                'status' => true,
                'rating' => 5,
                'order' => (int) Testimonial::max('order') + 1,
            ]),
            'isEdit' => false,
        ]);
    }

    public function store(TestimonialRequest $request)
    {
        $this->manager->create($request->validated(), $request->file('image'));

        return redirect()
            ->route('admin.testimonials.index')
            ->with('success', 'Testimonial created successfully.');
    }

    public function edit(Testimonial $testimonial)
    {
        return view('testimonials::admin.form', [
            'testimonial' => $testimonial,
            'isEdit' => true,
        ]);
    }

    public function update(TestimonialRequest $request, Testimonial $testimonial)
    {
        $this->manager->update($testimonial, $request->validated(), $request->file('image'));

        return redirect()
            ->route('admin.testimonials.index')
            ->with('success', 'Testimonial updated successfully.');
    }

    public function destroy(Testimonial $testimonial)
    {
        $this->manager->delete($testimonial);

        return redirect()
            ->route('admin.testimonials.index')
            ->with('success', 'Testimonial deleted successfully.');
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', Rule::exists('testimonials', 'id')],
        ]);

        DB::transaction(function () use ($validated) {
            foreach (array_values($validated['order']) as $index => $id) {
                Testimonial::query()->whereKey($id)->update(['order' => $index]);
            }
        });

        return response()->json([
            'message' => 'Testimonial order saved.',
        ]);
    }
}
