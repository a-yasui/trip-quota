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
 * @property int $uploaded_by_member_id
 * @property string $title
 * @property string $file_path
 * @property string $file_type
 * @property int $file_size
 * @property string|null $category
 * @property string|null $description
 * @property bool $is_shared_with_all
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Member> $sharedWithMembers
 * @property-read int|null $shared_with_members_count
 * @property-read \App\Models\TravelPlan $travelPlan
 * @property-read \App\Models\Member $uploadedByMember
 * @method static \Database\Factories\TravelDocumentFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelDocument newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelDocument newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelDocument ofCategory($category)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelDocument ofType($type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelDocument onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelDocument query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelDocument sharedWithAll()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelDocument whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelDocument whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelDocument whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelDocument whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelDocument whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelDocument whereFileSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelDocument whereFileType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelDocument whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelDocument whereIsSharedWithAll($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelDocument whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelDocument whereTravelPlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelDocument whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelDocument whereUploadedByMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelDocument withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TravelDocument withoutTrashed()
 * @mixin \Eloquent
 */
class TravelDocument extends Model
{
    /** @use HasFactory<\Database\Factories\TravelDocumentFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'travel_plan_id',
        'uploaded_by_member_id',
        'title',
        'file_path',
        'file_type',
        'file_size',
        'category',
        'description',
        'is_shared_with_all',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'file_size' => 'integer',
        'is_shared_with_all' => 'boolean',
    ];

    /**
     * Get the travel plan that owns the document.
     */
    public function travelPlan()
    {
        return $this->belongsTo(TravelPlan::class);
    }

    /**
     * Get the member who uploaded the document.
     */
    public function uploadedByMember()
    {
        return $this->belongsTo(Member::class, 'uploaded_by_member_id');
    }

    /**
     * Get the members who can view the document.
     */
    public function sharedWithMembers()
    {
        return $this->belongsToMany(Member::class, 'document_member')
            ->withTimestamps();
    }

    /**
     * Scope a query to only include documents of a specific category.
     */
    public function scopeOfCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to only include documents of a specific file type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('file_type', $type);
    }

    /**
     * Scope a query to only include documents shared with all members.
     */
    public function scopeSharedWithAll($query)
    {
        return $query->where('is_shared_with_all', true);
    }
}
