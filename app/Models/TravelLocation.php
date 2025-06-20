<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Database\Factories\TravelLocationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelLocation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelLocation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelLocation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelLocation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelLocation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelLocation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class TravelLocation extends Model
{
    use HasFactory;

    protected $fillable = [];
}
