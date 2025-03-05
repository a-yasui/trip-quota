<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 旅行計画の基本情報を管理するテーブル。タイトル、出発日、帰宅日、タイムゾーンなどの旅行の基本情報と、作成者および削除権限保持者の情報を保存する。
 * 
 *
 * @property int $id
 * @property string $title
 * @property int $creator_id
 * @property int $deletion_permission_holder_id
 * @property \Illuminate\Support\Carbon $departure_date
 * @property \Illuminate\Support\Carbon|null $return_date
 * @property string $timezone
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Accommodation> $accommodations
 * @property-read int|null $accommodations_count
 * @property-read \App\Models\User $creator
 * @property-read \App\Models\User $deletionPermissionHolder
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ExpenseSettlement> $expenseSettlements
 * @property-read int|null $expense_settlements_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Expense> $expenses
 * @property-read int|null $expenses_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Group> $groups
 * @property-read int|null $groups_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Itinerary> $itineraries
 * @property-read int|null $itineraries_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TravelDocument> $travelDocuments
 * @property-read int|null $travel_documents_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TravelLocation> $travelLocations
 * @property-read int|null $travel_locations_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelPlan active()
 * @method static \Database\Factories\TravelPlanFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelPlan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelPlan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelPlan onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelPlan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelPlan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelPlan whereCreatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelPlan whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelPlan whereDeletionPermissionHolderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelPlan whereDepartureDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelPlan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelPlan whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelPlan whereReturnDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelPlan whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelPlan whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelPlan whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelPlan withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelPlan withoutTrashed()
 * @mixin \Eloquent
 */
class TravelPlan extends Model
{
    /** @use HasFactory<\Database\Factories\TravelPlanFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'creator_id',
        'deletion_permission_holder_id',
        'departure_date',
        'return_date',
        'timezone',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'departure_date' => 'date',
        'return_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the creator of the travel plan.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Get the user who has deletion permission for the travel plan.
     */
    public function deletionPermissionHolder()
    {
        return $this->belongsTo(User::class, 'deletion_permission_holder_id');
    }

    /**
     * Get the groups for the travel plan.
     */
    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    /**
     * Get the accommodations for the travel plan.
     */
    public function accommodations()
    {
        return $this->hasMany(Accommodation::class);
    }

    /**
     * Get the itineraries for the travel plan.
     */
    public function itineraries()
    {
        return $this->hasMany(Itinerary::class);
    }

    /**
     * Get the expenses for the travel plan.
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Get the travel locations for the travel plan.
     */
    public function travelLocations()
    {
        return $this->hasMany(TravelLocation::class);
    }

    /**
     * Get the travel documents for the travel plan.
     */
    public function travelDocuments()
    {
        return $this->hasMany(TravelDocument::class);
    }

    /**
     * Get the expense settlements for the travel plan.
     */
    public function expenseSettlements()
    {
        return $this->hasMany(ExpenseSettlement::class);
    }

    /**
     * Scope a query to only include active travel plans.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
