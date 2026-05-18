<x-filament-widgets::widget>
    <livewire:sn-comment-fi-comments
        :properties="$this->getProperties()"
        :scope-type="$scopeType" :scope-id="$scopeId"
        :can-add-comment="$canAddComment"
        :commentable="$commentable"
        :commenter="$commenter"
        :content-type="$contentType" :comment-status="$commentStatus"
        page-name="comment-page"
        page-type="scroll"
        :load-children="false"
        :contained="$contained"
        :key="'fi-components-sn-comments:' . $widgetType . ':' . $record->id" />
</x-filament-widgets::widget>