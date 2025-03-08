<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 支出とメンバーの関連付けを管理する中間テーブル。各メンバーの負担額と支払い状況を記録し、割り勘計算の詳細を保存する。
 *
 *
 * @property int $id
 * @property int $expense_id
 * @property int $member_id
 * @property numeric|null $share_amount
 * @property bool $is_paid
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Expense $expense
 * @property-read \App\Models\Member $member
 *
 * @method static \Database\Factories\ExpenseMemberFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseMember newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseMember newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseMember query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseMember whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseMember whereExpenseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseMember whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseMember whereIsPaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseMember whereMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseMember whereShareAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseMember whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ExpenseMember extends Model
{
    /** @use HasFactory<\Database\Factories\ExpenseMemberFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'expense_member';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'expense_id',
        'member_id',
        'share_amount',
        'is_paid',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'share_amount' => 'decimal:2',
        'is_paid' => 'boolean',
    ];

    /**
     * Get the expense that owns the pivot.
     */
    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    /**
     * Get the member that owns the pivot.
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
