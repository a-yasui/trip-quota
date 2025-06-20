<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $provider
 * @property string $provider_id
 * @property string|null $access_token
 * @property string|null $refresh_token
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 *
 * @method static \Database\Factories\OAuthProviderFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthProvider newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthProvider newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthProvider query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthProvider whereAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthProvider whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthProvider whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthProvider whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthProvider whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthProvider whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthProvider whereRefreshToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthProvider whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthProvider whereUserId($value)
 *
 * @mixin \Eloquent
 */
class OAuthProvider extends Model
{
    use HasFactory;

    protected $table = 'oauth_providers';

    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
        'access_token',
        'refresh_token',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    /**
     * OAuth連携しているユーザー
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 指定したプロバイダーIDでOAuth連携を検索
     */
    public static function findByProvider(string $provider, string $providerId): ?self
    {
        return self::where('provider', $provider)
            ->where('provider_id', $providerId)
            ->first();
    }

    /**
     * アクセストークンが有効かチェック
     */
    public function isTokenValid(): bool
    {
        return is_null($this->expires_at) || $this->expires_at->isFuture();
    }
}
