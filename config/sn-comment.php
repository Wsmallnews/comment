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
    'like_model' => \Wsmallnews\Comment\Models\Comment::class,

    /*
     * Model name for liker.
     */
    'user_model' => class_exists(\App\Models\User::class) ? \App\Models\User::class : null,
];
