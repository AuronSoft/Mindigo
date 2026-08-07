<?php

namespace Mindigo\Auth\Http\Controllers;

use Illuminate\Routing\Controller;
use Mindigo\Auth\Http\Requests\ResetPasswordRequest;
use Mindigo\Auth\Http\Requests\SendOtpRequest;
use Mindigo\Auth\Http\Requests\VerifyOtpRequest;
use Mindigo\Auth\Services\ForgotPasswordService;

class ForgotPasswordController extends Controller
{
    public function __construct(
        private readonly ForgotPasswordService $service
    ) {}

    public function index()
    {
        return view('Mindigo-auth::forgot-password');
    }

    public function sendOtp(SendOtpRequest $request)
    {
        $this->service->sendOtp($request->validated());

        return response()->json([
            'message' => __('Mindigo-auth::app.steps.email.otp_sent'),
        ]);
    }

    public function verifyOtp(VerifyOtpRequest $request)
    {
        $result = $this->service->verifyOtp($request->validated());

        if (! $result) {
            return response()->json([
                'message' => __('Mindigo-auth::app.steps.otp.otp_invalid'),
            ], 422);
        }

        return response()->json([
            'message' => __('Mindigo-auth::app.steps.otp.otp_success'),
        ]);
    }

    public function reset(ResetPasswordRequest $request)
    {
        $result = $this->service->resetPassword($request->validated());

        if (! $result) {
            return response()->json([
                'message' => __('Mindigo-auth::app.steps.reset.session_expired'),
            ], 422);
        }

        return response()->json([
            'message' => __('Mindigo-auth::app.steps.reset.reset_success'),
        ]);
    }
}
