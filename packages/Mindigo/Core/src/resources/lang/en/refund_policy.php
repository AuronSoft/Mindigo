<?php

return [
    'hero' => [
        'badge' => 'Legal Center',
        'title' => 'Refund Policy',
        'description' => 'This policy defines the conditions, scope, process, and handling timelines for refunds related to service plans, licenses, paid features, and payment transactions on Auronsoft.',
    ],
    'meta' => [
        'effective_date' => 'Effective date: July 1, 2026',
        'version' => 'Version: 1.0',
        'scope' => 'Applies to individual users, teachers, organizations, and paid transactions on Auronsoft',
    ],
    'toc_title' => 'Table of Contents',
    'intro' => [
        'Auronsoft wants users to clearly understand refund rights, limits, and processes before subscribing to or renewing services.',
        'This policy applies unless a contract, quotation, order form, or separate written agreement between Auronsoft and your organization states otherwise.',
    ],
    'sections' => [
        [
            'id' => 'scope',
            'title' => '1. Scope',
            'paragraphs' => [
                'This Refund Policy applies to valid payments made directly to Auronsoft or through payment gateways designated by Auronsoft.',
            ],
            'items' => [
                'Individual plans, teacher plans, organizational plans, or platform licenses.',
                'Paid features, add-ons, storage, AI quotas, or expanded services if Auronsoft states that they are refundable.',
                'It does not apply to transactions made through third parties if those parties have their own refund policies, unless Auronsoft states otherwise.',
            ],
        ],
        [
            'id' => 'eligible',
            'title' => '2. Refund-eligible Cases',
            'paragraphs' => [
                'Auronsoft may consider a full or partial refund in the cases below, depending on usage status, plan type, and transaction evidence.',
            ],
            'items' => [
                'You accidentally paid multiple times for the same service plan in the same usage period.',
                'Your account was charged but the service was not activated due to a verified system or payment gateway error.',
                'You request a refund within 7 days from the first payment date of a new plan and have not substantially used paid features.',
                'Auronsoft cannot provide the core service as described and cannot fix the issue within a reasonable time after receiving a support request.',
            ],
        ],
        [
            'id' => 'not-eligible',
            'title' => '3. Non-refundable Cases',
            'paragraphs' => [
                'Some cases are not eligible for refund to maintain fairness, prevent abuse, and preserve service quality.',
            ],
            'items' => [
                'You have substantially used the service, created or exported many exams, used AI quotas, downloaded data, operated classrooms, or received the main benefits of the plan.',
                'The refund request is submitted after the published refund period, unless applicable law or a separate agreement provides otherwise.',
                'The account is suspended or terminated due to Terms of Use violations, fraud, abuse, system attacks, or infringement of third-party rights.',
                'You no longer need the service, forgot to cancel renewal, selected the wrong plan, or did not review the plan description while the service was provided correctly.',
                'Implementation, consulting, customization, training, integration, data migration, or completed professional services under a separate agreement.',
            ],
        ],
        [
            'id' => 'trial',
            'title' => '4. Trials, Promotions, and Discount Codes',
            'paragraphs' => [
                'Free trials, promotional codes, limited-time offers, or sponsored plans may have separate conditions. When a trial ends, renewal or conversion to a paid plan follows the information displayed at registration.',
            ],
            'items' => [
                'Discounted or free value is generally not convertible to cash.',
                'If a transaction using a discount code is refunded, the maximum refund is the actual amount paid.',
                'Special organizational offers may be non-refundable if the contract or quotation clearly states so.',
            ],
        ],
        [
            'id' => 'subscriptions',
            'title' => '5. Renewals and Cancellations',
            'paragraphs' => [
                'If a service plan renews automatically, users or organizations are responsible for checking billing cycles, notification emails, and account settings before renewal.',
            ],
            'items' => [
                'Cancellation prevents future renewals but does not automatically refund time already paid for.',
                'Some plans may remain usable until the end of the paid period after renewal is canceled.',
                'If you believe the system renewed incorrectly, please submit a request with transaction evidence for Auronsoft to review.',
            ],
        ],
        [
            'id' => 'organization',
            'title' => '6. Organizational Plans and Separate Agreements',
            'paragraphs' => [
                'For schools, centers, businesses, or organizations purchasing multiple accounts, refund terms will prioritize the signed contract, quotation, order form, or service agreement.',
            ],
            'items' => [
                'Setup, training, integration, data migration, or customization fees are generally non-refundable once Auronsoft has performed the work.',
                'Reducing account quantity, changing plans, or switching billing periods may be handled by credit adjustment or service credit if both parties agree.',
                'Organizational refund requests must be submitted by an authorized representative or verified administrator account.',
            ],
        ],
        [
            'id' => 'request',
            'title' => '7. How to Request a Refund',
            'paragraphs' => [
                'To request a refund, please submit complete information through Auronsoft’s official support channel. Missing information may delay processing.',
            ],
            'items' => [
                'Auronsoft account email, organization name if any, and the requester’s role.',
                'Transaction ID, payment date, amount, payment method, and invoice or receipt if available.',
                'Reason for refund, issue description, screenshots, or relevant evidence.',
                'Contact information so Auronsoft can verify and respond with the result.',
            ],
        ],
        [
            'id' => 'processing',
            'title' => '8. Processing Time and Method',
            'paragraphs' => [
                'Auronsoft usually responds to refund requests within 5 business days after receiving all required information. The time for funds to arrive may depend on banks, e-wallets, payment gateways, or card issuers.',
            ],
            'cards' => [
                ['title' => 'Request verification', 'body' => 'Auronsoft reviews the account, usage history, transaction, plan activation status, and refund conditions.'],
                ['title' => 'Processing result', 'body' => 'If approved, refunds may be returned to the original payment method or handled as service credit where appropriate.'],
                ['title' => 'Receiving funds', 'body' => 'After Auronsoft confirms the refund, funds usually arrive within 5 to 15 business days depending on the payment provider.'],
                ['title' => 'Transaction fees', 'body' => 'Bank fees, payment gateway fees, or exchange-rate differences may not be refunded if the third party does not return them to Auronsoft.'],
            ],
        ],
        [
            'id' => 'chargebacks',
            'title' => '9. Payment Disputes and Fraud',
            'paragraphs' => [
                'If you identify an unauthorized transaction, please contact Auronsoft promptly for review. Opening a dispute through a bank or payment gateway may temporarily restrict account access until the matter is clarified.',
            ],
            'items' => [
                'Auronsoft may deny a refund if fraud, policy abuse, continued service use after a refund request, or inaccurate information is detected.',
                'Accounts related to chargebacks, fraud, or prolonged disputes may have access to paid features temporarily suspended.',
            ],
        ],
        [
            'id' => 'tax',
            'title' => '10. Invoices, Tax, and Records',
            'paragraphs' => [
                'If an invoice has been issued, refund or fee adjustment may need to follow applicable invoice, tax, and accounting rules. Users or organizations are responsible for cooperating and providing necessary information for record handling.',
            ],
        ],
        [
            'id' => 'updates',
            'title' => '11. Policy Updates',
            'paragraphs' => [
                'Auronsoft may update this Refund Policy to reflect product, payment method, legal, or business model changes. The new version will be published on the system and apply from the stated effective date.',
            ],
        ],
        [
            'id' => 'contact',
            'title' => '12. Contact',
            'paragraphs' => [
                'If you have questions about payments, invoices, or refund requests, please contact Auronsoft through the official support channel.',
            ],
            'cards' => [
                ['title' => 'Payment support', 'body' => 'Email: support@mindigo.vn'],
                ['title' => 'Account and data matters', 'body' => 'Email: privacy@mindigo.vn'],
            ],
        ],
    ],
    'footer' => [
        'copyright' => '© '.date('Y').' Auronsoft. All rights reserved.',
        'home' => 'Home',
        'terms' => 'Terms of use',
        'privacy' => 'Privacy policy',
    ],
];
