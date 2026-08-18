<?php

namespace App\Filament\Pages;

use App\Support\YamahaDivisions;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class YamahaDivisionVisibility extends Page
{
    protected string $view = 'filament.admin.pages.yamaha-division-visibility';

    protected static string|\UnitEnum|null $navigationGroup = 'Website';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationLabel = 'Yamaha Product Categories';

    protected static ?string $title = 'Yamaha Product Categories';

    protected static ?int $navigationSort = 3;

    public bool $show_road = true;

    public bool $show_off_road = true;

    public bool $show_atv_rov = true;

    public bool $show_golf_car = true;

    public bool $show_watercraft = true;

    public function mount(): void
    {
        $this->show_road       = YamahaDivisions::isVisible('road');
        $this->show_off_road   = YamahaDivisions::isVisible('off-road');
        $this->show_atv_rov    = YamahaDivisions::isVisible('atv-rov');
        $this->show_golf_car   = YamahaDivisions::isVisible('golf-car');
        $this->show_watercraft = YamahaDivisions::isVisible('watercraft');
    }

    /**
     * @return array<string, array{slug: string, label: string, property: string}>
     */
    public function divisions(): array
    {
        $labels = config('yamaha_nav.groups');

        return [
            ['slug' => 'road',       'label' => $labels['road']['label'],       'property' => 'show_road'],
            ['slug' => 'off-road',   'label' => $labels['off-road']['label'],   'property' => 'show_off_road'],
            ['slug' => 'atv-rov',    'label' => $labels['atv-rov']['label'],    'property' => 'show_atv_rov'],
            ['slug' => 'golf-car',   'label' => $labels['golf-car']['label'],   'property' => 'show_golf_car'],
            ['slug' => 'watercraft', 'label' => $labels['watercraft']['label'], 'property' => 'show_watercraft'],
        ];
    }

    public function save(): void
    {
        YamahaDivisions::setVisible('road', $this->show_road);
        YamahaDivisions::setVisible('off-road', $this->show_off_road);
        YamahaDivisions::setVisible('atv-rov', $this->show_atv_rov);
        YamahaDivisions::setVisible('golf-car', $this->show_golf_car);
        YamahaDivisions::setVisible('watercraft', $this->show_watercraft);

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }
}
