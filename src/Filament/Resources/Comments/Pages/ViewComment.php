<?php

namespace Wsmallnews\Comment\Filament\Resources\Comments\Pages;

use Filament\Resources\Pages\ViewRecord;
use Wsmallnews\Comment\Filament\Resources\Comments\CommentResource;
use Wsmallnews\Support\Filament\Resources\Concerns\Pages\Scopeable;

class ViewComment extends ViewRecord
{
    use Scopeable;

    protected static string $resource = CommentResource::class;

    protected string $view = 'sn-comment::filament.resources.comments.pages.view-comment';
}
