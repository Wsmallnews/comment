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
use Wsmallnews\Support\Enums\EditorType;
use Wsmallnews\Support\Support\Utils as SupportUtils;

trait CommentAction
{
    public function commentAction(): Action
    {
        return $this->configureAction(
            CreateAction::make('comment')
                ->label('添加评论')
                ->modalHeading('添加评论')
        );
    }

    public function replyAction(): Action
    {
        return $this->configureAction(
            CreateAction::make('reply')
                ->label('回复')
                ->modalHeading('回复评论')
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
            ->modalDescription('温馨提示：优质评论更容易获得他人回复。')
            ->schema(function (array $arguments) {
                $parentCommentId = $arguments['id'] ?? null;
                $parentComment = $parentCommentId ? Utils::getCommentModel()::find($parentCommentId) : null;

                $schemas = match ($this->editorType) {
                    EditorType::RichEditor => $this->getRichEditorComponents($parentComment),
                    EditorType::MarkdownEditor => $this->getMarkdownEditorComponents($parentComment),
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
                $data['editor_type'] = $this->editorType;

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
            ->visible($this->hasAuthUser())
            ->stickyModalHeader()
            ->stickyModalFooter()
            ->modalWidth(function () {
                if ($this->isFormattedEditor()) {
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
                ->label('评论内容')
                ->placeholder(function () use ($parentComment) {
                    return $parentComment ? '回复 @' . $parentComment->commenter_name : '请输入您的评论';
                })
                ->required(),
            Forms\Components\FileUpload::make('images')
                ->label('评论图片')
                ->image()
                ->disk(SupportUtils::getFilesystemDisk())
                ->directory(Utils::getFileDirectory('comments'))
                ->visibility('public')
                ->multiple()
                ->openable()
                ->downloadable()
                ->reorderable()
                ->appendFiles()
                ->maxFiles(9)
                ->uploadingMessage('评论图片上传中...')
                ->imagePreviewHeight('100'),
        ];
    }

    /**
     * 富文本编辑器组件
     *
     * @param  Comment|null  $parentComment  上级评论
     * @return array
     */
    protected function getRichEditorComponents($parentComment)
    {
        return [
            Schemas\Components\Group::make()
                ->relationship('commentContent')
                ->schema([
                    Forms\Components\RichEditor::make('content')
                        ->label('评论内容')
                        ->placeholder(function () use ($parentComment) {
                            return $parentComment ? '回复 @' . $parentComment->commenter_name : '请输入您的评论';
                        })
                        ->fileAttachmentsDirectory(Utils::getFileDirectory('comment_contents'))
                        ->required()
                        ->disableToolbarButtons([
                            'attachFiles',
                        ]),
                    Forms\Components\Hidden::make('content_type')
                        ->default('html'),
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
    protected function getMarkdownEditorComponents($parentComment)
    {
        return [
            Schemas\Components\Group::make()
                ->relationship('commentContent')
                ->schema([
                    Forms\Components\MarkdownEditor::make('content')
                        ->label('评论内容')
                        ->placeholder(function () use ($parentComment) {
                            return $parentComment ? '回复 @' . $parentComment->commenter_name : '请输入您的评论（支持Markdown）';
                        })
                        ->fileAttachmentsDirectory(Utils::getFileDirectory('comment_contents'))
                        ->required()
                        ->disableToolbarButtons([
                            'attachFiles',
                        ]),
                    Forms\Components\Hidden::make('content_type')
                        ->default('markdown'),
                ])
                ->columns(1),
        ];
    }
}
