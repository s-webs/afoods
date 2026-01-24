<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class BreadcrumbsItem extends Component
{
    public string $name;
    public string $link;
    public string $icon;

    public function __construct(string $name, string $icon, string $link = '##')
    {
        $this->name = $name;
        $this->icon = $icon;
        $this->link = $link;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.breadcrumbs-item');
    }
}
