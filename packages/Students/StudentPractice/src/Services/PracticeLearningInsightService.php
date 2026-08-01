<?php

namespace Mindigo\StudentPractice\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Mindigo\Auth\Models\User;
use Mindigo\StudentPractice\Models\PracticeLearningInsight;

class PracticeLearningInsightService
{
    public const VERSION = 'analytics_v1';

    public function synchronize(User $student, array $analytics): Collection
    {
        $definitions = $this->definitions($analytics);

        DB::transaction(function () use ($student, $analytics, $definitions): void {
            User::query()->whereKey($student->getAuthIdentifier())->lockForUpdate()->firstOrFail();

            foreach (['strength', 'weakness', 'trend'] as $type) {
                PracticeLearningInsight::query()->where('student_id', $student->getAuthIdentifier())
                    ->where('status', PracticeLearningInsight::STATUS_ACTIVE)
                    ->where('fingerprint', 'like', $type.':'.$analytics['scope'].':%')
                    ->update(['status' => PracticeLearningInsight::STATUS_SUPERSEDED]);
            }

            foreach ($definitions as $definition) {
                PracticeLearningInsight::query()->updateOrCreate(
                    ['student_id' => $student->getAuthIdentifier(), 'fingerprint' => $definition['fingerprint']],
                    [
                        ...$definition,
                        'engine_version' => self::VERSION,
                        'status' => PracticeLearningInsight::STATUS_ACTIVE,
                        'period_start' => $analytics['period']['start'],
                        'period_end' => $analytics['period']['end'],
                        'generated_at' => now(),
                    ]
                );
            }
        });

        return PracticeLearningInsight::query()->with(['skill.subject:id,name'])
            ->where('student_id', $student->getAuthIdentifier())
            ->where('status', PracticeLearningInsight::STATUS_ACTIVE)
            ->where('fingerprint', 'like', '%:'.$analytics['scope'].':%')
            ->orderByDesc('priority')->get();
    }

    private function definitions(array $analytics): array
    {
        $definitions = [];
        if ($analytics['strength'] !== null) {
            $definitions[] = $this->skillDefinition('strength', 'strong_skill', 60, $analytics['strength'], $analytics['scope']);
        }
        if ($analytics['weakness'] !== null) {
            $definitions[] = $this->skillDefinition('weakness', 'weak_skill', 100, $analytics['weakness'], $analytics['scope']);
        }
        if ($analytics['overview']['attempts'] > 0) {
            $trend = $analytics['improvement'];
            $definitions[] = [
                'practice_skill_id' => null,
                'fingerprint' => 'trend:'.$analytics['scope'].':'.$trend['direction'],
                'type' => 'trend',
                'insight_code' => 'trend_'.$trend['direction'],
                'priority' => $trend['direction'] === 'declining' ? 90 : 50,
                'metrics' => ['change' => $trend['change'], 'current' => $trend['current'], 'previous' => $trend['previous'], 'scope' => $analytics['scope']],
            ];
        }

        return $definitions;
    }

    private function skillDefinition(string $type, string $code, int $priority, array $skill, string $scope): array
    {
        return [
            'practice_skill_id' => $skill['id'],
            'fingerprint' => $type.':'.$scope.':'.$skill['id'],
            'type' => $type,
            'insight_code' => $code,
            'priority' => $priority,
            'metrics' => [
                'mastery_score' => $skill['mastery_score'],
                'accuracy' => $skill['accuracy'],
                'attempts' => $skill['attempts'],
                'scope' => $scope,
            ],
        ];
    }
}
