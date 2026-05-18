<div class="w-full">
    <div @class([
        'sn-container px-4 py-8' => $contained,
        'w-full flex flex-col gap-4',
    ])>
        @if ($this->parentId === 0 && $this->filamentCommentAction->isVisible())
            <div class="w-full flex justify-end">
                {{ $this->filamentCommentAction }}
            </div>
        @endif

        @if ($comments->isNotEmpty())
            <x-sn-support::paginators.container class="flex flex-col gap-4" :page-type="$pageType" :page-info="$pageInfo" :paginator-link="$paginatorLink" :page-name="$pageName">
                @foreach($comments as $comment)
                    <livewire:sn-comment-fi-comment
                        :scope-type="$scopeType" :scope-id="$scopeId"
                        :commenter="$commenter" :be-replyer="$beReplyer"
                        :commentable="$commentable" :comment="$comment" :user="$user"
                        :content-type="$contentType" :comment-status="$commentStatus"
                        :load-children="$loadChildren"
                        :key="'fi-components-sn-comment:' . $comment->id"
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
                        {{ $this->getEmptyLabel() }}
                    </x-slot>

                    <x-slot name="description">
                        {{ $this->getEmptyTipLabel() }}
                    </x-slot>
                </x-filament::empty-state>
            @endif
        @endif
    </div>

    <x-filament-actions::modals />
</div>