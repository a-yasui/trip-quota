<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 旅行に参加するメンバー情報を管理するテーブル。登録ユーザーと未登録メンバー両方を扱い、各メンバーの到着日・出発日などの情報も保存する。
 * 
 *
 * @property int $id
 * @property string $name
 * @property string|null $email
 * @property int|null $user_id
 * @property int $group_id
 * @property \Illuminate\Support\Carbon|null $arrival_date
 * @property \Illuminate\Support\Carbon|null $departure_date
 * @property bool $is_registered
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Accommodation> $accommodations
 * @property-read int|null $accommodations_count
 * @property-read \App\Models\MemberAccountAssociation|null $accountAssociation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TravelLocation> $addedTravelLocations
 * @property-read int|null $added_travel_locations_count
 * @property-read \App\Models\Group $group
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Itinerary> $itineraries
 * @property-read int|null $itineraries_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ExpenseSettlement> $paidExpenseSettlements
 * @property-read int|null $paid_expense_settlements_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Expense> $paidExpenses
 * @property-read int|null $paid_expenses_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ExpenseSettlement> $receivedExpenseSettlements
 * @property-read int|null $received_expense_settlements_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Expense> $sharedExpenses
 * @property-read int|null $shared_expenses_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TravelDocument> $sharedTravelDocuments
 * @property-read int|null $shared_travel_documents_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TravelDocument> $uploadedTravelDocuments
 * @property-read int|null $uploaded_travel_documents_count
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member active()
 * @method static \Database\Factories\MemberFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member registered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereArrivalDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereDepartureDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereIsRegistered($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member withoutTrashed()
 * @mixin \Eloquent
 */
class Member extends Model
{
    /** @use HasFactory<\Database\Factories\MemberFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'user_id',
        'group_id',
        'arrival_date',
        'departure_date',
        'is_registered',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'arrival_date' => 'date',
        'departure_date' => 'date',
        'is_registered' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that owns the member.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the group that owns the member.
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the account association for the member.
     */
    public function accountAssociation()
    {
        return $this->hasOne(MemberAccountAssociation::class);
    }

    /**
     * Get the accommodations for the member.
     */
    public function accommodations()
    {
        return $this->belongsToMany(Accommodation::class, 'accommodation_member')
            ->withTimestamps();
    }

    /**
     * Get the itineraries for the member.
     */
    public function itineraries()
    {
        return $this->belongsToMany(Itinerary::class, 'itinerary_member')
            ->withTimestamps();
    }

    /**
     * Get the expenses paid by the member.
     */
    public function paidExpenses()
    {
        return $this->hasMany(Expense::class, 'payer_member_id');
    }

    /**
     * Get the expenses shared by the member.
     */
    public function sharedExpenses()
    {
        return $this->belongsToMany(Expense::class, 'expense_member')
            ->withPivot('share_amount', 'is_paid')
            ->withTimestamps();
    }

    /**
     * Get the travel locations added by the member.
     */
    public function addedTravelLocations()
    {
        return $this->hasMany(TravelLocation::class, 'added_by_member_id');
    }

    /**
     * Get the travel documents uploaded by the member.
     */
    public function uploadedTravelDocuments()
    {
        return $this->hasMany(TravelDocument::class, 'uploaded_by_member_id');
    }

    /**
     * Get the travel documents shared with the member.
     */
    public function sharedTravelDocuments()
    {
        return $this->belongsToMany(TravelDocument::class, 'document_member')
            ->withTimestamps();
    }

    /**
     * Get the expense settlements where the member is the payer.
     */
    public function paidExpenseSettlements()
    {
        return $this->hasMany(ExpenseSettlement::class, 'payer_member_id');
    }

    /**
     * Get the expense settlements where the member is the receiver.
     */
    public function receivedExpenseSettlements()
    {
        return $this->hasMany(ExpenseSettlement::class, 'receiver_member_id');
    }

    /**
     * Scope a query to only include active members.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include registered members.
     */
    public function scopeRegistered($query)
    {
        return $query->where('is_registered', true);
    }
}
