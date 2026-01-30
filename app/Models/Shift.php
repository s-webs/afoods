<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $fillable = [
        'opened_at',
        'closed_at',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];
}
