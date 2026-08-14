<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0fdf4; margin: 0; padding: 40px 0; }
        .wrap { max-width: 480px; margin: 0 auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #15803d, #22c55e); padding: 32px; text-align: center; }
        .header h1 { color: #fff; font-size: 22px; margin: 12px 0 0; font-weight: 900; letter-spacing: -0.5px; }
        .body { padding: 32px; }
        .body p { color: #475569; font-size: 14px; line-height: 1.7; margin: 0 0 16px; }
        .body strong { color: #14532d; }
        .otp-box { background: #f0fdf4; border: 2px dashed #22c55e; border-radius: 12px; padding: 24px; text-align: center; margin: 24px 0; }
        .otp-code { font-size: 40px; font-weight: 900; letter-spacing: 12px; color: #15803d; font-family: monospace; }
        .otp-note { font-size: 12px; color: #94a3b8; margin-top: 8px; }
        .otp-note strong { color: #16a34a; }
        .notice { background: #fefce8; border-left: 3px solid #eab308; border-radius: 8px; padding: 12px 16px; font-size: 13px; color: #713f12; margin-top: 8px; }
        .footer { background: #f8fafc; padding: 20px 32px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
        .footer strong { color: #16a34a; }
    </style>
</head>
<body>
    <div class="wrap">
        {{-- Header --}}
        <div class="header">
            <svg width="56" height="56" viewBox="0 0 200 220" fill="none">
                <path d="M48 160 L22 148 L38 158 L16 152 L35 164" fill="#15803d" stroke="#14532d" stroke-width="1"/>
                <circle cx="105" cy="145" r="90" fill="white" opacity="0.15" stroke="white" stroke-width="3"/>
                <ellipse cx="115" cy="185" rx="55" ry="38" fill="white" opacity="0.1"/>
                <path d="M95 58 Q85 20 105 8 Q118 22 112 58" fill="white" opacity="0.8" stroke="white" stroke-width="2.5"/>
                <path d="M108 55 Q100 18 118 10 Q128 26 120 56" fill="white" opacity="0.6" stroke="white" stroke-width="2"/>
                <path d="M52 118 L95 108 L88 128 Z" fill="white" opacity="0.5"/>
                <path d="M148 118 L108 108 L114 128 Z" fill="white" opacity="0.5"/>
                <circle cx="82" cy="135" r="20" fill="white"/>
                <circle cx="86" cy="138" r="12" fill="#14532d"/>
                <circle cx="91" cy="132" r="5" fill="white"/>
                <circle cx="128" cy="135" r="20" fill="white"/>
                <circle cx="132" cy="138" r="12" fill="#14532d"/>
                <circle cx="137" cy="132" r="5" fill="white"/>
                <path d="M85 158 Q105 148 130 158 L118 175 Q105 180 92 175 Z" fill="#f59e0b"/>
                <path d="M92 175 Q105 182 118 175 L112 190 Q105 195 98 190 Z" fill="#d97706"/>
            </svg>
            <h1>Mindigo</h1>
        </div>

        {{-- Body --}}
        <div class="body">
            <p>Xin chào,</p>

            @if(($type ?? 'login') === 'forgot_password')
                <p>Bạn vừa yêu cầu <strong>đặt lại mật khẩu</strong> cho tài khoản <strong>{{ $email }}</strong>. Sử dụng mã OTP bên dưới để tiếp tục:</p>
            @else
                <p>Bạn vừa yêu cầu <strong>đăng nhập</strong> vào Mindigo bằng Mindigo ID với tài khoản <strong>{{ $email }}</strong>. Sử dụng mã OTP bên dưới để tiếp tục:</p>
            @endif

            <div class="otp-box">
                <div class="otp-code">{{ $otp }}</div>
                <div class="otp-note">
                    Mã có hiệu lực trong
                    <strong>{{ ($type ?? 'login') === 'forgot_password' ? '10' : '15' }} phút</strong>
                </div>
            </div>

            @if(($type ?? 'login') === 'forgot_password')
                <div class="notice" style="display: flex; align-items: flex-start; gap: 10px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#92400e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0; margin-top: 1px;">
                        <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/>
                        <line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                    <span>Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này và kiểm tra lại bảo mật tài khoản.</span>
                </div>
            @else
                <div class="notice" style="display: flex; align-items: flex-start; gap: 10px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#92400e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0; margin-top: 1px;">
                        <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/>
                        <line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                    <span>Nếu bạn không yêu cầu đăng nhập, vui lòng bỏ qua email này và bảo mật tài khoản của bạn.</span>
                </div>
            @endif
        </div>

        {{-- Footer --}}
        <div class="footer">
            © {{ date('Y') }} <strong>Auronsoft</strong>. All rights reserved.
        </div>
    </div>
</body>
</html>
