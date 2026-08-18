<?php

namespace App\Filament\Pages;

use App\Http\Controllers\HondaController;
use App\Support\HondaCategories;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class HondaCategoryVisibility extends Page
{
    protected string $view = 'filament.admin.pages.honda-category-visibility';

    protected static string|\UnitEnum|null $navigationGroup = 'Website';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationLabel = 'Honda Product Categories';

    protected static ?string $title = 'Honda Product Categories';

    protected static ?int $navigationSort = 5;

    public bool $show_offroad = true;

    public bool $show_onroad = true;

    public bool $show_workrange = true;

    public function mount(): void
    {
        $this->show_offroad   = HondaCategories::isVisible('offroad');
        $this->show_onroad    = HondaCategories::isVisible('onroad');
        $this->show_workrange = HondaCategories::isVisible('workrange');
    }

    /**
     * @return array<int, array{slug: string, label: string, property: string}>
     */
    public function categories(): array
    {
        return [
            ['slug' => 'offroad',   'label' => HondaController::labelForCategory('offroad'),   'property' => 'show_offroad'],
            ['slug' => 'onroad',    'label' => HondaController::labelForCategory('onroad'),    'property' => 'show_onroad'],
            ['slug' => 'workrange', 'label' => HondaController::labelForCategory('workrange'), 'property' => 'show_workrange'],
        ];
    }

    public function save(): void
    {
        HondaCategories::setVisible('offroad', $this->show_offroad);
        HondaCategories::setVisible('onroad', $this->show_onroad);
        HondaCategories::setVisible('workrange', $this->show_workrange);

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }
}
