<?php

namespace Wsmallnews\Comment\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $casts = [
        'status' => \Wsmallnews\Comment\Enums\CommentStatus::class,
    ];

    public function commentable()
    {
        return $this->morphTo(__FUNCTION__, 'commentable_type', 'commentable_id');
    }
}
