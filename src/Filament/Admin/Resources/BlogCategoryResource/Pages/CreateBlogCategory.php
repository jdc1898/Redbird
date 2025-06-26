<?php

namespace Fullstack\Redbird\Filament\Admin\Resources\BlogCategoryResource\Pages;

use Fullstack\Redbird\Filament\Admin\Resources\BlogCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBlogCategory extends CreateRecord
{
    protected static string $resource = BlogCategoryResource::class;
}
