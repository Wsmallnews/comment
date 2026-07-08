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
use Illuminate\Support\Collection;
use Wsmallnews\Comment\Enums\CommentStatus;
use Wsmallnews\Comment\Models\Comment;
use Wsmallnews\Comment\Services\CommentCounterService;
use Wsmallnews\Comment\Support\Utils;
use Wsmallnews\Support\Enums\ContentType;
use Wsmallnews\Support\Filament\Actions\ActionComponents;
use Wsmallnews\Support\Filament\Filters\FilterComponents;
use Wsmallnews\Support\Filament\Tables\ColumnComponents;
use Wsmallnews\Support\Helpers\FilamentModelHelper;

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
            ->modifyQueryUsing(fn (Builder $query) => $query->with('commentContent'))
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
                    static::bulkDeleteAction(),
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

    protected static function contentColumn(): Tables\Columns\TextColumn
    {
        return ColumnComponents::contentColumn(
            name: 'content',
            label: __('sn-comment::comment.comment_resource.table.content'),
            searchable: ['content'],
            actionResolver: function ($action) {
                $action->modalContent(fn ($record) => view('sn-support::filament.tables.columns.content-modal', [
                    'contentType' => $record->content_type,
                    'content' => $record->content_type === ContentType::Textarea ? $record->content : $record->commentContent?->content,
                ]));

                return $action;
            }
        );
    }

    protected static function commenterColumn(): Tables\Columns\TextColumn
    {
        return ColumnComponents::morphColumn(
            'commenter_type',
            __('sn-comment::comment.comment_resource.table.commenter'),
            fn ($record) => $record->commenter,
        );
    }

    protected static function commentableColumn(): Tables\Columns\TextColumn
    {
        return ColumnComponents::morphColumn(
            'commentable_type',
            __('sn-comment::comment.comment_resource.table.commentable'),
            fn ($record) => $record->commentable,
        );
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
        return FilterComponents::morphFilter(
            type: 'commentable',
            label: __('sn-comment::comment.comment_resource.filter.commentable'),
            options: function () {
                $types = Utils::getCommentModel()::query()
                    ->distinct()
                    ->whereNotNull('commentable_type')
                    ->pluck('commentable_type', 'commentable_type');

                return FilamentModelHelper::getTypeOptions($types);
            },
            keywordSearchFields: ['title'],
            morphKeywordPlaceholder: __('sn-comment::comment.comment_resource.filter.commentable_keyword_placeholder')
        );

        return Tables\Filters\Filter::make('commentable')
            ->label(__('sn-comment::comment.comment_resource.filter.commentable'))
            ->schema([
                Schemas\Components\FusedGroup::make([
                    Forms\Components\Select::make('commentable_type')
                        ->options(function () {
                            $types = Utils::getCommentModel()::query()
                                ->distinct()
                                ->whereNotNull('commentable_type')
                                ->pluck('commentable_type', 'commentable_type');

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
        return DeleteAction::make()
            ->modalDescription(__('sn-comment::comment.comment_resource.action.delete_description'))
            ->using(function (Comment $record): bool {
                if ($record->trashed()) {
                    return false;
                }

                // 处理评论数量，自动判断是否有子评论，有则级联处理计数器
                CommentCounterService::afterCommentDeleted($record);

                if ($record->children?->isNotEmpty()) {
                    $record->children->each->delete();
                }

                $record->delete();

                return true;
            });
    }

    // ========================= Bulk Actions =========================

    protected static function bulkApproveAction(): BulkAction
    {
        return ActionComponents::bulkAction(
            name: 'bulk_approve', 
            process: function (BulkAction $action, Comment $record): void {
                $oldStatus = $record->status;

                if ($oldStatus === CommentStatus::Normal) {
                    $action->reportBulkProcessingFailure();

                    return;
                }

                $record->update(['status' => CommentStatus::Normal]);
                CommentCounterService::afterStatusChanged($record, $oldStatus, CommentStatus::Normal);
            }
        )
            ->label(__('sn-comment::comment.comment_resource.action.bulk_approve'))
            ->icon(Heroicon::CheckCircle)
            ->color('success')
            ->modalHeading(__('sn-comment::comment.comment_resource.action.bulk_approve_heading'))
            ->modalDescription(__('sn-comment::comment.comment_resource.action.bulk_approve_description'));
    }

    protected static function bulkHideAction(): BulkAction
    {
        return ActionComponents::bulkAction(
            name: 'bulk_hide', 
            process: function (BulkAction $action, Comment $record): void {
                $oldStatus = $record->status;

                if ($oldStatus === CommentStatus::Hidden) {
                    $action->reportBulkProcessingFailure();

                    return;
                }

                $record->update(['status' => CommentStatus::Hidden]);
                CommentCounterService::afterStatusChanged($record, $oldStatus, CommentStatus::Hidden);
            }
        )
            ->label(__('sn-comment::comment.comment_resource.action.bulk_hide'))
            ->icon(Heroicon::EyeSlash)
            ->color('gray')
            ->modalHeading(__('sn-comment::comment.comment_resource.action.bulk_hide_heading'))
            ->modalDescription(__('sn-comment::comment.comment_resource.action.bulk_hide_description'));
    }

    protected static function bulkRejectAction(): BulkAction
    {
        return ActionComponents::bulkAction(
            name: 'bulk_reject', 
            process: function (BulkAction $action, Comment $record): void {
                $oldStatus = $record->status;

                if ($oldStatus === CommentStatus::Rejected) {
                    $action->reportBulkProcessingFailure();

                    return;
                }

                $record->update(['status' => CommentStatus::Rejected]);
                CommentCounterService::afterStatusChanged($record, $oldStatus, CommentStatus::Rejected);
            }
        )
            ->label(__('sn-comment::comment.comment_resource.action.bulk_reject'))
            ->icon(Heroicon::ShieldExclamation)
            ->color('danger')
            ->modalHeading(__('sn-comment::comment.comment_resource.action.bulk_reject_heading'))
            ->modalDescription(__('sn-comment::comment.comment_resource.action.bulk_reject_description'));
    }

    protected static function bulkDeleteAction(): BulkAction
    {
        return DeleteBulkAction::make()
            ->modalDescription(__('sn-comment::comment.comment_resource.action.bulk_delete_description'))
            ->using(function (DeleteBulkAction $action, Collection $records): void {
                ActionComponents::safeBulkProcess(
                    action: $action,
                    records: $records,
                    process: function (BulkAction $action, Comment $record): void {
                        // 已被级联删除的子评论静默跳过，不计入失败
                        if ($record->trashed()) {
                            return;
                        }

                        // 处理评论数量，自动判断是否有子评论，有则级联处理计数器
                        CommentCounterService::afterCommentDeleted($record);

                        if ($record->children?->isNotEmpty()) {
                            $record->children->each->delete();
                        }

                        $record->delete() || $action->reportBulkProcessingFailure();
                    },
                    prepare: fn (Collection $records) => $records->load('children'),
                );
            });
    }


    public static function getCommentableTypeLabel(string $type): string
    {
        return FilamentModelHelper::getTypeLabel($type);
    }
}
