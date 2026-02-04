<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Debtor extends Model
{
    protected $fillable = [
        'counterparty_id',
        'name',
        'amount',
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

    public function counterparty(): BelongsTo
    {
        return $this->belongsTo(Counterparty::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
}
