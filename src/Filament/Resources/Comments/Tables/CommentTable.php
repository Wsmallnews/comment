<?php

namespace Wsmallnews\Comment\Filament\Resources\Comments\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Schemas;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Wsmallnews\Comment\Enums\CommentStatus;
use Wsmallnews\Comment\Services\CommentCounterService;
use Wsmallnews\Comment\Support\Utils;
use Wsmallnews\Support\Filament\Concerns\ModelFormat;

class CommentTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                static::IDColumn(),
                static::contentColumn(),
                static::commenterColumn(),
                static::commentableColumn(),
                static::statusColumn(),
                static::createdAtColumn(),
                static::updateAtColumn(),
            ])
            ->defaultSort('created_at', 'desc')
            ->searchable()
            ->filters([
                static::commentableFilter(),
                static::statusFilter(),
            ])
            ->recordActions([
                ActionGroup::make([
                    static::viewRelatedAction(),
                    ViewAction::make(),
                    static::deleteAction(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    static::bulkApproveAction(),
                    static::bulkHideAction(),
                    static::bulkRejectAction(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    // ========================= Columns =========================

    protected static function IDColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('id')
            ->label('ID')
            ->searchable()
            ->sortable()
            ->alignCenter()
            ->toggleable();
    }

    protected static function contentColumn(): Tables\Columns\ViewColumn
    {
        return Tables\Columns\ViewColumn::make('content')
            ->label(__('sn-comment::comment.comment_resource.table.content'))
            ->view('sn-comment::filament.resources.comments.tables.columns.comment-content')
            ->toggleable();
    }

    protected static function commenterColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('commenter_name')
            ->label(__('sn-comment::comment.comment_resource.table.commenter'))
            ->searchable()
            ->sortable()
            ->toggleable();
    }

    protected static function commentableColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('commentable_type')
            ->label(__('sn-comment::comment.comment_resource.table.commentable'))
            ->formatStateUsing(function ($state, $record) {
                $title = ModelFormat::getTitle($record->commentable);
                $typeLabel = ModelFormat::getTypeLabel($state);

                return "#{$record->commentable_id} {$title}";
            })
            ->description(fn ($record) => ModelFormat::getTypeLabel($record->commentable_type))
            ->url(fn ($record) => ModelFormat::getUrl($record->commentable))
            ->searchable()
            ->sortable()
            ->toggleable();
    }

    protected static function statusColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('status')
            ->label(__('sn-comment::comment.comment_resource.table.status'))
            ->toggleable();
    }

    protected static function createdAtColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('created_at')
            ->label(__('sn-comment::comment.comment_resource.table.created_at'))
            ->sortable()
            ->toggleable();
    }
    protected static function updateAtColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('updated_at')
            ->label(__('sn-comment::comment.comment_resource.table.updated_at'))
            ->sortable()
            ->toggleable();
    }

    // ========================= Filters =========================

    protected static function commentableFilter(): Tables\Filters\Filter
    {
        return Tables\Filters\Filter::make('commentable')
            ->label(__('sn-comment::comment.comment_resource.filter.commentable'))
            ->schema([
                Schemas\Components\FusedGroup::make([
                    Forms\Components\Select::make('commentable_type')
                        ->options(function () {
                            $types = Utils::getCommentModel()::query()
                                ->distinct()
                                ->whereNotNull('commentable_type')
                                ->pluck('commentable_type');

                            return $types->mapWithKeys(fn ($type) => [
                                $type => static::getCommentableTypeLabel($type),
                            ])->toArray();
                        })
                        ->selectablePlaceholder(false)
                        ->columnSpan(1),
                    Forms\Components\TextInput::make('commentable_keyword')
                        ->placeholder(__('sn-comment::comment.comment_resource.filter.commentable_keyword_placeholder'))
                        ->columnSpan(2),
                ])->columns(3),
            ])
            ->query(function (Builder $query, array $data): Builder {
                return $query
                    ->when(
                        $data['commentable_type'] ?? null,
                        fn (Builder $query, $type) => $query->where('commentable_type', $type)
                    )
                    ->when(
                        $data['commentable_keyword'] ?? null,
                        fn (Builder $query, $keyword) => $query->where(function ($query) use ($keyword) {
                            $query->where('commentable_id', $keyword);
                        })
                    );
            });
    }

    protected static function statusFilter(): Tables\Filters\SelectFilter
    {
        return Tables\Filters\SelectFilter::make('status')
            ->label(__('sn-comment::comment.comment_resource.filter.status'))
            ->options(CommentStatus::class);
    }

    // ========================= Actions =========================

    protected static function viewRelatedAction(): Action
    {
        return Action::make('viewRelated')
            ->label(__('sn-comment::comment.comment_resource.action.view_related'))
            ->icon(Heroicon::Bars3BottomLeft)
            ->color('gray')
            ->modal()
            ->modalWidth(Width::ThreeExtraLarge)
            ->modalContent(function ($record) {
                $rootComment = $record->parent_id
                    ? Utils::getCommentModel()::find($record->parent_id)
                    : $record;

                return view('sn-comment::filament.resources.comment.comment-tree-modal', [
                    'rootComment' => $rootComment,
                    'commentable' => $record->commentable,
                ]);
            });
    }

    protected static function deleteAction(): DeleteAction
    {
        return DeleteAction::make();
    }

    // ========================= Bulk Actions =========================

    protected static function bulkApproveAction(): BulkAction
    {
        return BulkAction::make('bulk_approve')
            ->label(__('sn-comment::comment.comment_resource.action.bulk_approve'))
            ->icon(Heroicon::CheckCircle)
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading(__('sn-comment::comment.comment_resource.action.bulk_approve_heading'))
            ->modalDescription(__('sn-comment::comment.comment_resource.action.bulk_approve_description'))
            ->action(function ($records) {
                static::changeStatus($records, CommentStatus::Normal);
            });
    }

    protected static function bulkHideAction(): BulkAction
    {
        return BulkAction::make('bulk_hide')
            ->label(__('sn-comment::comment.comment_resource.action.bulk_hide'))
            ->icon(Heroicon::EyeSlash)
            ->color('gray')
            ->requiresConfirmation()
            ->modalHeading(__('sn-comment::comment.comment_resource.action.bulk_hide_heading'))
            ->modalDescription(__('sn-comment::comment.comment_resource.action.bulk_hide_description'))
            ->action(function ($records) {
                static::changeStatus($records, CommentStatus::Hidden);
            });
    }

    protected static function bulkRejectAction(): BulkAction
    {
        return BulkAction::make('bulk_reject')
            ->label(__('sn-comment::comment.comment_resource.action.bulk_reject'))
            ->icon(Heroicon::ShieldExclamation)
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading(__('sn-comment::comment.comment_resource.action.bulk_reject_heading'))
            ->modalDescription(__('sn-comment::comment.comment_resource.action.bulk_reject_description'))
            ->action(function ($records) {
                static::changeStatus($records, CommentStatus::Rejected);
            });
    }

    protected static function changeStatus($records, CommentStatus $newStatus): void
    {
        foreach ($records as $comment) {
            $oldStatus = $comment->status;
            if ($oldStatus === $newStatus) {
                continue;
            }

            $comment->update(['status' => $newStatus]);
            CommentCounterService::afterStatusChanged($comment, $oldStatus, $newStatus);
        }
    }

    // ========================= Helpers =========================

    public static function getCommentableTypeLabel(string $type): string
    {
        return ModelFormat::getTypeLabel($type);
    }
}
