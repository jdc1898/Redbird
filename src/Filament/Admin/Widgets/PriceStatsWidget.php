<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Price;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PriceStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    public static function canView(): bool
    {
        return str_contains(request()->url(), '/prices');
    }

    protected function getStats(): array
    {
        $prices = Price::withTrashed();

        return [
            Stat::make('Total Prices', $prices->count())
                ->icon('heroicon-m-squares-2x2')
                ->color('gray'),

            Stat::make('Active Prices', $prices->where('active', true)->whereNull('deleted_at')->count())
                ->icon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Archived Prices', $prices->whereNotNull('deleted_at')->count())
                ->icon('heroicon-o-archive-box')
                ->color('danger'),
        ];
    }
}
