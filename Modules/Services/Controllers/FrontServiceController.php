<?php

namespace Modules\Services\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Services\Services\ServiceManager;

class FrontServiceController extends Controller
{
    public function __construct(protected ServiceManager $manager)
    {
    }

    public function index(Request $request)
    {
        return view('services::frontend.index', [
            'services' => $this->manager->services($request->only('category')),
            'featuredServices' => $this->manager->featured(3),
            'categories' => $this->manager->categories(),
            'activeCategory' => $request->query('category'),
        ]);
    }

    public function show(string $slug)
    {
        $service = $this->manager->findActiveBySlug($slug);

        return view('services::frontend.show', [
            'service' => $service,
            'relatedServices' => $this->manager->services([
                'category' => $service->category?->slug,
            ], 4),
        ]);
    }
}
