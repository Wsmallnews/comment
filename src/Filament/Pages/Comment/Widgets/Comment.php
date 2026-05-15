<?php

namespace Wsmallnews\Comment\Filament\Pages\Comment\Widgets;

use Filament\Widgets\Widget;
use Livewire\Attributes\Reactive;

class Comment extends Widget
{
    #[Reactive]
    public ?CategoryType $record = null;

    public ?array $properties = [];

    protected int | string | array $columnSpan = 'full';

    // 这个小部件还没写

    protected string $view = 'sn-comment::filament.pages.comment.widgets.comment';
}
