<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * ユーザーが持つアカウント
     */
    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    /**
     * ユーザーのOAuthプロバイダー連携
     */
    public function oauthProviders()
    {
        return $this->hasMany(OAuthProvider::class);
    }

    /**
     * ユーザー設定
     */
    public function userSettings()
    {
        return $this->hasOne(UserSetting::class);
    }

    /**
     * ユーザーが参加しているメンバー
     */
    public function members()
    {
        return $this->hasMany(Member::class);
    }

    /**
     * ユーザーが作成した旅行計画
     */
    public function createdTravelPlans()
    {
        return $this->hasMany(TravelPlan::class, 'creator_user_id');
    }

    /**
     * ユーザーが所有している旅行計画（削除権限を持つ）
     */
    public function ownedTravelPlans()
    {
        return $this->hasMany(TravelPlan::class, 'owner_user_id');
    }

    /**
     * パスワードが設定されているかチェック
     */
    public function hasPassword(): bool
    {
        return ! is_null($this->password);
    }

    /**
     * 指定したOAuthプロバイダーが連携されているかチェック
     */
    public function hasOAuthProvider(string $provider): bool
    {
        return $this->oauthProviders()->where('provider', $provider)->exists();
    }
}
