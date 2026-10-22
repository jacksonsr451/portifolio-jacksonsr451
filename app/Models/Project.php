<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['slug', 'status_key', 'stack', 'links', 'evidence', 'display_order', 'featured', 'archived_at', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return ['stack' => 'array', 'links' => 'array', 'evidence' => 'array', 'featured' => 'boolean', 'archived_at' => 'datetime'];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ProjectTranslation::class);
    }
}
