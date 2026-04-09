<?php

namespace Modules\Careers\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Careers\Models\Job;
use Modules\Careers\Requests\Web\JobApplicationRequest;
use Modules\Careers\Services\CareersService;
use Modules\Settings\Models\Setting;

class CareerController extends Controller
{
    public function __construct(protected CareersService $careers)
    {
    }

    public function index(Request $request)
    {
        return view('careers::web.index', [
            'jobs' => $this->careers->frontendJobs($request->only(['category', 'location', 'job_type', 'search'])),
            'categories' => $this->careers->categories(true),
            'locations' => $this->careers->locations(),
            'jobTypes' => Job::jobTypes(),
            'featuredJobs' => $this->careers->featuredJobs(),
            'siteName' => Setting::value('site_name', config('app.name')),
        ]);
    }

    public function show(string $slug)
    {
        $job = $this->careers->findFrontendJob($slug);

        return view('careers::web.show', [
            'job' => $job,
            'siteName' => Setting::value('site_name', config('app.name')),
        ]);
    }

    public function apply(string $slug)
    {
        $job = $this->careers->findFrontendJob($slug);

        return view('careers::web.apply', [
            'job' => $job,
            'siteName' => Setting::value('site_name', config('app.name')),
        ]);
    }

    public function submit(JobApplicationRequest $request, string $slug)
    {
        $job = $this->careers->findFrontendJob($slug);

        $this->careers->submitApplication($job, $request->validated(), $request->file('resume'));

        return redirect()
            ->route('careers.apply.show', $job->slug)
            ->with('success', 'Your application has been submitted successfully.');
    }
}
