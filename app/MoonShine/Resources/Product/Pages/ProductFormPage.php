<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Product\Pages;

use App\MoonShine\Resources\Category\CategoryResource;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Slug;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FormBuilderContract;
use MoonShine\TinyMce\Fields\TinyMce;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use App\MoonShine\Resources\Product\ProductResource;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\Layout\Column;
use MoonShine\UI\Components\Layout\Grid;
use MoonShine\UI\Components\Tabs;
use MoonShine\UI\Components\Tabs\Tab;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\Image;
use MoonShine\UI\Fields\Json;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;
use Throwable;


/**
 * @extends FormPage<ProductResource>
 */
class ProductFormPage extends FormPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Box::make([
                BelongsTo::make('Категория', 'category', 'name', resource: CategoryResource::class),
            ]),
            Box::make([
                Tabs::make([
                   Tab::make('Основная информация', [Grid::make([
                       Column::make([
                           Text::make('Штрих-код / Артикул', 'barcode'),
                           Text::make('Название', 'name'),
                           Text::make('Название (NEW)', 'new_name'),
                           Image::make('Картинки', 'images')
                               ->disk('public')
                               ->dir('uploads/products')
                               ->multiple()
                               ->removable(),
                           Grid::make([
                               Column::make([
                                   Number::make('Текущая цена', 'price_amount')->default(0)
                               ])->columnSpan(4),
                               Column::make([
                                   Number::make('Цена со скидкой', 'sale_price_amount')->default(0)
                               ])->columnSpan(4),
                               Column::make([
                                   Number::make('Количество в наличии', 'quantity')->default(0)
                               ])->columnSpan(4),
                           ]),
                           Slug::make('Slug', 'slug')->from('name'),
                       ])
                           ->columnSpan(6),
                       Column::make([
                           TinyMce::make('Описание', 'description'),
                       ])
                           ->columnSpan(6),
                   ]),]),
                   Tab::make('Спецификации', [
                       Json::make('Спецификации', 'specs'),
                       Json::make('Настройки', 'obj'),
                   ]),
                ]),
            ]),
        ];
    }

    protected function buttons(): ListOf
    {
        return parent::buttons();
    }

    protected function formButtons(): ListOf
    {
        return parent::formButtons();
    }

    protected function rules(DataWrapperContract $item): array
    {
        return [];
    }

    /**
     * @param FormBuilder $component
     *
     * @return FormBuilder
     */
    protected function modifyFormComponent(FormBuilderContract $component): FormBuilderContract
    {
        return $component;
    }

    /**
     * @return list<ComponentContract>
     * @throws Throwable
     */
    protected function topLayer(): array
    {
        return [
            ...parent::topLayer()
        ];
    }

    /**
     * @return list<ComponentContract>
     * @throws Throwable
     */
    protected function mainLayer(): array
    {
        return [
            ...parent::mainLayer()
        ];
    }

    /**
     * @return list<ComponentContract>
     * @throws Throwable
     */
    protected function bottomLayer(): array
    {
        return [
            ...parent::bottomLayer()
        ];
    }
}
