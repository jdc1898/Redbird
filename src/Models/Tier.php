<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tier extends Model
{
    protected $guarded = [];

    protected $casts = [
        'unit_amount' => 'integer',
        'flat_amount' => 'integer',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}
