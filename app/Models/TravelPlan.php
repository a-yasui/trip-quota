<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditingTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @property int $id
 * @property string $uuid
 * @property string $plan_name
 * @property int $creator_user_id
 * @property int $owner_user_id
 * @property \Illuminate\Support\Carbon $departure_date
 * @property \Illuminate\Support\Carbon|null $return_date
 * @property string $timezone
 * @property bool $is_active
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Accommodation> $accommodations
 * @property-read int|null $accommodations_count
 * @property-read \App\Models\User $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ExpenseSettlement> $expenseSettlements
 * @property-read int|null $expense_settlements_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Expense> $expenses
 * @property-read int|null $expenses_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GroupInvitation> $groupInvitations
 * @property-read int|null $group_invitations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Group> $groups
 * @property-read int|null $groups_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Itinerary> $itineraries
 * @property-read int|null $itineraries_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Member> $members
 * @property-read int|null $members_count
 * @property-read \App\Models\User $owner
 *
 * @method static \Database\Factories\TravelPlanFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelPlan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelPlan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelPlan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelPlan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelPlan whereCreatorUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelPlan whereDepartureDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelPlan whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelPlan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelPlan whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelPlan whereOwnerUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelPlan wherePlanName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelPlan whereReturnDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelPlan whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelPlan whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelPlan whereUuid($value)
 *
 * @mixin \Eloquent
 */
class TravelPlan extends Model implements Auditable
{
    use AuditingTrait, HasFactory;

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
