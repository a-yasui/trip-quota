<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

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
