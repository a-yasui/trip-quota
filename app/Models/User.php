<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Account> $accounts
 * @property-read int|null $accounts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TravelPlan> $createdTravelPlans
 * @property-read int|null $created_travel_plans_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Member> $members
 * @property-read int|null $members_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OAuthProvider> $oauthProviders
 * @property-read int|null $oauth_providers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TravelPlan> $ownedTravelPlans
 * @property-read int|null $owned_travel_plans_count
 * @property-read \App\Models\UserSetting|null $userSettings
 *
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
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
