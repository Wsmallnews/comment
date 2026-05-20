<?php

use Wsmallnews\Comment\Enums\CommentStatus;
use Wsmallnews\Comment\Filament\Pages\Comment\CommentPage;
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
     * Default comment status
     */
    'default_status' => CommentStatus::Normal,

    /**
     * Custom models
     */
    'models' => [
        'comment' => Models\Comment::class,
        'comment_content' => Models\CommentContent::class,
    ],

    /**
     * Panel register
     */
    'panel_register' => [
        'pages' => [
            CommentPage::class,
        ],
    ],

    /**
     * File base directory (only used by filament default upload component (Forms\Components\FileUpload))
     */
    'file_directory' => 'sn/comment/',
];
