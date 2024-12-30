@props([
    'pageType',
    'pageInfo',
    'pageName',
    'paginatorLink'
])

<div class="container mx-auto">
    @if ($pageType == 'scroll')
        @if ($pageInfo['load_status'] == 'loading')
            <div class="flex justify-center items-center text-gray-400" x-intersect="$wire.nextPage('{{ $pageName }}')">
                <x-filament::loading-indicator class="h-5 w-5 mr-2" /> 正在加载更多
            </div>
        @elseif ($pageInfo['load_status'] == 'empty')
            <div class="flex justify-center items-center text-gray-400" >
                暂没有评论数据
            </div>
        @elseif ($pageInfo['load_status'] == 'nomore')
            <div class="flex justify-center items-center text-gray-400" >
                已经到底啦
            </div>
        @endif
    @elseif ($pageType == 'manual')
        <div class="relative text-sm text-gray-400 flex items-center">
            <div class="w-8 inline-block mr-2">
                <div class="h-[1px] w-8  border-b border-gray-400 absolute top-1/2"></div>
            </div>

            @if ($pageInfo['load_status'] == 'loading')
                <div class="flex justify-center items-center" wire:loading.flex>
                    <x-filament::loading-indicator class="h-5 w-5 mr-2 inline-block" /> 正在加载更多
                </div>
                <div wire:loading.remove>
                    <span class="cursor-pointer" wire:click="nextPage('{{ $pageName }}')">展开更多</span>
                </div>
            @elseif ($pageInfo['load_status'] == 'empty')
                <span>暂没有评论数据</span>
            @elseif ($pageInfo['load_status'] == 'nomore')
                <span>已经到底啦</span>
            @endif

            @if ($pageInfo['load_status'] != 'empty')
                <span class="ml-2 cursor-pointer" @click="$dispatch('hidden')">收起</span>
            @endif
        </div>
    @elseif ($pageType == 'paginator')
        {!! $paginatorLink !!}
    @endif
</div>