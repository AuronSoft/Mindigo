<?php

return [
    'title' => 'Phân quyền',
    'breadcrumb' => 'Phân quyền',
    'heading' => 'Phân quyền hệ thống',
    'description' => 'Bảng quyền cố định cho ba vai trò của hệ thống. Cách này giữ kiểm soát truy cập gọn, rõ và ổn định.',
    'fixed_roles' => 'Vai trò cố định',
    'total_permissions' => ':count quyền',
    'matrix_label' => 'Ma trận truy cập',
    'matrix_title' => 'Quyền theo từng vai trò',
    'readonly' => 'Cấu hình trong code',
    'admin_locked' => 'Admin luôn có toàn quyền',
    'save' => 'Lưu phân quyền',
    'saved' => 'Đã lưu phân quyền.',
    'cancel' => 'Hủy',
    'confirm_title' => 'Lưu phân quyền',
    'confirm_message' => 'Các thay đổi quyền sẽ áp dụng ngay cho người dùng thuộc từng vai trò.',
    'permission' => 'Quyền',
    'allowed' => 'Được phép',
    'denied' => 'Không được phép',

    'roles' => [
        'admin' => 'Quản trị',
        'teacher' => 'Giáo viên',
        'student' => 'Học viên',
    ],

    'role_descriptions' => [
        'admin' => 'Toàn quyền quản trị hệ thống, cấu hình, nhật ký và tài khoản.',
        'teacher' => 'Tạo và quản lý đề thi, câu hỏi, báo cáo cho lớp học.',
        'student' => 'Làm bài thi được giao và xem kết quả học tập cá nhân.',
    ],

    'groups' => [
        'platform' => 'Nền tảng',
        'content' => 'Nội dung thi',
        'learner' => 'Luồng học viên',
        'support' => 'Hỗ trợ',
        'admin' => 'Quản trị',
    ],

    'permissions' => [
        'learning_tools' => [
            'view' => 'Truy cập công cụ học tập',
            'use' => 'Sử dụng công cụ học tập',
            'manage_resources' => 'Quản lý kho kiến thức',
        ],
        'dashboard' => [
            'view' => 'Xem dashboard',
        ],
        'reports' => [
            'view' => 'Xem báo cáo',
        ],
        'exams' => [
            'view' => 'Xem đề thi',
            'create' => 'Tạo đề thi',
            'update' => 'Cập nhật đề thi',
            'publish' => 'Xuất bản đề thi',
            'delete' => 'Xóa đề thi',
            'attempt' => 'Làm bài thi',
        ],
        'subjects' => [
            'view' => 'Xem môn học',
            'create' => 'Tạo môn học',
            'update' => 'Cập nhật môn học',
            'delete' => 'Xóa môn học',
        ],
        'classrooms' => [
            'view' => 'Xem lớp học',
            'create' => 'Tạo lớp học',
            'update' => 'Cập nhật lớp học',
            'manage_students' => 'Quản lý học sinh trong lớp',
            'delete' => 'Xóa lớp học',
        ],
        'questions' => [
            'view' => 'Xem câu hỏi',
            'create' => 'Tạo câu hỏi',
            'update' => 'Cập nhật câu hỏi',
            'review' => 'Duyệt câu hỏi',
            'delete' => 'Xóa câu hỏi',
        ],
        'results' => [
            'view' => 'Xem kết quả',
        ],
        'support_tickets' => [
            'view' => 'Xem yêu cầu hỗ trợ',
            'create' => 'Tạo yêu cầu hỗ trợ',
            'reply' => 'Phản hồi yêu cầu hỗ trợ',
            'manage' => 'Xử lý yêu cầu hỗ trợ',
            'delete' => 'Xóa yêu cầu hỗ trợ',
        ],
        'users' => [
            'view' => 'Xem tài khoản',
            'create' => 'Tạo tài khoản',
            'update' => 'Cập nhật tài khoản',
            'delete' => 'Xóa tài khoản',
            'restore' => 'Khôi phục tài khoản',
        ],
        'system_settings' => [
            'view' => 'Xem cấu hình hệ thống',
            'update' => 'Cập nhật cấu hình hệ thống',
        ],
        'audit_logs' => [
            'view' => 'Xem nhật ký thao tác',
        ],
        'role_permissions' => [
            'view' => 'Xem phân quyền',
        ],
    ],
];
