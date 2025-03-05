<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 
 *
 * @property int $id
 * @property int $group_id
 * @property string $key
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Group $group
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemBranchGroupKey active()
 * @method static \Database\Factories\SystemBranchGroupKeyFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemBranchGroupKey newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemBranchGroupKey newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemBranchGroupKey onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemBranchGroupKey query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemBranchGroupKey whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemBranchGroupKey whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemBranchGroupKey whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemBranchGroupKey whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemBranchGroupKey whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemBranchGroupKey whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemBranchGroupKey whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemBranchGroupKey withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemBranchGroupKey withoutTrashed()
 * @mixin \Eloquent
 */
class SystemBranchGroupKey extends Model
{
    /** @use HasFactory<\Database\Factories\SystemBranchGroupKeyFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'group_id',
        'key',
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
     * Get the group that owns the system branch group key.
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Scope a query to only include active keys.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
