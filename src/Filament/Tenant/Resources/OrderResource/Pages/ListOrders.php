<?php

namespace App\Filament\Tenant\Resources\OrderResource\Pages;

use App\Filament\Tenant\Resources\OrderResource;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),

            'success' => Tab::make('Success')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('status', 'success');
                }),

            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('status', 'pending');
                }),

            'failed' => Tab::make('Failed')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('status', 'failed');
                }),

        ];
    }
}
