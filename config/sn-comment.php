<?php

use Wsmallnews\Comment\Models;
use Wsmallnews\Support\Enums\ContentType;

return [
    /**
     * Default scopeable
     */
    'scopeable' => [
        'scope_type' => 'sn-comment',
        'scope_id' => 0,
    ],

    /**
     * Default comment contentType
     */
    'default_content_type' => ContentType::Textarea,

    /**
     * Custom models
     */
    'models' => [
        'comment' => Models\Comment::class,
        'comment_content' => Models\CommentContent::class,
    ],

    /**
     * 文件基础目录，会自动拼接当前年月日 (仅用于 filament 默认上传组件 (Forms\Components\FileUpload))
     */
    'file_directory' => 'sn/comment/',
];
