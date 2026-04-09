<?php

namespace Modules\Careers\Controllers\Admin;

use App\Http\Controllers\Controller;
use Modules\Careers\Models\JobCategory;
use Modules\Careers\Requests\Admin\JobCategoryRequest;
use Modules\Careers\Services\CareersService;

class JobCategoryController extends Controller
{
    public function __construct(protected CareersService $careers)
    {
    }

    public function index()
    {
        return view('careers::admin.categories.index', [
            'categories' => JobCategory::query()
                ->withCount('jobs')
                ->ordered()
                ->get(),
        ]);
    }

    public function create()
    {
        return view('careers::admin.categories.form', [
            'category' => new JobCategory(['is_active' => true]),
            'isEdit' => false,
        ]);
    }

    public function store(JobCategoryRequest $request)
    {
        $this->careers->createCategory($request->validated());

        return redirect()
            ->route('admin.job-categories.index')
            ->with('success', 'Job category created successfully.');
    }

    public function edit(JobCategory $job_category)
    {
        return view('careers::admin.categories.form', [
            'category' => $job_category,
            'isEdit' => true,
        ]);
    }

    public function update(JobCategoryRequest $request, JobCategory $job_category)
    {
        $this->careers->updateCategory($job_category, $request->validated());

        return redirect()
            ->route('admin.job-categories.index')
            ->with('success', 'Job category updated successfully.');
    }

    public function destroy(JobCategory $job_category)
    {
        $job_category->delete();

        return redirect()
            ->route('admin.job-categories.index')
            ->with('success', 'Job category deleted successfully.');
    }
}
