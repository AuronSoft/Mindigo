<?php

return [
    'title' => 'Live Sessions',
    'subtitle' => 'Manage your online class sessions',
    'teaching_live' => 'Teaching content',
    'create' => 'New session',
    'edit' => 'Edit session',
    'cancel' => 'Cancel',
    'delete' => 'Delete',
    'save' => 'Save session',

    // Filter
    'filter_classroom_label' => 'Classroom',
    'all_classrooms' => 'All classrooms',
    'filter_submit' => 'Filter',
    'clear_filter' => 'Clear filter',
    'filter_button' => 'Filters',
    'filter_active' => 'Active filter',
    'filter_title' => 'Filter live sessions',
    'filter_desc' => 'Choose a classroom to narrow the session list.',
    'filter_hint_title' => 'Tip',
    'filter_hint_desc' => 'Filters live in a drawer so the main data area stays wide and easy to scan.',

    // Empty
    'empty_title' => 'No live sessions yet',
    'empty_desc' => 'Create your first session to start teaching online with your class.',

    // Table columns
    'col_number' => '#',
    'col_title' => 'Title',
    'col_classroom' => 'Classroom',
    'col_schedule' => 'Schedule',
    'col_status' => 'Status',
    'col_actions' => 'Actions',

    // Status
    'status_scheduled' => 'Scheduled',
    'status_live' => 'Live',
    'status_ended' => 'Ended',
    'status_cancelled' => 'Cancelled',

    // Actions
    'start' => 'Start',
    'join_room' => 'Join room',
    'end' => 'End',
    'leave_room' => 'Leave room',

    // Form
    'section_basic_info' => 'Session info',
    'section_schedule' => 'Classroom & schedule',
    'field_title' => 'Title',
    'field_desc' => 'Description',
    'field_classroom' => 'Classroom',
    'field_start' => 'Start time',
    'field_end' => 'Expected end time',
    'title_placeholder' => 'e.g. Chapter 3 review session',
    'desc_placeholder' => 'Notes / agenda for the session...',
    'classroom_select_placeholder' => '-- Select classroom --',
    'classroom_option' => ':name (:code) · :count students',

    // Room
    'room_title' => 'Online classroom',
    'room_loading' => 'Connecting to the room...',

    // Messages
    'created_success' => 'Live session created.',
    'updated_success' => 'Session updated.',
    'deleted_success' => 'Session deleted.',
    'ended_success' => 'Session ended.',
    'delete_confirm_title' => 'Delete session?',
    'delete_confirm_message' => 'Are you sure you want to delete this session? This cannot be undone.',

    // Validation
    'validation' => [
        'title_required' => 'Please enter a session title.',
        'classroom_required' => 'Please select a classroom.',
        'classroom_exists' => 'Invalid classroom.',
        'start_required' => 'Please choose a start time.',
        'end_after' => 'End time must be after start time.',
    ],
];
