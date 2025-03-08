<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * システム管理者情報を管理するテーブル。管理者の認証情報、権限、ログイン履歴などを保存する。一般ユーザーとは別に管理される。
 *
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $role
 * @property array<array-key, mixed>|null $permissions
 * @property \Illuminate\Support\Carbon|null $last_login_at
 * @property string|null $last_login_ip
 * @property bool $is_active
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminUser active()
 * @method static \Database\Factories\AdminUserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminUser onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminUser query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminUser whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminUser whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminUser whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminUser whereLastLoginAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminUser whereLastLoginIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminUser whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminUser wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminUser wherePermissions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminUser whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminUser whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminUser withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminUser withoutTrashed()
 *
 * @mixin \Eloquent
 */
class AdminUser extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\AdminUserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'permissions',
        'is_active',
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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'permissions' => 'json',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    /**
     * Scope a query to only include active admin users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
