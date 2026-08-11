<?php

namespace Mindigo\TeacherLiveSession\Data;

final readonly class ProviderCapabilities
{
    public function __construct(
        public bool $embedded,
        public bool $guestLinks,
        public bool $attendanceSync,
        public bool $recording,
    ) {}
}
