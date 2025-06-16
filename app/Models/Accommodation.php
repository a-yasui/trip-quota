<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $travel_plan_id
 * @property int $created_by_member_id
 * @property string $name
 * @property string|null $address
 * @property \Illuminate\Support\Carbon $check_in_date
 * @property \Illuminate\Support\Carbon $check_out_date
 * @property \Illuminate\Support\Carbon|null $check_in_time
 * @property \Illuminate\Support\Carbon|null $check_out_time
 * @property numeric|null $price_per_night
 * @property string $currency
 * @property string|null $notes
 * @property string|null $confirmation_number
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Member $createdBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Member> $members
 * @property-read int|null $members_count
 * @property-read \App\Models\TravelPlan $travelPlan
 *
 * @method static \Database\Factories\AccommodationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereCheckInDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereCheckInTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereCheckOutDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereCheckOutTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereConfirmationNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereCreatedByMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation wherePricePerNight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereTravelPlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Accommodation extends Model
{
    use HasFactory;

    protected $fillable = [
        'travel_plan_id',
        'created_by_member_id',
        'name',
        'address',
        'check_in_date',
        'check_out_date',
        'check_in_time',
        'check_out_time',
        'price_per_night',
        'currency',
        'notes',
        'confirmation_number',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'check_in_time' => 'datetime:H:i',
        'check_out_time' => 'datetime:H:i',
        'price_per_night' => 'decimal:2',
    ];

    public function travelPlan()
    {
        return $this->belongsTo(TravelPlan::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(Member::class, 'created_by_member_id');
    }

    public function members()
    {
        return $this->belongsToMany(Member::class, 'accommodation_members');
    }
}
