<?php

namespace Fullstack\Redbird\Filament\Admin\Resources\RoadmapItemResource\Pages;

use Fullstack\Redbird\Filament\Admin\Resources\RoadmapItemResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListRoadmapItems extends ListRecords
{
    protected static string $resource = RoadmapItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getDefaultActiveTab(): ?string
    {
        return 'approved';
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),

            'pending' => Tab::make('Pending Approval')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('status', 'pending');
                }),

            'approved' => Tab::make('Approved')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('status', 'approved');
                }),

            'in-progress' => Tab::make('In Progress')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('status', 'in-progress');
                }),

            'completed' => Tab::make('Completed')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('status', 'completed');
                }),

            'cancelled' => Tab::make('Cancelled')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('status', 'cancelled');
                }),

            'rejected' => Tab::make('Rejected')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('status', 'rejected');
                }),
        ];
    }
}
