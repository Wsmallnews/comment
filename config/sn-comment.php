<?php

use Wsmallnews\Comment\Models;

return [
    /**
     * Default scopeable
     */
    'scopeable' => [
        'scope_type' => 'sn-comment',
        'scope_id' => 0,
    ],

    /**
     * Custom models
     */
    'models' => [
        'comment' => Models\Comment::class,
    ],

    /**
     * 文件基础目录，会自动拼接当前年月日 (仅用于 filament 默认上传组件 (Forms\Components\FileUpload))
     */
    'file_directory' => 'sn/comment/',
];
