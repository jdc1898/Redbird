<?php

namespace Fullstack\Redbird\Filament\Admin\Resources\BlogCategoryResource\Pages;

use Fullstack\Redbird\Filament\Admin\Resources\BlogCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBlogCategory extends EditRecord
{
    protected static string $resource = BlogCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
