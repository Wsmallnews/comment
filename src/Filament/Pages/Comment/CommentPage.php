<?php

namespace Wsmallnews\Comment\Filament\Pages\Comment;

use BezhanSalleh\PluginEssentials\Concerns;
use Wsmallnews\Comment\CommentPlugin;
use Wsmallnews\Comment\Support\Utils;
use Wsmallnews\Support\Concerns\Resource\HasCustomProperties;
use Wsmallnews\Support\Enums\ContentType;

final class CommentPage extends Base
{
    use Concerns\Resource\BelongsToParent;
    use Concerns\Resource\BelongsToTenant;
    use Concerns\Resource\HasGlobalSearch;
    use Concerns\Resource\HasLabels;
    use Concerns\Resource\HasNavigation;
    use HasCustomProperties;

    public static function getScopeType(): string
    {
        return self::getCustomScopeType() ?? Utils::getScopeType();
    }

    public static function getScopeId(): int
    {
        return self::getCustomScopeId() ?? Utils::getScopeId();
    }

    public static function getContentType(): ContentType
    {
        return self::getCustomProperty('contentType') ?? Utils::getDefaultContentType();
    }

    public static function getEmptyLabel(): ?string
    {
        return self::getCustomProperty('emptyLabel') ?? parent::getEmptyLabel();
    }

    public static function getEmptyTipLabel(): ?string
    {
        return self::getCustomProperty('emptyTipLabel') ?? parent::getEmptyTipLabel();
    }

    public static function getEssentialsPlugin(): ?CommentPlugin
    {
        return CommentPlugin::get();
    }
}
