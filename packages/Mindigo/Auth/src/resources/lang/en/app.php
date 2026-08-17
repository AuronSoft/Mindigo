<?php

return [

    'brand' => [
        'name' => 'MindigoID',
        'copyright' => '© '.date('Y').' Auronsoft. All rights reserved.',
    ],

    'auth' => [
        'login_title' => 'Sign in',
        'login_subtitle' => 'to continue to :platform',
        'platform' => 'Mindigo LMS',

        'email' => 'Email',
        'email_placeholder' => 'student@example.com',
        'email_invalid' => 'Please enter a valid email address.',

        'password' => 'Password',
        'password_placeholder' => 'Enter your password',
        'forgot_password' => 'Forgot password?',

        'remember_me' => 'Keep me signed in',
        'login_button' => 'Sign in',

        'continue' => 'Continue',
        'try_other_method' => 'Try another method',
        'confirm' => 'Confirm',
        'back' => 'Back',

        'login_with_identity' => 'Sign in with Mindigo ID',

        'magic_link_sent' => 'A sign-in link has been sent to',
        'magic_link_check' => 'Please check your email to continue.',
        'resend' => 'Resend',
        'resend_otp' => 'Resend OTP',

        'otp_title' => 'Enter the OTP sent to your email',
        'otp_invalid' => 'Invalid OTP code.',
        'otp_not_received' => 'Didn’t receive the OTP?',

        'incognito_notice' => 'If this is not your device, sign in using',
        'incognito_highlight' => 'private browsing mode',
        'incognito_suffix' => 'to protect your account.',

        'magic_link_invalid' => 'This link is invalid or has expired.',
        'account_not_found' => 'Account not found.',
        'otp_invalid_expired' => 'Invalid or expired OTP code.',
        'account_suspended' => 'Your account has been suspended. Please contact the administrator.',
        'no_permission' => 'You do not have permission to access this area.',
    ],

    'sso' => [
        'divider' => 'Or continue with SSO',

        'google' => 'Sign in with Google',
        'microsoft' => 'Sign in with Microsoft',
        'apple' => 'Sign in with Apple',
        'saml' => 'Sign in with SAML',
    ],

    'support' => [
        'need_help' => 'Need help?',
        'contact_admin' => 'Contact administrator',
    ],

    'dashboard' => [
        'employees_active' => 'Active users',
        'today_up' => '↑ +12 today',

        'system_status' => 'System status',
        'system_online' => 'Running smoothly',

        'today_approval' => 'Today submissions',
        'approved' => '✓ 24 approved',
        'pending' => '⏳ 6 pending',

        'salary_this_month' => 'Progress this month',
        'salary_processed' => '↑ Completed',

        'recruitment' => 'Question bank',
        'candidates' => '12 tests',
        'offers' => '3 sets',

        'attendance_today' => 'Today activity',
        'attendance_ontime' => '● On time',

        'training' => 'Practice courses',
        'courses' => '8 courses',
        'training_running' => 'In progress',
    ],

    'onboarding' => [
        'label' => 'Introducing the Mindigo platform',
        'navigation' => 'Onboarding navigation',
        'go_to' => 'Go to slide :slide',
        'schedule' => [
            'eyebrow' => 'Smart scheduling',
            'title' => 'Plan classes and exams with confidence',
            'description' => 'Keep classes, exams and reminders in one clear schedule that stays up to date for everyone.',
            'card_title' => 'Today’s schedule',
            'card_meta' => 'Monday, the 18th',
            'note_one' => 'English class 11A',
            'note_one_meta' => '08:00 · Online classroom',
            'note_two' => 'Upcoming assessment',
            'note_two_meta' => '2 days left to prepare',
        ],
        'classroom' => [
            'eyebrow' => 'Connected classroom',
            'title' => 'Teach and learn in one shared space',
            'description' => 'Resources, assignments, discussions and feedback stay connected so every class runs smoothly.',
            'card_title' => 'My classroom',
            'card_meta' => '24 active learners',
            'note_one' => 'Assignment published',
            'note_one_meta' => 'Due today at 20:00',
            'note_two' => 'New discussion',
            'note_two_meta' => '8 classroom replies',
        ],
        'progress' => [
            'eyebrow' => 'Actionable progress',
            'title' => 'Turn learning data into better outcomes',
            'description' => 'Track results in real time, identify skills that need attention and support every learner at the right moment.',
            'card_title' => 'This week’s progress',
            'card_meta' => 'Updated in real time',
            'note_one' => 'Goals completed',
            'note_one_meta' => '12 of 14 activities',
            'note_two' => 'Positive momentum',
            'note_two_meta' => 'Up 8% from last week',
        ],
    ],

    'hero' => [
        'title_line_1' => 'Modern',
        'title_line_2' => 'exam practice',
        'title_highlight' => 'platform',

        'description' => 'Smart testing system with automatic grading and learning progress tracking.',

        'businesses' => 'Users',
        'uptime' => 'Uptime',
        'employees' => 'Students',
    ],

    'footer' => [
        'protected_by' => 'Protected by',
        'terms' => 'Terms',
        'privacy' => 'Privacy',
        'help' => 'Help',
    ],

    'language' => [
        'vi' => 'Tiếng Việt',
        'en' => 'English',
    ],

    'title' => 'Forgot Password',

    'steps' => [

        'email' => [
            'title' => 'Forgot password?',
            'description' => 'Enter your email to receive a password reset OTP.',
            'send_otp' => 'Send OTP',
            'email_required' => 'Please enter your email address.',
            'otp_sent' => 'The OTP has been sent to your email.',
        ],

        'otp' => [
            'title' => 'Enter OTP',
            'description' => 'We sent a 6-digit code to your email',
            'not_received' => 'Didn’t receive the code?',
            'resend' => 'Resend',
            'confirm' => 'Confirm',
            'otp_required' => 'Please enter all 6 digits.',
            'otp_invalid' => 'Invalid OTP code.',
            'otp_success' => 'OTP verified successfully.',
        ],

        'reset' => [
            'title' => 'Reset Password',
            'description' => 'Set a new password to continue.',

            'new_password' => 'New Password',
            'new_password_placeholder' => 'Minimum 8 characters',

            'confirm_password' => 'Confirm Password',
            'confirm_password_placeholder' => 'Re-enter password',

            'submit' => 'Reset Password',

            'password_min' => 'Password must be at least 8 characters.',
            'password_not_match' => 'Password confirmation does not match.',

            'session_expired' => 'Your session has expired. Please try again.',
            'reset_success' => 'Password reset successfully. Redirecting to login...',
        ],

    ],

    'navigation' => [
        'back_login' => 'Back to login',
        'back' => 'Back',
    ],

    'toast' => [
        'login_success' => 'Login successful! Welcome back.',
        'logout_success' => 'Logged out successfully. See you again!',
        'logging_out' => 'Logging out...',
    ],

    'confirm' => [
        'logout' => [
            'title' => 'Logout',
            'message' => 'Are you sure you want to logout?',
            'confirm_text' => 'Logout',
            'cancel_text' => 'Cancel',
        ],
    ],

    'validation' => [
        'email' => [
            'required' => 'Please enter your email address.',
            'email' => 'Please enter a valid email address.',
            'exists' => 'No account found with this email.',
        ],
        'password' => [
            'required' => 'Please enter your password.',
            'min' => 'Password must be at least :min characters.',
            'confirmed' => 'Password confirmation does not match.',
        ],
        'otp' => [
            'required' => 'Please enter the OTP code.',
            'size' => 'OTP code must be :size digits.',
        ],
        'type' => [
            'in' => 'Invalid verification type.',
        ],
    ],

    'mail' => [
        'magic_link_subject' => 'Your MindigoID Sign-in Link',
        'otp_login_subject' => 'MindigoID Login OTP — MindigoHRM',
        'otp_forgot_password_subject' => 'Password Reset OTP — MindigoHRM',
    ],
];
