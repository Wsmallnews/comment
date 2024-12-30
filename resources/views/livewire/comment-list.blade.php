<div class="container mx-auto">
    <livewire:sn-comment-add />

    @foreach($comments as $comment)
        <livewire:sn-comment-card key="comment-{{$comment->id}}" :comment="$comment" :load-children="$loadChildren" />
    @endforeach

    {{-- 分页 --}}
    <div class="container">
        <x-sn-comment::paginators.scroll :page-type="$pageType" :page-info="$pageInfo" :paginator-link="$paginatorLink" :page-name="$pageName" />

        {{-- @if ($pageType == 'paginator')
            {!! $paginatorLink !!}
        @else
            <livewire:sn-paginator :page-info="$pageInfo" :page-type="$pageType" :page-name="$pageName" />
        @endif --}}
    </div>
</div>
