<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Slide;

use Illuminate\Database\Eloquent\Model;
use App\Models\Slide;
use App\MoonShine\Resources\Slide\Pages\SlideIndexPage;
use App\MoonShine\Resources\Slide\Pages\SlideFormPage;
use App\MoonShine\Resources\Slide\Pages\SlideDetailPage;

use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Contracts\Core\PageContract;

/**
 * @extends ModelResource<Slide, SlideIndexPage, SlideFormPage, SlideDetailPage>
 */
class SlideResource extends ModelResource
{
    protected string $model = Slide::class;

    protected string $title = 'Баннеры';

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            SlideIndexPage::class,
            SlideFormPage::class,
            SlideDetailPage::class,
        ];
    }
}
