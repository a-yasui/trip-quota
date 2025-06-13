<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Database\Factories\BranchGroupConnectionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchGroupConnection newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchGroupConnection newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchGroupConnection query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchGroupConnection whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchGroupConnection whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BranchGroupConnection whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class BranchGroupConnection extends Model
{
    use HasFactory;

    protected $fillable = [];
}
