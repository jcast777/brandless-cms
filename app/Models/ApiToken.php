<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ApiToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'token',
        'abilities',
        'expires_at',
        'last_used_at',
        'usage_count',
        'is_active',
        'description'
    ];

    protected $casts = [
        'abilities' => 'array',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
        'is_active' => 'boolean',
        'usage_count' => 'integer'
    ];

    protected $hidden = [
        'token',
    ];

    /**
     * Generate a new API token.
     */
    public static function generateToken(
        ?User $user = null,
        string $name = 'API Token',
        ?array $abilities = null,
        ?Carbon $expiresAt = null,
        ?string $description = null
    ): array {
        $plainTextToken = Str::random(64);
        $hashedToken = hash('sha256', $plainTextToken);

        $token = self::create([
            'name' => $name,
            'token' => $hashedToken,
            'abilities' => $abilities ?? ['read'],
            'expires_at' => $expiresAt,
            'description' => $description,
            'is_active' => true,
            'usage_count' => 0
        ]);

        return [
            'token' => $token,
            'plain_text_token' => $plainTextToken
        ];
    }

    /**
     * Check if token has specific ability.
     */
    public function hasAbility(string $ability): bool
    {
        return in_array('*', $this->abilities) || in_array($ability, $this->abilities);
    }

    /**
     * Revoke the token.
     */
    public function revoke(): bool
    {
        return $this->update(['is_active' => false]);
    }

    /**
     * Check if token is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if token is valid (active and not expired).
     */
    public function isValid(): bool
    {
        return $this->is_active && !$this->isExpired();
    }
}
