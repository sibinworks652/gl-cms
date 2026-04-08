<?php

namespace Modules\Email\Controllers;

use App\Http\Controllers\Controller;
use Modules\Email\Models\EmailTemplate;
use Modules\Email\Requests\EmailTestRequest;
use Modules\Email\Requests\SmtpTestRequest;
use Modules\Email\Services\EmailService;
use Modules\Email\Services\TemplateService;

class EmailTestingController extends Controller
{
    public function __construct(
        protected EmailService $email,
        protected TemplateService $templates
    ) {
    }

    public function index()
    {
        return view('email::admin.testing', [
            'templates' => EmailTemplate::query()->active()->ordered()->get(),
            'settings' => $this->email->settings(),
        ]);
    }

    public function send(EmailTestRequest $request)
    {
        $validated = $request->validated();
        $template = EmailTemplate::query()->findOrFail($validated['template_id']);
        $payload = array_filter($validated['payload'] ?? [], fn ($value) => filled($value));
        $emails = $request->emails();

        try {
            $this->email->sendTest(
                $template,
                $emails,
                $payload ?: $this->templates->dummyData($template)
            );

            return redirect()
                ->route('admin.email.testing.index')
                ->with('email_test_status', [
                    'type' => 'success',
                    'message' => 'Test email sent successfully to ' . implode(', ', $emails) . '.',
                ]);
        } catch (\Throwable $exception) {
            return redirect()
                ->route('admin.email.testing.index')
                ->withInput()
                ->with('email_test_status', [
                    'type' => 'danger',
                    'message' => 'Test email failed: ' . $exception->getMessage(),
                ]);
        }
    }

    public function smtp(SmtpTestRequest $request)
    {
        return redirect()
            ->route('admin.email.testing.index')
            ->with('smtp_test_status', $this->email->testSavedSmtp());
    }

    public function preview(EmailTemplate $template)
    {
        return response($this->email->render($template, $this->templates->dummyData($template)));
    }
}
