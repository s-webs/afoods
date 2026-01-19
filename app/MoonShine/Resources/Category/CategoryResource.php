<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Category;

use Illuminate\Database\Eloquent\Model;
use App\Models\Category;
use App\MoonShine\Resources\Category\Pages\CategoryIndexPage;
use App\MoonShine\Resources\Category\Pages\CategoryFormPage;
use App\MoonShine\Resources\Category\Pages\CategoryDetailPage;

use Leeto\MoonShineTree\Resources\TreeResource;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Contracts\Core\PageContract;

/**
 * @extends ModelResource<Category, CategoryIndexPage, CategoryFormPage, CategoryDetailPage>
 */
class CategoryResource extends TreeResource
{
    protected string $model = Category::class;

    protected string $title = 'Категории';

    protected string $column = 'name';

    protected string $sortColumn = 'sort_order';

    public function treeKey(): ?string
    {
        return 'parent_id'; // Foreign key for parent-child relationship
    }

    public function sortKey(): string
    {
        return 'sort_order'; // Column for sorting
    }

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            CategoryIndexPage::class,
            CategoryFormPage::class,
            CategoryDetailPage::class,
        ];
    }
}
