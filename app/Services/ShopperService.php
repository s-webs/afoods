<?php

namespace App\Services;

use App\Models\Shopper;

class ShopperService
{
    /**
     * Get or create shopper for current user or guest.
     */
    public static function getCurrentShopper(): Shopper
    {
        $userId = auth()->id();

        if ($userId) {
            // Для авторизованного пользователя
            $shopper = Shopper::where('user_id', $userId)->first();

            if (!$shopper) {
                $shopper = Shopper::create([
                    'user_id' => $userId,
                    'addresses' => [],
                ]);
            }

            return $shopper;
        }

        // Для гостя создаем новую запись
        return Shopper::create([
            'user_id' => null,
            'addresses' => [],
        ]);
    }

    /**
     * Create or update shopper when user adds address or phone.
     */
    public static function createOrUpdateFromData(array $data): Shopper
    {
        $userId = auth()->id();

        if ($userId) {
            // Для авторизованного пользователя
            $shopper = Shopper::firstOrCreate(
                ['user_id' => $userId],
                ['addresses' => []]
            );

            if (isset($data['phone'])) {
                $shopper->update(['phone' => $data['phone']]);
            }

            if (isset($data['address'])) {
                $shopper->addAddress($data['address']);
            }

            return $shopper;
        }

        // Для гостя создаем новую запись
        return Shopper::create([
            'user_id' => null,
            'phone' => $data['phone'] ?? null,
            'addresses' => isset($data['address']) ? [$data['address']] : [],
        ]);
    }
}
