<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProductStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    public static function canView(): bool
    {
        return str_contains(request()->url(), '/products');
    }

    protected function getStats(): array
    {
        $products = Product::withTrashed();

        return [
            Stat::make('Total Products', $products->count())
                ->icon('heroicon-m-squares-2x2')
                ->color('gray'),

            Stat::make('Active Products', $products->where('active', true)->whereNull('deleted_at')->count())
                ->icon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Archived Products', $products->whereNotNull('deleted_at')->count())
                ->icon('heroicon-o-archive-box')
                ->color('danger'),
        ];
    }
}
