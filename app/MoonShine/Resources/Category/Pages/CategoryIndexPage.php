<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Category\Pages;

use Leeto\MoonShineTree\View\Components\TreeComponent;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use App\MoonShine\Resources\Category\CategoryResource;


/**
 * @extends IndexPage<CategoryResource>
 */
class CategoryIndexPage extends IndexPage
{
    protected function mainLayer(): array
    {
        return [
            TreeComponent::make($this->getResource()),
        ];
    }
}
