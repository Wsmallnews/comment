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
use Wsmallnews\Comment\Support\Utils;
use Wsmallnews\Support\Enums\ContentType;
use Wsmallnews\Support\Filament\Forms\FormComponents;

trait CommentAction
{
    public function filamentCommentAction(): Action
    {
        return $this->configureAction(
            CreateAction::make('comment')
                ->label(__('sn-comment::comment.add_comment'))
                ->modalHeading(__('sn-comment::comment.add_comment_heading'))
        );
    }

    public function filamentReplyAction(): Action
    {
        return $this->configureAction(
            CreateAction::make('reply')
                ->label(__('sn-comment::comment.reply_comment'))
                ->modalHeading(__('sn-comment::comment.reply_comment_heading'))
                ->link(),
            'reply'
        );
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
        $this->skipRender();        // 跳过渲染

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
            ->using(function (array $data, array $arguments): Model {
                $user = $this->getAuthUser();

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
                $comment->commenter()->associate($user);

                // 额外 评论者字段
                $data['commenter_name'] = $user->getFilamentName();
                $data['commenter_avatar_url'] = $user->getFilamentAvatarUrl();
                $data['status'] = CommentStatus::Normal;
                $data['content_type'] = $this->contentType;

                $comment->fill($data)->save();

                // 增加上级评论子评论数量
                if ($parentComment) {
                    // 确定上级，如果有 parent_id，就查上级，否者自己就是上级
                    $parent = $parentComment->parent_id ? $parentComment->parent : $parentComment;
                    // 增加评论数 （不更新时间戳）
                    $parent && $parent->whereKey($parent->getKey())->incrementJson('counter->comment_num');
                }

                return $comment;
            })
            ->model(Utils::getCommentModel())       // 当前保存主表模型
            ->visible($this->canAddComment && $this->hasAuthUser() && $this->commentable)       // 可以添加评论， 并且用户已经登录，并且 存在评论主体
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
            FormComponents::localImageUpload('images')
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
