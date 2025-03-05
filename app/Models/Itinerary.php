<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 
 *
 * @property int $id
 * @property int $travel_plan_id
 * @property string $transportation_type
 * @property string $departure_location
 * @property string $arrival_location
 * @property \Illuminate\Support\Carbon $departure_time
 * @property \Illuminate\Support\Carbon $arrival_time
 * @property string|null $company_name
 * @property string|null $reference_number
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Member> $members
 * @property-read int|null $members_count
 * @property-read \App\Models\TravelPlan $travelPlan
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary arrivingBefore($date)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary departingAfter($date)
 * @method static \Database\Factories\ItineraryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary ofType($type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereArrivalLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereArrivalTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereDepartureLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereDepartureTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereReferenceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereTransportationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereTravelPlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary withoutTrashed()
 * @mixin \Eloquent
 */
class Itinerary extends Model
{
    /** @use HasFactory<\Database\Factories\ItineraryFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'travel_plan_id',
        'transportation_type',
        'departure_location',
        'arrival_location',
        'departure_time',
        'arrival_time',
        'company_name',
        'reference_number',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'departure_time' => 'datetime',
        'arrival_time' => 'datetime',
    ];

    /**
     * Get the travel plan that owns the itinerary.
     */
    public function travelPlan()
    {
        return $this->belongsTo(TravelPlan::class);
    }

    /**
     * Get the members for the itinerary.
     */
    public function members()
    {
        return $this->belongsToMany(Member::class, 'itinerary_member')
            ->withTimestamps();
    }

    /**
     * Scope a query to only include itineraries of a specific transportation type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('transportation_type', $type);
    }

    /**
     * Scope a query to only include itineraries with a departure time after a given date.
     */
    public function scopeDepartingAfter($query, $date)
    {
        return $query->where('departure_time', '>=', $date);
    }

    /**
     * Scope a query to only include itineraries with an arrival time before a given date.
     */
    public function scopeArrivingBefore($query, $date)
    {
        return $query->where('arrival_time', '<=', $date);
    }
}
