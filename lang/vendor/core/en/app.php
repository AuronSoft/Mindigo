<?php

return [
    'hero' => [
        'q1_prompt'  => 'The bacterium E.coli:',
        'q1_options' => [
            ['text' => 'Absolutely aerobic', 'correct' => false],
            ['text' => 'Gram-negative cocc...', 'correct' => false],
            ['text' => 'Negative indole test', 'correct' => false],
            ['text' => 'Negative Vosges-Proskauer', 'correct' => true],
        ],
        'q2_prompt'  => 'The steps for quantifying E.coli are arranged in order:',
        'q2_options' => [
            ['text' => 'Prepare the medium', 'correct' => false],
            ['text' => 'Serial dilution method', 'correct' => true],
            ['text' => 'Count colonies after 24h', 'correct' => true],
        ],
    ],

    'contact' => [
        'faq_items' => [
            [
                'q' => 'Is Mindigo free to use?',
                'a' => 'Mindigo offers a free plan with all essential features. You can upgrade to Pro for advanced capabilities like AI exam generation, virtual exam rooms, and unlimited classroom management.',
            ],
            [
                'q' => 'Can I create exams from Word or PDF files?',
                'a' => 'Yes. Mindigo supports Word (.docx) and PDF uploads. AI will analyse the content and generate accurate multiple-choice questions in just a few minutes.',
            ],
            [
                'q' => 'Does Mindigo support online exams?',
                'a' => 'Yes. The Virtual Exam Room lets you host exams for hundreds of participants at once, monitor progress in real time, and export detailed results.',
            ],
            [
                'q' => 'How can I get technical support?',
                'a' => 'You can contact us via the toll-free hotline 1800 6868, email support@mindigo.vn, or send a message directly through the form on this page.',
            ],
            [
                'q' => 'Is Mindigo suitable for universities and businesses?',
                'a' => 'Absolutely. Mindigo already supports thousands of universities, training centres, and businesses across the country.',
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
                'exams'     => '5/month',
                'questions' => '30 questions',
                'practice'  => '10/month',
            ],
            'support'     => [
                'email'       => 'Email',
                'slack_teams' => 'Dedicated Slack/Teams',
            ],
        ],
    ],
];
