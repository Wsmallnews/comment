<?php

namespace Wsmallnews\Comment\Livewire\Components;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Illuminate\Database\Eloquent\Model;
use Wsmallnews\Comment\Livewire\Concerns\CanAddComment;
use Wsmallnews\Comment\Livewire\Concerns\CommentAction;
use Wsmallnews\Comment\Models\Comment as CommentModel;
use Wsmallnews\Support\Livewire\Concerns\HasAuth;
use Wsmallnews\Support\Livewire\Concerns\HasContentType;

class Comment extends Base implements HasActions, HasSchemas
{
    use CanAddComment;
    use CommentAction;
    use HasAuth;
    use HasContentType;
    use InteractsWithActions;
    use InteractsWithSchemas;

    /**
     * 评论关联模型
     */
    public Model $commentable;

    public CommentModel $comment;

    public bool $loadChildren = false;

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
        if (! $this->hasAuthUser()) {
            Notification::make()
                ->title(__('sn-comment::comment.like_failed'))
                ->body(__('sn-comment::comment.login_required'))
                ->danger()->send();

            return;
        }
        // 喜欢评论
        $this->getAuthUser()->toggleLike($this->comment);

        // 刷新 model
        $this->comment->refresh();

        // 附加喜欢状态
        $this->getAuthUser()->attachLikeStatus($this->comment);
    }

    public function render()
    {
        return view('sn-comment::livewire.components.comment');
    }
}
