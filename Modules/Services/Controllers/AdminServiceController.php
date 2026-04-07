<?php

namespace Modules\Services\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\Services\Models\Service;
use Modules\Services\Models\ServiceCategory;
use Modules\Services\Requests\ServiceRequest;
use Modules\Services\Services\ServiceManager;

class AdminServiceController extends Controller
{
    public function __construct(protected ServiceManager $manager)
    {
    }

    public function index(Request $request)
    {
        $services = Service::query()
            ->with('category')
            ->when($request->filled('category'), fn ($query) => $query->where('service_category_id', $request->integer('category')))
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->input('status') === 'active'))
            ->ordered()
            ->paginate(15)
            ->withQueryString();

        return view('services::admin.index', [
            'services' => $services,
            'categories' => ServiceCategory::query()->ordered()->get(),
        ]);
    }

    public function create()
    {
        return view('services::admin.form', [
            'service' => new Service(['is_active' => true, 'sort_order' => (int) Service::max('sort_order') + 1]),
            'categories' => ServiceCategory::query()->ordered()->get(),
            'isEdit' => false,
        ]);
    }

    public function store(ServiceRequest $request)
    {
        $this->manager->create($request->validated(), $request->file('image'));

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'Service created successfully.');
    }

    public function edit(Service $service)
    {
        return view('services::admin.form', [
            'service' => $service,
            'categories' => ServiceCategory::query()->ordered()->get(),
            'isEdit' => true,
        ]);
    }

    public function update(ServiceRequest $request, Service $service)
    {
        $this->manager->update($service, $request->validated(), $request->file('image'));

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'Service updated successfully.');
    }

    public function destroy(Service $service)
    {
        $this->manager->delete($service);

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'Service deleted successfully.');
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', Rule::exists('services', 'id')],
        ]);

        DB::transaction(function () use ($validated) {
            foreach (array_values($validated['order']) as $index => $id) {
                Service::query()->whereKey($id)->update(['sort_order' => $index]);
            }
        });

        return response()->json([
            'message' => 'Service order saved.',
        ]);
    }
}
