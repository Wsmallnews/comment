<?php

namespace Wsmallnews\Comment\Livewire\Concerns;

use Wsmallnews\Comment\Enums\CommentStatus;

trait HasCommentStatus
{
    public ?CommentStatus $commentStatus = null;
}
