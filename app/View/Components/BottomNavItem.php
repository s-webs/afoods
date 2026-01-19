<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class BottomNavItem extends Component
{
    public string $name;
    public string $icon;
    public string $routeName;

    public function __construct(string $name, string $icon, string $routeName = 'home')
    {
        $this->name = $name;
        $this->icon = $icon;
        $this->routeName = $routeName;
    }

    public function render(): View|Closure|string
    {
        return view('components.bottom-nav-item');
    }
}
