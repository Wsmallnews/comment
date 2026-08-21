<?php

namespace Wsmallnews\Comment\Livewire\Concerns;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms;
use Filament\Schemas;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;
use Wsmallnews\Comment\Enums\CommentStatus;
use Wsmallnews\Comment\Models\Comment;
use Wsmallnews\Comment\Services\CommentCounterService;
use Wsmallnews\Comment\Support\Utils;
use Wsmallnews\Support\Enums\ContentType;
use Wsmallnews\Support\Filament\Actions\ActionComponents;
use Wsmallnews\Support\Filament\Forms\FormComponents;

trait CommentAction
{
    public function filamentCommentAction(): Action
    {
        return $this->configureAction(
            CreateAction::make('filamentComment')
                ->label(__('sn-comment::comment.add_comment'))
                ->modalHeading(__('sn-comment::comment.add_comment_heading')),
            'filament-create'
        );
    }

    public function filamentReplyAction(): Action
    {
        return $this->configureAction(
            CreateAction::make('filamentReply')
                ->label(__('sn-comment::comment.reply_comment'))
                ->modalHeading(__('sn-comment::comment.reply_comment_heading'))
                ->link(),
            'filament-reply'
        );
    }

    public function filamentDeleteAction(): Action
    {
        return ActionComponents::deleteAction('filamentDelete')
            ->action(function (Action $action, array $arguments): void {
                $key = $arguments['key'] ?? null;
                $comment = $key ? Utils::getCommentModel()::snScope($this->getScopeType(), $this->getScopeId())->find($key) : null;

                if (! $comment) {
                    $action->failure();

                    return;
                }

                $parentId = $comment->parent_id;

                // 处理评论数量，自动判断是否有子评论，有则级联处理计数器
                CommentCounterService::afterCommentDeleted($comment);

                // 级联软删除子评论
                if ($comment->children?->isNotEmpty()) {
                    $comment->children->each->delete();
                }

                // 删除自己
                $comment->delete();

                // 通知评论列表和父评论刷新
                $this->dispatch('sn-comment-deleted-' . ($parentId ?? 0), data: ['commentId' => $comment->getKey()]);

                $action->success();
            });
    }

    public function filamentStatusAction(): Action
    {
        return Action::make('filamentStatus')
            ->label(__('sn-comment::comment.action.audit_status_action'))
            ->modalHeading(__('sn-comment::comment.action.audit_status_action_heading'))
            ->successNotificationTitle(__('sn-comment::comment.action.audit_status_action_success_notification_title'))
            ->defaultColor('info')
            ->schema(function (array $arguments) {
                $key = $arguments['key'] ?? null;
                $comment = null;
                $key && $comment = Utils::getCommentModel()::snScope($this->getScopeType(), $this->getScopeId())->find($key);

                return [
                    Forms\Components\Radio::make('status')
                        ->label(__('sn-comment::comment.status'))
                        ->options(CommentStatus::class)
                        ->default($comment?->status ?? CommentStatus::Normal)
                        ->inline()
                        ->required(),
                ];
            })
            ->action(function (Action $action, array $arguments, array $data): void {
                $key = $arguments['key'] ?? null;
                $comment = $key ? Utils::getCommentModel()::snScope($this->getScopeType(), $this->getScopeId())->find($key) : null;

                if ($comment) {
                    $oldStatus = $comment->status;
                    $comment->update(['status' => ($data['status'] ?? CommentStatus::Normal)]);

                    // 更新评论数量
                    CommentCounterService::afterStatusChanged($comment, $oldStatus, $comment->status);

                    // 触发事件
                    $this->dispatch('sn-comment-status-changed-' . $comment->getKey());
                }

                $action->success();
            });
    }

    public function commentAction(): Action
    {
        return $this->configureAction(
            CreateAction::make('comment')
                ->label(__('sn-comment::comment.add_comment'))
                ->modalHeading(__('sn-comment::comment.add_comment_heading'))
        );
    }

    public function replyAction(): Action
    {
        return $this->configureAction(
            CreateAction::make('reply')
                ->label(__('sn-comment::comment.reply_comment'))
                ->modalHeading(__('sn-comment::comment.reply_comment_heading'))
                ->link(),
            'reply'
        );
    }

