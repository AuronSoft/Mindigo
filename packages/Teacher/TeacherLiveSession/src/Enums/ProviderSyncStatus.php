<?php

namespace Mindigo\TeacherLiveSession\Enums;

enum ProviderSyncStatus: string
{
    case NotRequired = 'not_required';
    case Pending = 'pending';
    case Synced = 'synced';
    case Failed = 'failed';
}
