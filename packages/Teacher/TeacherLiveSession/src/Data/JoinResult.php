<?php

namespace Mindigo\TeacherLiveSession\Data;

final readonly class JoinResult
{
    public function __construct(
        public string $mode,
        public ?string $url = null,
        public ?string $token = null,
        public array $metadata = [],
    ) {}
}
