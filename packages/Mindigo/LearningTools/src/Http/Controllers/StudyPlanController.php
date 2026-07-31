<?php

namespace Mindigo\LearningTools\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Mindigo\ClassroomManagement\Models\Classroom;
use Mindigo\LearningTools\Http\Requests\StudyPlanRequest;
use Mindigo\LearningTools\Http\Requests\StudyPlanTaskRequest;
use Mindigo\LearningTools\Models\StudyPlan;
use Mindigo\LearningTools\Models\StudyPlanTask;
use Mindigo\SubjectManagement\Models\Subject;

class StudyPlanController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = StudyPlan::with(['subject', 'classroom', 'creator'])->withCount('tasks');

        if ($user->role === 'student') {
            $query->where(function (Builder $builder) use ($user): void {
                $builder->where(fn (Builder $personal) => $personal->where('creator_id', $user->getAuthIdentifier())->whereNull('classroom_id'))
                    ->orWhereHas('classroom.students', fn (Builder $students) => $students->whereKey($user->getAuthIdentifier()));
            });
        } else {
            $query->where('creator_id', $user->getAuthIdentifier());
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return view('learning-tools::plans.index', ['plans' => $query->latest()->paginate(12)->withQueryString()]);
    }

    public function create(Request $request): View
    {
        return view('learning-tools::plans.form', [
            'plan' => new StudyPlan,
            'subjects' => $this->subjects(),
            'classrooms' => $this->classrooms($request),
        ]);
    }

    public function store(StudyPlanRequest $request): RedirectResponse
    {
        $this->validateClassroomScope($request);
        $plan = StudyPlan::create([...$request->validated(), 'creator_id' => $request->user()->getAuthIdentifier()]);

        return to_route('learning-tools.plans.show', $plan)->with('success', __('learning-tools::app.plans.created'));
    }

    public function show(Request $request, StudyPlan $plan): View
    {
        $this->authorizeView($request, $plan);
        $userId = $request->user()->getAuthIdentifier();
        $plan->load(['subject', 'classroom', 'creator', 'tasks.completedBy' => fn ($query) => $query->whereKey($userId)]);
        $completedCount = $plan->tasks->filter(fn (StudyPlanTask $task) => $task->completedBy->isNotEmpty())->count();

        return view('learning-tools::plans.show', compact('plan', 'completedCount'));
    }

    public function edit(Request $request, StudyPlan $plan): View
    {
        $this->authorizeOwner($request, $plan);

        return view('learning-tools::plans.form', [
            'plan' => $plan,
            'subjects' => $this->subjects(),
            'classrooms' => $this->classrooms($request),
        ]);
    }

    public function update(StudyPlanRequest $request, StudyPlan $plan): RedirectResponse
    {
        $this->authorizeOwner($request, $plan);
        $this->validateClassroomScope($request);
        $plan->update($request->validated());

        return to_route('learning-tools.plans.show', $plan)->with('success', __('learning-tools::app.plans.updated'));
    }

    public function destroy(Request $request, StudyPlan $plan): RedirectResponse
    {
        $this->authorizeOwner($request, $plan);
        $plan->delete();

        return to_route('learning-tools.plans.index')->with('success', __('learning-tools::app.plans.deleted'));
    }

    public function storeTask(StudyPlanTaskRequest $request, StudyPlan $plan): RedirectResponse
    {
        $this->authorizeOwner($request, $plan);
        $plan->tasks()->create([...$request->validated(), 'position' => ($plan->tasks()->max('position') ?? 0) + 1]);

        return back()->with('success', __('learning-tools::app.plans.task_created'));
    }

    public function destroyTask(Request $request, StudyPlan $plan, StudyPlanTask $task): RedirectResponse
    {
        $this->authorizeOwner($request, $plan);
        abort_unless((int) $task->study_plan_id === (int) $plan->id, 404);
        $task->delete();

        return back()->with('success', __('learning-tools::app.plans.task_deleted'));
    }

    public function toggleTask(Request $request, StudyPlan $plan, StudyPlanTask $task): RedirectResponse
    {
        $this->authorizeView($request, $plan);
        abort_unless((int) $task->study_plan_id === (int) $plan->id, 404);
        $task->completedBy()->toggle([
            $request->user()->getAuthIdentifier() => ['completed_at' => now()],
        ]);

        return back();
    }

    private function authorizeView(Request $request, StudyPlan $plan): void
    {
        $user = $request->user();
        $allowed = (int) $plan->creator_id === (int) $user->getAuthIdentifier()
            || ($user->role === 'student' && $plan->classroom_id && $plan->classroom?->students()->whereKey($user->getAuthIdentifier())->exists());
        abort_unless($allowed, 403);
    }

    private function authorizeOwner(Request $request, StudyPlan $plan): void
    {
        abort_unless($request->user()->role === 'admin' || (int) $plan->creator_id === (int) $request->user()->getAuthIdentifier(), 403);
    }

    private function validateClassroomScope(StudyPlanRequest $request): void
    {
        if (! $request->filled('classroom_id')) {
            return;
        }
        abort_unless(
            $request->user()->role === 'teacher'
            && Classroom::whereKey($request->integer('classroom_id'))->where('teacher_id', $request->user()->getAuthIdentifier())->exists(),
            403
        );
    }

    private function subjects()
    {
        return Subject::where('status', 'active')->orderBy('name')->get();
    }

    private function classrooms(Request $request)
    {
        return $request->user()->role === 'teacher'
            ? Classroom::where('teacher_id', $request->user()->getAuthIdentifier())->where('status', 'active')->orderBy('name')->get()
            : collect();
    }
}
