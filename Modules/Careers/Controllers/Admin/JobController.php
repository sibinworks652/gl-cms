<?php

namespace Modules\Careers\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Careers\Models\Job;
use Modules\Careers\Requests\Admin\JobRequest;
use Modules\Careers\Services\CareersService;

class JobController extends Controller
{
    public function __construct(protected CareersService $careers)
    {
    }

    public function index(Request $request)
    {
        return view('careers::admin.jobs.index', [
            'jobs' => $this->careers->adminJobs($request->only(['category', 'status', 'job_type', 'search'])),
            'categories' => $this->careers->categories(),
            'jobTypes' => Job::jobTypes(),
            'statusOptions' => Job::statusOptions(),
        ]);
    }

    public function create()
    {
        return view('careers::admin.jobs.form', [
            'job' => new Job([
                'status' => 'active',
                'vacancies' => 1,
            ]),
            'categories' => $this->careers->categories(),
            'jobTypes' => Job::jobTypes(),
            'statusOptions' => Job::statusOptions(),
            'isEdit' => false,
        ]);
    }

    public function store(JobRequest $request)
    {
        $job = $this->careers->createJob($request->validated());

        return redirect()
            ->route('admin.jobs.edit', $job)
            ->with('success', 'Job created successfully.');
    }

    public function edit(Job $job)
    {
        return view('careers::admin.jobs.form', [
            'job' => $job,
            'categories' => $this->careers->categories(),
            'jobTypes' => Job::jobTypes(),
            'statusOptions' => Job::statusOptions(),
            'isEdit' => true,
        ]);
    }

    public function update(JobRequest $request, Job $job)
    {
        $this->careers->updateJob($job, $request->validated());

        return redirect()
            ->route('admin.jobs.edit', $job)
            ->with('success', 'Job updated successfully.');
    }

    public function destroy(Job $job)
    {
        $this->careers->deleteJob($job);

        return redirect()
            ->route('admin.jobs.index')
            ->with('success', 'Job deleted successfully.');
    }
}
