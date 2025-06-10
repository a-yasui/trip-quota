<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'language',
        'timezone',
        'email_notifications',
        'push_notifications',
        'currency'
    ];

    protected function casts(): array
    {
        return [
            'email_notifications' => 'boolean',
            'push_notifications' => 'boolean',
        ];
    }

    /**
     * 設定の所有者
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}