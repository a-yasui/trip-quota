<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Database\Factories\MemberAccountAssociationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAccountAssociation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAccountAssociation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAccountAssociation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAccountAssociation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAccountAssociation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberAccountAssociation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class MemberAccountAssociation extends Model
{
    use HasFactory;

    protected $fillable = [];
}
