<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $key
 * @property int $group_id
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Group $group
 *
 * @method static \Database\Factories\SystemBranchGroupKeyFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemBranchGroupKey newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemBranchGroupKey newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemBranchGroupKey query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemBranchGroupKey whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemBranchGroupKey whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemBranchGroupKey whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemBranchGroupKey whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemBranchGroupKey whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemBranchGroupKey whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemBranchGroupKey whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class SystemBranchGroupKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'group_id',
        'is_active',
        'expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
