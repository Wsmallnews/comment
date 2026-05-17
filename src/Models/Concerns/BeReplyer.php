<?php

namespace Wsmallnews\Comment\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Wsmallnews\Comment\Support\Utils;

trait BeReplyer
{
    public function beReplyerComments(): MorphMany
    {
        return $this->morphMany(Utils::getCommentModel(), 'beReplyer');
    }
}
