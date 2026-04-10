<?php

namespace Modules\Faq\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Faq\Services\FaqManager;
use Modules\Settings\Models\Setting;

class FaqFrontController extends Controller
{
    public function __construct(protected FaqManager $manager)
    {
    }

    public function index(Request $request)
    {
        return view('faq::web.index', [
            'faqs' => $this->manager->frontendFaqs($request->only(['category', 'search'])),
            'categories' => $this->manager->categories(),
            'siteName' => Setting::value('site_name', config('app.name')),
        ]);
    }
}
