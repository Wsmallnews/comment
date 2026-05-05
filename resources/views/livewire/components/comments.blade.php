<div class="w-full">
    <div @class([
        'sn-container px-4 py-8' => $contained,
        'w-full flex flex-col gap-4',
    ])>
        @if ($this->parentId === 0 && $this->commentAction->isVisible())
            <div class="w-full flex justify-end">
                {{ $this->commentAction }}
            </div>
        @endif

        @if ($comments->isNotEmpty())
            <x-sn-support::paginators.container class="flex flex-col gap-4" :page-type="$pageType" :page-info="$pageInfo" :paginator-link="$paginatorLink" :page-name="$pageName">
                @foreach($comments as $comment)
                    <livewire:sn-comment-components-comment
                        key="comment-{{$comment->id}}"
                        :scope-type="$scopeType" :scope-id="$scopeId"
                        :commentable="$commentable" :comment="$comment" :user="$user"
                        :content-type="$contentType"
                        :load-children="$loadChildren"
                    />
                @endforeach
            </x-sn-support::paginators.container>
        @else
            @if($this->parentId === 0)
                <x-filament::empty-state
                    :contained="false"
                    icon="heroicon-m-document-text"
                    icon-color="gray"
                >
                    <x-slot name="heading">
                        暂无评论
                    </x-slot>

                    <x-slot name="description">
                        优质评论更容易获得他人回复。
                    </x-slot>
                </x-filament::empty-state>
            @endif
        @endif
    </div>

    <x-filament-actions::modals />
</div>