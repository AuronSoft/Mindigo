<?php

return [
    'title' => 'Cấp quyền giáo viên',
    'description' => 'Cấp, tạm khóa hoặc thu hồi quyền giáo viên sau khi phỏng vấn đạt.',
    'action' => 'Thao tác cấp quyền',
    'note' => 'Ghi chú cấp quyền',
    'note_placeholder' => 'Bắt buộc khi tạm khóa hoặc thu hồi quyền giáo viên.',
    'save' => 'Lưu cấp quyền',
    'completed' => 'Đã cập nhật trạng thái cấp quyền giáo viên.',
    'provisioned_by' => 'Người cấp quyền',
    'provisioned_at' => 'Thời điểm cấp quyền',
    'missing_account' => 'Hồ sơ cần liên kết với tài khoản người dùng trước khi cấp quyền giáo viên.',
    'must_pass_interview' => 'Chỉ hồ sơ có kết quả phỏng vấn đạt mới được phê duyệt.',
    'invalid_suspend_state' => 'Chỉ quyền giáo viên đang hoạt động mới có thể tạm khóa.',
    'invalid_revoke_state' => 'Chỉ quyền giáo viên đang hoạt động hoặc đang tạm khóa mới có thể thu hồi.',
    'actions' => [
        'approve' => 'Phê duyệt và cấp quyền Teacher',
        'suspend' => 'Tạm khóa quyền Teacher',
        'revoke' => 'Thu hồi quyền Teacher',
    ],
    'statuses' => [
        'not_provisioned' => 'Chưa cấp quyền',
        'active' => 'Teacher đang hoạt động',
        'suspended' => 'Teacher bị tạm khóa',
        'revoked' => 'Teacher đã bị thu hồi',
    ],
    'notifications' => [
        'approved' => [
            'title' => 'Đã cấp quyền Teacher',
            'message' => 'Hồ sơ :code đã được duyệt và không gian giáo viên của bạn đã sẵn sàng.',
        ],
        'suspended' => [
            'title' => 'Quyền Teacher bị tạm khóa',
            'message' => 'Quyền Teacher của hồ sơ :code đã bị tạm khóa. :note',
        ],
        'revoked' => [
            'title' => 'Quyền Teacher bị thu hồi',
            'message' => 'Quyền Teacher của hồ sơ :code đã bị thu hồi. :note',
        ],
    ],
];
