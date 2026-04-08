<?php

namespace Modules\Email\Controllers;

use App\Http\Controllers\Controller;
use Modules\Email\Requests\EmailSettingsRequest;
use Modules\Email\Services\EmailService;

class EmailSettingsController extends Controller
{
    public function __construct(protected EmailService $email)
    {
    }

    public function edit()
    {
        return view('email::admin.settings', [
            'settings' => $this->email->settings(),
        ]);
    }

    public function update(EmailSettingsRequest $request)
    {
        $this->email->saveSettings($request->validated(), $request->file('email_logo'));

        return redirect()
            ->route('admin.email.settings.edit')
            ->with('success', 'Email settings updated successfully.');
    }
}
