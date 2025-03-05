<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 
 *
 * @property int $id
 * @property int $group_id
 * @property int $inviter_id
 * @property string $email
 * @property string $token
 * @property string $status
 * @property \Illuminate\Support\Carbon $expires_at
 * @property \Illuminate\Support\Carbon|null $accepted_at
 * @property \Illuminate\Support\Carbon|null $declined_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Group $group
 * @property-read \App\Models\User $inviter
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupInvitation accepted()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupInvitation active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupInvitation declined()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupInvitation expired()
 * @method static \Database\Factories\GroupInvitationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupInvitation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupInvitation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupInvitation onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupInvitation pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupInvitation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupInvitation whereAcceptedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupInvitation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupInvitation whereDeclinedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupInvitation whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupInvitation whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupInvitation whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupInvitation whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupInvitation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupInvitation whereInviterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupInvitation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupInvitation whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupInvitation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupInvitation withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupInvitation withoutTrashed()
 * @mixin \Eloquent
 */
class GroupInvitation extends Model
{
    /** @use HasFactory<\Database\Factories\GroupInvitationFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'group_id',
        'inviter_id',
        'email',
        'token',
        'status',
        'expires_at',
        'accepted_at',
        'declined_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
        'declined_at' => 'datetime',
    ];

    /**
     * Get the group that owns the invitation.
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the user who sent the invitation.
     */
    public function inviter()
    {
        return $this->belongsTo(User::class, 'inviter_id');
    }

    /**
     * Scope a query to only include pending invitations.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include accepted invitations.
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Scope a query to only include declined invitations.
     */
    public function scopeDeclined($query)
    {
        return $query->where('status', 'declined');
    }

    /**
     * Scope a query to only include expired invitations.
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')
            ->orWhere('expires_at', '<', now());
    }

    /**
     * Scope a query to only include active invitations.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'pending')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            });
    }
}
