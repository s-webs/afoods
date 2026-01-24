<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'cashier_id',
        'shift_id',
        'shopper_id',
        'date',
        'receipt_number',
        'items',
        'total_price',
    ];

    protected $casts = [
        'items' => 'array',
        'date' => 'datetime',
        'total_price' => 'integer',
    ];

    /**
     * Get the shopper that owns the sale.
     */
    public function shopper(): BelongsTo
    {
        return $this->belongsTo(Shopper::class);
    }
}
