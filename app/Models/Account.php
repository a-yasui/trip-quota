<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditingTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @property int $id
 * @property int $user_id
 * @property string $account_name
 * @property string|null $display_name
 * @property string|null $thumbnail_url
 * @property string|null $bio
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Member> $members
 * @property-read int|null $members_count
 * @property-read \App\Models\User $user
 *
 * @method static \Database\Factories\AccountFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account whereAccountName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account whereBio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account whereThumbnailUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account whereUserId($value)
 *
 * @mixin \Eloquent
 */
class Account extends Model implements Auditable
{
    use AuditingTrait, HasFactory;

    protected $fillable = [
        'user_id',
        'account_name',
        'display_name',
        'thumbnail_url',
        'bio',
    ];

    /**
     * アカウントの所有者
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * このアカウントが関連付けられているメンバー
     */
    public function members()
    {
        return $this->hasMany(Member::class);
    }

    /**
     * アカウント名のバリデーション（英数字、アンダースコア、ハイフンのみ、3文字以上）
     */
    public static function validateAccountName(string $accountName): bool
    {
        return preg_match('/^[a-zA-Z][\w\-_]{3,}$/', $accountName) === 1;
    }

    /**
     * アカウント名の大文字小文字を区別しない検索
     */
    public static function findByAccountNameIgnoreCase(string $accountName): ?self
    {
        return self::whereRaw('LOWER(account_name) = ?', [strtolower($accountName)])->first();
    }

    /**
     * アカウント名が利用可能かチェック（大文字小文字区別なし）
     */
    public static function isAccountNameAvailable(string $accountName, ?int $excludeId = null): bool
    {
        $query = self::whereRaw('LOWER(account_name) = ?', [strtolower($accountName)]);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return ! $query->exists();
    }
}
