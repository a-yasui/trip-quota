<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 宿泊先とメンバーの関連付けを管理する中間テーブル。どのメンバーがどの宿泊施設に滞在するかを記録する。
 * 
 *
 * @property int $id
 * @property int $accommodation_id
 * @property int $member_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Accommodation $accommodation
 * @property-read \App\Models\Member $member
 * @method static \Database\Factories\AccommodationMemberFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationMember newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationMember newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationMember query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationMember whereAccommodationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationMember whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationMember whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationMember whereMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccommodationMember whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AccommodationMember extends Model
{
    /** @use HasFactory<\Database\Factories\AccommodationMemberFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'accommodation_member';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'accommodation_id',
        'member_id',
    ];

    /**
     * Get the accommodation that owns the pivot.
     */
    public function accommodation()
    {
        return $this->belongsTo(Accommodation::class);
    }

    /**
     * Get the member that owns the pivot.
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
