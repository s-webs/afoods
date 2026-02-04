<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Counterparty extends Model
{
    protected $fillable = [
        'name',
        'bin',
        'kbe',
        'iik',
        'bank_name',
        'bik',
        'address',
        'director',
        'phone',
        'email',
    ];
}
