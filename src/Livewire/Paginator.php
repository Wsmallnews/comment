<?php

namespace Wsmallnews\Comment\Livewire;

use Livewire\Attributes\Reactive;
use Livewire\Component;

class Paginator extends Component
{
    public int | string $pageName;

    public int | string $perPage;

    public string $pageType;

    #[Reactive]
    public array $pageInfo = [];

    public function render()
    {
        return view('sn-comment::livewire.paginator');
    }
}
