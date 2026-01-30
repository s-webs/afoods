<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cashier extends Model
{
    protected $fillable = [
        'user_id',
        'uuid',
        'name',
        'device_id',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];
}
