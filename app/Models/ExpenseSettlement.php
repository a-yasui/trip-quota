<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 割り勘精算情報を管理するテーブル。メンバー間の支払い精算情報を記録し、誰が誰にいくら支払うべきかの情報を保存する。
 *
 *
 * @property int $id
 * @property int $travel_plan_id
 * @property int $payer_member_id
 * @property int $receiver_member_id
 * @property numeric $amount
 * @property string $currency
 * @property bool $is_settled
 * @property \Illuminate\Support\Carbon|null $settlement_date
 * @property string|null $settlement_method
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Member $payerMember
 * @property-read \App\Models\Member $receiverMember
 * @property-read \App\Models\TravelPlan $travelPlan
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseSettlement afterDate($date)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseSettlement beforeDate($date)
 * @method static \Database\Factories\ExpenseSettlementFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseSettlement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseSettlement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseSettlement onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseSettlement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseSettlement settled()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseSettlement unsettled()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseSettlement whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseSettlement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseSettlement whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseSettlement whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseSettlement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseSettlement whereIsSettled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseSettlement whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseSettlement wherePayerMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseSettlement whereReceiverMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseSettlement whereSettlementDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseSettlement whereSettlementMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseSettlement whereTravelPlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseSettlement whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseSettlement withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseSettlement withoutTrashed()
 *
 * @mixin \Eloquent
 */
class ExpenseSettlement extends Model
{
    /** @use HasFactory<\Database\Factories\ExpenseSettlementFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'travel_plan_id',
        'payer_member_id',
        'receiver_member_id',
        'amount',
        'currency',
        'is_settled',
        'settlement_date',
        'settlement_method',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'is_settled' => 'boolean',
        'settlement_date' => 'date',
    ];

    /**
     * Get the travel plan that owns the settlement.
     */
    public function travelPlan()
    {
        return $this->belongsTo(TravelPlan::class);
    }

    /**
     * Get the member who pays the settlement.
     */
    public function payerMember()
    {
        return $this->belongsTo(Member::class, 'payer_member_id');
    }

    /**
     * Get the member who receives the settlement.
     */
    public function receiverMember()
    {
        return $this->belongsTo(Member::class, 'receiver_member_id');
    }

    /**
     * Scope a query to only include settled settlements.
     */
    public function scopeSettled($query)
    {
        return $query->where('is_settled', true);
    }

    /**
     * Scope a query to only include unsettled settlements.
     */
    public function scopeUnsettled($query)
    {
        return $query->where('is_settled', false);
    }

    /**
     * Scope a query to only include settlements with a date after a given date.
     */
    public function scopeAfterDate($query, $date)
    {
        return $query->where('settlement_date', '>=', $date);
    }

    /**
     * Scope a query to only include settlements with a date before a given date.
     */
    public function scopeBeforeDate($query, $date)
    {
        return $query->where('settlement_date', '<=', $date);
    }
}
