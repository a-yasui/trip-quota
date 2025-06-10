<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Itinerary extends Model
{
    use HasFactory;

    protected $fillable = [
        'travel_plan_id',
        'group_id',
        'created_by_member_id',
        'title',
        'description',
        'date',
        'start_time',
        'end_time',
        'timezone',
        'transportation_type',
        'airline',
        'flight_number',
        'departure_time',
        'arrival_time',
        'departure_location',
        'arrival_location',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'departure_time' => 'datetime',
        'arrival_time' => 'datetime',
    ];

    public function travelPlan()
    {
        return $this->belongsTo(TravelPlan::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(Member::class, 'created_by_member_id');
    }

    public function members()
    {
        return $this->belongsToMany(Member::class);
    }
}
