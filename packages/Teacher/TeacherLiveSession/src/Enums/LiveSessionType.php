<?php

namespace Mindigo\TeacherLiveSession\Enums;

enum LiveSessionType: string
{
    case Regular = 'regular';
    case Makeup = 'makeup';
    case Flexible = 'flexible';
}