    /**
     * 配置 createAction 操作
     */
    private function configureAction(CreateAction $action, $type = 'create'): Action
    {
        return $action
            ->modalDescription(__('sn-comment::comment.comment_tip'))
            ->schema(function (array $arguments) {
                $parentCommentId = $arguments['id'] ?? null;
                $parentComment = $parentCommentId ? Utils::getCommentModel()::find($parentCommentId) : null;

                $schemas = match ($this->contentType) {
                    ContentType::Richtext => $this->getRichtextComponents($parentComment),
                    ContentType::Markdown => $this->getMarkdownComponents($parentComment),
                    default => $this->getTextareaComponents($parentComment),
                };

                return $schemas;
            })
            ->using(function (array $data, array $arguments) use ($type): Model {
                $authUser = $this->getAuthUser();

                $parentCommentId = $arguments['id'] ?? null;
                $parentComment = $parentCommentId ? Utils::getCommentModel()::find($parentCommentId) : null;

                // 填充租户信息
                $data['team_id'] = current_tenant()?->id;

                if ($parentComment) {
                    // 所有子评论都属于同一评论之下
                    $data['parent_id'] = $parentComment->parent_id ? $parentComment->parent_id : $parentComment->id;

                    // 被回复人
                    $data['be_replyer_type'] = $parentComment->commenter_type;
                    $data['be_replyer_id'] = $parentComment->commenter_id;
                    $data['be_replyer_name'] = $parentComment->commenter_name;
                    $data['be_replyer_avatar_url'] = $parentComment->commenter_avatar_url;
                }

                $data = array_merge($data, $this->getScopeable());

                $comment = new (Utils::getCommentModel());
                // 填充评论关联主体
                $comment->commentable()->associate($this->commentable);
                // 填充评论人
                $comment->commenter()->associate($authUser);

                // 额外 评论者字段
                $data['commenter_name'] = $authUser?->getSnName();
                $data['commenter_avatar_url'] = $authUser?->getSnAvatarUrl();
                $data['status'] = $this->commentStatus ?? Utils::getDefaultCommentStatus();
                $data['content_type'] = $this->contentType;

                $comment->fill($data)->save();

                // 更新计数器
                CommentCounterService::afterCommentCreated($comment, $parentComment, $this->commentable);

                // 根据操作类型触发不同事件
                if (str($type)->contains('reply')) {
                    $this->dispatch('sn-comment-replied-' . ($comment->parent_id ?? 0), data: ['comment' => $comment]);
                } else {
                    $this->dispatch('sn-comment-created', data: ['comment' => $comment, 'type' => $type]);
                }

                return $comment;
            })
            ->model(Utils::getCommentModel())       // 当前保存主表模型
            ->visible($this->canAddComment && $this->hasAuthUser() && isset($this->commentable) && $this->commentable)       // 可以添加评论， 并且用户已经登录，并且 存在评论主体
            ->stickyModalHeader()
            ->stickyModalFooter()
            ->modalWidth(function () {
                if ($this->isFormattedContent()) {
                    return Width::ThreeExtraLarge;
                }

                return Width::Large;
            })
            ->closeModalByClickingAway(false)
            ->createAnother(false);
    }

    /**
     * 文本域组件
     *
     * @param  Comment|null  $parentComment  上级评论
     * @return array
     */
    protected function getTextareaComponents($parentComment)
    {
        return [
            Forms\Components\Textarea::make('content')
                ->label(__('sn-comment::comment.comment_content'))
                ->placeholder(function () use ($parentComment) {
                    return $parentComment ? __('sn-comment::comment.reply') . ' @' . $parentComment->commenter_name : __('sn-comment::comment.comment_placeholder');
                })
                ->required(),
            FormComponents::plainImageUpload('images')
                ->label(__('sn-comment::comment.comment_image'))
                ->directory(Utils::getFileDirectory('comments'))
                ->multiple()
                ->maxFiles(9)
                ->uploadingMessage(__('sn-comment::comment.comment_image_uploading')),
        ];
    }

    /**
     * 富文本编辑器组件
     *
     * @param  Comment|null  $parentComment  上级评论
     * @return array
     */
    protected function getRichtextComponents($parentComment)
    {
        return [
            Schemas\Components\Group::make()
                ->relationship('commentContent')
                ->schema([
                    FormComponents::richEditor('content')
                        ->label(__('sn-comment::comment.comment_content'))
                        ->placeholder(function () use ($parentComment) {
                            return $parentComment ? __('sn-comment::comment.reply_to') . ' @' . $parentComment->commenter_name : __('sn-comment::comment.comment_placeholder');
                        })
                        ->fileAttachmentsDirectory(Utils::getFileDirectory('comment_contents'))
                        ->required(),
                    Forms\Components\Hidden::make('content_type')
                        ->default(ContentType::Richtext),
                ])
                ->columns(1),
        ];
    }

    /**
     * Markdown编辑器组件
     *
     * @param  Comment|null  $parentComment  上级评论
     * @return array
     */
    protected function getMarkdownComponents($parentComment)
    {
        return [
            Schemas\Components\Group::make()
                ->relationship('commentContent')
                ->schema([
                    FormComponents::markdownEditor('content')
                        ->label(__('sn-comment::comment.comment_content'))
                        ->placeholder(function () use ($parentComment) {
                            return $parentComment ? __('sn-comment::comment.reply_to') . ' @' . $parentComment->commenter_name : __('sn-comment::comment.comment_markdown_placeholder');
                        })
                        ->fileAttachmentsDirectory(Utils::getFileDirectory('comment_contents'))
                        ->required(),
                    Forms\Components\Hidden::make('content_type')
                        ->default(ContentType::Markdown),
                ])
                ->columns(1),
        ];
    }
}
