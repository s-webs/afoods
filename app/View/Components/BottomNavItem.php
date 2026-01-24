<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Illuminate\Support\Facades\Route;

class BottomNavItem extends Component
{
    public string $name;
    public string $icon;
    public string $routeName;
    public bool $isActive;

    public function __construct(string $name, string $icon, string $routeName = 'home')
    {
        $this->name = $name;
        $this->icon = $icon;
        $this->routeName = $routeName;
        $this->isActive = $this->checkActive($routeName);
    }

    private function checkActive(string $routeName): bool
    {
        $currentRoute = Route::currentRouteName();
        
        // Exact match
        if ($currentRoute === $routeName) {
            return true;
        }
        
        // For profile routes, check if current route starts with 'profile'
        if ($routeName === 'profile.show' && str_starts_with($currentRoute ?? '', 'profile')) {
            return true;
        }
        
        // For home route, check if it's exactly 'home'
        if ($routeName === 'home' && $currentRoute === 'home') {
            return true;
        }
        
        return false;
    }

    public function render(): View|Closure|string
    {
        return view('components.bottom-nav-item');
    }
}
