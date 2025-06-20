<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $travel_plan_id
 * @property int $group_id
 * @property int $invited_by_member_id
 * @property string $invitee_email
 * @property string|null $invitee_name
 * @property string $invitation_token
 * @property string $status
 * @property \Illuminate\Support\Carbon $expires_at
 * @property \Illuminate\Support\Carbon|null $responded_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Group $group
 * @property-read \App\Models\Member $invitedBy
 * @property-read \App\Models\TravelPlan $travelPlan
 *
 * @method static \Database\Factories\GroupInvitationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupInvitation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupInvitation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupInvitation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupInvitation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupInvitation whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupInvitation whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupInvitation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupInvitation whereInvitationToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupInvitation whereInvitedByMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupInvitation whereInviteeEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupInvitation whereInviteeName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupInvitation whereRespondedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupInvitation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupInvitation whereTravelPlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupInvitation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class GroupInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'travel_plan_id',
        'group_id',
        'invited_by_member_id',
        'invitee_email',
        'invitee_name',
        'invitation_token',
        'status',
        'expires_at',
        'responded_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    public function travelPlan()
    {
        return $this->belongsTo(TravelPlan::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function invitedBy()
    {
        return $this->belongsTo(Member::class, 'invited_by_member_id');
    }
}
