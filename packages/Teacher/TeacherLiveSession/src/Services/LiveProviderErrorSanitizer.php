<?php

namespace Mindigo\TeacherLiveSession\Services;

use Illuminate\Support\Str;
use Throwable;

final class LiveProviderErrorSanitizer
{
    public function from(Throwable|string $error): string
    {
        $message = $error instanceof Throwable ? class_basename($error).': '.$error->getMessage() : $error;
        $message = preg_replace('/(bearer\s+)[a-z0-9._~+\/-]+/i', '$1[REDACTED]', $message) ?? $message;
        $message = preg_replace('/([?&](?:access_token|refresh_token|client_secret|code|signature)=)[^&\s]+/i', '$1[REDACTED]', $message) ?? $message;
        $message = preg_replace('/("?(?:access_token|refresh_token|client_secret|authorization|password)"?\s*[:=]\s*)[^,\s}]+/i', '$1[REDACTED]', $message) ?? $message;

        return Str::limit($message, 1000);
    }
}
