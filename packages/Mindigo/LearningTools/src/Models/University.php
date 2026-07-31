<?php

namespace Mindigo\LearningTools\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class University extends Model
{
    protected $table = 'learning_universities';

    protected $fillable = ['code', 'name', 'short_name', 'province', 'website', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function programs(): HasMany
    {
        return $this->hasMany(AdmissionProgram::class);
    }
}
