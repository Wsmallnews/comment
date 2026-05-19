@php
    use Illuminate\Support\Arr;
    $childPageName = 'ccp_' . $comment->id . '_children';
@endphp

<div class="w-full">
    <div class="w-full flex gap-4 grow">
        <x-filament::avatar
            :src="files_url($comment->commenter_avatar_url)"
            :alt="$comment->commenter_name"
            size="lg"
        />

        <div class="flex flex-col gap-4 grow">
            <div class="w-full flex flex-col gap-2 grow">
                <div class="flex items-center gap-2 justify-between">
                    <div class="flex items-center gap-2">
                        <div class="sn-tip-text inline-block">{{ $comment->commenter_name }}</div>
    
                        @if ($comment->be_replyer_id)
                            <x-filament::icon icon="heroicon-m-play" class="sn-tip-text w-3 h-3" />
                            <div class="sn-tip-text inline-block">{{ $comment->be_replyer_name }}</div>
                        @endif
                    </div>
                    
                    <div class="flex items-center gap-2">
                        @if ($this->filamentDeleteAction->isVisible())
                            <span class="sn-tip-text sn-danger-text flex items-center cursor-pointer" wire:click="mountAction('filamentDelete', { key: {{ $comment->getKey() }} })">
                                <x-filament::loading-indicator class="h-4 w-4 mr-2 inline-block" wire:loading wire:target="mountAction('filamentDelete', { key: {{ $comment->getKey() }} })"/>
                                {{ __('filament-actions::delete.single.label') }}
                            </span>
                        @endif
                        @if ($this->filamentStatusAction->isVisible())
                            <span class="sn-tip-text sn-info-text flex items-center cursor-pointer" wire:click="mountAction('filamentStatus', { key: {{ $comment->getKey() }} })">
                                <x-filament::loading-indicator class="h-4 w-4 mr-2 inline-block" wire:loading wire:target="mountAction('filamentStatus', { key: {{ $comment->getKey() }} })"/>
                                {{ __('sn-comment::comment.action.audit_status_action') }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="w-full flex flex-col gap-2">
                    @if ($this->isFormattedContent() && $comment->commentContent)
                        <x-sn-support::collapse-content
                            :content-type="$comment->commentContent->content_type"
                            :content="$comment->commentContent->content"
                        />
                    @else
                        <x-sn-support::collapse-content
                            :content="$comment->content"
                        />
                    @endif
                    @if ($comment->images)
                        @php
                            $galleries = Arr::map($comment->images, function ($gallery) {
                                return files_url($gallery);
                            });
                        @endphp
                        <x-sn-support::lightbox class="w-full" :galleries="$galleries" thumb-class="size-20" />
                    @endif
                </div>

                <div class="sn-tip-text flex justify-between items-center">
                    <div class="flex gap-2">
                        <div title="{{ $comment->created_at }}">{{ $comment->created_at->diffForHumans() }}</div>

                        @if ($comment->from_district)
                            <div>•</div>
                            <span>{{ $comment->from_district }}</span>
                        @endif

                        @if ($this->filamentReplyAction->isVisible())
                            <span class="sn-tip-text sn-hover flex items-center cursor-pointer" wire:click="mountAction('filamentReply', { id: {{ $comment->id }} })">
                                <x-filament::loading-indicator class="h-4 w-4 mr-2 inline-block" wire:loading wire:target="mountAction('filamentReply', { id: {{ $comment->id }} })"/>
                                {{ __('sn-comment::comment.reply') }}
                            </span>
                        @endif
                    </div>
                    <div class="sn-tip-text flex items-center gap-1">
                        <x-filament::loading-indicator class="size-4" wire:loading wire:target="toggleLike" />
                        @if ($comment->has_liked)
                            <x-filament::icon icon="heroicon-m-heart" class="size-4 text-red-500 cursor-pointer" wire:click="toggleLike" wire:loading.remove wire:target="toggleLike" />
                        @else
                            <x-filament::icon icon="heroicon-o-heart" class="size-4 cursor-pointer" wire:click="toggleLike" wire:loading.remove wire:target="toggleLike" />
                        @endif
                        <span>{{ $comment->counter['like_num'] }}</span>
                    </div>
                </div>
            </div>

            {{-- 子评论列表 --}}
            @if ($comment->counter['comment_num'] > 0)
                @if (!$loadChildren)
                    <div class="sn-tip-text w-full flex items-center gap-2 relative">
                        <div class="w-8 inline-block">
                            <div class="h-0.25 w-8 border-b border-gray-400 absolute top-1/2"></div>
                        </div>
                        <div class="flex justify-center items-center gap-2" wire:loading.flex wire:target="startLoadChildren">
                            <x-filament::loading-indicator class="size-4 inline-block" />{{ __('sn-comment::comment.loading_more') }}
                        </div>
                        <div class="inline-block cursor-pointer" wire:loading.remove wire:target="startLoadChildren" wire:click="startLoadChildren">{{ __('sn-comment::comment.expand_replies', ['count' => $comment->counter['comment_num']]) }}</div>
                    </div>
                @else
                    <div class="w-full" @hidden="$wire.hiddenChildren">
                        <livewire:sn-comment-fi-comments
                            :scope-type="$scopeType" :scope-id="$scopeId"
                            :parent-id="$comment->id"
                            :commenter="$commenter" :be-replyer="$beReplyer"
                            :commentable="$commentable" :user="$user"
                            :content-type="$contentType" :comment-status="$commentStatus"
                            :page-name="$childPageName"
                            page-type="manual"
                            :load-children="false"
                            :contained="false"
                            :key="'fi-components-sn-comment-children:' . $comment->id"
                        />
                    </div>
                @endif
            @endif
        </div>
    </div>

    <x-filament-actions::modals />
</div>