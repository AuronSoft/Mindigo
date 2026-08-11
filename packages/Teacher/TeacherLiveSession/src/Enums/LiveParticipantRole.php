<?php

namespace Mindigo\TeacherLiveSession\Enums;

enum LiveParticipantRole: string
{
    case Host = 'host';
    case CoHost = 'co_host';
    case Student = 'student';
    case Guest = 'guest';
    case Observer = 'observer';

    public function canModerate(): bool
    {
        return in_array($this, [self::Host, self::CoHost], true);
    }
}
