<?php

namespace Wsmallnews\Comment\Livewire\Components;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Illuminate\Database\Eloquent\Model;
use Wsmallnews\Comment\Livewire\Concerns\CommentAction;
use Wsmallnews\Comment\Models\Comment as CommentModel;
use Wsmallnews\Support\Livewire\Concerns\HasAuth;

class Comment extends Base implements HasActions, HasSchemas
{
    use CommentAction;
    use HasAuth;
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

    // public function toggleLike()
    // {
    //     $this->comment->increment('like_num');
    //     $this->comment->refresh();

    //     return $this->comment->like_num;
    // }

    public function render()
    {
        return view('sn-comment::livewire.components.comment');
    }
}
