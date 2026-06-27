<?php

namespace Wsmallnews\Comment\Filament\Resources\Comments;

use BezhanSalleh\PluginEssentials\Concerns;
use Wsmallnews\Comment\CommentPlugin;
use Wsmallnews\Comment\Filament\Resources\Comments\Pages\ListComments;
use Wsmallnews\Comment\Filament\Resources\Comments\Pages\ViewComment;
use Wsmallnews\Comment\Support\Utils;
use Wsmallnews\Support\Concerns\Resource\HasCustomProperties;

final class CommentResource extends BaseResource
{
    use Concerns\Resource\BelongsToParent;
    use Concerns\Resource\BelongsToTenant;
    use Concerns\Resource\HasGlobalSearch;
    use Concerns\Resource\HasLabels;
    use Concerns\Resource\HasNavigation;
    use HasCustomProperties;

    public static function getPages(): array
    {
        return [
            'index' => ListComments::route('/'),
            'view' => ViewComment::route('/{record}'),
        ];
    }

    public static function getScopeType(): string
    {
        return self::getCustomScopeType() ?? Utils::getScopeType();
    }

    public static function getScopeId(): int
    {
        return self::getCustomScopeId() ?? Utils::getScopeId();
    }

    public static function getEssentialsPlugin(): ?CommentPlugin
    {
        return CommentPlugin::get();
    }
}
