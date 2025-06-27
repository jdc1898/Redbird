<?php

namespace Fullstack\Redbird\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    /** @use HasFactory<\Database\Factories\BlogPostFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'content',
        'slug',
        'category_id',
        'user_id',
        'image',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'title' => 'string',
        'description' => 'string',
        'content' => 'string',
        'slug' => 'string',
        'category_id' => 'integer',
        'user_id' => 'integer',
        'image' => 'string',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
