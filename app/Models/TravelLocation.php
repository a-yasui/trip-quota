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
 * @property int $group_id
 * @property int $added_by_member_id
 * @property string $name
 * @property string|null $address
 * @property string|null $google_maps_url
 * @property numeric|null $latitude
 * @property numeric|null $longitude
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $visit_datetime
 * @property string|null $category
 * @property string|null $image_path
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Member $addedByMember
 * @property-read \App\Models\Group $group
 * @property-read \App\Models\TravelPlan $travelPlan
 * @method static \Database\Factories\TravelLocationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelLocation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelLocation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelLocation ofCategory($category)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelLocation onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelLocation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelLocation visitingAfter($date)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelLocation visitingBefore($date)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelLocation whereAddedByMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelLocation whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelLocation whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelLocation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelLocation whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelLocation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelLocation whereGoogleMapsUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelLocation whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelLocation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelLocation whereImagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelLocation whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelLocation whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelLocation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelLocation whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelLocation whereTravelPlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelLocation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelLocation whereVisitDatetime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelLocation withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelLocation withoutTrashed()
 * @mixin \Eloquent
 */
class TravelLocation extends Model
{
    /** @use HasFactory<\Database\Factories\TravelLocationFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'travel_plan_id',
        'group_id',
        'added_by_member_id',
        'name',
        'address',
        'google_maps_url',
        'latitude',
        'longitude',
        'description',
        'visit_datetime',
        'category',
        'image_path',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'visit_datetime' => 'datetime',
    ];

    /**
     * Get the travel plan that owns the location.
     */
    public function travelPlan()
    {
        return $this->belongsTo(TravelPlan::class);
    }

    /**
     * Get the group that owns the location.
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the member who added the location.
     */
    public function addedByMember()
    {
        return $this->belongsTo(Member::class, 'added_by_member_id');
    }

    /**
     * Scope a query to only include locations of a specific category.
     */
    public function scopeOfCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to only include locations with a visit datetime after a given date.
     */
    public function scopeVisitingAfter($query, $date)
    {
        return $query->where('visit_datetime', '>=', $date);
    }

    /**
     * Scope a query to only include locations with a visit datetime before a given date.
     */
    public function scopeVisitingBefore($query, $date)
    {
        return $query->where('visit_datetime', '<=', $date);
    }
}
