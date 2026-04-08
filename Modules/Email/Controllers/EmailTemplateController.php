<?php

namespace Modules\Email\Controllers;

use App\Http\Controllers\Controller;
use Modules\Email\Models\EmailTemplate;
use Modules\Email\Requests\BuilderImageRequest;
use Modules\Email\Requests\EmailTemplateRequest;
use Modules\Email\Services\EmailService;
use Modules\Email\Services\TemplateService;

class EmailTemplateController extends Controller
{
    public function __construct(
        protected TemplateService $templates,
        protected EmailService $email
    ) {
    }

    public function index()
    {
        return view('email::admin.templates.index', [
            'templates' => EmailTemplate::query()->ordered()->paginate(15),
        ]);
    }

    public function create()
    {
        return view('email::admin.templates.form', [
            'template' => new EmailTemplate([
                'status' => true,
                'variables' => [],
                'use_header' => true,
                'use_footer' => true,
                'use_signature' => true,
            ]),
            'isEdit' => false,
            'starterVariables' => ['name', 'email', 'message', 'invoice_number', 'date', 'amount', 'status', 'button_url'],
        ]);
    }

    public function store(EmailTemplateRequest $request)
    {
        $template = $this->templates->create($request->validated());

        return redirect()
            ->route('admin.email.templates.edit', $template)
            ->with('success', 'Email template created successfully.');
    }

    public function edit(EmailTemplate $template)
    {
        return view('email::admin.templates.form', [
            'template' => $template,
            'isEdit' => true,
            'starterVariables' => ['name', 'email', 'message', 'invoice_number', 'date', 'amount', 'status', 'button_url'],
        ]);
    }

    public function update(EmailTemplateRequest $request, EmailTemplate $template)
    {
        $this->templates->update($template, $request->validated());

        return redirect()
            ->route('admin.email.templates.edit', $template)
            ->with('success', 'Email template updated successfully.');
    }

    public function destroy(EmailTemplate $template)
    {
        $template->delete();

        return redirect()
            ->route('admin.email.templates.index')
            ->with('success', 'Email template deleted successfully.');
    }

    public function preview(EmailTemplate $template)
    {
        return response($this->email->render($template, $this->templates->dummyData($template)));
    }

    public function uploadImage(BuilderImageRequest $request)
    {
        return response()->json($this->email->uploadBuilderImage($request->file('image')), 201);
    }
}
