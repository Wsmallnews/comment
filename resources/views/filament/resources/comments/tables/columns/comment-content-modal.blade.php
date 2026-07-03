@php
    /** @var \Wsmallnews\Comment\Models\Comment $record */
@endphp

@if ($record->commentContent)
    <x-sn-support::content
        :content-type="$record->commentContent->content_type"
        :content="$record->commentContent->content"
    />
@else
    <x-sn-support::content
        :content-type="$record->content_type"
        :content="$record->content"
    />
@endif
