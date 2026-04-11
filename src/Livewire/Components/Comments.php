<?php

namespace Wsmallnews\Comment\Livewire\Components;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Livewire\WithoutUrlPagination;
use Wsmallnews\Comment\Support\Utils;
use Wsmallnews\Comment\Livewire\Concerns\CommentAction;
use Wsmallnews\Support\Livewire\Concerns\CanBeContained;
use Wsmallnews\Support\Livewire\Concerns\CanPagination;
use Wsmallnews\Support\Livewire\Concerns\HasAuth;

class Comments extends Base implements HasActions, HasSchemas
{
    use CommentAction;
    use CanBeContained;
    use CanPagination;
    use HasAuth;
    use InteractsWithActions;
    use InteractsWithSchemas;
    use WithoutUrlPagination;

    /**
     * 父级评论 id
     *
     * @var integer
     */
    public int $parentId = 0;

    /**
     * 是否直接加载子集评论
     *
     * @var boolean
     */
    public bool $loadChildren = false;

    /**
     * 评论关联模型
     *
     * @var Model
     */
    public Model $commentable;
    
    public Collection $comments;

    public function mount()
    {
        $this->comments = $this->comments ?? collect([]);
    }

    protected function getCurrents()
    {
        return $this->comments;
    }

    public function render()
    {
        // 查询图文
        $query = Utils::getCommentModel()::snScope(...$this->getScopeable())->normal()
            ->where('parent_id', $this->parentId)
            ->orderBy('id', 'desc');

        // 分页
        $this->comments = $this->withPagination($query);

        return view('sn-comment::livewire.components.comments', [
            'paginatorLink' => $this->links,
        ]);
    }
}
