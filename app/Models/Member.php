<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $travel_plan_id
 * @property int|null $user_id
 * @property int|null $account_id
 * @property string $name
 * @property string|null $email
 * @property bool $is_confirmed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Accommodation> $accommodations
 * @property-read int|null $accommodations_count
 * @property-read \App\Models\Account|null $account
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Accommodation> $createdAccommodations
 * @property-read int|null $created_accommodations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Itinerary> $createdItineraries
 * @property-read int|null $created_itineraries_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Expense> $expenses
 * @property-read int|null $expenses_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Itinerary> $itineraries
 * @property-read int|null $itineraries_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Expense> $paidExpenses
 * @property-read int|null $paid_expenses_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ExpenseSettlement> $payeeSettlements
 * @property-read int|null $payee_settlements_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ExpenseSettlement> $payerSettlements
 * @property-read int|null $payer_settlements_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GroupInvitation> $sentInvitations
 * @property-read int|null $sent_invitations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TravelDocument> $travelDocuments
 * @property-read int|null $travel_documents_count
 * @property-read \App\Models\TravelPlan $travelPlan
 * @property-read \App\Models\User|null $user
 *
 * @method static \Database\Factories\MemberFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereIsConfirmed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereTravelPlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereUserId($value)
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Group> $groups
 * @property-read int|null $groups_count
 *
 * @mixin \Eloquent
 */
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
        return $this->belongsToMany(Accommodation::class, 'accommodation_members');
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

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_member');
    }
}
