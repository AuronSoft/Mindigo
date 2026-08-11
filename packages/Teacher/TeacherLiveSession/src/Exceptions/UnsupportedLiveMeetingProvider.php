<?php

namespace Mindigo\TeacherLiveSession\Exceptions;

use DomainException;

final class UnsupportedLiveMeetingProvider extends DomainException
{
    public static function for(string $provider): self
    {
        return new self("Live meeting provider [{$provider}] is not registered.");
    }
}
