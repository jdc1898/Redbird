<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailProvider extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'domain',
        'logo',
        'secret',
        'endpoint',
    ];
}
