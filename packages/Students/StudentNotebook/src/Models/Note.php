<?php

namespace Mindigo\StudentNotebook\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mindigo\Auth\Models\User;

class Note extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'notes';

    protected $fillable = [
        'student_id',
        'title',
        'content',
    ];

    // Relationships

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
