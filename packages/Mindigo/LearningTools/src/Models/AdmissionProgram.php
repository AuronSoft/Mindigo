<?php

namespace Mindigo\LearningTools\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdmissionProgram extends Model
{
    protected $table = 'learning_admission_programs';

    protected $fillable = ['university_id', 'major_code', 'major_name', 'year', 'method', 'combinations', 'benchmark_score', 'quota', 'source_url', 'source_name', 'published_at', 'verified_at', 'source_hash'];

    protected function casts(): array
    {
        return ['combinations' => 'array', 'benchmark_score' => 'float', 'year' => 'integer', 'published_at' => 'datetime', 'verified_at' => 'datetime'];
    }

    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }
}
