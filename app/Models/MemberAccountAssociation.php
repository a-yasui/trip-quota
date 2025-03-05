<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * メンバーとアカウントの関連付けを管理するテーブル。メンバーが使用するアカウントの変更履歴も保存し、アカウント変更の追跡を可能にする。
 * 
 *
 * @property int $id
 * @property int $member_id
 * @property int $account_id
 * @property int|null $previous_account_id
 * @property int $changed_by_user_id
 * @property string|null $change_reason
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Account $account
 * @property-read \App\Models\User $changedByUser
 * @property-read \App\Models\Member $member
 * @property-read \App\Models\Account|null $previousAccount
 * @method static \Database\Factories\MemberAccountAssociationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAccountAssociation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAccountAssociation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAccountAssociation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAccountAssociation whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAccountAssociation whereChangeReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAccountAssociation whereChangedByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAccountAssociation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAccountAssociation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAccountAssociation whereMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAccountAssociation wherePreviousAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAccountAssociation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MemberAccountAssociation extends Model
{
    /** @use HasFactory<\Database\Factories\MemberAccountAssociationFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'member_id',
        'account_id',
        'previous_account_id',
        'changed_by_user_id',
        'change_reason',
    ];

    /**
     * Get the member that owns the association.
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Get the account associated with the member.
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the previous account associated with the member.
     */
    public function previousAccount()
    {
        return $this->belongsTo(Account::class, 'previous_account_id');
    }

    /**
     * Get the user who changed the association.
     */
    public function changedByUser()
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
