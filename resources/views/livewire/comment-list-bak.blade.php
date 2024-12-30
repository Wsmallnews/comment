<div class="container mx-auto">
    @foreach($comments as $comment)
        <livewire:sn-comment-card :key="$comment->id" :comment="$comment" />
    @endforeach

    {{-- 分页 --}}
    @if ($paginationType == 'scroll')
        @if (!$paginatorsInfo['is_last_page'])
            <div class="mx-auto h-1 bg-red-500" x-intersect="$wire.nextPage('{{ $pageName }}')" ></div>
        @endif
    @elseif ($paginationType == 'manual')
        <div class="relative text-sm">
            <div class="w-8 inline-block">
                <div class="h-[1px] w-8  border-b border-gray-400 absolute top-1/2"></div>
            </div>

            @if (!$paginatorsInfo['is_last_page'])
                <div class="inline-block text-gray-400 ml-2" wire:click="nextPage('{{ $pageName }}')">展开更多</div>
            @endif

            <div class="inline-block text-gray-400 ml-2" @click="$dispatch('hidden')">收起</div>
        </div>
    @elseif ($paginationType == 'pagination')
        {!! $pagination !!}
    @endif
</div>


{{-- 这里是调用 blade 匿名组件的示例 --}}
<x-sn-comment::paginators.scroll :paginator="$paginatorsInfo" :page-name="$pageName">
    @foreach($comments as $comment)
        <livewire:sn-comment-card :key="$comment->id" :comment="$comment" />
    @endforeach
</x-sn-comment.paginators.croll>