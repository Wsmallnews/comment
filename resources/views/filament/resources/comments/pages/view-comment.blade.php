@php
    use Wsmallnews\Comment\Enums\CommentStatus;
    use Wsmallnews\Comment\Support\Utils;
    use Wsmallnews\Support\Enums\ContentType;
    use Wsmallnews\Support\Helpers\FilamentModelHelper;

    $record = $this->getRecord();

    // 关联模型
    $commenter = $record->commenter;
    $beReplyer = $record->beReplyer;
    $commentable = $record->commentable;

    // 内容
    $contentType = $record->content_type;
    $content = $contentType === ContentType::Textarea ? $record->content : $record->commentContent?->content;
@endphp

<x-filament-panels::page>
    <div class="w-full flex flex-col gap-4">
        <div class="sn-container p-6 space-y-5">

            {{-- 评论者 & 被回复者 --}}
            <div class="flex flex-col lg:flex-row gap-4">
                {{-- 评论者 --}}
                @if ($commenter)
                    @php
                        $commenterUrl = FilamentModelHelper::getUrl($commenter);
                        $commenterCover = FilamentModelHelper::getCoverUrl($commenter);
                        $commenterTitle = FilamentModelHelper::getTitle($commenter);
                        $commenterDesc = FilamentModelHelper::getDescription($commenter);
                    @endphp
                    <div class="flex-1 flex items-center gap-4 p-4 sn-contour sn-rounded">
                        <div class="sn-avatar sn-avatar-lg overflow-hidden">
                            @if ($commenterCover)
                                <img class="w-full h-full object-cover" src="{{ files_url($commenterCover) }}" alt="{{ $commenterTitle }}" />
                            @else
                                <div class="sn-image-placeholder">
                                    <x-filament::icon icon="heroicon-m-user" class="w-6 h-6" />
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                @if ($commenterUrl)
                                    <a href="{{ $commenterUrl }}" class="no-underline">
                                        <span class="sn-h3-text sn-hover">{{ $commenterTitle }}</span>
                                    </a>
                                @else
                                    <span class="sn-h3-text">{{ $commenterTitle }}</span>
                                @endif
                                <span class="sn-badge sn-badge-primary sn-badge-sm">
                                    {{ FilamentModelHelper::getModelLabel($commenter) }}
                                </span>
                            </div>
                            @if ($commenterDesc)
                                <div class="sn-descript-text">{{ $commenterDesc }}</div>
                            @endif
                        </div>
                        <div class="sn-badge sn-badge-gray">
                            {{ __('sn-comment::comment.comment_resource.commenter') }}
                        </div>
                    </div>
                @endif

                {{-- 被回复者 --}}
                @if ($beReplyer)
                    @php
                        $beReplyerUrl = FilamentModelHelper::getUrl($beReplyer);
                        $beReplyerCover = FilamentModelHelper::getCoverUrl($beReplyer);
                        $beReplyerTitle = FilamentModelHelper::getTitle($beReplyer);
                        $beReplyerDesc = FilamentModelHelper::getDescription($beReplyer);
                    @endphp
                    <div class="flex-1 flex items-center gap-3 px-4 py-3 sn-rounded" style="background: var(--color-warning-50, #fffbeb); border: 1px solid var(--color-warning-200, #fde68a);">
                        <div class="sn-avatar sn-avatar-sm overflow-hidden">
                            @if ($beReplyerCover)
                                <img class="w-full h-full object-cover" src="{{ files_url($beReplyerCover) }}" alt="{{ $beReplyerTitle }}" />
                            @else
                                <div class="sn-image-placeholder">
                                    <x-filament::icon icon="heroicon-m-user" class="w-6 h-6" />
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <x-filament::icon icon="heroicon-m-play" class="w-3.5 h-3.5 text-amber-500 shrink-0" />
                                @if ($beReplyerUrl)
                                    <a href="{{ $beReplyerUrl }}" class="no-underline">
                                        <span class="sn-h4-text sn-hover">{{ $beReplyerTitle }}</span>
                                    </a>
                                @else
                                    <span class="sn-h4-text">{{ $beReplyerTitle }}</span>
                                @endif
                                <span class="sn-badge sn-badge-primary sn-badge-sm">
                                    {{ FilamentModelHelper::getModelLabel($beReplyer) }}
                                </span>
                            </div>
                            @if ($beReplyerDesc)
                                <div class="sn-descript-text">{{ $beReplyerDesc }}</div>
                            @endif
                        </div>
                        <span class="sn-badge sn-badge-warning sn-badge-sm">
                            {{ __('sn-comment::comment.comment_resource.be_replyer') }}
                        </span>
                    </div>
                @endif
            </div>

            {{-- 评论内容 + 统计 --}}
            <div class="w-full flex flex-col items-center gap-4">
                <div class="w-full flex items-center justify-between">
                    <h4 class="sn-tip-text uppercase tracking-wider font-semibold">
                        {{ __('sn-comment::comment.comment_resource.comment_content') }}
                    </h4>
                    <div class="flex items-center gap-4 sn-tip-text">
                        <span class="flex items-center gap-1.5">
                            <x-filament::icon icon="heroicon-s-chat-bubble-left-right" class="w-3.5 h-3.5 sn-primary-text" />
                            <span>{{ __('sn-comment::comment.comment_resource.visible_replies') }}</span>
                            <span class="font-semibold sn-content-text">{{ $record->counter['comment_num'] ?? 0 }}</span>
                        </span>
                        <span class="flex items-center gap-1.5">
                            <x-filament::icon icon="heroicon-s-chat-bubble-left" class="w-3.5 h-3.5 sn-gray-text" />
                            <span>{{ __('sn-comment::comment.comment_resource.total_replies') }}</span>
                            <span class="font-semibold sn-content-text">{{ $record->counter['total_comment_num'] ?? 0 }}</span>
                        </span>
                        <span class="flex items-center gap-1.5">
                            <x-filament::icon icon="heroicon-s-heart" class="w-3.5 h-3.5 sn-danger-text" />
                            <span>{{ __('sn-comment::comment.comment_resource.likes') }}</span>
                            <span class="font-semibold sn-content-text">{{ $record->counter['like_num'] ?? 0 }}</span>
                        </span>
                    </div>
                </div>
                <div class="sn-contour sn-rounded w-full p-4">
                    <x-sn-support::content
                        :content-type="$contentType"
                        :content="$content"
                    />
                </div>

                {{-- 作用域 & 时间 --}}
                <div class="w-full flex items-center justify-end gap-3 sn-tip-text">
                    <div class="sn-badge sn-badge-gray flex items-center gap-1">
                        {{ $record->scope_type }} : {{ $record->scope_id }}
                    </div>
                    <div class="flex items-center gap-1">
                        <x-filament::icon icon="heroicon-m-clock" class="w-3.5 h-3.5" />
                        <span>{{ $record->created_at->format('Y-m-d H:i') }}</span>
                        <span class="sn-gray-text">·</span>
                        <span>{{ $record->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 元信息区：评论主体、父级评论 --}}
        <div class="sn-container p-6 space-y-5">

            {{-- 评论主体 --}}
            @if ($commentable)
                @php
                    $commentableUrl = FilamentModelHelper::getUrl($commentable);
                    $commentableCover = FilamentModelHelper::getCoverUrl($commentable);
                    $commentableTitle = FilamentModelHelper::getTitle($commentable);
                    $commentableDesc = FilamentModelHelper::getDescription($commentable);
                @endphp
                <div>
                    <h4 class="sn-tip-text uppercase tracking-wider font-semibold mb-3">
                        {{ __('sn-comment::comment.comment_resource.commentable') }}
                    </h4>
                    <div class="flex items-center gap-3 sn-contour sn-rounded p-4">
                        <div class="sn-avatar overflow-hidden" style="border-radius: 0.375rem;">
                            @if ($commentableCover)
                                <img class="w-full h-full object-cover" src="{{ files_url($commentableCover) }}" alt="{{ $commentableTitle }}" />
                            @else
                                <div class="sn-image-placeholder">
                                    <x-filament::icon icon="heroicon-m-document" class="w-5 h-5" />
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                @if ($commentableUrl)
                                    <a href="{{ $commentableUrl }}" class="no-underline flex items-center gap-1">
                                        <span class="sn-primary-text">#{{ $commentable->getKey() }}</span>
                                        <span class="sn-badge sn-badge-primary sn-badge-sm">
                                            {{ FilamentModelHelper::getModelLabel($commentable) }}
                                        </span>
                                        <span class="sn-content-text sn-hover truncate">{{ $commentableTitle }}</span>
                                    </a>
                                @else
                                    <span class="sn-primary-text">#{{ $commentable->getKey() }}</span>
                                    <span class="sn-badge sn-badge-primary sn-badge-sm">
                                        {{ FilamentModelHelper::getModelLabel($commentable) }}
                                    </span>
                                    <span class="sn-content-text truncate">{{ $commentableTitle }}</span>
                                @endif
                            </div>
                            @if ($commentableDesc)
                                <div class="sn-descript-text truncate">{{ $commentableDesc }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- 父级评论 --}}
            @if ($record->parent_id)
                @php
                    $parent = Utils::getCommentModel()::find($record->parent_id);
                @endphp
                @if ($parent)
                    <div>
                        <h4 class="sn-tip-text uppercase tracking-wider font-semibold mb-3">
                            {{ __('sn-comment::comment.comment_resource.parent_comment') }}
                        </h4>
                        <div class="sn-contour sn-rounded p-4">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="sn-avatar sn-avatar-sm">
                                    <span class="flex items-center justify-center w-full h-full text-xs font-bold" style="background: var(--color-primary-100); color: var(--color-primary-700);">
                                        {{ mb_substr($parent->commenter_name ?? '?', 0, 1) }}
                                    </span>
                                </div>
                                <span class="sn-h4-text">{{ $parent->commenter_name }}</span>
                                <span class="sn-tip-text">#{{ $parent->id }}</span>
                            </div>
                            <div class="sn-descript-text sn-truncate-2 pl-8">
                                {{ \Illuminate\Support\Str::limit($parent->content, 120) }}
                            </div>
                        </div>
                    </div>
                @endif
            @endif

        </div>

    </div>
</x-filament-panels::page>
