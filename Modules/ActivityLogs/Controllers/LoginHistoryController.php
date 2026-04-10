<?php

namespace Modules\ActivityLogs\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\ActivityLogs\Services\ActivityLogManager;

class LoginHistoryController extends Controller
{
    public function __construct(protected ActivityLogManager $manager)
    {
    }

    public function index(Request $request)
    {
        abort_unless(
            $request->user('admin')?->can('login-histories.view') || $request->user('admin')?->can('login-histories.view-own'),
            403
        );

        return view('activity-logs::login-histories', [
            'histories' => $this->manager->paginateLoginHistories($request->user('admin')),
        ]);
    }
}
