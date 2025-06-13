<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $travel_plan_id
 * @property int $group_id
 * @property int $paid_by_member_id
 * @property string $title
 * @property string|null $description
 * @property numeric $amount
 * @property string $currency
 * @property \Illuminate\Support\Carbon $expense_date
 * @property bool $is_split_confirmed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Group $group
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Member> $members
 * @property-read int|null $members_count
 * @property-read \App\Models\Member $paidBy
 * @property-read \App\Models\TravelPlan $travelPlan
 *
 * @method static \Database\Factories\ExpenseFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereExpenseDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereIsSplitConfirmed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense wherePaidByMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereTravelPlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
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
