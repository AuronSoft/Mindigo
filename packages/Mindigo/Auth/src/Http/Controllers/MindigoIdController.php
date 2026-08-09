<?php

namespace Mindigo\Auth\Http\Controllers;

use App\Support\RoleRedirector;
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
        $type = $request->type;

        if (! $this->service->findUser($email)) {
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

        if (! $record) {
            return redirect()->route('login')
                ->withErrors(['Mindigo_id' => __('Mindigo-auth::app.auth.magic_link_invalid')]);
        }

        $user = $this->service->findUser($record->email);

        if (! $user) {
            return redirect()->route('login')
                ->withErrors(['Mindigo_id' => __('Mindigo-auth::app.auth.account_not_found')]);
        }

        $record->update(['used' => true]);
        $this->service->loginUser($request, $user);

        if ($user->role === 'admin') {
            return redirect()->intended('/dashboard');
        }

        return RoleRedirector::redirectAfterLoginFor($user);
    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $email = strtolower($request->email);
        $record = $this->service->verifyOtpToken($email, $request->otp);

        if (! $record) {
            return response()->json([
                'message' => __('Mindigo-auth::app.auth.otp_invalid_expired'),
            ], 422);
        }

        $user = $this->service->findUser($email);

        if (! $user) {
            return response()->json([
                'message' => __('Mindigo-auth::app.auth.account_not_found'),
            ], 422);
        }

        $record->update(['used' => true]);
        $this->service->loginUser($request, $user);

        $redirect = $user->role === 'admin'
            ? redirect()->intended('/dashboard')->getTargetUrl()
            : RoleRedirector::redirectAfterLoginFor($user)->getTargetUrl();

        return response()->json([
            'ok' => true,
            'redirect' => $redirect,
        ]);
    }
}
