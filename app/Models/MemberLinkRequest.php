<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberLinkRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'requested_by_user_id',
        'target_user_id',
        'target_email',
        'target_account_name',
        'status',
        'message',
        'expires_at',
        'responded_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    // リレーション
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function requestedByUser()
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    // スコープ
    public function scopePending($query)
    {
        return $query->where('status', 'pending')->where('expires_at', '>', now());
    }

    public function scopeForUser($query, User $user)
    {
        return $query->where('target_user_id', $user->id);
    }

    // メソッド
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isPending(): bool
    {
        return $this->status === 'pending' && ! $this->isExpired();
    }

    public function approve(): void
    {
        $this->update([
            'status' => 'approved',
            'responded_at' => now(),
        ]);
    }

    public function decline(): void
    {
        $this->update([
            'status' => 'declined',
            'responded_at' => now(),
        ]);
    }
}
