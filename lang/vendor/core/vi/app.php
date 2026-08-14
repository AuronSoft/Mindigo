<?php

return [
    'hero' => [
        'q1_prompt'  => 'Vi khuẩn E.coli:',
        'q1_options' => [
            ['text' => 'Hiếu khí tuyệt đối', 'correct' => false],
            ['text' => 'Cầu khuẩn Gram âm', 'correct' => false],
            ['text' => 'Phản ứng indole âm tính', 'correct' => false],
            ['text' => 'Phản ứng Voges-Proskauer âm tính', 'correct' => true],
        ],
        'q2_prompt'  => 'Trình tự định lượng E.coli được sắp xếp theo thứ tự:',
        'q2_options' => [
            ['text' => 'Chuẩn bị môi trường', 'correct' => false],
            ['text' => 'Pha loãng theo dãy', 'correct' => true],
            ['text' => 'Đếm khuẩn lạc sau 24h', 'correct' => true],
        ],
    ],

    'personalization' => [
        'phone2' => [
            'q_preview' => 'Câu 1: Chọn từ phù hợp...',
        ],
    ],

    'mobile' => [
        'instruction' => 'Chọn đáp án đúng cho câu hỏi dưới đây...',
    ],

    'contact' => [
        'brand_ai'     => 'Tích hợp AI',
        'brand_madein' => 'Phát triển tại Việt Nam',
        'faq_items' => [
            [
                'q' => 'Auronsoft có miễn phí không?',
                'a' => 'Auronsoft có gói miễn phí với đầy đủ tính năng cơ bản. Bạn có thể nâng cấp lên gói Pro để trải nghiệm các tính năng nâng cao như tạo đề bằng AI, phòng thi ảo và quản lý lớp học không giới hạn.',
            ],
            [
                'q' => 'Tôi có thể tạo đề thi từ file Word/PDF không?',
                'a' => 'Có. Auronsoft hỗ trợ tải lên file Word (.docx) và PDF. AI sẽ tự động phân tích nội dung và tạo ra bộ câu hỏi trắc nghiệm chính xác trong vài phút.',
            ],
            [
                'q' => 'Auronsoft có hỗ trợ tổ chức thi trực tuyến không?',
                'a' => 'Có. Tính năng Phòng thi ảo cho phép bạn tổ chức kỳ thi với hàng trăm thí sinh cùng lúc, theo dõi tiến độ theo thời gian thực và xuất kết quả chi tiết.',
            ],
            [
                'q' => 'Làm sao để được hỗ trợ kỹ thuật?',
                'a' => 'Bạn có thể liên hệ qua hotline 1800 6868 (miễn phí), email support@mindigo.vn hoặc gửi tin nhắn trực tiếp qua form trên trang này.',
            ],
            [
                'q' => 'Auronsoft có phù hợp với trường đại học và doanh nghiệp không?',
                'a' => 'Hoàn toàn phù hợp. Auronsoft đang phục vụ hàng nghìn trường đại học, trung tâm đào tạo và doanh nghiệp trên cả nước.',
            ],
        ],
    ],

    'pricing' => [
        'comparison' => [
            'api_webhook' => 'API & Webhook',
            'white_label' => 'White-label',
            'sso_saml'    => 'SSO / SAML',
            'sla'         => 'SLA 99.9%',
            'limits'      => [
                'exams'     => '5/tháng',
                'questions' => '30 câu',
                'practice'  => '10/tháng',
            ],
            'support'     => [
                'email'       => 'Email',
                'slack_teams' => 'Slack/Teams riêng',
            ],
        ],
    ],
];
