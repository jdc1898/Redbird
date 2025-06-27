<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoadmapItem extends Model
{
    /** @use HasFactory<\Database\Factories\RoadmapItemFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'status',
        'type',
        'upvotes',
        'user_id',
    ];

    protected $casts = [
        'status' => 'string',
        'type' => 'string',
        'upvotes' => 'integer',
        'user_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
