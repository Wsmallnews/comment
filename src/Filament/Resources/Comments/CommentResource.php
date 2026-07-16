<?php

namespace Wsmallnews\Comment\Filament\Resources\Comments;

use Wsmallnews\Comment\CommentPlugin;
use Wsmallnews\Comment\Filament\Resources\Comments\Pages\ListComments;
use Wsmallnews\Comment\Filament\Resources\Comments\Pages\ViewComment;
use Wsmallnews\Support\Filament\Concerns\CanBeConfigured;
use Wsmallnews\Support\Filament\Resources\ResourceConfiguration;

final class CommentResource extends BaseResource
{
    use CanBeConfigured;

    protected static ?string $configurationClass = ResourceConfiguration::class;

    public static function getPages(): array
    {
        return [
            'index' => ListComments::route('/'),
            'view' => ViewComment::route('/{record}'),
        ];
    }

    public static function getEssentialsPlugin(): ?CommentPlugin
    {
        return CommentPlugin::get();
    }
}
