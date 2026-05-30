<?php

namespace Mindigo\Auth\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mindigo\Auth\Http\Requests\MindigoIdRequest;
use Mindigo\Auth\Http\Requests\VerifyOtpRequest;
use Mindigo\Auth\Services\MindigoIdService;

class MindigoIdController extends Controller
{
    public function __construct(
        private readonly MindigoIdService $service
    ) {}

    public function send(MindigoIdRequest $request): JsonResponse
    {
        $email = strtolower($request->email);
        $type  = $request->type;

        // Silent fail — tránh user enumeration
        if (!$this->service->findUser($email)) {
            return response()->json(['ok' => true]);
        }

        $this->service->clearOldTokens($email, $type);

        if ($type === 'magic_link') {
            $this->service->sendMagicLink($email);
        } else {
            $this->service->sendOtp($email);
        }

        return response()->json(['ok' => true]);
    }

    public function verifyMagicLink(Request $request): RedirectResponse
    {
        $record = $this->service->verifyMagicLinkToken(
            $request->query('token', '')
        );

        if (!$record) {
            return redirect()->route('login')
                ->withErrors(['Mindigo_id' => 'Liên kết không hợp lệ hoặc đã hết hạn.']);
        }

        $user = $this->service->findUser($record->email);

        if (!$user) {
            return redirect()->route('login')
                ->withErrors(['Mindigo_id' => 'Tài khoản không tồn tại.']);
        }

        $record->update(['used' => true]);
        $this->service->loginUser($request, $user);

        if ($user->role === 'admin') {
            return redirect()->intended('/dashboard');
        }

        session()->forget('url.intended');

        return redirect(match ($user->role) {
            'teacher' => '/teacher',
            default   => '/',
        });
    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $email  = strtolower($request->email);
        $record = $this->service->verifyOtpToken($email, $request->otp);

        if (!$record) {
            return response()->json([
                'message' => 'Mã OTP không hợp lệ hoặc đã hết hạn.',
            ], 422);
        }

        $user = $this->service->findUser($email);

        if (!$user) {
            return response()->json(['message' => 'Tài khoản không tồn tại.'], 422);
        }

        $record->update(['used' => true]);
        $this->service->loginUser($request, $user);

        return response()->json([
            'ok'       => true,
            'redirect' => redirect()->intended(match ($user->role) { 'teacher' => '/teacher', 'student' => '/', default => '/dashboard' })->getTargetUrl(),
        ]);
    }
}