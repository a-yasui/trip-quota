<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'expires_at'
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