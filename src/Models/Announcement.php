<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    /** @use HasFactory<\Database\Factories\AnnouncementFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'is_active',
        'starts_at',
        'ends_at',
        'is_active',
        'is_dissmissible',
        'show_on_front_end',
        'show_on_user_dashboard',
        'show_for_customers',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_dismissable' => 'boolean',
        'show_on_front_end' => 'boolean',
        'show_on_user_dashboard' => 'boolean',
        'show_for_customers' => 'boolean',
    ];
}
