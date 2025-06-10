<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseSettlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'travel_plan_id',
        'payer_member_id',
        'payee_member_id',
        'amount',
        'currency',
        'is_settled',
        'settled_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_settled' => 'boolean',
        'settled_at' => 'datetime',
    ];

    public function travelPlan()
    {
        return $this->belongsTo(TravelPlan::class);
    }

    public function payer()
    {
        return $this->belongsTo(Member::class, 'payer_member_id');
    }

    public function payee()
    {
        return $this->belongsTo(Member::class, 'payee_member_id');
    }
}
