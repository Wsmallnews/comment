<?php

namespace Wsmallnews\Comment\Filament\Pages\Comment\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Reactive;
use Wsmallnews\Comment\Livewire\Concerns\CanAddComment;
use Wsmallnews\Comment\Livewire\Concerns\HasCommentStatus;
use Wsmallnews\Support\Livewire\Concerns\CanBeContained;
use Wsmallnews\Support\Livewire\Concerns\HasContentType;
use Wsmallnews\Support\Livewire\Concerns\HasProperties;
use Wsmallnews\Support\Livewire\Concerns\Scopeable;

class Comment extends Widget
{
    use CanAddComment;
    use CanBeContained;
    use HasCommentStatus;
    use HasContentType;
    use HasProperties;
    use Scopeable;

    #[Reactive]
    public ?Model $record = null;

    /**
     * commentable = 评论主体小部件 | commenter = 评论者小部件
     *
     * @var string
     */
    public string $widgetType = 'commentable';

    protected int | string | array $columnSpan = 'full';

    protected string $view = 'sn-comment::filament.pages.comment.widgets.comment';

    public function getViewData(): array
    {
        return [
            'commentable' => $this->widgetType == 'commentable' ? $this->record : null,
            'commenter' => $this->widgetType == 'commenter' ? $this->record : null,
        ];
    }
}
