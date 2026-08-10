<?php

namespace Mindigo\AcademicCalendar\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Mindigo\AcademicCalendar\Models\AcademicCalendarException;
use Mindigo\Auth\Models\User;

class CalendarExceptionService
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        return AcademicCalendarException::query()
            ->with(['course:id,name', 'creator:id,name'])
            ->whereNull('classroom_id')
            ->when(($filters['scope'] ?? null) === 'global', fn (Builder $query) => $query->whereNull('course_id'))
            ->when(($filters['scope'] ?? null) === 'course', fn (Builder $query) => $query->whereNotNull('course_id'))
            ->when(filled($filters['course_id'] ?? null), fn (Builder $query) => $query->where('course_id', $filters['course_id']))
            ->when(filled($filters['from'] ?? null), fn (Builder $query) => $query->whereDate('exception_date', '>=', $filters['from']))
            ->when(filled($filters['to'] ?? null), fn (Builder $query) => $query->whereDate('exception_date', '<=', $filters['to']))
            ->orderBy('exception_date')
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function store(User $actor, array $data): AcademicCalendarException
    {
        return DB::transaction(function () use ($actor, $data): AcademicCalendarException {
            $courseId = $data['scope'] === 'course' ? (int) $data['course_id'] : null;

            $exception = AcademicCalendarException::query()
                ->whereNull('classroom_id')
                ->where('kind', AcademicCalendarException::KIND_NO_CLASS)
                ->where('exception_date', $data['exception_date'])
                ->when($courseId, fn (Builder $query) => $query->where('course_id', $courseId), fn (Builder $query) => $query->whereNull('course_id'))
                ->lockForUpdate()
                ->first() ?? new AcademicCalendarException;

            $exception->fill([
                'course_id' => $courseId,
                'classroom_id' => null,
                'created_by' => $actor->id,
                'exception_date' => $data['exception_date'],
                'kind' => AcademicCalendarException::KIND_NO_CLASS,
                'title' => $data['title'],
                'reason' => $data['reason'],
            ])->save();

            return $exception;
        });
    }

    public function delete(AcademicCalendarException $exception): void
    {
        abort_if($exception->classroom_id !== null, 404);
        $exception->delete();
    }
}
