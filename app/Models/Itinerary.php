<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
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
 * @property string|null $transportation_type
 * @property string|null $airline
 * @property string|null $flight_number
 * @property \Illuminate\Support\Carbon|null $departure_time
 * @property \Illuminate\Support\Carbon|null $arrival_time
 * @property string|null $departure_location
 * @property string|null $arrival_location
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Member $createdBy
 * @property-read \App\Models\Group|null $group
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Member> $members
 * @property-read int|null $members_count
 * @property-read \App\Models\TravelPlan $travelPlan
 *
 * @method static \Database\Factories\ItineraryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereAirline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereArrivalLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereArrivalTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereCreatedByMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereDepartureLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereDepartureTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereFlightNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereTransportationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereTravelPlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Itinerary whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Itinerary extends Model
{
    use HasFactory;

    protected $fillable = [
        'travel_plan_id',
        'group_id',
        'created_by_member_id',
        'title',
        'description',
        'date',
        'start_time',
        'end_time',
        'timezone',
        'transportation_type',
        'airline',
        'flight_number',
        'departure_time',
        'arrival_time',
        'departure_location',
        'arrival_location',
        'location',
        'train_line',
        'departure_station',
        'arrival_station',
        'train_type',
        'departure_terminal',
        'arrival_terminal',
        'company',
        'departure_airport',
        'arrival_airport',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'departure_time' => 'datetime',
        'arrival_time' => 'datetime',
    ];

    public function travelPlan()
    {
        return $this->belongsTo(TravelPlan::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(Member::class, 'created_by_member_id');
    }

    public function members()
    {
        return $this->belongsToMany(Member::class);
    }

    /**
     * 移動手段の日本語名を取得
     */
    public function getTransportationTypeNameAttribute(): string
    {
        return match($this->transportation_type) {
            'walking' => '徒歩',
            'bike' => '自転車',
            'car' => '車',
            'bus' => 'バス',
            'train' => '電車',
            'ferry' => 'フェリー',
            'airplane' => '飛行機',
            default => '未設定'
        };
    }

    /**
     * 移動手段詳細情報を取得
     */
    public function getTransportationDetailsAttribute(): array
    {
        return match($this->transportation_type) {
            'airplane' => [
                'airline' => $this->airline,
                'flight_number' => $this->flight_number,
                'departure_airport' => $this->departure_airport,
                'arrival_airport' => $this->arrival_airport,
            ],
            'train' => [
                'line' => $this->train_line,
                'departure_station' => $this->departure_station,
                'arrival_station' => $this->arrival_station,
                'train_type' => $this->train_type,
            ],
            'bus', 'ferry' => [
                'company' => $this->company,
                'departure_terminal' => $this->departure_terminal,
                'arrival_terminal' => $this->arrival_terminal,
            ],
            default => []
        };
    }

    /**
     * 移動手段詳細のサマリー文字列を取得
     */
    public function getTransportationSummaryAttribute(): ?string
    {
        return match($this->transportation_type) {
            'airplane' => $this->airline && $this->flight_number 
                ? "{$this->airline} {$this->flight_number}" 
                : null,
            'train' => $this->train_line && $this->train_type
                ? "{$this->train_line}（{$this->train_type}）"
                : $this->train_line,
            'bus', 'ferry' => $this->company,
            default => null
        };
    }

    /**
     * 出発地・到着地のサマリーを取得
     */
    public function getRouteInfoAttribute(): ?string
    {
        $departure = match($this->transportation_type) {
            'airplane' => $this->departure_airport,
            'train' => $this->departure_station,
            'bus', 'ferry' => $this->departure_terminal,
            default => $this->departure_location
        };

        $arrival = match($this->transportation_type) {
            'airplane' => $this->arrival_airport,
            'train' => $this->arrival_station,
            'bus', 'ferry' => $this->arrival_terminal,
            default => $this->arrival_location
        };

        if ($departure && $arrival) {
            return "{$departure} → {$arrival}";
        }

        return $departure ?: $arrival;
    }
}
