<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ユーザーの設定情報を管理するテーブル。言語、タイムゾーン、通貨、通知設定などのユーザー固有の設定を保存する。
 *
 *
 * @property int $id
 * @property int $user_id
 * @property string $language
 * @property string $timezone
 * @property string $currency
 * @property bool $email_notifications
 * @property bool $push_notifications
 * @property array<array-key, mixed>|null $notification_preferences
 * @property array<array-key, mixed>|null $ui_preferences
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 *
 * @method static \Database\Factories\UserSettingFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting whereEmailNotifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting whereNotificationPreferences($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting wherePushNotifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting whereUiPreferences($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting whereUserId($value)
 *
 * @mixin \Eloquent
 */
class UserSetting extends Model
{
    /** @use HasFactory<\Database\Factories\UserSettingFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'language',
        'timezone',
        'currency',
        'email_notifications',
        'push_notifications',
        'notification_preferences',
        'ui_preferences',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_notifications' => 'boolean',
        'push_notifications' => 'boolean',
        'notification_preferences' => 'json',
        'ui_preferences' => 'json',
    ];

    /**
     * Get the user that owns the settings.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
