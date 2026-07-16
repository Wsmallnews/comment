<?php

namespace Wsmallnews\Comment\Filament\Pages\Comment;

use Wsmallnews\Comment\CommentPlugin;
use Wsmallnews\Comment\Enums\CommentStatus;
use Wsmallnews\Comment\Support\Utils;
use Wsmallnews\Support\Enums\ContentType;
use Wsmallnews\Support\Filament\Concerns\CanBeConfigured;
use Wsmallnews\Support\Filament\Pages\PageConfiguration;

final class CommentPage extends Base
{
    use CanBeConfigured;

    protected static ?string $configurationClass = PageConfiguration::class;

    public static function getContentType(): ContentType
    {
        return static::resolveCustomProperty('contentType') ?? Utils::getDefaultContentType();
    }

    public static function getCommentStatus(): CommentStatus
    {
        return static::resolveCustomProperty('commentStatus') ?? Utils::getDefaultCommentStatus();
    }

    public static function getEmptyLabel(): ?string
    {
        return static::resolveCustomProperty('emptyLabel') ?? parent::getEmptyLabel();
    }

    public static function getEmptyTipLabel(): ?string
    {
        return static::resolveCustomProperty('emptyTipLabel') ?? parent::getEmptyTipLabel();
    }

    public static function getEssentialsPlugin(): ?CommentPlugin
    {
        return CommentPlugin::get();
    }
}
