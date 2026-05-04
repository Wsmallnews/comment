<?php

namespace Wsmallnews\Comment\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Wsmallnews\Comment\Support\Utils;

trait Commenter
{
    public function comments(): MorphMany
    {
        return $this->morphMany(Utils::getCommentModel(), 'commenter');
    }
}
