<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'travel_plan_id',
        'type',
        'name',
        'branch_key',
        'description',
    ];

    public function travelPlan()
    {
        return $this->belongsTo(TravelPlan::class);
    }

    public function itineraries()
    {
        return $this->hasMany(Itinerary::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function groupInvitations()
    {
        return $this->hasMany(GroupInvitation::class);
    }

    public function systemBranchGroupKeys()
    {
        return $this->hasMany(SystemBranchGroupKey::class);
    }
}
