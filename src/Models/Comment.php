<?php

namespace Wsmallnews\Comment\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Wsmallnews\Comment\Enums\CommentStatus;
use Wsmallnews\Comment\Support\Utils;
use Wsmallnews\Preference\Models\Concerns\Preferenceable;
use Wsmallnews\Preference\Models\Concerns\Preferenceable\Likeable;
use Wsmallnews\Support\Casts\CounterCast;
use Wsmallnews\Support\Contracts\HasSnSubject;
use Wsmallnews\Support\Enums\ContentType;
use Wsmallnews\Support\Models\Concerns\HasActivityLog;
use Wsmallnews\Support\Models\SupportModel;
use Wsmallnews\Support\Support\Utils as SupportUtils;

class Comment extends SupportModel implements HasSnSubject
{
    use HasActivityLog;
    use Likeable;
    use Preferenceable;
    use SoftDeletes;

    protected $table = 'sn_comments';

    protected $casts = [
        'counter' => CounterCast::class,
        'images' => 'array',
        'options' => 'array',
        'status' => CommentStatus::class,
        'content_type' => ContentType::class,
    ];

    protected static array $recordEvents = ['deleted'];

    protected function getActivityTitleAttribute(): string
    {
        return 'id';
    }

    protected function getActivityIgnoreAttributes(): array
    {
        return ['updated_at'];
    }

    public function getSnSubjectId(): int
    {
        return $this->id;
    }

    public function getSnSubjectTitle(): string | HtmlString | null
    {
        return $this->content ? Str::limit($this->content, 50) : $this->content_type->getLabel();
    }

    public function getSnSubjectDescription(): string | HtmlString | null
    {
        return $this->commenter_name;
    }

    public function getSnSubjectCoverUrl(): string | HtmlString | null
    {
        return $this->commenter_avatar_url;
    }

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

    public function commentContent(): MorphOne
    {
        return $this->morphOne(Utils::getCommentContentModel(), 'contentable');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(SupportUtils::getTenantModel());
    }
}
