<?php

return [
    'title' => 'Teacher provisioning',
    'description' => 'Grant, suspend, or revoke teacher access after a passed interview.',
    'action' => 'Provisioning action',
    'note' => 'Provisioning note',
    'note_placeholder' => 'Required when suspending or revoking teacher access.',
    'save' => 'Save provisioning',
    'completed' => 'Teacher provisioning has been updated.',
    'provisioned_by' => 'Provisioned by',
    'provisioned_at' => 'Provisioned at',
    'missing_account' => 'This application must be linked to a user account before teacher access can be granted.',
    'must_pass_interview' => 'Only applications with a passed interview can be approved.',
    'invalid_suspend_state' => 'Only active teacher provisioning can be suspended.',
    'invalid_revoke_state' => 'Only active or suspended teacher provisioning can be revoked.',
    'actions' => [
        'approve' => 'Approve and grant Teacher role',
        'suspend' => 'Suspend Teacher access',
        'revoke' => 'Revoke Teacher access',
    ],
    'statuses' => [
        'not_provisioned' => 'Not provisioned',
        'active' => 'Teacher active',
        'suspended' => 'Teacher suspended',
        'revoked' => 'Teacher revoked',
    ],
    'notifications' => [
        'approved' => [
            'title' => 'Teacher access granted',
            'message' => 'Your application :code has been approved and your Teacher workspace is ready.',
        ],
        'suspended' => [
            'title' => 'Teacher access suspended',
            'message' => 'Teacher access for application :code has been suspended. :note',
        ],
        'revoked' => [
            'title' => 'Teacher access revoked',
            'message' => 'Teacher access for application :code has been revoked. :note',
        ],
    ],
];
