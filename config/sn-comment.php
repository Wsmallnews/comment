<?php

use Wsmallnews\Comment\Enums\CommentStatus;
use Wsmallnews\Comment\Filament\Pages\Comment\CommentPage;
use Wsmallnews\Comment\Filament\Resources\Comments\CommentResource;
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
     *
     * global_default 共享默认（非 FQCN 的 string key）会合并到所有条目：
     *   - navigation_group: 所有页面/资源的默认导航组
     *
     * 条目格式：
     *   - 简单 FQCN：ClassName::class（仅合并共享默认）
     *   - 键值对：ClassName::class => ['key' => 'value']（合并共享默认 + 自定义覆盖）
     *   - 配置项键名使用 snake_case（如 navigation_label、navigation_icon）
     */
    'panel_register' => [
        'global_default' => [
            'navigation_group' => 'sn-comment::comment.global_default.navigation_group',
        ],
        'resources' => [
            CommentResource::class,
        ],
        'pages' => [
            CommentPage::class,
        ],
    ],

    /**
     * Whether to automatically register the scheduled task for auto-auditing comments.
     */
    'schedule_auto_audit' => [
        /**
         * Whether to automatically register the scheduled task for auto-auditing comments.
         */
        'enabled' => false,

        /**
         * Auto-audit comments frequency
         * Support all Laravel schedule frequency methods, like:
         * 'everyMinute', 'everyFiveMinutes', 'everyTenMinutes',
         * 'everyThirtyMinutes', 'hourly', 'daily', 'weekly'
         *
         * Parameter format:
         * dailyAt:13:00 => dailyAt('13:00') | monthlyOn:4,15:00 => monthlyOn(4, '15:00')
         */
        'frequency' => 'everyFiveMinutes',

        /**
         * Enable without overlapping tasks
         */
        'without_overlapping' => true,

        /**
         * Overlapping expire minutes (minutes)
         */
        'overlapping_expire_minutes' => 10,
    ],

    /**
     * File base directory (only used by filament default upload component (Forms\Components\FileUpload))
     */
    'file_directory' => 'sn/comment/',
];
