<?php

namespace Wsmallnews\Comment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{

    protected $table = 'sn_comments';

    protected $casts = [
        'status' => \Wsmallnews\Comment\Enums\CommentStatus::class
    ];


    protected function childrenNum(): Attribute
    {
        $children = $this->children;
        return Attribute::make(
            get: fn () => $children->count(),
        );
    }


    public function commentable()
    {
        return $this->morphTo(__FUNCTION__, 'commentable_type', 'commentable_id');
    }


    public function user()
    {
        return $this->belongsTo(config('sn-comment.user_model'), 'user_id');
    }


    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
