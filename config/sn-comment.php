<?php

return [

    /**
     * comment table name
     */
    'comment_table_name' => 'sn_comments',

    /*
     * User tables foreign key name.
     */
    'user_foreign_key' => 'user_id',

    /*
     * Model name for comment record.
     */
    'comment_model' => \Wsmallnews\Comment\Models\Comment::class,

    /*
     * Model name for user.
     */
    'user_model' => class_exists(\App\Models\User::class) ? \App\Models\User::class : null,

    /**
     * Number of comment per page
     */
    'per_page' => 5,

    /**
     * Edit Paginate URL query string parameters
     */
    'page_name' => 'sn_comment_page',

    /**
     * paginator type
     * scroll:滚动加载更多,paginator:分页器,manual:手动
     */
    'page_type' => 'paginator',
];
