<?php

namespace Wsmallnews\Comment\Livewire;

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

    public function mount($pageName = '', $perPage = 0, $pageType = '')
    {
        // 分页名字
        $this->pageName = $pageName ?: config('sn-comment.page_name');

        // 每页条数
        $this->perPage = $perPage ?: config('sn-comment.per_page');

        // 分页类型
        $this->pageType = $pageType ?: config('sn-comment.page_type');

        $this->comments = $this->comments ?? collect([]);
    }

    public function render()
    {
        // \Illuminate\Support\Facades\DB::listen(function($query) {
        //     echo $query->sql . json_encode($query->bindings);
        // });
        $current = Comment::query()->where('parent_id', $this->parentId);

        if ($this->pageType == 'paginator') {
            $current = $current->paginate($this->perPage, pageName: $this->pageName);
            $this->comments = $current->getCollection();        // 获取 collection 格式的数据
        } else {
            $current = $current->simplePaginate($this->perPage, pageName: $this->pageName);
            $this->comments = $this->comments->merge($current->items());
        }

        // 分页信息
        $this->pageInfo = [
            'count' => $current->count(),                                       // 当前查询最终的结果数量
            'per_page' => $current->perPage(),                                  // 每页条件
            'current_page' => $current->currentPage(),                          // 当前页码
            'load_status' => 'loading',                                         // 默认加载中
            'is_last_page' => 0,                                                // 默认不是最有一页
        ];

        if ($this->pageType == 'paginator') {
            $this->pageInfo['total'] = $current->total();                  // 满足条件总条数
            $this->pageInfo['last_page'] = $current->lastPage();           // 最后的页码

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
