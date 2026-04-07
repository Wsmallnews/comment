<?php

namespace Wsmallnews\Comment\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Wsmallnews\Comment\Enums\CommentStatus;
use Wsmallnews\Support\Models\SupportModel;

class Comment extends SupportModel
{
    protected $table = 'sn_comments';

    protected $casts = [
        'options' => 'array',
        'status' => CommentStatus::class,
    ];

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function commenter(): MorphTo
    {
        return $this->morphTo();
    }

    public function beReplyer(): MorphTo
    {
        return $this->morphTo();
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('created_at', 'asc');
    }
}
