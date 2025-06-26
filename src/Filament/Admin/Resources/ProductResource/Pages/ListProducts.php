<?php

namespace Fullstack\Redbird\Filament\Admin\Resources\ProductResource\Pages;

use Fullstack\Redbird\Filament\Admin\Resources\ProductResource;
use App\Models\Product;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderWidgets(): array
    {
        return ProductResource::getHeaderWidgets();
    }

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    public function mount(): void
    {
        parent::mount();

        // Log the active tab when the page is mounted
        Log::info('Page mounted with tab:', ['tab' => $this->activeTab]);
    }

    protected function getTableQuery(): Builder
    {
        DB::enableQueryLog();

        $query = parent::getTableQuery();
        Log::info('Base query:', ['sql' => $query->toSql(), 'bindings' => $query->getBindings()]);

        // If we're on the archived tab, let's check what records exist
        if ($this->activeTab === 'archived') {
            $records = Product::onlyTrashed()->get();
            Log::info('Available archived records:', [
                'count' => $records->count(),
                'records' => $records->toArray(),
            ]);
        }

        DB::disableQueryLog();

        return $query;
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Products')
                ->icon('heroicon-m-squares-2x2')
                ->modifyQueryUsing(fn (Builder $query) => $query->withoutTrashed()),
            'active' => Tab::make('Active')
                ->icon('heroicon-m-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->withoutTrashed()->where('active', true)),
            'inactive' => Tab::make('Inactive')
                ->icon('heroicon-m-x-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->withoutTrashed()->where('active', false)),
            'synced' => Tab::make('Synced')
                ->icon('heroicon-m-check-badge')
                ->modifyQueryUsing(fn (Builder $query) => $query->withoutTrashed()->where('is_synced', true)),
            'not_synced' => Tab::make('Not Synced')
                ->icon('heroicon-m-exclamation-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->withoutTrashed()->where('is_synced', false)),
            'archived' => Tab::make('Archived')
                ->icon('heroicon-m-archive-box')
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed()->orderBy('deleted_at', 'desc')),
        ];
    }
}
