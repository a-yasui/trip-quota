<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $travel_plan_id
 * @property int $payer_member_id
 * @property int $payee_member_id
 * @property numeric $amount
 * @property string $currency
 * @property \Illuminate\Support\Carbon|null $settled_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Member $payee
 * @property-read \App\Models\Member $payer
 * @property-read \App\Models\TravelPlan $travelPlan
 *
 * @method static \Database\Factories\ExpenseSettlementFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseSettlement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseSettlement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseSettlement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseSettlement whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseSettlement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseSettlement whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseSettlement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseSettlement wherePayeeMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseSettlement wherePayerMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseSettlement whereSettledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseSettlement whereTravelPlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseSettlement whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ExpenseSettlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'travel_plan_id',
        'payer_member_id',
        'payee_member_id',
        'amount',
        'currency',
        'settled_at',
    ];

    protected $guarded = ['is_settled'];

    protected $casts = [
        'amount' => 'decimal:2',
        'settled_at' => 'datetime',
    ];

    public function getIsSettledAttribute(): bool
    {
        return $this->settled_at !== null;
    }

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
