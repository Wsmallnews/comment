<?php

namespace Wsmallnews\Comment\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Wsmallnews\Comment\Enums\CommentStatus;
use Wsmallnews\Preference\Models\Concerns\Preferenceable;
use Wsmallnews\Preference\Models\Concerns\Preferenceable\Likeable;
use Wsmallnews\Support\Models\SupportModel;

class Comment extends SupportModel
{
    use Likeable;
    use Preferenceable;
    use SoftDeletes;

    protected $table = 'sn_comments';

    protected $casts = [
        'images' => 'array',
        'options' => 'array',
        'status' => CommentStatus::class,
    ];

    public function scopeNormal($query)
    {
        return $query->where('status', CommentStatus::Normal);
    }

    public function scopeUnaudited($query)
    {
        return $query->where('status', CommentStatus::Unaudited);
    }

    public function scopeHidden($query)
    {
        return $query->where('status', CommentStatus::Hidden);
    }

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

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('created_at', 'asc');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
}
