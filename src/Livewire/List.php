<?php

namespace Wsmallnews\Comment\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Wsmallnews\Comment\Models\Comment;

class CommentList extends Component
{
    use WithPagination;

    public int $parent_id;

    public string $type;

    public function mount($parent_id = 0, $type = '')
    {
        $this->parent_id = $parent_id;
        $this->type = $type;
    }

    public function render()
    {
        return view('sn-comment::livewire.comment-list', [
            'comments' => Comment::query()->with(['children'])->where('parent_id', $this->parent_id)->paginate(10),
        ])->title('评论列表');
    }
}
