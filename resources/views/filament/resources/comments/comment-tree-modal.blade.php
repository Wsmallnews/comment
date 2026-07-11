@php
    use Wsmallnews\Comment\Support\Utils;
@endphp

<livewire:sn-comment-fi-comment-components::comments
    :scope-type="$rootComment->scope_type"
    :scope-id="$rootComment->scope_id"
    :ids="[$rootComment->id]"
    :commentable="$commentable"
    :load-children="true"
    :content-type="$rootComment->content_type"
    :can-add-comment="false"
    page-type="paginator"
    :contained="false"
    :key="'comment-related-tree-' . $rootComment->id"
/>
