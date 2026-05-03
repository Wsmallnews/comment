<?php

namespace Wsmallnews\Comment\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Wsmallnews\Support\Models\SupportModel;
use Wsmallnews\Support\Support\Utils as SupportUtils;

class CommentContent extends SupportModel
{
    protected $table = 'sn_comment_contents';

    protected $casts = [];

    /**
     * Boot the model and apply default scope attributes.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Auto-fill team_id on creation if tenancy is enabled
        static::creating(function ($model) {
            if (has_tenancy() && ! isset($model->team_id)) {
                $model->team_id = current_tenant()?->id;
            }
        });
    }

    public function contentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(SupportUtils::getTenantModel());
    }
}