<?php

namespace Fullstack\Redbird\Filament\Admin\Resources\BlogPostResource\Pages;

use Fullstack\Redbird\Filament\Admin\Resources\BlogPostResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBlogPost extends CreateRecord
{
    protected static string $resource = BlogPostResource::class;
}
