<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class BannerSlide extends Component
{
    public ?string $link;
    public string $image;
    public string $name;

    public function __construct(
        ?string $link = null,
        string  $image,
        string  $name = ''
    )
    {
        $this->link = $link ?: null; // на всякий случай приведём пустую строку к null
        $this->image = $image;
        $this->name = $name;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.banner-slide');
    }
}
