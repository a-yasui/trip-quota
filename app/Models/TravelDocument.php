<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Member> $members
 * @property-read int|null $members_count
 *
 * @method static \Database\Factories\TravelDocumentFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelDocument newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelDocument newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelDocument query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelDocument whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelDocument whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelDocument whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class TravelDocument extends Model
{
    use HasFactory;

    protected $fillable = [];

    public function members()
    {
        return $this->belongsToMany(Member::class, 'document_member');
    }
}
