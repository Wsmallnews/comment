@php
    $childPageName = config('sn-comment.page_name') . '_' . $comment->id . '_children';
@endphp

<div class="flex py-4" >
    <x-filament::avatar
        :src="$comment->user_avatar"
        :alt="$comment->user_nickname"
        size="lg"
    />

    <div class="flex-1 flex flex-col ml-3">
        <div class="text-sm text-gray-500">
            <div class="inline-block">{{ $comment->user_nickname }}</div>
            <div class="inline-block float-right">...</div>
        </div>

        <div class="py-1 text-base text-gray-700">
            {{ $comment->content }}
        </div>

        <div class="flex justify-between items-center text-sm text-gray-400">
            <div>
                <span>{{ $comment->created_at }}</span> • <span>{{ $comment->from_district }}</span>
                <span class="ml-4 text-gray-500" @click="$dispatch('open-modal', { id: 'edit-user' })">回复</span>
            </div>
            <div class="flex items-center inline-block float-right text-sm" @click="like_num = await $wire.toggleLike()" x-data="{
                like_num: {{ $comment->like_num }}
            }" >
                <x-heroicon-o-heart class="size-4 inline mr-1" /> <span x-text="like_num"><span>
            </div>
        </div>

        {{-- 子评论列表 --}}
        @if ($comment->comment_num > 0)
            @if (!$loadChildren)
                <div class="relative text-sm text-gray-400 flex items-center">
                    <div class="w-8 inline-block">
                        <div class="h-[1px] w-8  border-b border-gray-400 absolute top-1/2"></div>
                    </div>
                    <div class="flex justify-center items-center ml-2" wire:loading.flex wire:target="startLoadChildren">
                        <x-filament::loading-indicator class="h-5 w-5 mr-2 inline-block" /> 正在加载更多
                    </div>
                    <div class="inline-block ml-2" wire:loading.remove wire:target="startLoadChildren" wire:click="startLoadChildren">展开 {{ $comment->comment_num }} 条回复</div>
                </div>
            @else
                <div class="container mx-auto" @hidden="$wire.hiddenChildren">
                    <livewire:sn-comment-list key="children-{{$comment->id}}" :parent-id="$comment->id" :page-name="$childPageName" page-type="manual" :load-children="false" />
                </div>
            @endif
        @endif
    </div>



    <x-filament::modal width="md" icon="heroicon-o-information-circle" icon-color="danger" alignment="center" sticky-header sticky-footer :close-by-clicking-away="false" :close-by-escaping="false" id="edit-user">
        {{-- Modal content --}}

        <x-slot name="heading">
            Modal heading
        </x-slot>

        <x-slot name="description">
            Modal description
        </x-slot>

        <x-slot name="footer">
            我是底部内容
        </x-slot>


        <x-slot name="footerActions">
            我是底部动作
        </x-slot>

        <div>
            <form wire:submit="create">
                {{ $this->form }}

                <button type="submit">
                    Submit
                </button>
            </form>

            <x-filament-actions::modals />
        </div>
    </x-filament::modal>
</div>