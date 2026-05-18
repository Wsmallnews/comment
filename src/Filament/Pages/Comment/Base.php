<?php

namespace Wsmallnews\Comment\Filament\Pages\Comment;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;
use Wsmallnews\Comment\Enums\CommentStatus;
use Wsmallnews\Support\Enums\ContentType;
use Wsmallnews\Support\Filament\Pages\Concerns\Scopeable;

abstract class Base extends Page
{
    use Scopeable;

    protected static ?string $pluralModelLabel = null;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedChatBubbleLeft;

    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::ChatBubbleLeft;

    protected static ?string $slug = 'comments';

    protected static string $recordTitleAttribute = 'content';

    protected static ?int $navigationSort = 1;

    protected static ?string $emptyLabel = null;

    protected static ?string $emptyTipLabel = null;

    protected static ContentType $contentType = ContentType::Textarea;

    protected static ?CommentStatus $commentStatus = null;

    protected string $view = 'sn-comment::filament.pages.comment.comment-page';

    public static function getModelLabel(): string
    {
        return static::$modelLabel ?? __('sn-comment::comment.filament.comment.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return static::$pluralModelLabel ?? __('sn-comment::comment.filament.comment.plural_model_label');
    }

    public function getTitle(): string | Htmlable
    {
        return static::$title ?? __('sn-comment::comment.filament.comment.title');
    }

    public static function getNavigationLabel(): string
    {
        return static::$navigationLabel ?? static::$title ?? __('sn-comment::comment.filament.comment.navigation_label');
    }

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return static::$navigationGroup ?? __('sn-comment::comment.filament.comment.navigation_group');
    }

    public static function getContentType(): ContentType
    {
        return static::$contentType ?? ContentType::Textarea;
    }

    public static function getCommentStatus(): ?CommentStatus
    {
        return static::$commentStatus;
    }

    public static function getEmptyLabel(): ?string
    {
        return static::$emptyLabel ?? __('sn-comment::comment.filament.comment.no_comments');
    }

    public static function getEmptyTipLabel(): ?string
    {
        return static::$emptyTipLabel ?? __('sn-comment::comment.filament.comment.no_comments_description');
    }

    public static function getProperties(): array
    {
        return [
            'emptyLabel' => static::getEmptyLabel(),
            'emptyTipLabel' => static::getEmptyTipLabel(),
        ];
    }
}
