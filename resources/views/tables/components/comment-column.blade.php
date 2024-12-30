@php
    $record = $getRecord();
@endphp

<div
    {{
        $attributes
            ->merge($getExtraAttributes(), escape: false)
            ->class([
                'fi-ta-text grid w-full gap-y-1',
                'px-3 py-4' => ! $isInline()
            ])
    }}
>

    <livewire:sn-comment-card :comment="$record" />

    @if ($record->children_num)
        展开{{$record->children_num}}条评论
        <div class="flex">
            <livewire:sn-comment-list :parent_id="$record->id" />
        </div>
    @endif


    {{-- <div class="flex flex-row flex-nowrap">
        <x-filament::avatar
            :src="$record->user_avatar"
            :alt="$record->user_nickname"
        />

        <div class="flex flex-col flex-nowrap mx-3">
            <div class="text-sm text-gray-500">{{ $record->user_nickname }}</div>

            <div class="text-base text-gray-700">
                {{ $record->content }}
            </div>

            <div class="text-sm text-gray-400">{{ $record->created_at }}</div>

            @if(count($record->children))
                <div x-data="{ is_show: 1 }" >
                    <button @click="is_show = !is_show">toggle</button>
                    <div x-show="is_show" wire:transition class="child-box flex flex-col flex-nowrap my-3" >
                        @foreach($record->children as $child)
                            <div class="flex flex-row flex-nowrap">
                                <x-filament::avatar
                                    :src="$child->user_avatar"
                                    :alt="$child->user_nickname"
                                />

                                <div class="flex flex-col flex-nowrap mx-3">
                                    <div class="text-sm text-gray-500">{{ $child->user_nickname }}</div>

                                    <div class="text-base text-gray-700">
                                        {{ $child->content }}
                                    </div>

                                    <div class="text-sm text-gray-400">{{ $child->created_at }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div> --}}
</div>