<?php

namespace Wsmallnews\Comment\Livewire;

use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;
use Wsmallnews\Comment\Models\Comment;

class CommentListBak extends Component
{
    use WithPagination;
    use WithoutUrlPagination;

    public int $parentId = 0;

    public int | string $pageName;

    public int | string $perPage;

    public string $paginationType;

    public Collection $comments;

    public array $paginatorsInfo = [];

    public $currentData;


    public $title;

    public function mount($pageName = '', $perPage = 0, $paginationType = '')
    {
        // 分页名字
        $this->pageName = $pageName ?: config('sn-comment.page_name');

        // 每页条数
        $this->perPage = $perPage ?: config('sn-comment.per_page');

        // 分页类型
        $this->paginationType = $paginationType ?: config('sn-comment.pagination_type');

        $this->comments = $this->comments ?? collect([]);
    }


    #[On('save-page')]
    public function save($bbb = '')
    {
        $this->comments[0]->content = $bbb ?: $this->title;
        unset($this->comments[2]);
        $this->pageName = $bbb ?: $this->title;
    }



    public function pageEvent($event)
    {
        echo $event;exit;
    }


    public function render()
    {
        // \Illuminate\Support\Facades\DB::listen(function($query) {
        //     echo $query->sql;
        // });
        $current = Comment::query()->where('parent_id', $this->parentId);

        if ($this->paginationType == 'pagination') {
            $current = $current->paginate($this->perPage, pageName: $this->pageName);
            $this->comments = $current->getCollection();        // 获取 collection 格式的数据
        } else {
            $current = $current->simplePaginate($this->perPage, pageName: $this->pageName);
            $this->comments = $this->comments->merge($current->items());
        }

        // 分页信息
        $this->paginatorsInfo = [
            'count' => $current->count(),
            'per_page' => $current->perPage(),
            'current_page' => $current->currentPage(),
        ];

        if ($this->paginationType == 'pagination') {
            $this->paginatorsInfo['total'] = $current->total();
            $this->paginatorsInfo['last_page'] = $current->lastPage();

            $this->paginatorsInfo['is_last_page'] = 0;        // 默认不是最有一页
            if ($this->paginatorsInfo['current_page'] >= $this->paginatorsInfo['last_page']) {
                $this->paginatorsInfo['is_last_page'] = 1;
            }
        } else {
            $this->paginatorsInfo['is_last_page'] = 0;        // 默认不是最有一页
            if ($this->paginatorsInfo['count'] < $this->paginatorsInfo['per_page']) {
                $this->paginatorsInfo['is_last_page'] = 1;
            }
        }

        // $this->paginatorsInfo = [
        //     'count' => $current->count(),
        //     'total' => $current->total(),
        //     'per_page' => $current->perPage(),
        //     'current_page' => $current->currentPage(),
        //     'last_page' => $current->lastPage(),
        // ];

        return view('sn-comment::livewire.comment-list', [
            'pagination' => $current->links()
        ])->title('评论列表');
    }
}
