<?php

return [
    'title' => 'Học trực tuyến',
    'subtitle' => 'Quản lý các buổi học trực tuyến của bạn',
    'create' => 'Tạo buổi học',
    'edit' => 'Sửa buổi học',
    'cancel' => 'Huỷ',
    'delete' => 'Xoá',
    'save' => 'Lưu buổi học',

    // Filter
    'filter_classroom_label' => 'Lớp học',
    'all_classrooms' => 'Tất cả lớp',
    'filter_submit' => 'Lọc',
    'clear_filter' => 'Xoá lọc',
    'filter_button' => 'Bộ lọc',
    'filter_active' => 'Đang lọc',
    'filter_title' => 'Lọc buổi học trực tuyến',
    'filter_desc' => 'Chọn lớp để thu gọn danh sách buổi học.',
    'filter_hint_title' => 'Gợi ý',
    'filter_hint_desc' => 'Bộ lọc được đặt trong drawer để khu vực dữ liệu chính luôn rộng và dễ nhìn.',

    // Empty
    'empty_title' => 'Chưa có buổi học trực tuyến',
    'empty_desc' => 'Tạo buổi học đầu tiên để bắt đầu giảng dạy trực tuyến với lớp của bạn.',

    // Table columns
    'col_number' => 'STT',
    'col_title' => 'Tiêu đề',
    'col_classroom' => 'Lớp',
    'col_schedule' => 'Thời gian',
    'col_status' => 'Trạng thái',
    'col_actions' => 'Thao tác',

    // Status
    'status_scheduled' => 'Đã lên lịch',
    'status_live' => 'Đang diễn ra',
    'status_ended' => 'Đã kết thúc',
    'status_cancelled' => 'Đã huỷ',

    // Actions
    'start' => 'Bắt đầu',
    'join_room' => 'Vào phòng',
    'end' => 'Kết thúc',
    'leave_room' => 'Rời phòng',

    // Form
    'section_basic_info' => 'Thông tin buổi học',
    'section_schedule' => 'Lớp học & thời gian',
    'field_title' => 'Tiêu đề',
    'field_desc' => 'Mô tả',
    'field_classroom' => 'Lớp học',
    'field_start' => 'Thời gian bắt đầu',
    'field_end' => 'Thời gian kết thúc (dự kiến)',
    'title_placeholder' => 'VD: Buổi ôn tập chương 3',
    'desc_placeholder' => 'Nội dung, ghi chú cho buổi học...',
    'classroom_select_placeholder' => '-- Chọn lớp --',
    'classroom_option' => ':name (:code) · :count học sinh',

    // Room
    'room_title' => 'Phòng học trực tuyến',
    'room_loading' => 'Đang kết nối phòng học...',

    // Messages
    'created_success' => 'Đã tạo buổi học trực tuyến.',
    'updated_success' => 'Đã cập nhật buổi học.',
    'deleted_success' => 'Đã xoá buổi học.',
    'ended_success' => 'Đã kết thúc buổi học.',
    'delete_confirm_title' => 'Xoá buổi học?',
    'delete_confirm_message' => 'Bạn có chắc muốn xoá buổi học này? Hành động không thể hoàn tác.',

    // Validation
    'validation' => [
        'title_required' => 'Vui lòng nhập tiêu đề buổi học.',
        'classroom_required' => 'Vui lòng chọn lớp học.',
        'classroom_exists' => 'Lớp học không hợp lệ.',
        'start_required' => 'Vui lòng chọn thời gian bắt đầu.',
        'end_after' => 'Thời gian kết thúc phải sau thời gian bắt đầu.',
    ],
];
