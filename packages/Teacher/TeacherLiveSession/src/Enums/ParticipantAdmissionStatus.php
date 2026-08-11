<?php

namespace Mindigo\TeacherLiveSession\Enums;

enum ParticipantAdmissionStatus: string
{
    case Waiting = 'waiting';
    case Admitted = 'admitted';
    case Denied = 'denied';
    case Removed = 'removed';
}
