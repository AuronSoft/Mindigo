<?php

return [
    'title' => 'Cap quyen giao vien',
    'description' => 'Cap, tam khoa hoac thu hoi quyen giao vien sau khi phong van dat.',
    'action' => 'Thao tac cap quyen',
    'note' => 'Ghi chu cap quyen',
    'note_placeholder' => 'Bat buoc khi tam khoa hoac thu hoi quyen giao vien.',
    'save' => 'Luu cap quyen',
    'completed' => 'Da cap nhat trang thai cap quyen giao vien.',
    'provisioned_by' => 'Nguoi cap quyen',
    'provisioned_at' => 'Thoi diem cap quyen',
    'missing_account' => 'Ho so can lien ket voi tai khoan nguoi dung truoc khi cap quyen giao vien.',
    'must_pass_interview' => 'Chi ho so co ket qua phong van dat moi duoc phe duyet.',
    'invalid_suspend_state' => 'Chi quyen giao vien dang hoat dong moi co the tam khoa.',
    'invalid_revoke_state' => 'Chi quyen giao vien dang hoat dong hoac dang tam khoa moi co the thu hoi.',
    'actions' => [
        'approve' => 'Phe duyet va cap quyen Teacher',
        'suspend' => 'Tam khoa quyen Teacher',
        'revoke' => 'Thu hoi quyen Teacher',
    ],
    'statuses' => [
        'not_provisioned' => 'Chua cap quyen',
        'active' => 'Teacher dang hoat dong',
        'suspended' => 'Teacher bi tam khoa',
        'revoked' => 'Teacher da bi thu hoi',
    ],
    'notifications' => [
        'approved' => [
            'title' => 'Da cap quyen Teacher',
            'message' => 'Ho so :code da duoc duyet va khong gian giao vien cua ban da san sang.',
        ],
        'suspended' => [
            'title' => 'Quyen Teacher bi tam khoa',
            'message' => 'Quyen Teacher cua ho so :code da bi tam khoa. :note',
        ],
        'revoked' => [
            'title' => 'Quyen Teacher bi thu hoi',
            'message' => 'Quyen Teacher cua ho so :code da bi thu hoi. :note',
        ],
    ],
];
