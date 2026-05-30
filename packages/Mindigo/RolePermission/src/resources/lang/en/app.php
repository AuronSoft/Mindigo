<?php

return [
    'title' => 'Role permissions',
    'breadcrumb' => 'Role permissions',
    'heading' => 'Role permissions',
    'description' => 'A fixed permission map for the three system roles. This keeps access control simple and predictable.',
    'fixed_roles' => 'Fixed roles',
    'total_permissions' => ':count permissions',
    'matrix_label' => 'Access matrix',
    'matrix_title' => 'Permission coverage by role',
    'readonly' => 'Configured in code',
    'admin_locked' => 'Admin is always full access',
    'save' => 'Save permissions',
    'saved' => 'Role permissions have been saved.',
    'cancel' => 'Cancel',
    'confirm_title' => 'Save role permissions',
    'confirm_message' => 'These permission changes will apply immediately to users in each role.',
    'permission' => 'Permission',
    'allowed' => 'Allowed',
    'denied' => 'Denied',

    'roles' => [
        'admin' => 'Admin',
        'teacher' => 'Teacher',
        'student' => 'Student',
    ],

    'role_descriptions' => [
        'admin' => 'Full control over the admin system, settings, logs and users.',
        'teacher' => 'Creates and maintains exams, questions and reports for classes.',
        'student' => 'Takes assigned exams and views personal learning results.',
    ],

    'groups' => [
        'platform' => 'Platform',
        'content' => 'Exam content',
        'learner' => 'Learner workflow',
        'support' => 'Support',
        'admin' => 'Administration',
    ],

    'permissions' => [
        'dashboard' => [
            'view' => 'View dashboard',
        ],
        'reports' => [
            'view' => 'View reports',
        ],
        'exams' => [
            'view' => 'View exams',
            'create' => 'Create exams',
            'update' => 'Update exams',
            'publish' => 'Publish exams',
            'delete' => 'Delete exams',
            'attempt' => 'Take exams',
        ],
        'subjects' => [
            'view' => 'View subjects',
            'create' => 'Create subjects',
            'update' => 'Update subjects',
            'delete' => 'Delete subjects',
        ],
        'classrooms' => [
            'view' => 'View classrooms',
            'create' => 'Create classrooms',
            'update' => 'Update classrooms',
            'manage_students' => 'Manage classroom students',
            'delete' => 'Delete classrooms',
        ],
        'questions' => [
            'view' => 'View questions',
            'create' => 'Create questions',
            'update' => 'Update questions',
            'review' => 'Review questions',
            'delete' => 'Delete questions',
        ],
        'results' => [
            'view' => 'View results',
        ],
        'support_tickets' => [
            'view' => 'View support tickets',
            'create' => 'Create support tickets',
            'reply' => 'Reply to support tickets',
            'manage' => 'Manage support tickets',
            'delete' => 'Delete support tickets',
        ],
        'users' => [
            'view' => 'View users',
            'create' => 'Create users',
            'update' => 'Update users',
            'delete' => 'Delete users',
            'restore' => 'Restore users',
        ],
        'system_settings' => [
            'view' => 'View system settings',
            'update' => 'Update system settings',
        ],
        'audit_logs' => [
            'view' => 'View audit logs',
        ],
        'role_permissions' => [
            'view' => 'View role permissions',
        ],
    ],
];
