<?php

namespace Mindigo\TeacherLiveSession\Models;

use Illuminate\Database\Eloquent\Model;

final class LiveSessionRecordingChunk extends Model
{
    protected $fillable = ['recording_id', 'sequence', 'storage_path', 'size_bytes', 'checksum'];
}
