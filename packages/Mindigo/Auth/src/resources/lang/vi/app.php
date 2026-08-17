<?php

return [

    'brand' => [
        'name' => 'MindigoID',
        'copyright' => '© '.date('Y').' Auronsoft. All rights reserved.',
    ],

    'auth' => [
        'login_title' => 'Đăng nhập',
        'login_subtitle' => 'để tiếp tục với :platform',
        'platform' => 'Mindigo LMS',

        'email' => 'Email',
        'email_placeholder' => 'student@example.com',
        'email_invalid' => 'Vui lòng nhập email hợp lệ.',

        'password' => 'Mật khẩu',
        'password_placeholder' => 'Nhập mật khẩu của bạn',
        'forgot_password' => 'Quên mật khẩu?',

        'remember_me' => 'Ghi nhớ đăng nhập',
        'login_button' => 'Đăng nhập',

        'continue' => 'Tiếp tục',
        'try_other_method' => 'Thử phương thức khác',
        'confirm' => 'Xác nhận',
        'back' => 'Quay lại',

        'login_with_identity' => 'Đăng nhập bằng Mindigo ID',

        'magic_link_sent' => 'Mindigo ID đã gửi một liên kết đến',
        'magic_link_check' => 'Vui lòng kiểm tra email để đăng nhập.',
        'resend' => 'Gửi lại',
        'resend_otp' => 'Gửi lại OTP',

        'otp_title' => 'Nhập mã OTP đã được gửi đến email',
        'otp_invalid' => 'Mã OTP không chính xác.',
        'otp_not_received' => 'Không nhận được mã OTP?',

        'incognito_notice' => 'Nếu đây không phải thiết bị của bạn, hãy đăng nhập bằng',
        'incognito_highlight' => 'chế độ ẩn danh',
        'incognito_suffix' => 'để bảo vệ tài khoản.',

        'magic_link_invalid' => 'Liên kết không hợp lệ hoặc đã hết hạn.',
        'account_not_found' => 'Tài khoản không tồn tại.',
        'otp_invalid_expired' => 'Mã OTP không hợp lệ hoặc đã hết hạn.',
        'account_suspended' => 'Tài khoản của bạn đã bị đình chỉ. Vui lòng liên hệ quản trị viên.',
        'no_permission' => 'Bạn không có quyền truy cập khu vực này.',

    ],

    'sso' => [
        'divider' => 'Hoặc đăng nhập bằng SSO',

        'google' => 'Đăng nhập bằng Google',
        'microsoft' => 'Đăng nhập bằng Microsoft',
        'apple' => 'Đăng nhập bằng Apple',
        'saml' => 'Đăng nhập bằng SAML',
    ],

    'support' => [
        'need_help' => 'Cần hỗ trợ?',
        'contact_admin' => 'Liên hệ quản trị viên',
    ],

    'dashboard' => [
        'employees_active' => 'Thí sinh đang hoạt động',
        'today_up' => '↑ +12 hôm nay',

        'system_status' => 'Trạng thái hệ thống',
        'system_online' => 'Hoạt động ổn định',

        'today_approval' => 'Bài làm hôm nay',
        'approved' => '✓ 24 đã nộp',
        'pending' => '⏳ 6 đang làm',

        'salary_this_month' => 'Tiến độ học tháng này',
        'salary_processed' => '↑ Đã hoàn thành',

        'recruitment' => 'Ngân hàng đề thi',
        'candidates' => '12 đề thi',
        'offers' => '3 bộ đề',

        'attendance_today' => 'Lượt thi hôm nay',
        'attendance_ontime' => '● Đúng giờ',

        'training' => 'Khóa luyện thi',
        'courses' => '8 khóa',
        'training_running' => 'Đang diễn ra',
    ],

    'onboarding' => [
        'label' => 'Giới thiệu nền tảng Mindigo',
        'navigation' => 'Điều hướng nội dung giới thiệu',
        'go_to' => 'Chuyển đến nội dung :slide',
        'schedule' => [
            'eyebrow' => 'Lịch trình thông minh',
            'title' => 'Sắp xếp lịch học và kỳ thi dễ dàng',
            'description' => 'Quản lý lớp học, lịch thi và nhắc việc trong một lịch trình thống nhất, rõ ràng và luôn được cập nhật.',
            'card_title' => 'Lịch học hôm nay',
            'card_meta' => 'Thứ Hai, ngày 18',
            'note_one' => 'Lớp Tiếng Anh 11A',
            'note_one_meta' => '08:00 · Phòng trực tuyến',
            'note_two' => 'Bài kiểm tra sắp tới',
            'note_two_meta' => 'Còn 2 ngày để chuẩn bị',
        ],
        'classroom' => [
            'eyebrow' => 'Lớp học kết nối',
            'title' => 'Dạy và học trong cùng một không gian',
            'description' => 'Tài liệu, bài tập, thảo luận và phản hồi được kết nối xuyên suốt để mỗi lớp học vận hành hiệu quả hơn.',
            'card_title' => 'Lớp học của tôi',
            'card_meta' => '24 học viên đang hoạt động',
            'note_one' => 'Bài tập đã được giao',
            'note_one_meta' => 'Hạn nộp vào 20:00',
            'note_two' => 'Thảo luận mới',
            'note_two_meta' => '8 phản hồi từ lớp học',
        ],
        'progress' => [
            'eyebrow' => 'Tiến độ trực quan',
            'title' => 'Biến dữ liệu học tập thành hành động',
            'description' => 'Theo dõi kết quả theo thời gian thực, nhận diện kỹ năng cần cải thiện và đưa ra hỗ trợ đúng lúc.',
            'card_title' => 'Tiến độ tuần này',
            'card_meta' => 'Cập nhật theo thời gian thực',
            'note_one' => 'Mục tiêu đã hoàn thành',
            'note_one_meta' => '12 trong 14 hoạt động',
            'note_two' => 'Tiến bộ tích cực',
            'note_two_meta' => 'Tăng 8% so với tuần trước',
        ],
    ],

    'hero' => [
        'title_line_1' => 'Nền tảng',
        'title_line_2' => 'ôn thi trắc nghiệm',
        'title_highlight' => 'hiện đại',

        'description' => 'Hệ thống luyện thi thông minh, chấm điểm tự động và theo dõi tiến độ học tập hiệu quả.',

        'businesses' => 'Người dùng',
        'uptime' => 'Uptime',
        'employees' => 'Học viên',
    ],

    'footer' => [
        'protected_by' => 'Được bảo vệ bởi',
        'terms' => 'Điều khoản',
        'privacy' => 'Quyền riêng tư',
        'help' => 'Trợ giúp',
    ],

    'language' => [
        'vi' => 'Tiếng Việt',
        'en' => 'English',
    ],

    'title' => 'Quên mật khẩu',

    'steps' => [

        'email' => [
            'title' => 'Quên mật khẩu?',
            'description' => 'Nhập email để nhận mã OTP đặt lại mật khẩu.',
            'send_otp' => 'Gửi mã OTP',
            'email_required' => 'Vui lòng nhập email.',
            'otp_sent' => 'Mã OTP đã được gửi đến email của bạn.',
        ],

        'otp' => [
            'title' => 'Nhập mã OTP',
            'description' => 'Chúng tôi đã gửi mã 6 số đến email của bạn',
            'not_received' => 'Không nhận được mã?',
            'resend' => 'Gửi lại',
            'confirm' => 'Xác nhận',
            'otp_required' => 'Vui lòng nhập đủ 6 số.',
            'otp_invalid' => 'Mã OTP không hợp lệ.',
            'otp_success' => 'Xác nhận OTP thành công.',
        ],

        'reset' => [
            'title' => 'Đặt lại mật khẩu',
            'description' => 'Nhập mật khẩu mới để tiếp tục sử dụng hệ thống.',

            'new_password' => 'Mật khẩu mới',
            'new_password_placeholder' => 'Tối thiểu 8 ký tự',

            'confirm_password' => 'Xác nhận mật khẩu',
            'confirm_password_placeholder' => 'Nhập lại mật khẩu',

            'submit' => 'Đặt lại mật khẩu',

            'password_min' => 'Mật khẩu tối thiểu 8 ký tự.',
            'password_not_match' => 'Mật khẩu xác nhận không khớp.',

            'session_expired' => 'Phiên xác thực đã hết hạn. Vui lòng thử lại.',
            'reset_success' => 'Đặt lại mật khẩu thành công. Đang chuyển tới trang đăng nhập...',
        ],

    ],

    'navigation' => [
        'back_login' => 'Quay lại đăng nhập',
        'back' => 'Quay lại',
    ],

    'toast' => [
        'login_success' => 'Đăng nhập thành công! Chào mừng bạn.',
        'logout_success' => 'Đăng xuất thành công.',
        'logging_out' => 'Đang đăng xuất...',
    ],

    'confirm' => [
        'logout' => [
            'title' => 'Đăng xuất',
            'message' => 'Bạn có chắc chắn muốn đăng xuất khỏi hệ thống không?',
            'confirm_text' => 'Đăng xuất',
            'cancel_text' => 'Hủy',
        ],
    ],

    'validation' => [
        'email' => [
            'required' => 'Vui lòng nhập email.',
            'email' => 'Email không hợp lệ.',
            'exists' => 'Không tìm thấy tài khoản với email này.',
        ],
        'password' => [
            'required' => 'Vui lòng nhập mật khẩu.',
            'min' => 'Mật khẩu tối thiểu :min ký tự.',
            'confirmed' => 'Mật khẩu xác nhận không khớp.',
        ],
        'otp' => [
            'required' => 'Vui lòng nhập mã OTP.',
            'size' => 'Mã OTP gồm :size chữ số.',
        ],
        'type' => [
            'in' => 'Loại xác thực không hợp lệ.',
        ],
    ],

    'mail' => [
        'magic_link_subject' => 'Liên kết đăng nhập MindigoID',
        'otp_login_subject' => 'Mã OTP đăng nhập MindigoID — MindigoHRM',
        'otp_forgot_password_subject' => 'Mã OTP đặt lại mật khẩu — MindigoHRM',
    ],
];
