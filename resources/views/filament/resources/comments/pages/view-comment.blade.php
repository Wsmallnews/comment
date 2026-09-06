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

    // 父级评论
    $parent = $record->parent_id ? Utils::getCommentModel()::find($record->parent_id) : null;
    $parentCommenter = $parent?->commenter;
@endphp

<x-filament-panels::page>
    <div class="w-full flex flex-col sn-gap">
        <div class="sn-container sn-padded flex flex-col sn-gap">
            {{-- 评论者 & 被回复者 --}}
            <div class="flex flex-col lg:flex-row sn-gap">
                {{-- 评论者 --}}
                @if ($commenter)
                    @php
                        $commenterUrl = FilamentModelHelper::getUrl($commenter);
                        $commenterCover = FilamentModelHelper::getCoverUrl($commenter);
                        $commenterTitle = FilamentModelHelper::getTitle($commenter);
                        $commenterDesc = FilamentModelHelper::getDescription($commenter);
                    @endphp
                    <div class="sn-container-primary sn-rounded flex-1 flex items-center gap-4 sn-padded">
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
                                <span class="sn-primary-text">#{{ $commenter->getKey() }}</span>
                                <span class="sn-badge sn-badge-primary">
                                    {{ FilamentModelHelper::getModelLabel($commenter) }}
                                </span>
                                @if ($commenterUrl)
                                    <a href="{{ $commenterUrl }}" class="no-underline">
                                        <span class="sn-content-text sn-hover truncate">{{ $commenterTitle }}</span>
                                    </a>
                                @else
                                    <span class="sn-content-text truncate">{{ $commenterTitle }}</span>
                                @endif
                            </div>
                            @if ($commenterDesc)
                                <div class="sn-descript-text truncate">{{ $commenterDesc }}</div>
                            @endif
                        </div>
                        <div class="sn-badge sn-badge-primary">
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
                    <div class="sn-container flex-1 flex items-center gap-4 sn-padded">
                        <div class="sn-avatar sn-avatar-lg overflow-hidden">
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
                                <span class="sn-primary-text">#{{ $beReplyer->getKey() }}</span>
                                <span class="sn-badge sn-badge-primary">
                                    {{ FilamentModelHelper::getModelLabel($beReplyer) }}
                                </span>
                                @if ($beReplyerUrl)
                                    <a href="{{ $beReplyerUrl }}" class="no-underline">
                                        <span class="sn-content-text sn-hover truncate">{{ $beReplyerTitle }}</span>
                                    </a>
                                @else
                                    <span class="sn-content-text truncate">{{ $beReplyerTitle }}</span>
                                @endif
                            </div>
                            @if ($beReplyerDesc)
                                <div class="sn-descript-text truncate">{{ $beReplyerDesc }}</div>
                            @endif
                        </div>
                        <span class="sn-badge sn-badge-gray">
                            {{ __('sn-comment::comment.comment_resource.be_replyer') }}
                        </span>
                    </div>
                @endif
            </div>

            {{-- 评论内容 + 统计 --}}
            <div class="w-full flex flex-col items-center sn-gap">
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
                <div class="sn-container w-full sn-padded">
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
        <div class="sn-container sn-padded flex flex-col sn-gap">
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
                    <div class="sn-container flex items-center gap-3 sn-padded">
                        <div class="sn-image overflow-hidden">
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
                                <span class="sn-primary-text">#{{ $commentable->getKey() }}</span>
                                <span class="sn-badge sn-badge-primary">
                                    {{ FilamentModelHelper::getModelLabel($commentable) }}
                                </span>
                                @if ($commentableUrl)
                                    <a href="{{ $commentableUrl }}" class="no-underline">
                                        <span class="sn-content-text sn-hover truncate">{{ $commentableTitle }}</span>
                                    </a>
                                @else
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
            @if ($parent)
                <div>
                    <h4 class="sn-tip-text uppercase tracking-wider font-semibold mb-3">
                        {{ __('sn-comment::comment.comment_resource.parent_comment') }}
                    </h4>
                    <div class="sn-container sn-padded space-y-3">
                        {{-- 父级评论的评论者信息 --}}
                        @if ($parentCommenter)
                            @php
                                $parentCommenterUrl = FilamentModelHelper::getUrl($parentCommenter);
                                $parentCommenterCover = FilamentModelHelper::getCoverUrl($parentCommenter);
                                $parentCommenterTitle = FilamentModelHelper::getTitle($parentCommenter);
                                $parentCommenterDesc = FilamentModelHelper::getDescription($parentCommenter);
                            @endphp
                            <div class="flex items-center gap-3">
                                <div class="sn-avatar sn-avatar-sm overflow-hidden">
                                    @if ($parentCommenterCover)
                                        <img class="w-full h-full object-cover" src="{{ files_url($parentCommenterCover) }}" alt="{{ $parentCommenterTitle }}" />
                                    @else
                                        <div class="sn-image-placeholder">
                                            <x-filament::icon icon="heroicon-m-user" class="w-4 h-4" />
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="sn-primary-text">#{{ $parentCommenter->getKey() }}</span>
                                        <span class="sn-badge sn-badge-primary">
                                            {{ FilamentModelHelper::getModelLabel($parentCommenter) }}
                                        </span>
                                        @if ($parentCommenterUrl)
                                            <a href="{{ $parentCommenterUrl }}" class="no-underline">
                                                <span class="sn-content-text sn-hover truncate">{{ $parentCommenterTitle }}</span>
                                            </a>
                                        @else
                                            <span class="sn-content-text truncate">{{ $parentCommenterTitle }}</span>
                                        @endif
                                    </div>
                                    @if ($parentCommenterDesc)
                                        <div class="sn-descript-text truncate">{{ $parentCommenterDesc }}</div>
                                    @endif
                                </div>
                                <span class="sn-tip-text">#{{ $parent->id }}</span>
                            </div>
                        @else
                            {{-- 无评论者模型时，回退显示 commenter_name --}}
                            <div class="flex items-center gap-2">
                                <div class="sn-avatar sn-avatar-sm">
                                    <span class="flex items-center justify-center w-full h-full text-xs font-bold">
                                        {{ mb_substr($parent->commenter_name ?? '?', 0, 1) }}
                                    </span>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="sn-primary-text">#{{ $parent->commenter_id }}</span>
                                        <span class="sn-badge sn-badge-primary">
                                            {{ FilamentModelHelper::getTypeLabel($parent->commenter_type) }}
                                        </span>
                                        <span class="sn-content-text truncate">{{ $parent->commenter_name }}</span>
                                    </div>
                                </div>

                                <span class="sn-tip-text">#{{ $parent->id }}</span>
                            </div>
                        @endif

                        {{-- 父级评论内容 --}}
                        @php
                            $parentContentType = $parent->content_type;
                            $parentContent = $parentContentType === ContentType::Textarea ? $parent->content : $parent->commentContent?->content;
                        @endphp
                        <div class="sn-container w-full sn-padded">
                            <x-sn-support::content
                                :content-type="$parentContentType"
                                :content="$parentContent"
                            />
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
