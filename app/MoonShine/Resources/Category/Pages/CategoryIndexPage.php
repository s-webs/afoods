<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Category\Pages;

use App\Models\Category;
use Leeto\MoonShineTree\View\Components\TreeComponent;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use App\MoonShine\Resources\Category\CategoryResource;
use MoonShine\UI\Components\Metrics\Wrapped\ValueMetric;


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

    protected function metrics(): array
    {
        return [
            ValueMetric::make('Категорий')
                ->value(fn() => Category::count())
        ];
    }
}
