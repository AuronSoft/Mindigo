<?php

namespace Mindigo\TeacherLiveSession\Enums;

enum LiveSessionProvider: string
{
    case Native = 'native';
    case GoogleMeet = 'google_meet';
    case Zoom = 'zoom';
    /** Read-only compatibility for records created before Phase 1. */
    case LegacyJitsi = 'jitsi';

    public function isExternal(): bool
    {
        return $this !== self::Native;
    }
}
