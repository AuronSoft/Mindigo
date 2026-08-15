<?php

namespace Mindigo\ExamManagement\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Models\ExamMigrationRun;
use Mindigo\ExamManagement\Services\ExamCutoverService;
use Mindigo\ExamManagement\Services\ExamInventoryService;
use Mindigo\ExamManagement\Services\LegacyExamMigrationService;

class ExamCutoverController extends Controller
{
    public function __construct(
        private readonly ExamCutoverService $cutover,
        private readonly LegacyExamMigrationService $migration,
        private readonly ExamInventoryService $inventory,
    ) {}

    public function index(): View
    {
        return view('Mindigo-exam-management::cutover.index', [
            'mode' => $this->cutover->mode(),
            'betaTeacherIds' => $this->cutover->betaTeacherIds(),
            'teachers' => User::query()->where('role', 'teacher')->where('is_active', true)->orderBy('name')->get(['id', 'name', 'email']),
            'preview' => $this->migration->preview(),
            'comparison' => $this->migration->compare(),
            'inventory' => $this->inventory->collect(),
            'runs' => ExamMigrationRun::query()->with('initiator:id,name')->latest()->limit(15)->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'mode' => ['required', Rule::in(ExamCutoverService::MODES)],
            'beta_teacher_ids' => ['nullable', 'array'],
            'beta_teacher_ids.*' => ['integer', 'distinct', Rule::exists('users', 'id')->where('role', 'teacher')],
            'confirmation' => ['required', 'in:CUTOVER'],
        ]);
        $this->cutover->configure($data['mode'], $data['beta_teacher_ids'] ?? []);

        return back()->with('success', __('Mindigo-exam-management::app.cutover.updated'));
    }

    public function migrate(Request $request): RedirectResponse
    {
        $request->validate(['confirmation' => ['required', 'in:MIGRATE']]);
        abort_unless($this->cutover->mode() === ExamCutoverService::MODE_PARALLEL, 409);
        $run = $this->migration->migrate([], $request->user()->getAuthIdentifier());

        return back()->with($run->status === 'completed' ? 'success' : 'error', __('Mindigo-exam-management::app.cutover.migration_finished', ['status' => $run->status]));
    }
}
