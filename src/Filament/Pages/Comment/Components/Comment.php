<?php

namespace Wsmallnews\Comment\Filament\Pages\Comment\Components;

use Filament\Facades\Filament;
use Filament\Pages\BasePage;
use Illuminate\Database\Eloquent\Model;
use Wsmallnews\Comment\Models\Comment as CommentModel;
use Wsmallnews\Support\Livewire\Concerns\HasContentType;
use Wsmallnews\Support\Livewire\Concerns\Scopeable;

class Comment extends BasePage
{
    use HasContentType;
    use Scopeable;

    /**
     * 评论关联模型
     */
    public Model $commentable;

    public CommentModel $comment;

    public bool $loadChildren = false;

    protected string $view = 'sn-comment::filament.pages.comment.components.comment';

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
        // 当前登录用户
        $user = Filament::auth()->user();

        // 喜欢评论
        $user->toggleLike($this->comment);

        // 刷新 model
        $this->comment->refresh();

        // 附加喜欢状态
        $user->attachLikeStatus($this->comment);
    }
}
