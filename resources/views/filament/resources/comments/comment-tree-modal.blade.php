@php
    use Wsmallnews\Comment\Support\Utils;
@endphp

<div class="p-4">
    <div class="mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ __('sn-comment::comment.comment_resource.related_comments') }}
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            #{{ $rootComment->id }} — {{ Str::limit($rootComment->content, 60) }}
        </p>
    </div>

    <livewire:sn-comment-fi-comment-components::comments
        :scope-type="$rootComment->scope_type"
        :scope-id="$rootComment->scope_id"
        :ids="[$rootComment->id]"
        :commentable="$commentable"
        :load-children="true"
        content-type="textarea"
        page-type="manual"
        :contained="false"
        :key="'comment-related-tree-' . $rootComment->id"
    />
</div>
