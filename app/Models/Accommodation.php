<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 宿泊先情報を管理するテーブル。ホテル名、住所、チェックイン・チェックアウト日、予約番号などの宿泊施設に関する情報を保存する。
 * 
 *
 * @property int $id
 * @property int $travel_plan_id
 * @property string $name
 * @property string $address
 * @property \Illuminate\Support\Carbon $check_in_date
 * @property \Illuminate\Support\Carbon $check_out_date
 * @property string|null $booking_reference
 * @property string|null $phone_number
 * @property string|null $website
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Member> $members
 * @property-read int|null $members_count
 * @property-read \App\Models\TravelPlan $travelPlan
 * @method static \Database\Factories\AccommodationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereBookingReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereCheckInDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereCheckOutDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereTravelPlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation whereWebsite($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accommodation withoutTrashed()
 * @mixin \Eloquent
 */
class Accommodation extends Model
{
    /** @use HasFactory<\Database\Factories\AccommodationFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'travel_plan_id',
        'name',
        'address',
        'check_in_date',
        'check_out_date',
        'booking_reference',
        'phone_number',
        'website',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
    ];

    /**
     * Get the travel plan that owns the accommodation.
     */
    public function travelPlan()
    {
        return $this->belongsTo(TravelPlan::class);
    }

    /**
     * Get the members staying at the accommodation.
     */
    public function members()
    {
        return $this->belongsToMany(Member::class, 'accommodation_member')
            ->withTimestamps();
    }
}
