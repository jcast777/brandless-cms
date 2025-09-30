<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Post extends Model implements HasMedia
{
    use HasFactory, HasSlug, InteractsWithMedia;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'status',
        'published_at',
        'author_id',
        'category_id',
        'views_count',
        'is_featured',
        'allow_comments',
        'meta',
        'seo',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
        'allow_comments' => 'boolean',
        'meta' => 'array',
        'seo' => 'array',
    ];

    protected $appends = [
        'featured_image_url',
    ];

    /**
     * Get the featured image URL for API responses.
     *
     * @return string|null
     */
    public function getFeaturedImageUrlAttribute()
    {
        if (!$this->featured_image) {
            return null;
        }

        // If the value is already a full URL, return it as is
        if (filter_var($this->featured_image, FILTER_VALIDATE_URL)) {
            return $this->featured_image;
        }

        try {
            // Generate a signed temporary URL (valid for 24 hours)
            $backendUrl = Storage::temporaryUrl($this->featured_image, now()->addHours(24));

            // Extract the path and query parameters from the backend URL
            $parsedUrl = parse_url($backendUrl);
            $fullPath = $parsedUrl['path'] ?? '';
            // Remove only the /storage/ prefix, preserving the filename including sample_ prefix
            $path = preg_replace('#^/storage/#', '', $fullPath);
            $query = $parsedUrl['query'] ?? '';

            // Return URL pointing to frontend's image proxy
            $frontendUrl = config('app.url');
            $proxyUrl = "{$frontendUrl}/storage/{$path}";

            return $query ? "{$proxyUrl}?{$query}" : $proxyUrl;
        } catch (\Exception $e) {
            // Fallback: return frontend proxy URL without signature
            $path = ltrim($this->featured_image, '/');
            $frontendUrl = config('app.url');
            return "{$frontendUrl}/storage/{$path}";
        }
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(200)
            ->sharpen(10);

        $this->addMediaConversion('medium')
            ->width(800)
            ->height(600)
            ->quality(90);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByAuthor($query, $authorId)
    {
        return $query->where('author_id', $authorId);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }
}
