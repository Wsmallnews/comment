<?php

namespace Wsmallnews\Comment\Filament\Pages\Comment\Components;

use Filament\Facades\Filament;
use Filament\Pages\BasePage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Livewire\WithoutUrlPagination;
use Wsmallnews\Comment\Support\Utils;
use Wsmallnews\Support\Livewire\Concerns\Scopeable;
use Wsmallnews\Support\Livewire\Concerns\CanBeContained;
use Wsmallnews\Support\Livewire\Concerns\CanPagination;
use Wsmallnews\Support\Livewire\Concerns\HasContentType;

class Comments extends BasePage
{
    use CanBeContained;
    use CanPagination;
    use HasContentType;
    use Scopeable;
    use WithoutUrlPagination;

    /**
     * 组件属性配置
     */
    public ?array $properties = [];
    /**
     * 父级评论 id
     */
    public int $parentId = 0;
    /**
     * 是否直接加载子集评论
     */
    public bool $loadChildren = false;
    /**
     * 评论关联模型
     */
    public ?Model $commentable = null;
    /**
     * 评论者
     */
    public ?Model $commenter = null;
    /**
     * 被回复者
     */
    public ?Model $beReplyer = null;
    /**
     * 评论列表
     */
    public Collection $comments;

    protected string $view = 'sn-comment::filament.pages.comment.components.comments';


    public function mount()
    {
        $this->comments = $this->comments ?? collect([]);
    }


    public function getEmptyLabel(): ?string
    {
        return (isset($this->properties['emptyLabel']) && filled($this->properties['emptyLabel'])) ? $this->properties['emptyLabel'] : __('sn-comment::comment.filament.comment.no_comments');
    }
    public function getEmptyTipLabel(): ?string
    {
        return (isset($this->properties['emptyTipLabel']) && filled($this->properties['emptyTipLabel'])) ? $this->properties['emptyTipLabel'] : __('sn-comment::comment.filament.comment.no_comments_description');
    }


    protected function getCurrents()
    {
        return $this->comments;
    }


    public function getViewData(): array
    {
        // 当前登录用户
        $user = Filament::auth()->user();

        $query = null;
        $query = match (true) {
            $this->commentable => $this->commentable->comments(),           // 通过当前评论的主体查询
            $this->commenter => $this->commenter->comments(),               // 通过评论者查询
            $this->beReplyer => $this->beReplyer->comments(),               // 通过被评论者查询
            default => Utils::getCommentModel()::query(),                   // 查询 scopeable 下所有评论
        };

        $query = $query->snScope(...$this->getScopeable())->normal()
            ->when($this->isFormattedContent(), function ($query) {
                $query->with('commentContent');
            })
            ->where('parent_id', $this->parentId)
            ->orderBy('id', 'desc');

        // 分页
        $this->comments = $this->withPagination($query);

        // 附加喜欢状态
        $user->attachLikeStatus($this->comments);

        return [
            'paginatorLink' => $this->links,
        ];
    }
}
