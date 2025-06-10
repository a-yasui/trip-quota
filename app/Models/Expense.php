<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'travel_plan_id',
        'group_id',
        'paid_by_member_id',
        'title',
        'description',
        'amount',
        'currency',
        'expense_date',
        'is_split_confirmed',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
        'is_split_confirmed' => 'boolean',
    ];

    public function travelPlan()
    {
        return $this->belongsTo(TravelPlan::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function paidBy()
    {
        return $this->belongsTo(Member::class, 'paid_by_member_id');
    }

    public function members()
    {
        return $this->belongsToMany(Member::class)->withPivot('is_participating', 'amount', 'is_confirmed')->withTimestamps();
    }
}
