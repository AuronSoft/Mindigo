<?php

namespace Mindigo\TeacherLiveSession\Data;

final readonly class ProviderMeeting
{
    public function __construct(
        public string $roomName,
        public ?string $meetingId = null,
        public ?string $joinUrl = null,
        public ?string $hostUrl = null,
        public ?string $status = null,
        public array $metadata = [],
    ) {}
}
