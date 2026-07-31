<?php

namespace Mindigo\LearningTools\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Mindigo\LearningTools\Models\AdmissionProgram;

class AdmissionLookupController extends Controller
{
    public function index(Request $request): View
    {
        $programs = AdmissionProgram::with('university')->whereHas('university', fn (Builder $query) => $query->where('is_active', true))
            ->when($request->filled('keyword'), function (Builder $query) use ($request): void {
                $keyword = '%'.$request->string('keyword')->trim().'%';
                $query->where(fn (Builder $search) => $search->where('major_name', 'like', $keyword)->orWhere('major_code', 'like', $keyword)->orWhereHas('university', fn (Builder $university) => $university->where('name', 'like', $keyword)->orWhere('code', 'like', $keyword)));
            })
            ->when($request->filled('year'), fn (Builder $query) => $query->where('year', $request->integer('year')))
            ->when($request->filled('combination'), fn (Builder $query) => $query->whereJsonContains('combinations', $request->string('combination')->toString()))
            ->when($request->filled('province'), fn (Builder $query) => $query->whereHas('university', fn (Builder $university) => $university->where('province', $request->string('province'))))
            ->latest('year')->paginate(15)->withQueryString();

        return view('learning-tools::admissions.index', [
            'programs' => $programs,
            'years' => AdmissionProgram::distinct()->orderByDesc('year')->pluck('year'),
            'provinces' => AdmissionProgram::join('learning_universities', 'learning_universities.id', '=', 'learning_admission_programs.university_id')->whereNotNull('province')->distinct()->orderBy('province')->pluck('province'),
            'combinations' => config('learning-tools.score_combinations', []),
            'favoriteIds' => $request->user()->belongsToMany(AdmissionProgram::class, 'learning_admission_favorites', 'user_id', 'admission_program_id')->pluck('learning_admission_programs.id'),
        ]);
    }

    public function favorite(Request $request, AdmissionProgram $program): RedirectResponse
    {
        $request->user()->belongsToMany(AdmissionProgram::class, 'learning_admission_favorites', 'user_id', 'admission_program_id')->toggle($program->id);

        return back()->with('success', __('learning-tools::app.admissions.favorite_updated'));
    }
}
