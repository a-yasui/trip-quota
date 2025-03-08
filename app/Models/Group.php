<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 旅行メンバーのグループを管理するテーブル。コアグループ（全メンバー）と班グループ（一部メンバー）の区別や、親子関係のある班グループの構造を表現する。
 *
 *
 * @property int $id
 * @property string $name
 * @property string $type
 * @property int $travel_plan_id
 * @property int|null $parent_group_id
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Group> $childGroups
 * @property-read int|null $child_groups_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GroupInvitation> $invitations
 * @property-read int|null $invitations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Member> $members
 * @property-read int|null $members_count
 * @property-read Group|null $parentGroup
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BranchGroupConnection> $sourceBranchGroupConnections
 * @property-read int|null $source_branch_group_connections_count
 * @property-read \App\Models\SystemBranchGroupKey|null $systemBranchGroupKey
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BranchGroupConnection> $targetBranchGroupConnections
 * @property-read int|null $target_branch_group_connections_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TravelLocation> $travelLocations
 * @property-read int|null $travel_locations_count
 * @property-read \App\Models\TravelPlan $travelPlan
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group branch()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group core()
 * @method static \Database\Factories\GroupFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group whereParentGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group whereTravelPlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Group extends Model
{
    /** @use HasFactory<\Database\Factories\GroupFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'type',
        'travel_plan_id',
        'parent_group_id',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => \App\Enums\GroupType::class,
    ];

    /**
     * Get the travel plan that owns the group.
     */
    public function travelPlan()
    {
        return $this->belongsTo(TravelPlan::class);
    }

    /**
     * Get the parent group.
     */
    public function parentGroup()
    {
        return $this->belongsTo(Group::class, 'parent_group_id');
    }

    /**
     * Get the child groups.
     */
    public function childGroups()
    {
        return $this->hasMany(Group::class, 'parent_group_id');
    }

    /**
     * Get the members for the group.
     */
    public function members()
    {
        return $this->belongsToMany(Member::class, 'group_member')
                    ->withTimestamps();
    }

    /**
     * Get the invitations for the group.
     */
    public function invitations()
    {
        return $this->hasMany(GroupInvitation::class);
    }

    /**
     * Get the system branch group key for the group.
     */
    public function systemBranchGroupKey()
    {
        return $this->hasOne(SystemBranchGroupKey::class);
    }

    /**
     * Get the source branch group connections for the group.
     */
    public function sourceBranchGroupConnections()
    {
        return $this->hasMany(BranchGroupConnection::class, 'source_group_id');
    }

    /**
     * Get the target branch group connections for the group.
     */
    public function targetBranchGroupConnections()
    {
        return $this->hasMany(BranchGroupConnection::class, 'target_group_id');
    }

    /**
     * Get the travel locations for the group.
     */
    public function travelLocations()
    {
        return $this->hasMany(TravelLocation::class);
    }

    /**
     * Scope a query to only include core groups.
     */
    public function scopeCore($query)
    {
        return $query->where('type', \App\Enums\GroupType::CORE);
    }

    /**
     * Scope a query to only include branch groups.
     */
    public function scopeBranch($query)
    {
        return $query->where('type', \App\Enums\GroupType::BRANCH);
    }

    public function branchMembers()
    {
        return $this->belongsToMany(Member::class, 'group_member')
                    ->withTimestamps();
    }

    public function coreMembers()
    {
        return $this->hasMany(Member::class);
    }
}
