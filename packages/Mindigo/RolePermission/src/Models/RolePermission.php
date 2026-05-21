<?php

namespace Mindigo\RolePermission\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    protected $fillable = [
        'role',
        'permission',
        'allowed',
    ];

    protected $casts = [
        'allowed' => 'boolean',
    ];
}
