<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 旅程とメンバーの関連付けを管理する中間テーブル。どのメンバーがどの旅程に参加するかを記録する。
 *
 *
 * @property int $id
 * @property int $itinerary_id
 * @property int $member_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Itinerary $itinerary
 * @property-read \App\Models\Member $member
 *
 * @method static \Database\Factories\ItineraryMemberFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItineraryMember newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItineraryMember newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItineraryMember query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItineraryMember whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItineraryMember whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItineraryMember whereItineraryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItineraryMember whereMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItineraryMember whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ItineraryMember extends Model
{
    /** @use HasFactory<\Database\Factories\ItineraryMemberFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'itinerary_member';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'itinerary_id',
        'member_id',
    ];

    /**
     * Get the itinerary that owns the pivot.
     */
    public function itinerary()
    {
        return $this->belongsTo(Itinerary::class);
    }

    /**
     * Get the member that owns the pivot.
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
