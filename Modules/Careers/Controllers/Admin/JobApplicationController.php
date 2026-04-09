<?php

namespace Modules\Careers\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Careers\Models\Job;
use Modules\Careers\Models\JobApplication;
use Modules\Careers\Requests\Admin\UpdateApplicationStatusRequest;
use Modules\Careers\Services\CareersService;

class JobApplicationController extends Controller
{
    public function __construct(protected CareersService $careers)
    {
    }

    public function index(Request $request)
    {
        return view('careers::admin.applications.index', [
            'applications' => $this->careers->applications($request->only(['job', 'status', 'search'])),
            'jobs' => Job::query()->orderBy('title')->get(['id', 'title']),
            'statusOptions' => JobApplication::statusOptions(),
        ]);
    }

    public function show(JobApplication $application)
    {
        return view('careers::admin.applications.show', [
            'application' => $application->load('job.category'),
            'statusOptions' => JobApplication::statusOptions(),
        ]);
    }

    public function updateStatus(UpdateApplicationStatusRequest $request, JobApplication $application)
    {
        $this->careers->updateApplicationStatus($application, $request->validated()['status']);

        return redirect()
            ->back()
            ->with('success', 'Application status updated successfully.');
    }

    public function downloadResume(JobApplication $application)
    {
        abort_unless($application->resume_path, 404);

        return response()->download(
            $this->careers->resumeDownloadPath($application),
            $application->resume_original_name ?: basename($application->resume_path)
        );
    }
}
