<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class BannerSlider extends Component
{
    public Collection $slides;

    public function __construct($slides = null)
    {
        $this->slides = collect($slides);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.banner-slider');
    }
}
