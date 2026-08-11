<?php

namespace Mindigo\TeacherLiveSession\Enums;

enum LiveSessionStatus: string
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Waiting = 'waiting';
    case Live = 'live';
    case Ended = 'ended';
    case Cancelled = 'cancelled';
    case Failed = 'failed';

    public function isTerminal(): bool
    {
        return in_array($this, [self::Ended, self::Cancelled, self::Failed], true);
    }
}
