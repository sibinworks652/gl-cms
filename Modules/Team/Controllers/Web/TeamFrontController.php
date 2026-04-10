<?php

namespace Modules\Team\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Settings\Models\Setting;
use Modules\Team\Services\TeamManager;

class TeamFrontController extends Controller
{
    public function __construct(protected TeamManager $manager)
    {
    }

    public function index(Request $request)
    {
        return view('team::web.index', [
            'members' => $this->manager->frontendMembers($request->only('department')),
            'departments' => $this->manager->departments(),
            'featuredMembers' => $this->manager->featured(),
            'siteName' => Setting::value('site_name', config('app.name')),
        ]);
    }

    public function show(string $slug)
    {
        $member = $this->manager->findActiveBySlug($slug);

        return view('team::web.show', [
            'member' => $member,
            'relatedMembers' => $this->manager->related($member),
            'siteName' => Setting::value('site_name', config('app.name')),
        ]);
    }
}
