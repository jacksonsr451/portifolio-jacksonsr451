<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTranslation extends Model
{
    protected $fillable = ['locale', 'title', 'summary', 'editorial_status', 'published_at', 'status', 'problem', 'context', 'role', 'architecture', 'decisions', 'tradeoffs', 'testing', 'challenges', 'evidence'];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
