<?php

namespace Mindigo\TeacherLiveSession\Data;

use Carbon\CarbonInterface;

final readonly class SessionContext
{
    public function __construct(
        public int $classroomId,
        public int $teacherId,
        public string $title,
        public ?string $description,
        public CarbonInterface $scheduledStart,
        public ?CarbonInterface $scheduledEnd,
        public string $idempotencyKey,
    ) {}
}
