<?php

namespace Mindigo\TeacherLiveSession\Data;

final readonly class ProviderHealth
{
    public function __construct(
        public bool $available,
        public ?string $message = null,
    ) {}
}
