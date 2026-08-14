<?php

namespace Mindigo\ExamManagement\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Mindigo\ExamManagement\Http\Requests\ExamTemplateRequest;
use Mindigo\ExamManagement\Models\ExamTemplate;
use Mindigo\ExamManagement\Services\ExamTemplateService;

class ExamTemplateController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly ExamTemplateService $templates) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ExamTemplate::class);

        return view('Mindigo-exam-management::templates.index', [
            'templates' => $this->templates->listFor($request->user())->paginate(15),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', ExamTemplate::class);

        return view('Mindigo-exam-management::templates.form', [
            'template' => new ExamTemplate,
            'questions' => $this->templates->availableQuestions($request->user()),
        ]);
    }

    public function store(ExamTemplateRequest $request): RedirectResponse
    {
        $template = $this->templates->create($request->user(), $request->validated());

        return redirect()->route('teacher.exam-templates.edit', $template)->with('success', __('Mindigo-exam-management::app.template_builder.created'));
    }

    public function edit(Request $request, ExamTemplate $template): View
    {
        $this->authorize('update', $template);
        $template->load(['versions' => fn ($query) => $query->where('version', $template->current_version), 'versions.sections.questions']);

        return view('Mindigo-exam-management::templates.form', [
            'template' => $template,
            'questions' => $this->templates->availableQuestions($request->user()),
        ]);
    }

    public function update(ExamTemplateRequest $request, ExamTemplate $template): RedirectResponse
    {
        $this->templates->update($template, $request->user(), $request->validated());

        return back()->with('success', __('Mindigo-exam-management::app.template_builder.updated'));
    }

    public function ready(Request $request, ExamTemplate $template): RedirectResponse
    {
        $this->authorize('update', $template);
        $this->templates->markReady($template);

        return back()->with('success', __('Mindigo-exam-management::app.template_builder.ready'));
    }
}
