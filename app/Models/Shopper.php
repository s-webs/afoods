<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shopper extends Model
{
    protected $fillable = [
        'user_id',
        'phone',
        'addresses',
    ];

    protected $casts = [
        'addresses' => 'array',
    ];

    /**
     * Get the user that owns the shopper.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get or create shopper for the current user or guest.
     */
    public static function getOrCreate(?int $userId = null): self
    {
        if ($userId) {
            return static::firstOrCreate(
                ['user_id' => $userId],
                ['addresses' => []]
            );
        }

        // Для гостей создаем новую запись без user_id
        return static::create([
            'user_id' => null,
            'addresses' => [],
        ]);
    }

    /**
     * Add address to the addresses array.
     */
    public function addAddress(array $address): void
    {
        $addresses = $this->addresses ?? [];
        $addresses[] = array_merge($address, [
            'id' => uniqid(),
            'created_at' => now()->toDateTimeString(),
        ]);
        $this->update(['addresses' => $addresses]);
    }

    /**
     * Update address in the addresses array.
     */
    public function updateAddress(string $addressId, array $addressData): bool
    {
        $addresses = $this->addresses ?? [];

        foreach ($addresses as $key => $address) {
            if (($address['id'] ?? null) === $addressId) {
                $addresses[$key] = array_merge($address, $addressData, [
                    'updated_at' => now()->toDateTimeString(),
                ]);
                $this->update(['addresses' => $addresses]);
                return true;
            }
        }

        return false;
    }

    /**
     * Remove address from the addresses array.
     */
    public function removeAddress(string $addressId): bool
    {
        $addresses = $this->addresses ?? [];

        foreach ($addresses as $key => $address) {
            if (($address['id'] ?? null) === $addressId) {
                unset($addresses[$key]);
                $this->update(['addresses' => array_values($addresses)]);
                return true;
            }
        }

        return false;
    }

    /**
     * Get default address.
     */
    public function getDefaultAddress(): ?array
    {
        $addresses = $this->addresses ?? [];

        foreach ($addresses as $address) {
            if (($address['is_default'] ?? false) === true) {
                return $address;
            }
        }

        return $addresses[0] ?? null;
    }

    /**
     * Get the orders for the shopper.
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
}
