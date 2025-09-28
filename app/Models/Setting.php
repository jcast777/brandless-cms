<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'options',
        'description',
        'sort_order',
        'is_public',
    ];

    protected $casts = [
        'options' => 'array',
        'is_public' => 'boolean',
    ];

    public static function get(string $key, $default = null, string $group = 'general')
    {
        $cacheKey = "setting.{$group}.{$key}";
        
        return Cache::remember($cacheKey, 3600, function () use ($key, $group, $default) {
            $setting = static::where('group', $group)->where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }
            
            return match ($setting->type) {
                'boolean' => (bool) $setting->value,
                'integer' => (int) $setting->value,
                'float' => (float) $setting->value,
                'json' => json_decode($setting->value, true),
                default => $setting->value,
            };
        });
    }

    public static function set(string $key, $value, string $group = 'general', string $type = 'text'): void
    {
        $setting = static::updateOrCreate(
            ['group' => $group, 'key' => $key],
            [
                'value' => is_array($value) ? json_encode($value) : $value,
                'type' => $type,
            ]
        );

        Cache::forget("setting.{$group}.{$key}");
    }

    public static function getGroup(string $group): array
    {
        $cacheKey = "settings.group.{$group}";
        
        return Cache::remember($cacheKey, 3600, function () use ($group) {
            return static::where('group', $group)
                ->orderBy('sort_order')
                ->get()
                ->mapWithKeys(function ($setting) {
                    $value = match ($setting->type) {
                        'boolean' => (bool) $setting->value,
                        'integer' => (int) $setting->value,
                        'float' => (float) $setting->value,
                        'json' => json_decode($setting->value, true),
                        default => $setting->value,
                    };
                    
                    return [$setting->key => $value];
                })
                ->toArray();
        });
    }

    public static function getPublic(): array
    {
        return Cache::remember('settings.public', 3600, function () {
            return static::where('is_public', true)
                ->get()
                ->groupBy('group')
                ->map(function ($settings) {
                    return $settings->mapWithKeys(function ($setting) {
                        $value = match ($setting->type) {
                            'boolean' => (bool) $setting->value,
                            'integer' => (int) $setting->value,
                            'float' => (float) $setting->value,
                            'json' => json_decode($setting->value, true),
                            default => $setting->value,
                        };
                        
                        return [$setting->key => $value];
                    });
                })
                ->toArray();
        });
    }

    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($setting) {
            Cache::forget("setting.{$setting->group}.{$setting->key}");
            Cache::forget("settings.group.{$setting->group}");
            Cache::forget('settings.public');
        });

        static::deleted(function ($setting) {
            Cache::forget("setting.{$setting->group}.{$setting->key}");
            Cache::forget("settings.group.{$setting->group}");
            Cache::forget('settings.public');
        });
    }
}
