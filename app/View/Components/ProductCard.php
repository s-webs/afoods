<?php

namespace App\View\Components;

use App\Models\Product;
use App\Services\CartService;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ProductCard extends Component
{
    public Product $product;
    public string $units;
    public string $currency;
    public int $cartQuantity;

    public function __construct(
        Product $product,
        string  $units = 'шт',
        string  $currency = '₸',
    )
    {
        $this->product = $product;
        $this->units = $units;
        $this->currency = $currency;
        $this->cartQuantity = CartService::getProductQuantity($product->id);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.product-card');
    }
}
