<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Shopper;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    /**
     * Show the user profile.
     */
    public function show()
    {
        if (!auth()->check()) {
            return view('auth.profile.guest');
        }

        $user = auth()->user();
        $shopper = $user->getOrCreateShopper();
        
        // Get user's orders (sales) through shopper
        $sales = Sale::where('shopper_id', $shopper->id)
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get();

        return view('auth.profile.show', [
            'user' => $user,
            'sales' => $sales,
        ]);
    }

    /**
     * Show the profile edit form.
     */
    public function edit()
    {
        $user = auth()->user();
        $shopper = $user->getOrCreateShopper();

        return view('auth.profile.edit', [
            'user' => $user,
            'shopper' => $shopper,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        $user = auth()->user();
        $shopper = $user->getOrCreateShopper();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        // Update phone in shopper
        $shopper->update([
            'phone' => $validated['phone'] ?? null,
        ]);

        return redirect()->route('profile.show')->with('status', 'Профиль успешно обновлен!');
    }

    /**
     * Show the password change form.
     */
    public function editPassword()
    {
        return view('auth.profile.edit-password');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('profile.show')->with('status', 'Пароль успешно изменен!');
    }

    /**
     * Show order history page.
     */
    public function orders()
    {
        $user = auth()->user();
        $shopper = $user->getOrCreateShopper();
        
        $sales = Sale::where('shopper_id', $shopper->id)
            ->orderBy('date', 'desc')
            ->paginate(15);

        return view('auth.profile.orders', [
            'sales' => $sales,
        ]);
    }

    /**
     * Show order details.
     */
    public function orderShow(int $saleId)
    {
        $user = auth()->user();
        $shopper = $user->getOrCreateShopper();
        
        $sale = Sale::where('id', $saleId)
            ->where('shopper_id', $shopper->id)
            ->firstOrFail();

        return view('auth.profile.order-show', [
            'sale' => $sale,
        ]);
    }
}
