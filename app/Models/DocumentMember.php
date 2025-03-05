<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property int $travel_document_id
 * @property int $member_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Member $member
 * @property-read \App\Models\TravelDocument $travelDocument
 * @method static \Database\Factories\DocumentMemberFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentMember newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentMember newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentMember query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentMember whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentMember whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentMember whereMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentMember whereTravelDocumentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentMember whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class DocumentMember extends Model
{
    /** @use HasFactory<\Database\Factories\DocumentMemberFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'document_member';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'travel_document_id',
        'member_id',
    ];

    /**
     * Get the travel document that owns the pivot.
     */
    public function travelDocument()
    {
        return $this->belongsTo(TravelDocument::class, 'travel_document_id');
    }

    /**
     * Get the member that owns the pivot.
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
