<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
