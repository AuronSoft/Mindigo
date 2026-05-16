<?php

namespace Mindigo\Auth\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Mindigo\Auth\Mail\MindigoIdMagicLinkMail;
use Mindigo\Auth\Mail\MindigoIdOtpMail;
use Mindigo\Auth\Models\User;
use Mindigo\Auth\Models\MindigoIdToken;

class MindigoIdService
{
    private const EXPIRES_MINUTES = 15;

    public function findUser(string $email): ?User
    {
        return User::where('email', strtolower($email))->first();
    }

    public function clearOldTokens(string $email, string $type): void
    {
        MindigoIdToken::where('email', $email)
            ->where('type', $type)
            ->where('used', false)
            ->delete();
    }

    public function sendMagicLink(string $email): void
    {
        $plainToken = Str::random(64);

        MindigoIdToken::create([
            'email'      => $email,
            'token'      => hash('sha256', $plainToken),
            'type'       => 'magic_link',
            'expires_at' => now()->addMinutes(self::EXPIRES_MINUTES),
        ]);

        $link = URL::temporarySignedRoute(
            'Mindigo-id.verify',
            now()->addMinutes(self::EXPIRES_MINUTES),
            ['token' => $plainToken]
        );

        Mail::to($email)->send(new MindigoIdMagicLinkMail($link));
    }

    public function sendOtp(string $email): void
    {
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        MindigoIdToken::create([
            'email'      => $email,
            'token'      => Str::random(64),
            'otp'        => $otp,
            'type'       => 'otp',
            'expires_at' => now()->addMinutes(self::EXPIRES_MINUTES),
        ]);

        Mail::to($email)->send(new MindigoIdOtpMail($otp, $email, 'login'));
    }

    public function verifyMagicLinkToken(string $tokenStr): ?MindigoIdToken
    {
        $record = MindigoIdToken::where('token', hash('sha256', $tokenStr))
            ->where('type', 'magic_link')
            ->first();

        return ($record && $record->isValid()) ? $record : null;
    }

    public function verifyOtpToken(string $email, string $otp): ?MindigoIdToken
    {
        $record = MindigoIdToken::where('email', strtolower($email))
            ->where('type', 'otp')
            ->where('used', false)
            ->latest('created_at')
            ->first();

        if (!$record || !$record->isValid()) {
            return null;
        }

        return hash_equals($record->otp, $otp) ? $record : null;
    }

    public function loginUser(Request $request, User $user): void
    {
        Auth::login($user, remember: true);
        $request->session()->regenerate();
    }
}