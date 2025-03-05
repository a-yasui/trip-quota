<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\OAuthProviderFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthProvider newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthProvider newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthProvider query()
 * @mixin \Eloquent
 */
class OAuthProvider extends Model
{
    /** @use HasFactory<\Database\Factories\OAuthProviderFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'provider',
        'provider_user_id',
        'access_token',
        'refresh_token',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    /**
     * Get the user that owns the OAuth provider.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
