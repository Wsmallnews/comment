<?php

namespace Wsmallnews\Comment\Filament\Pages\Comment\Components;

use Filament\Facades\Filament;
use Filament\Pages\BasePage;
use Illuminate\Database\Eloquent\Model;
use Wsmallnews\Comment\Livewire\Concerns\CanAddComment;
use Wsmallnews\Comment\Livewire\Concerns\CommentAction;
use Wsmallnews\Comment\Models\Comment as CommentModel;
use Wsmallnews\Support\Livewire\Concerns\HasAuth;
use Wsmallnews\Comment\Livewire\Concerns\HasCommentStatus;
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
