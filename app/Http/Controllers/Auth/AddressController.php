<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Shopper;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    /**
     * Show the address management page.
     */
    public function index()
    {
        $user = auth()->user();
        $shopper = $user->shopper ?? Shopper::getOrCreate($user->id);
        $addresses = $shopper->addresses ?? [];

        return view('auth.profile.addresses.index', [
            'shopper' => $shopper,
            'addresses' => $addresses,
        ]);
    }

    /**
     * Show the form for creating a new address.
     */
    public function create()
    {
        return view('auth.profile.addresses.create', [
            'yandexApiKey' => env('YANDEX_MAPS_API_KEY', ''),
        ]);
    }

    /**
     * Store a newly created address.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'latitude' => ['required', 'numeric', 'between:43.0,43.5'],
            'longitude' => ['required', 'numeric', 'between:76.5,77.0'],
            'house' => ['nullable', 'string', 'max:50'],
            'apartment' => ['nullable', 'string', 'max:50'],
            'entrance' => ['nullable', 'string', 'max:50'],
            'floor' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:500'],
            'is_default' => ['boolean'],
        ]);

        $user = auth()->user();
        $shopper = $user->shopper ?? Shopper::getOrCreate($user->id);

        $addressData = [
            'id' => uniqid(),
            'title' => $validated['title'] ?? null,
            'address' => $validated['address'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'house' => $validated['house'] ?? null,
            'apartment' => $validated['apartment'] ?? null,
            'entrance' => $validated['entrance'] ?? null,
            'floor' => $validated['floor'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_default' => $validated['is_default'] ?? false,
            'created_at' => now()->toDateTimeString(),
        ];

        // Если это адрес по умолчанию, снимаем флаг с других адресов
        if ($addressData['is_default']) {
            $addresses = $shopper->addresses ?? [];
            foreach ($addresses as $key => $addr) {
                $addresses[$key]['is_default'] = false;
            }
            $shopper->update(['addresses' => $addresses]);
        }

        $shopper->addAddress($addressData);

        return redirect()->route('profile.addresses.index')
            ->with('status', 'Адрес успешно добавлен!');
    }

    /**
     * Show the form for editing an address.
     */
    public function edit(string $addressId)
    {
        $user = auth()->user();
        $shopper = $user->shopper ?? Shopper::getOrCreate($user->id);
        $addresses = $shopper->addresses ?? [];

        $address = null;
        foreach ($addresses as $addr) {
            if (($addr['id'] ?? null) === $addressId) {
                $address = $addr;
                break;
            }
        }

        if (!$address) {
            abort(404, 'Адрес не найден');
        }

        return view('auth.profile.addresses.edit', [
            'address' => $address,
            'addressId' => $addressId,
            'yandexApiKey' => env('YANDEX_MAPS_API_KEY', ''),
        ]);
    }

    /**
     * Update the specified address.
     */
    public function update(Request $request, string $addressId)
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'latitude' => ['required', 'numeric', 'between:43.0,43.5'],
            'longitude' => ['required', 'numeric', 'between:76.5,77.0'],
            'house' => ['nullable', 'string', 'max:50'],
            'apartment' => ['nullable', 'string', 'max:50'],
            'entrance' => ['nullable', 'string', 'max:50'],
            'floor' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:500'],
            'is_default' => ['boolean'],
        ]);

        $user = auth()->user();
        $shopper = $user->shopper ?? Shopper::getOrCreate($user->id);
        $addresses = $shopper->addresses ?? [];

        $found = false;
        foreach ($addresses as $key => $addr) {
            if (($addr['id'] ?? null) === $addressId) {
                // Если устанавливаем как адрес по умолчанию, снимаем флаг с других
                if ($validated['is_default']) {
                    foreach ($addresses as $k => $a) {
                        if ($k !== $key) {
                            $addresses[$k]['is_default'] = false;
                        }
                    }
                }

                $addresses[$key] = array_merge($addr, [
                    'title' => $validated['title'] ?? null,
                    'address' => $validated['address'],
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                    'house' => $validated['house'] ?? null,
                    'apartment' => $validated['apartment'] ?? null,
                    'entrance' => $validated['entrance'] ?? null,
                    'floor' => $validated['floor'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'is_default' => $validated['is_default'] ?? false,
                    'updated_at' => now()->toDateTimeString(),
                ]);
                $found = true;
                break;
            }
        }

        if (!$found) {
            abort(404, 'Адрес не найден');
        }

        $shopper->update(['addresses' => array_values($addresses)]);

        return redirect()->route('profile.addresses.index')
            ->with('status', 'Адрес успешно обновлен!');
    }

    /**
     * Remove the specified address.
     */
    public function destroy(string $addressId)
    {
        $user = auth()->user();
        $shopper = $user->shopper ?? Shopper::getOrCreate($user->id);

        $shopper->removeAddress($addressId);

        return redirect()->route('profile.addresses.index')
            ->with('status', 'Адрес успешно удален!');
    }

    /**
     * Set address as default.
     */
    public function setDefault(string $addressId)
    {
        $user = auth()->user();
        $shopper = $user->shopper ?? Shopper::getOrCreate($user->id);
        $addresses = $shopper->addresses ?? [];

        foreach ($addresses as $key => $addr) {
            if (($addr['id'] ?? null) === $addressId) {
                $addresses[$key]['is_default'] = true;
            } else {
                $addresses[$key]['is_default'] = false;
            }
        }

        $shopper->update(['addresses' => array_values($addresses)]);

        return redirect()->route('profile.addresses.index')
            ->with('status', 'Адрес по умолчанию установлен!');
    }
}
