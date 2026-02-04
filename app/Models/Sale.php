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
        'debtor_id',
        'receipt_number',
        'total_price',
        'total_qty',
        'date',
        'items',
    ];

    protected $casts = [
        'items' => 'array',  // ← Это главное - автоматически сохраняет весь массив
        'total_price' => 'decimal:2',
        'total_qty' => 'decimal:3',
    ];
    /**
     * Get the shopper that owns the sale.
     */
    public function shopper(): BelongsTo
    {
        return $this->belongsTo(Shopper::class);
    }

    /**
     * Get the cashier that processed the sale.
     */
    public function cashier(): BelongsTo
    {
        return $this->belongsTo(Cashier::class);
    }

    /**
     * Get the shift during which the sale was made.
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Get the debtor associated with the sale.
     */
    public function debtor(): BelongsTo
    {
        return $this->belongsTo(Debtor::class);
    }
}
