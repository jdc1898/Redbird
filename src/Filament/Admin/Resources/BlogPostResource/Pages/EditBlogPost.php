<?php

namespace Fullstack\Redbird\Filament\Admin\Resources\BlogPostResource\Pages;

use Fullstack\Redbird\Filament\Admin\Resources\BlogPostResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBlogPost extends EditRecord
{
    protected static string $resource = BlogPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
