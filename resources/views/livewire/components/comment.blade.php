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
                <div class="flex items-center gap-2">
                    <div class="sn-tip-text inline-block">{{ $comment->commenter_name }}</div>

                    @if ($comment->be_replyer_id)
                        <x-filament::icon icon="heroicon-m-play" class="sn-tip-text w-3 h-3" />
                        <div class="sn-tip-text inline-block">{{ $comment->be_replyer_name }}</div>
                    @endif
                </div>

                <div class="w-full flex flex-col gap-2">
                    <div class="sn-content-text">
                        {{ $comment->content }}
                    </div>
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

                        @if ($this->replyAction->isVisible())
                            <span class="sn-tip-text sn-hover flex items-center cursor-pointer" wire:click="mountAction('reply', { id: {{ $comment->id }} })">
                                <x-filament::loading-indicator class="h-4 w-4 mr-2 inline-block" wire:loading wire:target="mountAction('reply', { id: {{ $comment->id }} })"/>
                                回复
                            </span>
                        @endif
                    </div>
                    <div class="sn-tip-text flex items-center gap-2" wire:click="toggleLike" x-data="{
                        like_num: {{ $comment->like_num }}
                    }" >
                        <x-filament::icon icon="heroicon-o-heart" class="size-4" />
                        <span>{{ $comment->like_num }}</span>
                        {{-- <span x-text="like_num"><span> --}}
                    </div>
                </div>
            </div>

            {{-- 子评论列表 --}}
            @if ($comment->comment_num > 0)
                @if (!$loadChildren)
                    <div class="sn-tip-text w-full flex items-center gap-2 relative">
                        <div class="w-8 inline-block">
                            <div class="h-[1px] w-8 border-b border-gray-400 absolute top-1/2"></div>
                        </div>
                        <div class="flex justify-center items-center gap-2" wire:loading.flex wire:target="startLoadChildren">
                            <x-filament::loading-indicator class="h-4 w-4 inline-block" />正在加载更多
                        </div>
                        <div class="inline-block" wire:loading.remove wire:target="startLoadChildren" wire:click="startLoadChildren">展开 {{ $comment->comment_num }} 条回复</div>
                    </div>
                @else
                    <div class="w-full" @hidden="$wire.hiddenChildren">
                        <livewire:sn-comment-components-comments 
                            key="children-{{$comment->id}}" 
                            :scope-type="$scopeType" :scope-id="$scopeId"
                            :parent-id="$comment->id" :commentable="$commentable" :user="$user"
                            :page-name="$childPageName" 
                            page-type="manual" 
                            :load-children="false" 
                            :contained="false"
                        />
                    </div>
                @endif
            @endif
        </div>
    </div>

    <x-filament-actions::modals />
</div>