<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Article extends Model
{
    protected $fillable = ['slug', 'author_id', 'created_by', 'updated_by', 'archived_at'];

    protected function casts(): array
    {
        return ['archived_at' => 'datetime'];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ArticleTranslation::class);
    }
}
