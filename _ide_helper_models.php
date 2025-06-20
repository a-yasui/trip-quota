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
 * @property string|null $timezone
 * @property-read \App\Models\Member $createdBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Member> $members
 * @property-read int|null $members_count
 * @property-read \App\Models\TravelPlan $travelPlan
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereTravelPlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereUpdatedAt($value)
 */
	class Accommodation extends \Eloquent {}
}

