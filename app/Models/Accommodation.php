<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accommodation extends Model
{
    use HasFactory;

    protected $fillable = [
        'travel_plan_id',
        'created_by_member_id',
        'name',
        'address',
        'check_in_date',
        'check_out_date',
        'check_in_time',
        'check_out_time',
        'price_per_night',
        'currency',
        'notes',
        'confirmation_number',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'check_in_time' => 'datetime:H:i',
        'check_out_time' => 'datetime:H:i',
        'price_per_night' => 'decimal:2',
    ];

    public function travelPlan()
    {
        return $this->belongsTo(TravelPlan::class);
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
