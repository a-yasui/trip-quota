<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $travel_plan_id
 * @property int|null $group_id
 * @property int $created_by_member_id
 * @property string $title
 * @property string|null $description
 * @property \Illuminate\Support\Carbon $date
 * @property \Illuminate\Support\Carbon|null $start_time
 * @property \Illuminate\Support\Carbon|null $end_time
 * @property string $timezone
 * @property \App\Enums\TransportationType|null $transportation_type
 * @property string|null $airline
 * @property string|null $flight_number
 * @property \Illuminate\Support\Carbon|null $departure_time
 * @property \Illuminate\Support\Carbon|null $arrival_time
 * @property string|null $departure_location
 * @property string|null $arrival_location
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $train_line
 * @property string|null $departure_station
 * @property string|null $arrival_station
 * @property string|null $train_type
 * @property string|null $departure_terminal
 * @property string|null $arrival_terminal
 * @property string|null $company
 * @property string|null $departure_airport
 * @property string|null $arrival_airport
 * @property string|null $location
 * @property \Illuminate\Support\Carbon|null $arrival_date
 * @property-read \App\Models\Member $createdBy
 * @property-read string|null $route_info
 * @property-read array $transportation_details
 * @property-read string $transportation_icon
 * @property-read string|null $transportation_summary
 * @property-read string $transportation_type_name
 * @property-read \App\Models\Group|null $group
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Member> $members
 * @property-read int|null $members_count
 * @property-read \App\Models\TravelPlan $travelPlan
 * @method static \Database\Factories\ItineraryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereAirline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereArrivalAirport($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereArrivalDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereArrivalLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereArrivalStation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereArrivalTerminal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereArrivalTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereCompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereCreatedByMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereDepartureAirport($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereDepartureLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereDepartureStation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereDepartureTerminal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereDepartureTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereFlightNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereTrainLine($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereTrainType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereTransportationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereTravelPlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereUpdatedAt($value)
 */
	class Itinerary extends \Eloquent {}
}

