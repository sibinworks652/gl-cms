<?php

namespace Modules\Testimonials\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Settings\Models\Setting;
use Modules\Testimonials\Services\TestimonialManager;

class TestimonialFrontController extends Controller
{
    public function __construct(protected TestimonialManager $manager)
    {
    }

    public function index(Request $request)
    {
        return view('testimonials::web.index', [
            'testimonials' => $this->manager->frontendTestimonials($request->only('featured')),
            'featuredTestimonials' => $this->manager->featured(),
            'siteName' => Setting::value('site_name', config('app.name')),
        ]);
    }

    public function show(string $slug)
    {
        $testimonial = $this->manager->findActiveBySlug($slug);

        return view('testimonials::web.show', [
            'testimonial' => $testimonial,
            'relatedTestimonials' => $this->manager->related($testimonial),
            'siteName' => Setting::value('site_name', config('app.name')),
        ]);
    }
}
