<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemBranchGroupKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'group_id',
        'is_active',
        'expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
