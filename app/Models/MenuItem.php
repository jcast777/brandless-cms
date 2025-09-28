<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_id',
        'parent_id',
        'title',
        'url',
        'type',
        'object_id',
        'target',
        'css_class',
        'sort_order',
        'is_active',
        'meta',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'meta' => 'array',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('sort_order');
    }

    public function object()
    {
        return match ($this->type) {
            'post' => $this->belongsTo(Post::class, 'object_id'),
            'page' => $this->belongsTo(Page::class, 'object_id'),
            'category' => $this->belongsTo(Category::class, 'object_id'),
            default => null,
        };
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    public function getUrlAttribute($value)
    {
        if ($value) {
            return $value;
        }

        return match ($this->type) {
            'post' => $this->object ? '/posts/' . $this->object->slug : '#',
            'page' => $this->object ? '/' . $this->object->slug : '#',
            'category' => $this->object ? '/category/' . $this->object->slug : '#',
            default => '#',
        };
    }
}
