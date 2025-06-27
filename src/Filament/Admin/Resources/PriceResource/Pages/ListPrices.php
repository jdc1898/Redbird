<?php

namespace App\Filament\Admin\Resources\PriceResource\Pages;

use App\Filament\Admin\Resources\PriceResource;
use App\Models\Price;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Components\Tab;
use App\Filament\Admin\Widgets\PriceStatsWidget;

class ListPrices extends ListRecords
{
    protected static string $resource = PriceResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            PriceStatsWidget::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Prices')
                ->icon('heroicon-m-squares-2x2'),
            'active' => Tab::make('Active')
                ->icon('heroicon-m-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('active', true)),
            'inactive' => Tab::make('Inactive')
                ->icon('heroicon-m-x-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('active', false)),
            'default' => Tab::make('Default')
                ->icon('heroicon-o-shield-check')
                ->modifyQueryUsing(fn (Builder $query) =>
                    $query->whereHas('product', function (Builder $query) {
                        $query->whereColumn('default_price', 'prices.id');
                    })
                ),
        ];
    }

    public function getDefaultActiveTab(): string
    {
        return 'all';
    }

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
