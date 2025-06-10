<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravelPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'plan_name',
        'creator_user_id',
        'owner_user_id',
        'departure_date',
        'return_date',
        'timezone',
        'is_active',
        'description',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'return_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_user_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    public function members()
    {
        return $this->hasMany(Member::class);
    }

    public function accommodations()
    {
        return $this->hasMany(Accommodation::class);
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

    public function expenseSettlements()
    {
        return $this->hasMany(ExpenseSettlement::class);
    }
}
