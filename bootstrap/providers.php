<?php

use App\Providers\AppServiceProvider;
use Mindigo\Notification\Providers\NotificationServiceProvider;
use Mindigo\TeacherAssignment\Providers\TeacherAssignmentServiceProvider;
use Mindigo\TeacherCourse\Providers\TeacherCourseServiceProvider;

return [
    AppServiceProvider::class,
    NotificationServiceProvider::class,
    TeacherAssignmentServiceProvider::class,
    TeacherCourseServiceProvider::class,
];
