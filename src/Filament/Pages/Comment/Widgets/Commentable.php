<?php

namespace Wsmallnews\Comment\Filament\Pages\Comment\Widgets;

use Filament\Widgets\Widget;
use Livewire\Attributes\Reactive;
use Illuminate\Database\Eloquent\Model;
use Wsmallnews\Comment\Livewire\Concerns\CanAddComment;
use Wsmallnews\Support\Livewire\Concerns\CanBeContained;
use Wsmallnews\Support\Livewire\Concerns\HasContentType;
use Wsmallnews\Support\Livewire\Concerns\Scopeable;

class Commentable extends Widget
{
    use CanAddComment;
    use CanBeContained;
    use HasContentType;
    use Scopeable;

    #[Reactive]
    public ?Model $record = null;

    public ?array $properties = [];

    protected int | string | array $columnSpan = 'full';

    protected string $view = 'sn-comment::filament.pages.comment.widgets.commentable';
}
