<?php

namespace Wsmallnews\Comment\Filament\Resources\Comments;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;
use Wsmallnews\Comment\Filament\Resources\Comments\Tables\CommentTable;
use Wsmallnews\Comment\Support\Utils;
use Wsmallnews\Support\Filament\Resources\Concerns\Scopeable;

abstract class BaseResource extends Resource
{
    use Scopeable;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedChatBubbleLeft;

    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::ChatBubbleLeft;

    protected static ?string $slug = 'comments';

    protected static ?string $recordTitleAttribute = 'content';

    protected static ?int $navigationSort = 1;

    public static function getModel(): string
    {
        return Utils::getCommentModel();
    }

    public static function getModelLabel(): string
    {
        return static::$modelLabel ?? __('sn-comment::comment.comment_resource.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return static::$pluralModelLabel ?? __('sn-comment::comment.comment_resource.plural_model_label');
    }

    public static function getNavigationLabel(): string
    {
        return static::$navigationLabel ?? __('sn-comment::comment.comment_resource.navigation_label');
    }

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return static::$navigationGroup ?? __('sn-comment::comment.global_default.navigation_group');
    }

    public static function table(Table $table): Table
    {
        return CommentTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return static::applyScopeableToQuery(parent::getEloquentQuery())
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
