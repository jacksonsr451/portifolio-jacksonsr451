<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleTranslation extends Model
{
    protected $fillable = ['locale', 'title', 'description', 'body_markdown', 'seo_title', 'seo_description', 'cover_alt', 'published_on', 'editorial_status', 'published_at'];

    protected function casts(): array
    {
        return ['published_on' => 'date', 'published_at' => 'datetime'];
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
