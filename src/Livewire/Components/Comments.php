<?php

namespace Wsmallnews\Comment\Livewire\Components;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Livewire\WithoutUrlPagination;
use Wsmallnews\Comment\Livewire\Concerns\CanAddComment;
use Wsmallnews\Comment\Livewire\Concerns\CommentAction;
use Wsmallnews\Comment\Livewire\Concerns\HasCommentStatus;
use Wsmallnews\Comment\Support\Utils;
use Wsmallnews\Support\Livewire\Concerns\CanBeContained;
use Wsmallnews\Support\Livewire\Concerns\CanPagination;
use Wsmallnews\Support\Livewire\Concerns\HasAuth;
use Wsmallnews\Support\Livewire\Concerns\HasContentType;
use Wsmallnews\Support\Livewire\Concerns\HasProperties;

class Comments extends Base implements HasActions, HasSchemas
{
    use CanAddComment;
    use CanBeContained;
    use CanPagination;
    use CommentAction;
    use HasAuth;
    use HasCommentStatus;
    use HasContentType;
    use HasProperties;
    use InteractsWithActions;
    use InteractsWithSchemas;
    use WithoutUrlPagination;

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
    public Model $commentable;

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

    public function mount()
    {
        $this->comments = $this->comments ?? collect([]);
    }

    public function getEmptyLabel(): ?string
    {
        return $this->getProperty('emptyLabel', __('sn-comment::comment.no_comments'));
    }

    public function getEmptyTipLabel(): ?string
    {
        return $this->getProperty('emptyTipLabel', __('sn-comment::comment.no_comments_description'));
    }

    protected function getCurrents()
    {
        return $this->comments;
    }

    public function render()
    {
        $query = match (true) {
            $this->commentable => $this->commentable->comments(),           // 通过当前评论的主体查询
            $this->commenter => $this->commenter->comments(),               // 通过评论者查询
            $this->beReplyer => $this->beReplyer->beReplyComments(),               // 通过被回复者查询 （没有意义，作为普通查询条件也无法处理 whereHasMorph 因为不确定 beReplyer_type 所属model[后续可以做成一个配置，或者参数，传入要筛选的 beReplyer_type 模型]）
            default => Utils::getCommentModel()::query(),                   // 查询 scopeable 下所有评论
        };

        $query = $query->snScope(...$this->getScopeable())->normal()
            ->when($this->isFormattedContent(), function ($query) {
                $query->with('commentContent');
            })
            ->where('parent_id', $this->parentId)
            ->orderBy('id', 'desc');

        $this->comments = $this->withPagination($query, $this->getFingerprint());

        $this->hasAuthUser() && $this->getAuthUser()->attachLikeStatus($this->comments);

        return view('sn-comment::livewire.components.comments', [
            'paginatorLink' => $this->links,
        ]);
    }

    protected function getFingerprint(): string
    {
        return md5(serialize([
            'parentId' => $this->parentId,
            'commentable' => $this->commentable?->getKey(),
            'commenter' => $this->commenter?->getKey(),
            'beReplyer' => $this->beReplyer?->getKey(),
            ...$this->getScopeable(),
        ]));
    }
}
