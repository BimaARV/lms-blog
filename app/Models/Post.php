<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasTranslatableSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property array $title
 * @property array $slug
 * @property array $body
 * @property int $views_count
 */
class Post extends Model
{
    use HasFactory;
    use HasTranslations;
    use HasTranslatableSlug;

    public array $translatable = ['title', 'slug', 'excerpt', 'body', 'meta_title', 'meta_description'];

    protected $fillable = [
        'user_id', 'category_id', 'title', 'slug', 'excerpt', 'body',
        'meta_title', 'meta_description', 'featured_image',
        'status', 'published_at', 'views_count', 'allow_comments',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'allow_comments' => 'boolean',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(120)
            ->usingLanguage('id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->where('status', 'approved')->latest();
    }

    public function allComments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    public function getReadingTimeAttribute(): int
    {
        $body = is_array($this->body) ? ($this->body[app()->getLocale()] ?? '') : $this->body;
        $wordCount = str_word_count(strip_tags($body));
        return max(1, (int) ceil($wordCount / 200));
    }
}
