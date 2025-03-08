<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 異なる旅行計画の班グループ間の接続を管理するテーブル。別の旅行計画の班グループと合流する際の関連付けを保存する。
 *
 *
 * @property int $id
 * @property int $source_group_id
 * @property int $target_group_id
 * @property int $created_by_user_id
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User $createdByUser
 * @property-read \App\Models\Group $sourceGroup
 * @property-read \App\Models\Group $targetGroup
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchGroupConnection active()
 * @method static \Database\Factories\BranchGroupConnectionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchGroupConnection newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchGroupConnection newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchGroupConnection onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchGroupConnection query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchGroupConnection whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchGroupConnection whereCreatedByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchGroupConnection whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchGroupConnection whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchGroupConnection whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchGroupConnection whereSourceGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchGroupConnection whereTargetGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchGroupConnection whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchGroupConnection withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchGroupConnection withoutTrashed()
 *
 * @mixin \Eloquent
 */
class BranchGroupConnection extends Model
{
    /** @use HasFactory<\Database\Factories\BranchGroupConnectionFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'source_group_id',
        'target_group_id',
        'created_by_user_id',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the source group.
     */
    public function sourceGroup()
    {
        return $this->belongsTo(Group::class, 'source_group_id');
    }

    /**
     * Get the target group.
     */
    public function targetGroup()
    {
        return $this->belongsTo(Group::class, 'target_group_id');
    }

    /**
     * Get the user who created the connection.
     */
    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Scope a query to only include active connections.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
