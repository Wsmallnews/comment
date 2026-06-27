@php
    use Wsmallnews\Support\Enums\ContentType;
    /** @var \Wsmallnews\Comment\Models\Comment $record */
@endphp

<div class="max-w-md">
    @if ($record->content_type === ContentType::Textarea)
        <div class="truncate text-sm text-gray-700 dark:text-gray-300" title="{{ $record->content }}">
            {{ \Illuminate\Support\Str::limit($record->content, 80) }}
        </div>
    @else
        <x-filament::button
            tag="button"
            color="gray"
            size="xs"
            icon="heroicon-m-eye"
            x-on:click.stop="$dispatch('open-modal', { id: 'comment-content-{{ $record->id }}' })"
        >
            {{ __('sn-comment::comment.comment_resource.action.view_content') }}
        </x-filament::button>

        <x-filament::modal id="comment-content-{{ $record->id }}" width="3xl">
            <x-slot name="heading">
                {{ __('sn-comment::comment.comment_resource.comment_content') }} #{{ $record->id }}
            </x-slot>

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
        </x-filament::modal>
    @endif
</div>
