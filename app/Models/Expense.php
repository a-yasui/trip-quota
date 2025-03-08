<?php

namespace App\Models;

use App\Enums\Currency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 旅行中の支出情報を管理するテーブル。支払者、金額、通貨、説明、日付、カテゴリなどの情報を保存し、割り勘計算の基礎となるデータを提供する。
 *
 *
 * @property int $id
 * @property int $travel_plan_id
 * @property int $payer_member_id
 * @property numeric $amount
 * @property \App\Enums\Currency $currency
 * @property string $description
 * @property \Illuminate\Support\Carbon $expense_date
 * @property string|null $category
 * @property string|null $notes
 * @property bool $is_settled
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Member> $members
 * @property-read int|null $members_count
 * @property-read \App\Models\Member $payerMember
 * @property-read \App\Models\TravelPlan $travelPlan
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense afterDate($date)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense beforeDate($date)
 * @method static \Database\Factories\ExpenseFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense ofCategory($category)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense settled()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense unsettled()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereExpenseDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereIsSettled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense wherePayerMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereTravelPlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Expense extends Model
{
    /** @use HasFactory<\Database\Factories\ExpenseFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'travel_plan_id',
        'payer_member_id',
        'amount',
        'currency',
        'description',
        'expense_date',
        'category',
        'notes',
        'is_settled',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
        'currency' => Currency::class,
        'is_settled' => 'boolean',
    ];

    /**
     * Get the travel plan that owns the expense.
     */
    public function travelPlan()
    {
        return $this->belongsTo(TravelPlan::class);
    }

    /**
     * Get the member who paid the expense.
     */
    public function payerMember()
    {
        return $this->belongsTo(Member::class, 'payer_member_id');
    }

    /**
     * Get the members who share the expense.
     */
    public function members()
    {
        return $this->belongsToMany(Member::class, 'expense_member')
            ->withPivot('share_amount', 'is_paid')
            ->withTimestamps();
    }

    /**
     * Scope a query to only include expenses of a specific category.
     */
    public function scopeOfCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to only include expenses with a date after a given date.
     */
    public function scopeAfterDate($query, $date)
    {
        return $query->where('expense_date', '>=', $date);
    }

    /**
     * Scope a query to only include expenses with a date before a given date.
     */
    public function scopeBeforeDate($query, $date)
    {
        return $query->where('expense_date', '<=', $date);
    }

    /**
     * Scope a query to only include settled expenses.
     */
    public function scopeSettled($query)
    {
        return $query->where('is_settled', true);
    }

    /**
     * Scope a query to only include unsettled expenses.
     */
    public function scopeUnsettled($query)
    {
        return $query->where('is_settled', false);
    }
}
