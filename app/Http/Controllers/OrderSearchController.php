<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Shopper;
use App\Models\User;
use Illuminate\Http\Request;

class OrderSearchController extends Controller
{
    /**
     * Show the order search page.
     */
    public function index()
    {
        return view('orders.search');
    }

    /**
     * Search orders by phone or email.
     */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        // At least one field must be provided
        if (empty($validated['phone']) && empty($validated['email'])) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['search' => 'Необходимо указать телефон или email']);
        }

        $shopperIds = [];

        // Search by phone
        if (!empty($validated['phone'])) {
            $shoppersByPhone = Shopper::where('phone', $validated['phone'])->pluck('id');
            $shopperIds = array_merge($shopperIds, $shoppersByPhone->toArray());
        }

        // Search by email (through User -> Shopper)
        if (!empty($validated['email'])) {
            $user = User::where('email', $validated['email'])->first();
            if ($user) {
                $shopper = Shopper::where('user_id', $user->id)->first();
                if ($shopper) {
                    $shopperIds[] = $shopper->id;
                }
            }
        }

        // Remove duplicates
        $shopperIds = array_unique($shopperIds);

        if (empty($shopperIds)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['search' => 'Заказы не найдены. Проверьте правильность введенных данных.']);
        }

        // Get orders for found shoppers
        $sales = Sale::whereIn('shopper_id', $shopperIds)
            ->orderBy('date', 'desc')
            ->paginate(15);

        return view('orders.search-results', [
            'sales' => $sales,
            'searchPhone' => $validated['phone'] ?? '',
            'searchEmail' => $validated['email'] ?? '',
        ]);
    }

    /**
     * Show order details (accessible for guests).
     */
    public function show(int $saleId, Request $request)
    {
        $sale = Sale::findOrFail($saleId);

        // Verify access by phone or email
        $hasAccess = false;
        $shopper = $sale->shopper;

        if ($shopper) {
            // Check by phone
            if ($request->has('phone') && $shopper->phone === $request->phone) {
                $hasAccess = true;
            }

            // Check by email (through User)
            if (!$hasAccess && $request->has('email') && $shopper->user_id) {
                $user = User::find($shopper->user_id);
                if ($user && $user->email === $request->email) {
                    $hasAccess = true;
                }
            }
        }

        if (!$hasAccess) {
            return redirect()->route('orders.search')
                ->withErrors(['access' => 'Для просмотра заказа необходимо указать телефон или email, указанные при оформлении заказа.']);
        }

        return view('orders.show', [
            'sale' => $sale,
        ]);
    }
}
