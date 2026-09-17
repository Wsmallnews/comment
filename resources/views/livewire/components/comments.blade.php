<div class="w-full @container">
    <div @class([
        'sn-container sn-padded' => $contained,
        'w-full flex flex-col sn-gap',
    ])>
        @if ($this->parentId === 0 && $this->commentAction->isVisible())
            <div class="w-full flex justify-end">
                {{ $this->commentAction }}
            </div>
        @endif

        @if ($comments->isNotEmpty())
            <x-sn-support::paginators.container class="flex flex-col sn-gap" :page-type="$pageType" :page-info="$pageInfo" :paginator-link="$paginatorLink" :page-name="$pageName">
                @foreach($comments as $comment)
                    <livewire:sn-comment::components.comment
                        :scope-type="$scopeType" :scope-id="$scopeId"
                        :commenter="$commenter" :be-replyer="$beReplyer"
                        :commentable="$commentable" :comment="$comment" :auth-user="$authUser"
                        :content-type="$contentType" :comment-status="$commentStatus"
                        :load-children="$loadChildren"
                        :key="'components-sn-comment:' . $comment->id"
                    />
                @endforeach
            </x-sn-support::paginators.container>
        @else
            @if($this->parentId === 0)
                <x-sn-support::empty
                    :icon="\Filament\Support\Icons\Heroicon::OutlinedDocumentText"
                    icon-color="gray"
                    :heading="$this->getEmptyLabel()"
                    :description="$this->getEmptyTipLabel()"
                    :contained="false"
                />
            @endif
        @endif
    </div>

    <x-filament-actions::modals />
</div>