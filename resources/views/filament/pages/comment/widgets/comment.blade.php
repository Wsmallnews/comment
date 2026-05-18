<x-filament-widgets::widget>
    <livewire:sn-comment-fi-comments
        :properties="$this->getProperties()"
        :scope-type="$scopeType" :scope-id="$scopeId"
        :can-add-comment="$canAddComment"
        :commentable="$commentable"
        :commenter="$commenter"
        :content-type="$contentType"
        page-name="comment-page"
        page-type="paginator"
        :load-children="false"
        :contained="$contained" />
</x-filament-widgets::widget>