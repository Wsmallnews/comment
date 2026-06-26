<?php

namespace Wsmallnews\Comment\Filament\Pages\Comment\Components;

use Filament\Facades\Filament;
use Filament\Pages\BasePage;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;
use Wsmallnews\Comment\Livewire\Concerns\CanAddComment;
use Wsmallnews\Comment\Livewire\Concerns\CommentAction;
use Wsmallnews\Comment\Livewire\Concerns\HasCommentStatus;
use Wsmallnews\Comment\Models\Comment as CommentModel;
use Wsmallnews\Support\Livewire\Concerns\HasAuth;
use Wsmallnews\Support\Livewire\Concerns\HasContentType;
use Wsmallnews\Support\Livewire\Concerns\Scopeable;

class Comment extends BasePage
{
    use CanAddComment;
    use CommentAction;
    use HasAuth;
    use HasCommentStatus;
    use HasContentType;
    use Scopeable;

    /**
     * 评论关联模型
     */
    public ?Model $commentable;

    /**
     * 评论者
     */
    public ?Model $commenter = null;

    /**
     * 被回复者
     */
    public ?Model $beReplyer = null;

    public CommentModel $comment;

    public bool $loadChildren = false;

    protected string $view = 'sn-comment::filament.pages.comment.components.comment';

    public function mount()
    {
        // 设置当前认证用户
        $this->hasAuthUser() || $this->authUser(Filament::auth()->user());
    }

    public function startLoadChildren()
    {
        $this->loadChildren = true;
    }

    public function hiddenChildren()
    {
        $this->loadChildren = false;
    }

    #[On('sn-comment-replied-{comment.id}')]
    public function onCommentReplied()
    {
        // 有新的子评论时，此事件可以刷新当前模型， 更新子评论数量，并默认展开子评论
        // 刷新 model
        $this->comment->refresh();

        // 展开子评论
        $this->startLoadChildren();
    }

    #[On('sn-comment-deleted-{comment.id}')]
    public function onCommentDeleted()
    {
        // 刷新 model
        $this->comment->refresh();

        // 子评论全部删除时，收起子评论列表（后端用全部评论数判断）
        if ($this->comment->counter['total_comment_num'] <= 0) {
            $this->hiddenChildren();
        }
    }

    #[On('sn-comment-status-changed-{comment.id}')]
    public function onStatusChanged()
    {
        $this->comment->refresh();
    }

    public function toggleLike()
    {
        // 喜欢评论
        $this->getAuthUser()->toggleLike($this->comment);

        // 刷新 model
        $this->comment->refresh();

        // 附加喜欢状态
        $this->getAuthUser()->attachLikeStatus($this->comment);
    }
}
