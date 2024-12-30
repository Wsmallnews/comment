<?php

namespace Wsmallnews\Comment\Livewire;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Wsmallnews\Comment\Models\Comment;

class CommentCard extends Component implements HasForms
{
    use InteractsWithForms;

    public Comment $comment;

    public bool $loadChildren = false;

    public function startLoadChildren()
    {
        $this->loadChildren = true;
    }


    public function hiddenChildren()
    {
        $this->loadChildren = false;
    }


    #[Renderless]
    public function toggleLike()
    {
        $this->comment->increment('like_num');

        return $this->comment->like_num;
    }

    public function render()
    {
        return view('sn-comment::livewire.comment-card');
    }
}
