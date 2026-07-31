<?php

namespace Mindigo\LearningTools\Services;

use Illuminate\Validation\ValidationException;

class GpaCalculatorService
{
    public function calculate(array $courses): array
    {
        $courses = collect($courses)->filter(fn (array $course) => filled($course['name'] ?? null) && filled($course['credits'] ?? null))->values();
        if ($courses->isEmpty()) {
            throw ValidationException::withMessages(['courses' => __('learning-tools::app.gpa.no_courses')]);
        }
        $scale = config('learning-tools.gpa.grade_scale', []);
        $normalized = $courses->map(function (array $course, int $index) use ($scale): array {
            $components = collect($course['components'] ?? [])->filter(fn (array $component) => filled($component['name'] ?? null) && isset($component['score'], $component['weight']))->values();
            if ($components->isNotEmpty()) {
                $weight = $components->sum(fn (array $component) => (float) $component['weight']);
                if (abs($weight - 100) > 0.01) {
                    throw ValidationException::withMessages(['courses.'.($index + 1) => __('learning-tools::app.gpa.invalid_weight', ['course' => $course['name'], 'weight' => $weight])]);
                }
                $course['score'] = round($components->sum(fn (array $component) => (float) $component['score'] * (float) $component['weight']) / 100, 2);
                $course['components'] = $components->all();
            }
            if (! isset($course['score'])) {
                throw ValidationException::withMessages(['courses.'.($index + 1) => __('learning-tools::app.gpa.missing_score', ['course' => $course['name']])]);
            }
            $grade = collect($scale)->first(fn (array $level) => (float) $course['score'] >= $level['min']);

            return [...$course, 'credits' => (int) $course['credits'], 'score' => (float) $course['score'], 'letter' => $grade['letter'], 'points' => $grade['points']];
        });
        $credits = $normalized->sum('credits');
        $averageTen = $normalized->sum(fn (array $course) => $course['score'] * $course['credits']) / $credits;
        $gpaFour = $normalized->sum(fn (array $course) => $course['points'] * $course['credits']) / $credits;
        $classification = collect(config('learning-tools.gpa.classifications', []))->search(fn (float $minimum) => $gpaFour >= $minimum);
        $classification = $classification === false ? 'insufficient' : $classification;

        return ['courses' => $normalized->all(), 'total_credits' => $credits, 'average_ten' => round($averageTen, 2), 'gpa_four' => round($gpaFour, 2), 'classification' => $classification];
    }
}
