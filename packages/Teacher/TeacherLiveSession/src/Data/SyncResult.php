<?php

namespace Mindigo\TeacherLiveSession\Data;

final readonly class SyncResult
{
    public function __construct(
        public string $status,
        public array $metadata = [],
        public ?string $error = null,
    ) {}
}
