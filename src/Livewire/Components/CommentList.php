<?php

namespace Wsmallnews\Comment\Livewire\Components;

use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Wsmallnews\Comment\Models\Comment;

class CommentList extends Component
{
    use WithoutUrlPagination;
    use WithPagination;

    public int $parentId = 0;

    public int | string $pageName;

    public int | string $perPage;

    public string $pageType;

    public Collection $comments;

    public array $pageInfo = [];

    public bool $loadChildren = false;

    protected $listeners = ['commentCreated' => 'refreshComments'];

    public function mount($pageName = '', $perPage = 0, $pageType = '')
    {
        // 分页名字
        $this->pageName = $pageName ?: config('sn-comment.pagination.page_name', 'page');

        // 每页条数
        $this->perPage = $perPage ?: config('sn-comment.pagination.per_page', 10);

        // 分页类型
        $this->pageType = $pageType ?: config('sn-comment.pagination.page_type', 'paginator');

        $this->comments = $this->comments ?? collect([]);
    }

    public function refreshComments()
    {
        // 重置评论列表
        $this->comments = collect([]);
        // 重置分页
        $this->resetPage();
    }

    public function render()
    {
        $current = Comment::query()
            ->where('parent_id', $this->parentId)
            ->orderBy('created_at', 'desc');

        if ($this->pageType == 'paginator') {
            $current = $current->paginate($this->perPage, pageName: $this->pageName);
            $this->comments = $current->getCollection();
        } else {
            $current = $current->simplePaginate($this->perPage, pageName: $this->pageName);
            $this->comments = $this->comments->merge($current->items());
        }

        // 分页信息
        $this->pageInfo = [
            'count' => $current->count(),
            'per_page' => $current->perPage(),
            'current_page' => $current->currentPage(),
            'load_status' => 'loading',
            'is_last_page' => 0,
        ];

        if ($this->pageType == 'paginator') {
            $this->pageInfo['total'] = $current->total();
            $this->pageInfo['last_page'] = $current->lastPage();

            if ($this->pageInfo['current_page'] >= $this->pageInfo['last_page']) {
                $this->pageInfo['is_last_page'] = 1;
                $this->pageInfo['load_status'] = 'nomore';

                if ($this->pageInfo['current_page'] == 1 && $this->pageInfo['count'] <= 0) {
                    $this->pageInfo['load_status'] = 'empty';
                }
            }
        } else {
            if ($this->pageInfo['count'] < $this->pageInfo['per_page']) {
                $this->pageInfo['is_last_page'] = 1;
                $this->pageInfo['load_status'] = 'nomore';

                if ($this->pageInfo['current_page'] == 1 && $this->pageInfo['count'] <= 0) {
                    $this->pageInfo['load_status'] = 'empty';
                }
            }
        }

        return view('sn-comment::livewire.comment-list', [
            'paginatorLink' => $current->links(),
        ])->title('评论列表');
    }
}
