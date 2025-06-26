<?php

namespace Fullstack\Redbird\Filament\Admin\Resources\OrderResource\Pages;

use Fullstack\Redbird\Filament\Admin\Resources\OrderResource;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),

            'success' => Tab::make('Success')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('status', 'success');
                }),

            'refunded' => Tab::make('Refunded')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('status', 'refunded');
                }),

            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('status', 'pending');
                }),

            'failed' => Tab::make('Failed')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('status', 'failed');
                }),

            'disputed' => Tab::make('Disputed')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('status', 'disputed');
                }),
        ];
    }
}
