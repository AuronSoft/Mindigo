<?php

namespace Mindigo\TeacherLiveSession\Models;

use Illuminate\Database\Eloquent\Model;

final class LiveSessionResource extends Model
{
    protected $fillable = ['live_session_id', 'uploaded_by', 'name', 'mime_type', 'storage_disk', 'storage_path', 'size_bytes', 'checksum'];
}
