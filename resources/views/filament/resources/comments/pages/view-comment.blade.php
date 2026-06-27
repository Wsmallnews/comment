@php
    use Wsmallnews\Comment\Enums\CommentStatus;
    use Wsmallnews\Comment\Support\Utils;
@endphp

<div>
    <div class="grid grid-cols-1 gap-6">
        {{-- 评论内容 --}}
        <section class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                        评论 #{{ $record->id }}
                    </h3>
                    <x-filament::badge :color="$record->status->getColor()" :icon="$record->status->getIcon()">
                        {{ $record->status->getLabel() }}
                    </x-filament::badge>
                </div>
            </div>

            <div class="p-6 space-y-5">
                {{-- 评论者 --}}
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center overflow-hidden">
                        @if ($record->commenter_avatar_url)
                            <x-filament::avatar :src="files_url($record->commenter_avatar_url)" :alt="$record->commenter_name" size="lg" />
                        @else
                            <x-filament::icon icon="heroicon-m-user" class="w-6 h-6 text-gray-500 dark:text-gray-400" />
                        @endif
                    </div>
                    <div>
                        <div class="font-medium text-gray-900 dark:text-white">{{ $record->commenter_name }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $record->commenter_type }}</div>
                    </div>
                </div>

                {{-- 被回复者 --}}
                @if ($record->be_replyer_id)
                    <div class="flex items-center gap-3 px-4 py-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800 rounded-xl">
                        <x-filament::icon icon="heroicon-m-play" class="w-4 h-4 text-amber-500 shrink-0" />
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('sn-comment::comment.comment_resource.reply_to') }}</span>
                        <span class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $record->be_replyer_name }}</span>
                        <span class="text-xs text-amber-500 ml-auto">{{ __('sn-comment::comment.comment_resource.be_replyer') }}</span>
                    </div>
                @endif

                {{-- 内容 --}}
                <div>
                    <div class="text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap leading-relaxed">
                        {{ $record->content }}
                    </div>
                </div>

                {{-- 统计 --}}
                <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                    <div class="flex items-center gap-1">
                        <x-filament::icon icon="heroicon-m-chat-bubble-left" class="w-4 h-4" />
                        <span>{{ __('sn-comment::comment.comment_resource.visible_replies') }}: {{ $record->counter['comment_num'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <x-filament::icon icon="heroicon-m-chat-bubble-left-right" class="w-4 h-4" />
                        <span>{{ __('sn-comment::comment.comment_resource.total_replies') }}: {{ $record->counter['total_comment_num'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <x-filament::icon icon="heroicon-m-heart" class="w-4 h-4" />
                        <span>{{ __('sn-comment::comment.comment_resource.likes') }}: {{ $record->counter['like_num'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- 元信息 + 评论主体 + 父评论 --}}
        <section class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-gray-400 dark:text-gray-500 uppercase tracking-wider font-semibold">
                            {{ __('sn-comment::comment.comment_resource.created_at') }}
                        </label>
                        <div class="text-sm font-medium text-gray-800 dark:text-gray-200 mt-0.5">{{ $record->created_at }}</div>
                        <div class="text-xs text-gray-400 mt-0.5">{{ $record->created_at->diffForHumans() }}</div>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 dark:text-gray-500 uppercase tracking-wider font-semibold">
                            {{ __('sn-comment::comment.comment_resource.scope') }}
                        </label>
                        <div class="flex items-center gap-2 mt-0.5">
                            <code class="text-xs border rounded px-2 py-0.5 text-gray-600 dark:text-gray-400">{{ $record->scope_type }}</code>
                            <span class="text-gray-300">·</span>
                            <code class="text-xs border rounded px-2 py-0.5 text-gray-600 dark:text-gray-400">{{ $record->scope_id }}</code>
                        </div>
                    </div>
                </div>

                {{-- 评论主体 --}}
                @if ($record->commentable)
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
                        <label class="text-xs text-gray-400 dark:text-gray-500 uppercase tracking-wider font-semibold mb-2 block">
                            {{ __('sn-comment::comment.comment_resource.commentable') }}
                        </label>
                        <div class="text-sm font-medium text-gray-800 dark:text-gray-200">
                            {{ class_basename($record->commentable_type) }} #{{ $record->commentable_id }}
                        </div>
                    </div>
                @endif

                {{-- 父级评论 --}}
                @if ($record->parent_id)
                    @php
                        $parent = Utils::getCommentModel()::find($record->parent_id);
                    @endphp
                    @if ($parent)
                        <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
                            <label class="text-xs text-gray-400 dark:text-gray-500 uppercase tracking-wider font-semibold mb-2 block">
                                {{ __('sn-comment::comment.comment_resource.parent_comment') }}
                            </label>
                            <div class="flex items-center gap-2">
                                <div class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                    #{{ $parent->id }} — {{ $parent->commenter_name }}
                                </div>
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">{{ \Illuminate\Support\Str::limit($parent->content, 120) }}</div>
                        </div>
                    @endif
                @endif
            </div>
        </section>
    </div>
</div>
