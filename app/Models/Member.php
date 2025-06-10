<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'travel_plan_id',
        'user_id',
        'account_id',
        'name',
        'email',
        'is_confirmed',
    ];

    protected $casts = [
        'is_confirmed' => 'boolean',
    ];

    public function travelPlan()
    {
        return $this->belongsTo(TravelPlan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function accommodations()
    {
        return $this->belongsToMany(Accommodation::class);
    }

    public function itineraries()
    {
        return $this->belongsToMany(Itinerary::class);
    }

    public function expenses()
    {
        return $this->belongsToMany(Expense::class)->withPivot('is_participating', 'amount', 'is_confirmed')->withTimestamps();
    }

    public function paidExpenses()
    {
        return $this->hasMany(Expense::class, 'paid_by_member_id');
    }

    public function createdAccommodations()
    {
        return $this->hasMany(Accommodation::class, 'created_by_member_id');
    }

    public function createdItineraries()
    {
        return $this->hasMany(Itinerary::class, 'created_by_member_id');
    }

    public function sentInvitations()
    {
        return $this->hasMany(GroupInvitation::class, 'invited_by_member_id');
    }

    public function payerSettlements()
    {
        return $this->hasMany(ExpenseSettlement::class, 'payer_member_id');
    }

    public function payeeSettlements()
    {
        return $this->hasMany(ExpenseSettlement::class, 'payee_member_id');
    }

    public function travelDocuments()
    {
        return $this->belongsToMany(TravelDocument::class, 'document_member');
    }
}
